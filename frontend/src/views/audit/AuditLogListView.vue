<template>
  <div class="flex flex-col gap-6 pb-6 animate-fade">
    <!-- Header -->
    <div class="section-header">
      <div>
        <h1 class="section-title">Audit Logs</h1>
        <p class="section-subtitle">Track system activity, user actions, and data modifications across the platform</p>
      </div>
      <div class="flex items-center gap-3">
        <button @click="exportLogs" class="btn btn-secondary" :disabled="logs.length === 0">
          <svg class="w-4 h-4 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" /></svg>
          Export CSV
        </button>
        <button @click="fetchLogs(1)" class="btn btn-primary">
          <svg class="w-4 h-4 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" /></svg>
          Refresh
        </button>
      </div>
    </div>

    <!-- Stats Row -->
    <div class="audit-stats-row">
      <div class="audit-stat-card">
        <div class="audit-stat-icon" style="background: linear-gradient(135deg, #3b82f6, #2563eb);">
          <svg width="18" height="18" fill="none" stroke="white" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" /></svg>
        </div>
        <div>
          <span class="audit-stat-value">{{ pagination.total || 0 }}</span>
          <span class="audit-stat-label">Total Entries</span>
        </div>
      </div>
      <div class="audit-stat-card">
        <div class="audit-stat-icon" style="background: linear-gradient(135deg, #10b981, #059669);">
          <svg width="18" height="18" fill="none" stroke="white" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" /></svg>
        </div>
        <div>
          <span class="audit-stat-value">{{ actionCounts.created }}</span>
          <span class="audit-stat-label">Created</span>
        </div>
      </div>
      <div class="audit-stat-card">
        <div class="audit-stat-icon" style="background: linear-gradient(135deg, #f59e0b, #d97706);">
          <svg width="18" height="18" fill="none" stroke="white" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" /></svg>
        </div>
        <div>
          <span class="audit-stat-value">{{ actionCounts.updated }}</span>
          <span class="audit-stat-label">Updated</span>
        </div>
      </div>
      <div class="audit-stat-card">
        <div class="audit-stat-icon" style="background: linear-gradient(135deg, #ef4444, #dc2626);">
          <svg width="18" height="18" fill="none" stroke="white" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" /></svg>
        </div>
        <div>
          <span class="audit-stat-value">{{ actionCounts.deleted }}</span>
          <span class="audit-stat-label">Deleted</span>
        </div>
      </div>
    </div>

    <!-- Filters -->
    <div class="card p-5 bg-slate-50/50">
      <div class="flex flex-wrap gap-4 items-end">
        <div class="flex-1 min-w-[200px]">
          <label class="block text-[11px] text-slate-500 font-bold capitalize tracking-widest mb-2 ml-1">Search User</label>
          <div class="relative group">
            <span class="absolute left-3.5 top-1/2 -translate-y-1/2 text-slate-400 group-focus-within:text-brand transition-colors">
              <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><circle cx="11" cy="11" r="8"/><path d="m21 21-4.35-4.35"/></svg>
            </span>
            <input v-model="searchUser" type="text" placeholder="Filter by user name..." class="input pl-10" @input="debounceSearch" />
          </div>
        </div>
        <div class="w-44">
          <label class="block text-[11px] text-slate-500 font-bold capitalize tracking-widest mb-2 ml-1">Action</label>
          <select v-model="filterAction" @change="fetchLogs(1)" class="input font-bold">
            <option value="">All Actions</option>
            <option value="created">Created</option>
            <option value="updated">Updated</option>
            <option value="deleted">Deleted</option>
            <option value="submitted">Submitted</option>
            <option value="approved">Approved</option>
            <option value="rejected">Rejected</option>
            <option value="login">Login</option>
            <option value="logout">Logout</option>
          </select>
        </div>
        <div class="w-44">
          <label class="block text-[11px] text-slate-500 font-bold capitalize tracking-widest mb-2 ml-1">Table</label>
          <select v-model="filterTable" @change="fetchLogs(1)" class="input font-bold">
            <option value="">All Tables</option>
            <option value="proposals">Proposals</option>
            <option value="projects">Projects</option>
            <option value="publications">Publications</option>
            <option value="users">Users</option>
            <option value="expenses">Expenses</option>
            <option value="ethics_requests">Ethics</option>
            <option value="finance_checks">Finance</option>
          </select>
        </div>
        <div class="w-44">
          <label class="block text-[11px] text-slate-500 font-bold capitalize tracking-widest mb-2 ml-1">From</label>
          <input v-model="filterFrom" type="date" @change="fetchLogs(1)" class="input" />
        </div>
        <div class="w-44">
          <label class="block text-[11px] text-slate-500 font-bold capitalize tracking-widest mb-2 ml-1">To</label>
          <input v-model="filterTo" type="date" @change="fetchLogs(1)" class="input" />
        </div>
        <button v-if="hasActiveFilters" @click="clearFilters" class="btn btn-secondary h-11 px-6 font-bold capitalize tracking-widest text-[11px]">
          Reset
        </button>
      </div>
    </div>

    <!-- Content -->
    <div v-if="loading" class="card p-5 flex flex-col justify-center items-center gap-4">
      <div class="animate-spin rounded-full h-10 w-10 border-b-2 border-brand"></div>
      <p class="text-[10px] font-bold text-slate-400 capitalize tracking-widest">Loading audit trail...</p>
    </div>

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
              <th>Resource</th>
              <th>Record</th>
              <th>Changes</th>
              <th>IP Address</th>
              <th>Timestamp</th>
            </tr>
          </thead>
          <tbody>
            <tr v-for="log in logs" :key="log.id" class="group cursor-pointer" @click="toggleDetail(log.id)">
              <td>
                <div class="flex items-center gap-2.5">
                  <div class="w-7 h-7 rounded-full flex items-center justify-center text-[10px] font-extrabold text-white" 
                       :style="{ background: getUserColor(log.user?.name) }">
                    {{ (log.user?.name || 'S')[0] }}
                  </div>
                  <div class="flex flex-col">
                    <span class="text-sm font-bold text-slate-700">{{ log.user?.name || 'System' }}</span>
                    <span class="text-[10px] text-slate-400 font-medium">{{ log.user?.email || '' }}</span>
                  </div>
                </div>
              </td>
              <td>
                <span class="audit-action-badge" :class="actionClass(log.action)">
                  <span class="audit-action-dot" :class="actionDotClass(log.action)"></span>
                  {{ log.action }}
                </span>
              </td>
              <td>
                <span class="font-mono text-xs font-bold text-slate-600 bg-slate-100 px-2 py-1 rounded-md">{{ formatTableName(log.table_name) }}</span>
              </td>
              <td>
                <span class="font-mono bg-slate-50 text-slate-500 px-1.5 py-0.5 rounded text-[10px] font-bold">#{{ log.record_id }}</span>
              </td>
              <td>
                <span v-if="log.old_values || log.new_values" class="text-brand text-xs font-bold cursor-pointer hover:underline">
                  {{ expandedId === log.id ? 'Hide' : 'View' }} diff
                </span>
                <span v-else class="text-[10px] text-slate-300 font-bold">—</span>
              </td>
              <td class="text-[10px] text-slate-400 font-mono">{{ log.ip_address || '—' }}</td>
              <td>
                <div class="flex flex-col">
                  <span class="text-xs text-slate-600 font-bold">{{ formatDate(log.created_at) }}</span>
                  <span class="text-[10px] text-slate-400">{{ formatTime(log.created_at) }}</span>
                </div>
              </td>
            </tr>
            <!-- Expanded Diff Row -->
            <tr v-for="log in logs" :key="'diff-'+log.id" v-show="expandedId === log.id && (log.old_values || log.new_values)">
              <td colspan="7" class="p-0!">
                <div class="diff-panel">
                  <div class="diff-grid">
                    <div v-if="log.old_values" class="diff-col diff-old">
                      <div class="diff-col-header">
                        <span class="diff-header-badge diff-header-old">Before</span>
                      </div>
                      <pre class="diff-content">{{ formatJson(log.old_values) }}</pre>
                    </div>
                    <div v-if="log.new_values" class="diff-col diff-new">
                      <div class="diff-col-header">
                        <span class="diff-header-badge diff-header-new">After</span>
                      </div>
                      <pre class="diff-content">{{ formatJson(log.new_values) }}</pre>
                    </div>
                  </div>
                </div>
              </td>
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
import { ref, reactive, computed, onMounted } from 'vue'
import api from '@/services/api'
import Pagination from '@/components/Pagination.vue'
import EmptyState from '@/components/EmptyState.vue'
import { formatDateTime } from '@/utils/formatters'

const logs = ref([]); const loading = ref(true)
const pagination = reactive({ current_page: 1, last_page: 1, total: 0 })
const filterAction = ref('')
const filterTable = ref('')
const filterFrom = ref('')
const filterTo = ref('')
const searchUser = ref('')
const expandedId = ref(null)

const actionCounts = reactive({ created: 0, updated: 0, deleted: 0 })

const hasActiveFilters = computed(() =>
  filterAction.value || filterTable.value || filterFrom.value || filterTo.value || searchUser.value
)

let debounceTimer
function debounceSearch() {
  clearTimeout(debounceTimer)
  debounceTimer = setTimeout(() => fetchLogs(1), 400)
}

function clearFilters() {
  filterAction.value = ''
  filterTable.value = ''
  filterFrom.value = ''
  filterTo.value = ''
  searchUser.value = ''
  fetchLogs(1)
}

function toggleDetail(id) {
  expandedId.value = expandedId.value === id ? null : id
}

async function fetchLogs(page = 1) {
  loading.value = true
  try {
    const params = { page, per_page: 50 }
    if (filterAction.value) params.action = filterAction.value
    if (filterTable.value) params.table_name = filterTable.value
    if (filterFrom.value) params.from_date = filterFrom.value
    if (filterTo.value) params.to_date = filterTo.value
    if (searchUser.value) params.search_user = searchUser.value

    const { data } = await api.get('/audit-logs', { params })
    logs.value = data.data
    Object.assign(pagination, { current_page: data.current_page, last_page: data.last_page, total: data.total })

    // Count actions from current page
    actionCounts.created = logs.value.filter(l => l.action === 'created').length
    actionCounts.updated = logs.value.filter(l => l.action === 'updated').length
    actionCounts.deleted = logs.value.filter(l => l.action === 'deleted').length
  } catch (e) {} finally { loading.value = false }
}

function formatTableName(name) {
  if (!name) return '—'
  return name.replace(/_/g, ' ').replace(/\b\w/g, l => l.toUpperCase())
}

function formatDate(d) {
  if (!d) return '—'
  return new Date(d).toLocaleDateString('en-US', { month: 'short', day: 'numeric', year: 'numeric' })
}

function formatTime(d) {
  if (!d) return ''
  return new Date(d).toLocaleTimeString('en-US', { hour: '2-digit', minute: '2-digit', second: '2-digit' })
}

function formatJson(obj) {
  if (typeof obj === 'string') {
    try { obj = JSON.parse(obj) } catch { return obj }
  }
  return JSON.stringify(obj, null, 2)
}

function getUserColor(name) {
  const colors = ['#3b82f6', '#10b981', '#f59e0b', '#8b5cf6', '#ef4444', '#ec4899', '#14b8a6', '#f97316']
  const idx = (name || '').charCodeAt(0) % colors.length
  return colors[idx]
}

function actionClass(action) {
  const map = {
    created: 'action-created', approved: 'action-approved',
    updated: 'action-updated', submitted: 'action-submitted',
    deleted: 'action-deleted', rejected: 'action-rejected',
    login: 'action-login', logout: 'action-logout'
  }
  return map[action] || 'action-default'
}

function actionDotClass(action) {
  const map = {
    created: 'dot-green', approved: 'dot-green',
    updated: 'dot-blue', submitted: 'dot-blue',
    deleted: 'dot-red', rejected: 'dot-red',
    login: 'dot-purple', logout: 'dot-gray'
  }
  return map[action] || 'dot-gray'
}

function exportLogs() {
  const headers = ['User', 'Action', 'Table', 'Record ID', 'IP Address', 'Timestamp']
  const rows = logs.value.map(l => [
    l.user?.name || 'System', l.action, l.table_name, l.record_id, l.ip_address || '', l.created_at
  ])
  const csv = [headers, ...rows].map(r => r.map(c => `"${c}"`).join(',')).join('\n')
  const blob = new Blob([csv], { type: 'text/csv' })
  const url = URL.createObjectURL(blob)
  const a = document.createElement('a')
  a.href = url; a.download = `audit-logs-${new Date().toISOString().split('T')[0]}.csv`
  a.click(); URL.revokeObjectURL(url)
}

onMounted(() => fetchLogs())
</script>

<style scoped>
/* Stats cards */
.audit-stats-row {
  display: grid;
  grid-template-columns: repeat(4, 1fr);
  gap: 16px;
}
.audit-stat-card {
  display: flex;
  align-items: center;
  gap: 16px;
  padding: 20px;
  background: white;
  border-radius: 16px;
  border: 1px solid #e2e8f0;
  box-shadow: 0 1px 3px rgba(0,0,0,0.04);
}
.audit-stat-icon {
  width: 42px;
  height: 42px;
  border-radius: 12px;
  display: flex;
  align-items: center;
  justify-content: center;
  flex-shrink: 0;
}
.audit-stat-value {
  display: block;
  font-size: 22px;
  font-weight: 900;
  color: #1e293b;
  line-height: 1;
}
.audit-stat-label {
  display: block;
  font-size: 11px;
  color: #94a3b8;
  font-weight: 700;
  text-transform: capitalize;
  letter-spacing: 0.05em;
  margin-top: 2px;
}

/* Action badges */
.audit-action-badge {
  display: inline-flex;
  align-items: center;
  gap: 6px;
  font-size: 11px;
  font-weight: 800;
  text-transform: capitalize;
  letter-spacing: 0.05em;
  padding: 4px 10px;
  border-radius: 8px;
}
.audit-action-dot {
  width: 6px;
  height: 6px;
  border-radius: 50%;
}
.dot-green { background: #10b981; }
.dot-blue { background: #3b82f6; }
.dot-red { background: #ef4444; }
.dot-purple { background: #8b5cf6; }
.dot-gray { background: #94a3b8; }

.action-created, .action-approved { background: #ecfdf5; color: #059669; }
.action-updated, .action-submitted { background: #eff6ff; color: #2563eb; }
.action-deleted, .action-rejected { background: #fef2f2; color: #dc2626; }
.action-login { background: #f5f3ff; color: #7c3aed; }
.action-logout { background: #f8fafc; color: #64748b; }
.action-default { background: #f8fafc; color: #64748b; }

/* Diff panel */
.diff-panel {
  background: #f8fafc;
  border-top: 1px solid #e2e8f0;
  padding: 16px;
}
.diff-grid {
  display: grid;
  grid-template-columns: 1fr 1fr;
  gap: 12px;
}
.diff-col {
  border-radius: 10px;
  overflow: hidden;
  border: 1px solid #e2e8f0;
}
.diff-old { background: #fef2f2; }
.diff-new { background: #ecfdf5; }
.diff-col-header {
  padding: 8px 12px;
  border-bottom: 1px solid #e2e8f0;
}
.diff-header-badge {
  font-size: 10px;
  font-weight: 800;
  text-transform: capitalize;
  letter-spacing: 0.1em;
  padding: 2px 8px;
  border-radius: 4px;
}
.diff-header-old { background: #fecaca; color: #991b1b; }
.diff-header-new { background: #bbf7d0; color: #166534; }
.diff-content {
  padding: 12px;
  font-size: 11px;
  font-family: 'JetBrains Mono', monospace;
  line-height: 1.6;
  white-space: pre-wrap;
  word-break: break-word;
  max-height: 240px;
  overflow: auto;
  margin: 0;
}

@media (max-width: 1024px) {
  .audit-stats-row { grid-template-columns: repeat(2, 1fr); }
}
@media (max-width: 640px) {
  .audit-stats-row { grid-template-columns: 1fr; }
  .diff-grid { grid-template-columns: 1fr; }
}
</style>
