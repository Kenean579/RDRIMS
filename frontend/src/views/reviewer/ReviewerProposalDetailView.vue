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

        <!-- Excel Export/Import Options -->
        <div class="bg-slate-50 border border-slate-200 rounded-lg p-4 mb-4 flex items-center justify-between gap-4">
          <div class="flex items-center gap-2">
            <svg class="w-5 h-5 text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" /></svg>
            <span class="text-sm text-slate-600 font-medium">Excel Options</span>
          </div>
          <div class="flex items-center gap-2">
            <button @click="exportReviewTemplate" :disabled="downloading" class="inline-flex items-center gap-2 px-4 py-2 text-sm font-medium text-white bg-emerald-600 rounded-lg hover:bg-emerald-700 transition disabled:opacity-60">
              <svg v-if="downloading" class="w-4 h-4 animate-spin" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"/></svg>
              <svg v-else class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4" /></svg>
              {{ downloading ? 'Downloading...' : 'Export Template' }}
            </button>
            <input type="file" ref="fileInput" @change="handleFileUpload" accept=".xlsx,.xls" class="hidden" />
            <button @click="$refs.fileInput.click()" :disabled="uploading" class="inline-flex items-center gap-2 px-4 py-2 text-sm font-medium text-white bg-blue-600 rounded-lg hover:bg-blue-700 transition disabled:opacity-60">
              <svg v-if="uploading" class="w-4 h-4 animate-spin" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"/></svg>
              <svg v-else class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12" /></svg>
              {{ uploading ? 'Importing...' : 'Import Review' }}
            </button>
          </div>
        </div>

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
const downloading = ref(false)
const uploading = ref(false)

async function exportReviewTemplate() {
  downloading.value = true
  try {
    const response = await api.get(`/proposals/${route.params.id}/review-template`, {
      responseType: 'blob'
    })
    const url = window.URL.createObjectURL(new Blob([response.data]))
    const link = document.createElement('a')
    link.href = url
    link.setAttribute('download', `review-template-${proposal.value.title}.xlsx`)
    document.body.appendChild(link)
    link.click()
    link.remove()
    notif.success('Review template downloaded successfully!')
  } catch (err) {
    notif.error(err.response?.data?.message || 'Failed to download template')
  } finally {
    downloading.value = false
  }
}

async function handleFileUpload(event) {
  const file = event.target.files[0]
  if (!file) return
  
  uploading.value = true
  try {
    const formData = new FormData()
    formData.append('file', file)
    
    const { data } = await api.post(`/reviewer/proposals/${route.params.id}/import-review`, formData, {
      headers: { 'Content-Type': 'multipart/form-data' }
    })
    
    // Preview the imported data
    if (confirm(`Imported Review:\n\nOverall Score: ${data.overall_score}\nDecision: ${data.decision_name}\n\nConfirm and submit?`)) {
      await api.post(`/reviewer/proposals/${route.params.id}/review`, data)
      notif.success('Review imported and submitted successfully!')
      await fetchProposal()
    }
  } catch (err) {
    notif.error(err.response?.data?.message || 'Failed to import review')
  } finally {
    uploading.value = false
    event.target.value = ''
  }
}

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
