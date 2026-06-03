<template>
  <div card>
    <div class="mb-6">
      <router-link to="/app/calls" class="text-sm text-blue-600 hover:underline mb-2 inline-block">← Back to Calls</router-link>
      <div class="flex items-center gap-3">
        <h1 class="text-xl font-bold text-gray-800">{{ call.title || 'Call Detail' }}</h1>
        <StatusBadge :status="call.status?.name || 'draft'" />
      </div>
    </div>

    <div v-if="loading" class="bg-white rounded-lg shadow-sm p-6">
      <LoadingSkeleton :rows="6" />
    </div>

    <template v-else>
      <!-- Apply Button -->
      <div v-if="call.status?.name === 'open' || !call.status" class="mb-6">
        <router-link :to="`/app/proposals/create?call_id=${call.id}`" class="inline-flex items-center gap-2 bg-blue-600 text-white px-6 py-3 rounded-lg hover:bg-blue-700 transition-colors">
          <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/></svg>
          Apply for this Call
        </router-link>
      </div>

      <!-- Call Info -->
      <div class="bg-white rounded-lg shadow-sm border border-gray-100 p-6 mb-6">
        <h2 class="text-base font-semibold text-gray-800 mb-4">Call Details</h2>
        <dl class="grid grid-cols-1 sm:grid-cols-2 gap-4 text-sm">
          <div class="sm:col-span-2">
            <dt class="text-gray-500">Title</dt>
            <dd class="font-medium text-gray-800 mt-0.5">{{ call.title }}</dd>
          </div>
          <div>
            <dt class="text-gray-500">Deadline</dt>
            <dd class="text-gray-800 mt-0.5">{{ formatDate(call.deadline) }}</dd>
          </div>
          <div>
            <dt class="text-gray-500">Academic Year</dt>
            <dd class="text-gray-800 mt-0.5">{{ call.academic_year?.name || 'N/A' }}</dd>
          </div>
          <div>
            <dt class="text-gray-500">Thematic Areas</dt>
            <dd class="text-gray-800 mt-0.5">{{ call.thematic_areas || 'Not specified' }}</dd>
          </div>
          <div>
            <dt class="text-gray-500">Created By</dt>
            <dd class="text-gray-800 mt-0.5">{{ call.created_by?.name || 'N/A' }}</dd>
          </div>
        </dl>
        <div class="mt-4">
          <p class="text-gray-500 text-sm mb-1">Description</p>
          <p class="text-sm text-gray-800 whitespace-pre-line">{{ call.description }}</p>
        </div>
      </div>

      <!-- Proposals for this Call -->
      <div class="bg-white rounded-lg shadow-sm border border-gray-100 p-6">
        <h2 class="text-base font-semibold text-gray-800 mb-4">
          Proposals ({{ proposals.length }})
        </h2>
        <div v-if="proposals.length === 0" class="text-center py-4 text-gray-400 text-sm">
          No proposals submitted for this call yet.
        </div>
        <div v-else class="space-y-2">
          <div v-for="proposal in proposals" :key="proposal.id"
            @click="$router.push(`/app/proposals/${proposal.id}`)"
            class="p-4 border border-gray-200 rounded-lg hover:bg-blue-50/50 cursor-pointer transition">
            <div class="flex items-start justify-between">
              <div class="flex-1">
                <p class="text-sm font-medium text-gray-800">{{ proposal.title }}</p>
                <div class="flex items-center gap-2 mt-1">
                  <StatusBadge :status="proposal.status?.name || 'draft'" />
                  <span class="text-xs text-gray-500">{{ formatCurrency(proposal.budget) }}</span>
                  <span class="text-xs text-gray-500">by {{ proposal.submitted_by?.name }}</span>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </template>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import { useRoute } from 'vue-router'
import api from '@/services/api'
import StatusBadge from '@/components/StatusBadge.vue'
import LoadingSkeleton from '@/components/LoadingSkeleton.vue'
import { formatDate, formatCurrency } from '@/utils/formatters'

const route = useRoute()
const call = ref({})
const proposals = ref([])
const loading = ref(true)

async function fetchCall() {
  loading.value = true
  try {
    const { data } = await api.get(`/calls/${route.params.id}`)
    call.value = data
    proposals.value = data.proposals || []
  } catch (e) {} finally {
    loading.value = false
  }
}

onMounted(fetchCall)
</script>
