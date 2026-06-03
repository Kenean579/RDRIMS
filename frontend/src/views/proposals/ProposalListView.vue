<template>
  <div class="flex flex-col gap-8 pb-12 animate-fade card">
    <!-- Header -->
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
      <div>
        <h1 class="text-3xl font-black text-slate-900 tracking-tight">Submissions</h1>
        <p class="text-slate-500 font-medium mt-1">See where your work is in the system.</p>
      </div>
      <router-link v-if="auth.hasPermission('submit_proposals') || auth.hasRole('super_admin','research_admin')" to="/app/proposals/create" class="btn btn-primary h-11 px-8 shadow-lg shadow-blue-500/20">
        <svg class="w-4 h-4 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M12 4v16m8-8H4" /></svg>
        Start Submission
      </router-link>
    </div>

    <!-- Filters -->
    <div class="card p-5 bg-slate-50/50">
      <div class="flex flex-wrap gap-5 items-end">
        <div class="flex-1 min-w-[300px]">
          <label class="block text-[11px] text-slate-500 font-black capitalize tracking-widest mb-2 ml-1">Search Keywords</label>
          <div class="relative group">
            <span class="absolute left-3.5 top-1/2 -translate-y-1/2 text-slate-400 group-focus-within:text-brand transition-colors">
              <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><circle cx="11" cy="11" r="8"/><path d="m21 21-4.35-4.35"/></svg>
            </span>
            <input v-model="filters.search" type="text" placeholder="Search by title or ID..." class="input pl-10" @input="debounceSearch" />
          </div>
        </div>
        <div class="w-56">
          <label class="block text-[11px] text-slate-500 font-black capitalize tracking-widest mb-2 ml-1">Status</label>
          <select v-model="filters.status" @change="fetchProposals(1)" class="input font-bold">
            <option value="">All Statuses</option>
            <option v-for="s in proposalStatuses" :key="s.id" :value="s.name">{{ formatStatusName(s.name) }}</option>
          </select>
        </div>
        <div class="w-56">
          <label class="block text-[11px] text-slate-500 font-black capitalize tracking-widest mb-2 ml-1">Type</label>
          <select v-model="filters.type" @change="fetchProposals(1)" class="input font-bold">
            <option value="">All Types</option>
            <option v-for="t in proposalTypes" :key="t.id" :value="t.name">{{ t.name.toUpperCase() }}</option>
          </select>
        </div>
        <button v-if="hasActiveFilters" @click="clearFilters" class="btn btn-secondary h-11 px-6 font-black capitalize tracking-widest text-[11px]">
          Reset
        </button>
      </div>
    </div>

    <!-- Content -->
    <div v-if="loading" class="card p-24 flex flex-col justify-center items-center gap-4">
      <div class="animate-spin rounded-full h-10 w-10 border-b-2 border-brand"></div>
      <p class="text-[10px] font-black text-slate-400 capitalize tracking-widest">Loading Submissions...</p>
    </div>
    
    <div v-else-if="error" class="card p-16 text-center">
      <p class="text-rose-500 font-black capitalize tracking-widest text-xs mb-4">{{ error }}</p>
      <button @click="fetchProposals(1)" class="btn btn-ghost text-xs font-black capitalize tracking-widest border border-slate-100 px-6">Retry</button>
    </div>

    <div v-else-if="proposals.length === 0" class="card">
      <EmptyState icon="📝" title="No submissions found" description="Try changing your search or add a new one." :action-label="(auth.hasPermission('submit_proposals') || auth.hasRole('super_admin')) ? 'Add First' : ''" action-icon="add" @action="$router.push('/app/proposals/create')" />
    </div>

    <div v-else class="space-y-6">
      <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-2 xl:grid-cols-3 gap-6">
        <div v-for="p in proposals" :key="p.id" 
          class="card p-6 flex flex-col group card-hover relative overflow-hidden border-l-4 border-l-brand hover:border-l-indigo-500 transition-all cursor-pointer"
          @click="$router.push(`/app/proposals/${p.id}`)"
        >
          <div class="flex items-start justify-between mb-4">
            <div class="flex-1 pr-4">
              <p class="text-[10px] font-black text-slate-400 capitalize tracking-widest mb-1.5 flex items-center gap-1.5">
                <span class="w-1.5 h-1.5 rounded-full bg-slate-300"></span>
                ID: {{ String(p.id).padStart(4, '0') }}
              </p>
              <h3 class="text-base font-black text-slate-800 leading-tight group-hover:text-brand transition-colors line-clamp-2 min-h-10">{{ p.title }}</h3>
            </div>
            <div class="w-12 h-12 rounded-2xl bg-linear-to-br from-blue-500 to-indigo-600 text-white flex items-center justify-center font-black shadow-lg shadow-blue-500/30 shrink-0">
               <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" /></svg>
            </div>
          </div>
          
          <div class="flex flex-wrap gap-2 mb-6">
            <span class="px-2.5 py-1 bg-indigo-50 text-indigo-600 border border-indigo-100 text-[9px] font-black capitalize tracking-widest rounded-lg">
              {{ p.type?.name?.toUpperCase() || 'GENERAL' }}
            </span>
            <StatusBadge :status="p.status?.name || 'draft'" />
          </div>

          <div class="mt-auto pt-4 border-t border-slate-100 flex items-center justify-between">
             <div class="flex flex-col">
               <span class="text-[10px] font-black text-slate-400 capitalize tracking-widest mb-0.5">Estimated Budget</span>
               <span class="text-sm font-black text-slate-900 tracking-tight">{{ formatCurrency(p.budget) }}</span>
             </div>
             <div class="flex gap-2">
               <router-link v-if="auth.hasRole('super_admin','research_admin') || p.submitted_by?.id === auth.user?.id" 
                 :to="`/app/proposals/${p.id}/edit`" 
                 class="btn btn-ghost text-[10px] font-black capitalize tracking-widest py-1.5 px-3 text-amber-600 hover:bg-amber-50"
                 @click.stop
               >
                 Edit
               </router-link>
               <button v-if="auth.hasRole('super_admin')" 
                 @click.stop="deleteProposal(p.id)" 
                 class="btn btn-ghost text-[10px] font-black capitalize tracking-widest py-1.5 px-3 text-rose-500 hover:bg-rose-50"
               >
                 Delete
               </button>
               <button class="w-8 h-8 rounded-full flex items-center justify-center bg-slate-50 text-slate-400 group-hover:bg-brand group-hover:text-white transition-all duration-300">
                 <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M9 5l7 7-7 7" /></svg>
               </button>
             </div>
          </div>
        </div>
      </div>
      <div class="px-8 py-4 bg-slate-50/50 rounded-2xl border border-slate-100 overflow-hidden">
        <Pagination :current-page="pagination.current_page" :total-pages="pagination.last_page" :total="pagination.total" @page-change="fetchProposals" />
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, reactive, computed, onMounted } from 'vue'
import { useAuthStore } from '@/stores/auth'
import { useNotificationStore } from '@/stores/notification'
import api from '@/services/api'
import StatusBadge from '@/components/StatusBadge.vue'
import Pagination from '@/components/Pagination.vue'
import EmptyState from '@/components/EmptyState.vue'
import { formatCurrency, formatDate } from '@/utils/formatters'
import { formatStatusName } from '@/utils/colors'

const auth = useAuthStore()
const notif = useNotificationStore()
const loading = ref(true); const error = ref(null); const proposals = ref([])
const pagination = reactive({ current_page: 1, last_page: 1, total: 0 })
const filters = reactive({ search: '', status: '', type: '' })
const proposalStatuses = ref([]); const proposalTypes = ref([])
let searchTimer = null
const hasActiveFilters = computed(() => filters.search || filters.status || filters.type)

async function fetchProposals(page = 1) {
  loading.value = true; error.value = null
  try { const params = { page }; if (filters.search) params.search = filters.search; if (filters.status) params.status = filters.status; if (filters.type) params.type = filters.type; const { data } = await api.get('/proposals', { params }); proposals.value = data.data; Object.assign(pagination, { current_page: data.current_page, last_page: data.last_page, total: data.total }) }
  catch (err) { error.value = err.response?.data?.message || 'Failed' } finally { loading.value = false }
}
function debounceSearch() { clearTimeout(searchTimer); searchTimer = setTimeout(() => fetchProposals(1), 400) }
function clearFilters() { filters.search = ''; filters.status = ''; filters.type = ''; fetchProposals(1) }

async function deleteProposal(id) {
  if (!confirm('Are you sure you want to delete this proposal? This action cannot be undone.')) return
  try {
    await api.delete(`/proposals/${id}`)
    notif.success('Proposal deleted successfully')
    fetchProposals(pagination.current_page)
  } catch (e) {
    notif.error(e.response?.data?.message || 'Failed to delete proposal')
  }
}

onMounted(async () => {
  fetchProposals()
  try {
    const [ss, ts] = await Promise.all([api.get('/lookups/proposal_statuses'), api.get('/lookups/proposal_types')])
    proposalStatuses.value = ss.data; proposalTypes.value = ts.data
  } catch (e) {}
})
</script>
