<template>
  <div card>
    <div class="mb-6">
      <router-link to="/app/reviewer/proposals" class="text-sm text-blue-600 hover:underline mb-2 inline-block">← Back to Reviews</router-link>
      <h1 class="text-xl font-bold text-gray-800">Review Proposal</h1>
      <p class="text-sm text-gray-500 mt-1">This is a blind review – author identity is hidden</p>
    </div>

    <div v-if="loading" class="bg-white rounded-lg shadow-sm p-6">
      <div class="space-y-4 animate-pulse"><div v-for="i in 6" :key="i" class="h-5 bg-gray-200 rounded"></div></div>
    </div>

    <div v-else-if="error" class="bg-red-50 border border-red-200 rounded-lg p-6 text-center">
      <p class="text-red-700 text-sm">{{ error }}</p>
      <button @click="fetchProposal" class="mt-2 text-sm text-red-600 hover:underline">Retry</button>
    </div>

    <template v-else>
      <!-- Proposal Content (anonymized) -->
      <div class="bg-white rounded-lg shadow-sm border border-gray-100 p-6 mb-6">
        <h2 class="text-base font-semibold text-gray-800 mb-4">Proposal Details</h2>
        <div class="space-y-4 text-sm">
          <div><p class="text-gray-500 mb-1">Title</p><p class="font-medium text-gray-800">{{ proposal.title }}</p></div>
          <div><p class="text-gray-500 mb-1">Type</p><p class="text-gray-800">{{ proposal.type?.name?.toUpperCase() || 'N/A' }}</p></div>
          <div><p class="text-gray-500 mb-1">Keywords</p><p class="text-gray-800">{{ proposal.keywords }}</p></div>
          <div><p class="text-gray-500 mb-1">Abstract</p><p class="text-gray-800 whitespace-pre-line">{{ proposal.abstract }}</p></div>
          <div><p class="text-gray-500 mb-1">Objectives</p><p class="text-gray-800 whitespace-pre-line">{{ proposal.objectives }}</p></div>
          <div><p class="text-gray-500 mb-1">Methodology</p><p class="text-gray-800 whitespace-pre-line">{{ proposal.methodology }}</p></div>
          <div><p class="text-gray-500 mb-1">Budget</p><p class="font-medium text-gray-800">{{ formatCurrency(proposal.budget) }}</p></div>
        </div>
      </div>

      <!-- Review Form -->
      <div class="bg-white rounded-lg shadow-sm border border-gray-100 p-6">
        <h2 class="text-base font-semibold text-gray-800 mb-4">Your Review</h2>

        <div v-if="alreadyReviewed" class="bg-blue-50 border border-blue-200 rounded-lg p-4 mb-4 text-sm text-blue-700">
          You have already submitted your review. Score: {{ existingScore }}
        </div>

        <form v-else @submit.prevent="submitReview" class="space-y-4">
          <div v-for="criterion in reviewCriteria" :key="criterion.id" class="p-4 border border-gray-200 rounded-lg">
            <div class="flex items-center justify-between mb-2">
              <label class="text-sm font-medium text-gray-800">{{ criterion.name }}</label>
              <span class="text-xs text-gray-500">Max: {{ criterion.max_score }}</span>
            </div>
            <p class="text-xs text-gray-500 mb-2">{{ criterion.description }}</p>
            <div class="flex items-center gap-3">
              <input v-model.number="scores[criterion.id]" type="number" :max="criterion.max_score" min="0"
                class="w-20 border border-gray-300 rounded-lg px-3 py-1.5 text-sm focus:ring-2 focus:ring-blue-500 outline-none"
                placeholder="0" required />
              <input v-model="comments[criterion.id]" type="text"
                class="flex-1 border border-gray-300 rounded-lg px-3 py-1.5 text-sm focus:ring-2 focus:ring-blue-500 outline-none"
                placeholder="Comments (optional)" />
            </div>
          </div>

          <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-1">Overall Score</label>
              <input v-model.number="overallScore" type="number" step="0.01" required
                class="w-full border border-gray-300 rounded-lg px-4 py-2.5 text-sm focus:ring-2 focus:ring-blue-500 outline-none" />
            </div>
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-1">Decision *</label>
              <select v-model="decisionId" required
                class="w-full border border-gray-300 rounded-lg px-4 py-2.5 text-sm focus:ring-2 focus:ring-blue-500 outline-none bg-white">
                <option value="">Select decision</option>
                <option v-for="d in reviewDecisions" :key="d.id" :value="d.id">{{ formatStatusName(d.name) }}</option>
              </select>
            </div>
          </div>

          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Overall Comments</label>
            <textarea v-model="overallComments" rows="3"
              class="w-full border border-gray-300 rounded-lg px-4 py-2.5 text-sm focus:ring-2 focus:ring-blue-500 outline-none resize-none"
              placeholder="Your overall assessment..."></textarea>
          </div>

          <div class="flex justify-end">
            <button type="submit" :disabled="submitting"
              class="px-6 py-2.5 bg-blue-600 text-white text-sm font-medium rounded-lg hover:bg-blue-700 transition disabled:opacity-60">
              {{ submitting ? 'Submitting...' : 'Submit Final Review' }}
            </button>
          </div>
        </form>
      </div>
    </template>
  </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue'
import { useRoute } from 'vue-router'
import { useNotificationStore } from '@/stores/notification'
import api from '@/services/api'
import { formatCurrency } from '@/utils/formatters'
import { formatStatusName } from '@/utils/colors'

const route = useRoute()
const notif = useNotificationStore()

const proposal = ref({})
const loading = ref(true)
const error = ref(null)
const reviewCriteria = ref([])
const reviewDecisions = ref([])
const scores = ref({})
const comments = ref({})
const overallScore = ref(null)
const overallComments = ref('')
const decisionId = ref('')
const submitting = ref(false)

const alreadyReviewed = computed(() => proposal.value.reviewPivot?.overall_score !== null)
const existingScore = computed(() => proposal.value.reviewPivot?.overall_score)

async function fetchProposal() {
  loading.value = true; error.value = null
  try {
    const { data } = await api.get(`/reviewer/proposals/${route.params.id}`)
    proposal.value = data
  } catch (err) {
    error.value = err.response?.data?.message || 'Failed to load proposal'
  } finally { loading.value = false }
}

async function submitReview() {
  submitting.value = true
  try {
    const payload = {
      scores: reviewCriteria.value.map(c => ({
        criterion_id: c.id,
        score: scores.value[c.id] || 0,
        comments: comments.value[c.id] || null
      })),
      overall_score: overallScore.value,
      overall_comments: overallComments.value,
      decision_id: decisionId.value
    }
    await api.post(`/reviewer/proposals/${route.params.id}/review`, payload)
    notif.success('Review submitted successfully!')
    await fetchProposal()
  } catch (err) {
    notif.error(err.response?.data?.message || 'Failed to submit review')
  } finally { submitting.value = false }
}

onMounted(async () => {
  await fetchProposal()
  try {
    const [criteriaRes, decisionsRes] = await Promise.all([
      api.get('/review-criteria'),
      api.get('/lookups/review_decisions')
    ])
    reviewCriteria.value = criteriaRes.data.filter(c => c.is_active)
    reviewDecisions.value = decisionsRes.data
  } catch (e) { /* ignore */ }
})
</script>
