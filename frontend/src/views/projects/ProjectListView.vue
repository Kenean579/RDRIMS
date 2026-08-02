<template>
  <div class="flex flex-col gap-5 pb-6 animate-fade card">
    <!-- Header -->
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
      <div>
        <h1 class="text-xl font-bold text-slate-900 tracking-tight">Research Projects</h1>
        <p class="text-slate-500 font-medium mt-1">Track the progress of all ongoing and finished research projects.</p>
      </div>
      <div class="flex items-center gap-3">
        <button @click="fetchProjects(1)" class="btn btn-secondary h-11 px-6 shadow-sm group">
          <svg class="w-4 h-4 group-hover:rotate-180 transition-transform duration-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" /></svg>
          Refresh
        </button>
      </div>
    </div>

    <!-- Filters -->
    <div class="card p-8 bg-slate-50/50">
      <div class="flex flex-col sm:flex-row gap-5 items-start">
        <div class="flex-1 w-full relative">
          <label class="block text-xs text-slate-500 font-medium mb-2 ml-1">Search</label>
          <div class="relative group">
            <span class="absolute left-3.5 top-1/2 -translate-y-1/2 text-slate-400 group-focus-within:text-brand transition-colors">
              <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><circle cx="11" cy="11" r="8"/><path d="m21 21-4.35-4.35"/></svg>
            </span>
            <input v-model="search" type="text" placeholder="Search by title or researcher..." class="input pl-10" @input="debounceFetch" />
          </div>
        </div>
        <div class="w-full sm:w-56">
          <label class="block text-xs text-slate-500 font-medium mb-2 ml-1">Status</label>
          <select v-model="status" @change="fetchProjects(1)" class="input font-semibold">
            <option value="">All Statuses</option>
            <option v-for="s in projectStatuses" :key="s.id" :value="s.name">{{ formatStatusName(s.name) }}</option>
          </select>
        </div>
      </div>
    </div>

    <!-- Content -->
    <div v-if="loading" class="card p-8 flex flex-col justify-center items-center gap-4">
      <div class="animate-spin rounded-full h-10 w-10 border-b-2 border-brand"></div>
      <p class="text-xs font-medium text-slate-400">Loading projects...</p>
    </div>

    <div v-else-if="loadError" class="card p-8 flex flex-col justify-center items-center gap-4 text-center">
      <p class="text-sm font-semibold text-rose-600">{{ loadError }}</p>
      <button type="button" class="btn btn-secondary" @click="fetchProjects(1)">Try again</button>
    </div>
    
    <div v-else-if="projects.length === 0" class="card">
       <EmptyState icon="📁" title="No projects found" description="There are no research projects matching your search." />
    </div>
    <div v-else class="grid grid-cols-1 lg:grid-cols-2 xl:grid-cols-3 gap-6">
      <div v-for="p in projects" :key="p.id" 
        class="bg-white rounded-2xl border border-slate-100 p-4 flex flex-col group hover:shadow-md transition-all cursor-pointer"
        @click="$router.push(`/app/projects/${p.id}`)"
      >
        <div class="flex items-start justify-between mb-4">
          <div class="flex-1 pr-4 min-w-0">
            <h3 class="text-base font-semibold text-slate-800 leading-tight group-hover:text-emerald-700 transition-colors line-clamp-2 min-h-10">{{ p.title }}</h3>
          </div>
        </div>
        
        <div class="flex items-center gap-3 mb-6">
           <div class="w-8 h-8 rounded-2xl overflow-hidden bg-slate-50 flex items-center justify-center text-xs font-medium text-slate-500 border border-slate-100 group-hover:bg-emerald-50 group-hover:text-emerald-700 transition-all shrink-0">
              <img v-if="imageUrl(p.pi?.profile_image)" :src="imageUrl(p.pi?.profile_image)" class="w-full h-full object-cover"/>
              <span v-else>{{ getInitials(p.pi?.name) }}</span>
           </div>
           <div class="min-w-0">
             <p class="text-xs font-medium text-slate-400 leading-none mb-1">Lead PI</p>
             <p class="text-xs font-semibold text-slate-700 truncate">{{ p.pi?.name || 'Researcher' }}</p>
           </div>
        </div>

        <div class="space-y-3 mb-6">
           <div class="flex items-center justify-between text-xs font-medium text-slate-400">
              <span>Execution Progress</span>
              <span class="text-emerald-600">{{ calculateProgress(p) }}%</span>
           </div>
           <div class="w-full h-2 bg-slate-100 rounded-full overflow-hidden border border-slate-50">
             <div class="h-full bg-emerald-500 rounded-full transition-all duration-1000 ease-out shadow-sm" :style="{ width: calculateProgress(p) + '%' }"></div>
           </div>
        </div>

        <div class="mt-auto pt-4 border-t border-slate-100 flex items-center justify-between">
           <div class="flex flex-col">
             <span class="text-xs font-medium text-slate-400">Target End Date</span>
             <span class="text-xs font-semibold text-slate-700">{{ formatDate(p.end_date) }}</span>
           </div>
           <StatusBadge :status="p.status?.name || 'active'" />
        </div>
      </div>
      <div class="lg:col-span-2 xl:col-span-3 px-5 py-4 bg-slate-50/50 rounded-2xl border border-slate-100 overflow-hidden mt-2">
        <Pagination :current-page="pagination.current_page" :total-pages="pagination.last_page" :total="pagination.total" @page-change="fetchProjects" />
      </div>
    </div>
  </div>
</template>
<script setup>
import { ref, reactive, onMounted } from 'vue'
import api from '@/services/api'
import StatusBadge from '@/components/StatusBadge.vue'
import Pagination from '@/components/Pagination.vue'
import EmptyState from '@/components/EmptyState.vue'
import { formatDate, getInitials, imageUrl } from '@/utils/formatters'
import { formatStatusName } from '@/utils/colors'
const loading = ref(true); const projects = ref([]); const search = ref(''); const status = ref('')
const loadError = ref('')
const pagination = reactive({ current_page: 1, last_page: 1, total: 0 })
const projectStatuses = ref([])
let timer = null
async function fetchProjects(page = 1) {
  loading.value = true
  loadError.value = ''

  try {
    const params = { page, search: search.value }
    if (status.value) params.status = status.value

    const { data } = await api.get('/projects', { params, timeout: 15000 })
    projects.value = Array.isArray(data?.data) ? data.data : []
    Object.assign(pagination, {
      current_page: data?.meta?.current_page ?? data?.current_page ?? 1,
      last_page: data?.meta?.last_page ?? data?.last_page ?? 1,
      total: data?.meta?.total ?? data?.total ?? projects.value.length
    })
  } catch (error) {
    projects.value = []
    loadError.value = error.code === 'ECONNABORTED'
      ? 'The projects request timed out. Please verify that the backend and database are running.'
      : (error.response?.data?.message || 'Failed to load research projects.')
    console.error('Failed to load projects:', error)
  } finally {
    loading.value = false
  }
}
function calculateProgress(p) { if (!p.milestones || p.milestones.length === 0) return 0; const comp = p.milestones.filter(m => m.status === 'completed').length; return Math.round((comp / p.milestones.length) * 100) }
function debounceFetch() { clearTimeout(timer); timer = setTimeout(() => fetchProjects(1), 400) }
onMounted(() => {
  fetchProjects()

  api.get('/lookups/project_statuses', { timeout: 10000 })
    .then(({ data }) => { projectStatuses.value = Array.isArray(data) ? data : [] })
    .catch(error => console.error('Failed to load project statuses:', error))
})
</script>
