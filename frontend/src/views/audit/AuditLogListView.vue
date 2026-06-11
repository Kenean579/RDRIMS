<template>
  <div class="flex flex-col gap-5 pb-6 animate-fade card">
    <!-- Header -->
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
      <div>
        <h1 class="text-xl font-bold text-slate-900 tracking-tight">Audit Logs</h1>
        <p class="text-slate-500 font-medium mt-1">Track all system actions and modifications.</p>
      </div>
      <div class="flex items-center gap-3">
        <button @click="exportCSV" class="btn btn-secondary h-11 px-5 text-xs font-bold">
          <svg class="w-4 h-4 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3 3m2 8H12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2h12a2 2 0 002-2z" /></svg>
          Export CSV
        </button>
        <button @click="fetchLogs" class="btn btn-primary h-11 px-5 text-xs font-bold">
          <svg class="w-4 h-4 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" /></svg>
          Refresh
        </button>
      </div>
    </div>

    <!-- Filters -->
    <div class="card p-8 bg-slate-50/50">
      <div class="grid grid-cols-1 md:grid-cols-5 gap-4">
        <div>
          <label class="block text-xs text-slate-500 font-medium mb-2 ml-1">User</label>
          <select v-model="filters.user_id" @change="fetchLogs" class="input font-bold">
            <option value="">All Users</option>
            <option v-for="u in users" :key="u.id" :value="u.id">{{ u.name }}</option>
          </select>
        </div>
        <div>
          <label class="block text-xs text-slate-500 font-medium mb-2 ml-1">Action</label>
          <select v-model="filters.action" @change="fetchLogs" class="input font-bold">
            <option value="">All Actions</option>
            <option value="created">Created</option>
            <option value="updated">Updated</option>
            <option value="deleted">Deleted</option>
            <option value="restored">Restored</option>
          </select>
        </div>
        <div>
          <label class="block text-xs text-slate-500 font-medium mb-2 ml-1">Table</label>
          <select v-model="filters.table_name" @change="fetchLogs" class="input font-bold">
            <option value="">All Tables</option>
            <option v-for="t in tables" :key="t" :value="t">{{ t }}</option>
          </select>
        </div>
        <div>
          <label class="block text-xs text-slate-500 font-medium mb-2 ml-1">From Date</label>
          <input v-model="filters.from_date" type="date" @change="fetchLogs" class="input font-bold" />
        </div>
        <div>
          <label class="block text-xs text-slate-500 font-medium mb-2 ml-1">To Date</label>
          <input v-model="filters.to_date" type="date" @change="fetchLogs" class="input font-bold" />
        </div>
      </div>
    </div>

    <!-- Content -->
    <div v-if="loading" class="card p-8 flex flex-col justify-center items-center gap-4">
      <div class="animate-spin rounded-full h-10 w-10 border-b-2 border-brand"></div>
      <p class="text-xs font-medium text-slate-400">Loading audit logs...</p>
    </div>

    <div v-else-if="logs.length === 0" class="card">
      <EmptyState icon="📋" title="No audit logs found" description="No matching audit records found with the current filters." />
    </div>

    <div v-else class="card overflow-hidden">
      <div class="overflow-x-auto">
        <table class="table-auto">
          <thead>
            <tr>
              <th class="pl-8 py-4 text-xs font-medium text-slate-400">Timestamp</th>
              <th class="py-4 text-xs font-medium text-slate-400">User</th>
              <th class="py-4 text-xs font-medium text-slate-400">Action</th>
              <th class="py-4 text-xs font-medium text-slate-400">Table</th>
              <th class="py-4 text-xs font-medium text-slate-400">Record ID</th>
              <th class="py-4 text-xs font-medium text-slate-400">IP Address</th>
            </tr>
          </thead>
          <tbody class="divide-y divide-slate-50">
            <tr v-for="log in logs" :key="log.id" class="group hover:bg-slate-50/50 transition-colors">
              <td class="pl-8 py-4 text-xs font-medium text-slate-600">{{ formatDate(log.created_at) }}</td>
              <td class="py-4">
                <div class="flex items-center gap-2">
                  <div class="w-7 h-7 rounded-full bg-slate-100 flex items-center justify-center text-xs font-bold text-slate-500 border border-slate-100">
                    {{ log.user?.name?.substring(0,1) || '?' }}
                  </div>
                  <span class="text-xs font-bold text-slate-700">{{ log.user?.name || 'System' }}</span>
                </div>
              </td>
              <td class="py-4">
                <span class="px-3 py-1 text-xs font-bold rounded-lg" :class="getActionClass(log.action)">
                  {{ log.action }}
                </span>
              </td>
              <td class="py-4 text-xs font-mono text-slate-500">{{ log.table_name }}</td>
              <td class="py-4 text-xs font-mono text-slate-500">#{{ log.record_id }}</td>
              <td class="py-4 text-xs font-mono text-slate-400">{{ log.ip_address }}</td>
            </tr>
          </tbody>
        </table>
      </div>
      <div class="p-4 bg-slate-50/50">
        <Pagination :current-page="pagination.current_page" :total-pages="pagination.last_page" :total="pagination.total" @page-change="fetchLogs" />
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, reactive, onMounted } from 'vue'
import { useAuthStore } from '@/stores/auth'
import api from '@/services/api'
import Pagination from '@/components/Pagination.vue'
import EmptyState from '@/components/EmptyState.vue'
import { formatDate } from '@/utils/formatters'

const auth = useAuthStore()
const loading = ref(true)
const logs = ref([])
const users = ref([])
const tables = ref(['proposals', 'projects', 'outputs', 'publications', 'users', 'files', 'calls', 'events', 'milestones', 'tasks', 'finance_checks', 'ethics_requests'])
const filters = reactive({ user_id: '', action: '', table_name: '', from_date: '', to_date: '' })
const pagination = reactive({ current_page: 1, last_page: 1, total: 0 })

async function fetchLogs(page = 1) {
  loading.value = true
  try {
    const params = { page }
    if (filters.user_id) params.user_id = filters.user_id
    if (filters.action) params.action = filters.action
    if (filters.table_name) params.table_name = filters.table_name
    if (filters.from_date) params.from_date = filters.from_date
    if (filters.to_date) params.to_date = filters.to_date
    
    const { data } = await api.get('/audit-logs', { params })
    logs.value = data.data
    Object.assign(pagination, { current_page: data.current_page, last_page: data.last_page, total: data.total })
  } catch (err) {
    console.error('Failed to load audit logs:', err)
  } finally {
    loading.value = false
  }
}

async function fetchUsers() {
  try {
    const { data } = await api.get('/users')
    users.value = data.data
  } catch (err) {
    console.error('Failed to load users:', err)
  }
}

async function exportCSV() {
  if (logs.value.length === 0) {
    alert('No data to export')
    return
  }
  
  const headers = ['Timestamp', 'User', 'Email', 'Action', 'Table', 'Record ID', 'IP Address']
  const rows = logs.value.map(log => [
    formatDate(log.created_at),
    log.user?.name || 'System',
    log.user?.email || '',
    log.action,
    log.table_name,
    log.record_id,
    log.ip_address
  ])
  
  const csvContent = [headers, ...rows].map(row => row.join(',')).join('\n')
  const blob = new Blob([csvContent], { type: 'text/csv' })
  const url = window.URL.createObjectURL(blob)
  
  const a = document.createElement('a')
  a.href = url
  a.download = `audit-logs-${new Date().toISOString().split('T')[0]}.csv`
  document.body.appendChild(a)
  a.click()
  document.body.removeChild(a)
  window.URL.revokeObjectURL(url)
}

function getActionClass(action) {
  const classes = {
    'created': 'bg-emerald-100 text-emerald-700',
    'updated': 'bg-blue-100 text-blue-700',
    'deleted': 'bg-rose-100 text-rose-700',
    'restored': 'bg-amber-100 text-amber-700',
    'default': 'bg-slate-100 text-slate-700'
  }
  return classes[action] || classes['default']
}

onMounted(async () => {
  if (!auth.hasRole('super_admin', 'research_admin', 'campus_admin', 'faculty_admin', 'department_head', 'director')) {
    // User shouldn't be here, redirect them
    window.location.href = '/app/dashboard'
    return
  }
  
  await fetchUsers()
  fetchLogs()
})
</script>
