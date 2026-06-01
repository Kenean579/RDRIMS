<template>
  <div card>
    <div class="mb-6">
      <h1 class="text-xl font-bold text-gray-800">My Reviews</h1>
      <p class="text-sm text-gray-500 mt-1">Proposals assigned to you for review</p>
    </div>

    <div v-if="loading" class="bg-white rounded-lg shadow-sm border border-gray-100 p-6">
      <div class="space-y-4 animate-pulse"><div v-for="i in 4" :key="i" class="h-6 bg-gray-200 rounded"></div></div>
    </div>

    <div v-else-if="error" class="bg-red-50 border border-red-200 rounded-lg p-6 text-center">
      <p class="text-red-700 text-sm">{{ error }}</p>
      <button @click="fetchProposals" class="mt-2 text-sm text-red-600 hover:underline">Retry</button>
    </div>

    <div v-else-if="proposals.length === 0" class="bg-white rounded-lg shadow-sm border border-gray-100 p-12 text-center">
      <p class="text-4xl mb-3">✅</p>
      <h3 class="text-base font-medium text-gray-800 mb-1">No reviews pending</h3>
      <p class="text-sm text-gray-500">You have no proposals assigned for review.</p>
    </div>

    <div v-else class="bg-white rounded-lg shadow-sm border border-gray-100 overflow-hidden">
      <div class="overflow-x-auto">
        <table class="min-w-full">
          <thead class="bg-gray-50 border-b border-gray-200">
            <tr>
              <th class="text-left px-5 py-3 text-xs font-medium text-gray-500 uppercase">Title</th>
              <th class="text-left px-5 py-3 text-xs font-medium text-gray-500 uppercase">Status</th>
              <th class="text-left px-5 py-3 text-xs font-medium text-gray-500 uppercase">Your Score</th>
              <th class="text-left px-5 py-3 text-xs font-medium text-gray-500 uppercase">Assigned</th>
            </tr>
          </thead>
          <tbody class="divide-y divide-gray-100">
            <tr v-for="p in proposals" :key="p.id" @click="$router.push(`/app/reviewer/proposals/${p.id}`)"
              class="hover:bg-blue-50/50 cursor-pointer transition">
              <td class="px-5 py-3">
                <p class="text-sm font-medium text-gray-800 truncate max-w-xs">{{ p.title }}</p>
              </td>
              <td class="px-5 py-3"><StatusBadge :status="p.status?.name || 'draft'" /></td>
              <td class="px-5 py-3 text-sm text-gray-700">{{ p.reviewPivot?.overall_score || 'Not scored' }}</td>
              <td class="px-5 py-3 text-sm text-gray-500">{{ formatDate(p.reviewPivot?.assigned_at) }}</td>
            </tr>
          </tbody>
        </table>
      </div>
      <div class="px-5 py-3 border-t border-gray-100">
        <Pagination :current-page="pagination.current_page" :total-pages="pagination.last_page"
          :total="pagination.total" @page-change="fetchProposals" />
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, reactive, onMounted } from 'vue'
import api from '@/services/api'
import StatusBadge from '@/components/StatusBadge.vue'
import Pagination from '@/components/Pagination.vue'
import { formatDate } from '@/utils/formatters'

const loading = ref(true)
const error = ref(null)
const proposals = ref([])
const pagination = reactive({ current_page: 1, last_page: 1, total: 0 })

async function fetchProposals(page = 1) {
  loading.value = true; error.value = null
  try {
    const { data } = await api.get('/reviewer/proposals', { params: { page } })
    proposals.value = data.data
    Object.assign(pagination, { current_page: data.current_page, last_page: data.last_page, total: data.total })
  } catch (err) {
    error.value = err.response?.data?.message || 'Failed to load reviews'
  } finally { loading.value = false }
}

onMounted(() => fetchProposals())
</script>
