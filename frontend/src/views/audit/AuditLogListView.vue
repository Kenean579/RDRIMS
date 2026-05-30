<template>
  <div class="flex flex-col gap-6 card">
    <!-- Header -->
    <div class="section-header">
      <div>
        <h1 class="section-title">Audit Logs</h1>
        <p class="section-subtitle">Track system activity, user actions, and data modifications</p>
      </div>
      <button @click="fetchLogs(1)" class="btn btn-secondary">
        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" /></svg>
        Refresh
      </button>
    </div>

    <!-- Filters -->
    <div class="card p-4">
      <div class="flex flex-col sm:flex-row gap-4">
        <select v-model="filterAction" @change="fetchLogs(1)" class="input w-full sm:w-48">
          <option value="">All Actions</option>
          <option value="created">Created</option>
          <option value="updated">Updated</option>
          <option value="deleted">Deleted</option>
          <option value="submitted">Submitted</option>
          <option value="approved">Approved</option>
        </select>
        <input v-model="filterDate" type="date" @change="fetchLogs(1)" class="input w-full sm:w-48" />
      </div>
    </div>

    <!-- Content -->
    <div v-if="loading" class="card p-8"><LoadingSkeleton :rows="5" /></div>
    <div v-else-if="logs.length === 0" class="card">
      <EmptyState icon="📋" title="No logs found" description="System activity logs will appear here once users start interacting with the platform." />
    </div>

    <div v-else class="card overflow-hidden">
      <div class="overflow-x-auto">
        <table class="table-auto">
          <thead>
            <tr>
              <th>User</th>
              <th>Action</th>
              <th>Table</th>
              <th>Record ID</th>
              <th>Timestamp</th>
            </tr>
          </thead>
          <tbody>
            <tr v-for="log in logs" :key="log.id" class="group">
              <td>
                <div class="flex items-center gap-2">
                  <div class="w-6 h-6 rounded-full bg-slate-100 flex items-center justify-center text-[10px] font-bold text-slate-600">
                    {{ (log.user?.name || 'S')[0] }}
                  </div>
                  <span class="text-sm font-medium text-slate-700">{{ log.user?.name || 'System' }}</span>
                </div>
              </td>
              <td>
                <span class="badge" :class="{
                  'badge-green': log.action === 'created' || log.action === 'approved',
                  'badge-blue': log.action === 'updated' || log.action === 'submitted',
                  'badge-red': log.action === 'deleted',
                  'badge-gray': !['created','updated','deleted','submitted','approved'].includes(log.action)
                }">{{ log.action }}</span>
              </td>
              <td class="font-mono text-xs text-slate-500">{{ log.table_name }}</td>
              <td>
                <span class="font-mono bg-slate-50 text-slate-500 px-1.5 py-0.5 rounded text-[10px]">#{{ log.record_id }}</span>
              </td>
              <td class="text-xs text-slate-400 font-medium">{{ formatDateTime(log.created_at) }}</td>
            </tr>
          </tbody>
        </table>
      </div>
      <div class="p-4 border-t border-slate-100">
        <Pagination :current-page="pagination.current_page" :total-pages="pagination.last_page" :total="pagination.total" @page-change="fetchLogs" />
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, reactive, onMounted } from 'vue'
import api from '@/services/api'
import Pagination from '@/components/Pagination.vue'
import LoadingSkeleton from '@/components/LoadingSkeleton.vue'
import EmptyState from '@/components/EmptyState.vue'
import { formatDateTime } from '@/utils/formatters'

const logs = ref([]); const loading = ref(true)
const pagination = reactive({ current_page: 1, last_page: 1, total: 0 })
const filterAction = ref(''); const filterDate = ref('')

async function fetchLogs(page = 1) {
  loading.value = true
  try {
    const params = { page }
    if (filterAction.value) params.action = filterAction.value
    if (filterDate.value) params.from_date = filterDate.value
    const { data } = await api.get('/audit-logs', { params })
    logs.value = data.data
    Object.assign(pagination, { current_page: data.current_page, last_page: data.last_page, total: data.total })
  } catch (e) {} finally { loading.value = false }
}

onMounted(() => fetchLogs())
</script>
