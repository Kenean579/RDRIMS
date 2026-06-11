<template>
  <div class="flex flex-col gap-6 pb-6 animate-fade card">
    <!-- Header -->
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
      <div>
        <h1 class="text-xl font-bold text-slate-900 tracking-tight">
          {{ isAdmin ? 'All Review Assignments' : 'My Assigned Proposals' }}
        </h1>
        <p class="text-slate-500 font-medium mt-1 text-xs">
          {{ isAdmin
            ? 'View and monitor all reviewer assignments across the system.'
            : 'Proposals assigned to you for scientific and technical evaluation.'
          }}
        </p>
      </div>
      <div class="flex items-center gap-2">
         <div v-if="proposals.length" class="px-3 py-1 bg-amber-50 text-amber-600 border border-amber-100 rounded-lg text-xs font-bold  tracking-widest">
           {{ proposals.filter(p => !p.reviewPivot?.overall_score).length }} Pending Reviews
         </div>
      </div>
    </div>

    <!-- Filters -->
    <div class="card p-3 bg-slate-50/50 flex flex-col sm:flex-row gap-3">
      <div class="relative flex-1 group">
        <span class="absolute left-4 top-1/2 -translate-y-1/2 text-slate-400 group-focus-within:text-brand transition-colors">
          <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"><circle cx="11" cy="11" r="8"/><path d="m21 21-4.35-4.35"/></svg>
        </span>
        <input v-model="search" type="text" placeholder="Search by proposal title..." class="w-full bg-white h-10 pl-10 pr-4 rounded-xl border border-slate-100 text-xs font-bold text-slate-700 outline-none focus:ring-2 focus:ring-brand/10 transition-all" />
      </div>
      <input v-if="isAdmin" v-model="reviewerFilter" type="text" placeholder="Filter by reviewer name..." class="bg-white h-10 px-4 rounded-xl border border-slate-100 text-xs font-bold text-slate-700 outline-none w-full sm:w-56" />
      <select v-model="statusFilter" class="bg-white h-10 px-4 rounded-xl border border-slate-100 text-xs font-bold text-slate-700 outline-none w-full sm:w-48">
        <option value="all">All Assignments</option>
        <option value="pending">Pending Review</option>
        <option value="completed">Reviewed</option>
      </select>
    </div>

    <!-- Content -->
    <div v-if="loading" class="grid grid-cols-1 md:grid-cols-2 gap-5">
      <div v-for="i in 4" :key="i" class="card h-40 animate-pulse bg-slate-50 border-0"></div>
    </div>
    
    <div v-else-if="error" class="card p-8 border-rose-100 bg-rose-50/20 text-center">
       <p class="text-rose-600 text-xs font-bold mb-4">{{ error }}</p>
       <button @click="fetchProposals(1)" class="btn btn-secondary px-6 h-10 text-xs">Retry Synchronize</button>
    </div>

    <div v-else-if="filteredProposals.length === 0" class="card">
      <EmptyState icon="📂" title="No assignments found" description="You have no proposals matching the current filters." />
    </div>

    <div v-else class="grid grid-cols-1 lg:grid-cols-2 gap-5">
      <div v-for="p in filteredProposals" :key="p.id" 
        @click="$router.push(`/app/reviewer/proposals/${p.id}`)"
        class="card group card-hover p-8 relative overflow-hidden transition-all flex flex-col cursor-pointer border-l-4"
        :class="p.reviewPivot?.overall_score ? 'border-l-emerald-400' : 'border-l-amber-400'">
        
        <div class="flex items-start justify-between mb-4">
          <div class="flex-1 pr-4 min-w-0">
            <h3 class="text-base font-bold text-slate-800 leading-tight group-hover:text-brand transition-colors line-clamp-2 min-h-12">{{ p.title }}</h3>
            <div class="flex items-center gap-2 mt-2">
               <span class="px-2 py-0.5 bg-slate-100 text-slate-500 text-xs font-bold rounded  tracking-tighter border border-slate-100">ID: {{ String(p.id).padStart(4, '0') }}</span>
               <span class="px-2 py-0.5 bg-brand/10 text-brand text-xs font-bold rounded  tracking-tighter border border-brand/10">{{ p.thematic_area?.name || 'General' }}</span>
            </div>
          </div>
          <div class="shrink-0 flex flex-col items-end gap-2">
            <StatusBadge :status="p.status?.name || 'under_review'" />
            <div v-if="p.reviewPivot?.overall_score" class="flex flex-col items-end">
              <span class="text-[8px] font-bold text-slate-400 ">Your Score</span>
              <span class="text-lg font-bold text-emerald-600 leading-none">{{ p.reviewPivot.overall_score }}<span class="text-xs text-slate-300">/100</span></span>
            </div>
            <div v-else class="flex flex-col items-end">
              <span class="text-xs font-bold text-amber-500 animate-pulse  tracking-widest">Awaiting Score</span>
            </div>
          </div>
        </div>

        <div class="mt-auto pt-6 border-t border-slate-50 flex items-center justify-between text-xs font-bold">
           <div class="flex items-center gap-3">
             <div class="flex flex-col">
               <span class="text-slate-400  text-[8px] tracking-widest mb-0.5">Assigned On</span>
               <span class="text-slate-700">{{ formatDate(p.reviewPivot?.assigned_at || p.pivot?.assigned_at) }}</span>
             </div>
             <div class="w-px h-6 bg-slate-100"></div>
             <div class="flex flex-col" v-if="p.reviewPivot?.reviewer">
               <span class="text-slate-400  text-[8px] tracking-widest mb-0.5">Assigned To</span>
               <span class="text-brand font-bold">{{ p.reviewPivot.reviewer.name }}</span>
             </div>
             <div class="w-px h-6 bg-slate-100" v-if="p.reviewPivot?.reviewer"></div>
             <div class="flex flex-col">
               <span class="text-slate-400  text-[8px] tracking-widest mb-0.5">Submitted By</span>
               <span class="text-slate-700">{{ p.user?.name || 'Academic Investigator' }}</span>
             </div>
           </div>
           
           <div class="flex items-center gap-1.5 text-brand group-hover:translate-x-1 transition-transform">
              <span>Start Review</span>
              <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M14 5l7 7m0 0l-7 7m7-7H3"/></svg>
           </div>
        </div>
      </div>
    </div>

    <div v-if="pagination.last_page > 1" class="mt-8 pt-6 border-t border-slate-50">
      <Pagination :current-page="pagination.current_page" :total-pages="pagination.last_page" :total="pagination.total" @page-change="fetchProposals" />
    </div>
  </div>
</template>

<script setup>
import { ref, reactive, computed, onMounted } from 'vue'
import api from '@/services/api'
import { useAuthStore } from '@/stores/auth'
import StatusBadge from '@/components/StatusBadge.vue'
import Pagination from '@/components/Pagination.vue'
import EmptyState from '@/components/EmptyState.vue'
import { formatDate } from '@/utils/formatters'

const auth = useAuthStore()
const isAdmin = computed(() => auth.hasRole('super_admin', 'research_admin'))

const loading = ref(true)
const error = ref(null)
const proposals = ref([])
const search = ref('')
const statusFilter = ref('all')
const reviewerFilter = ref('')
const pagination = reactive({ current_page: 1, last_page: 1, total: 0 })

const filteredProposals = computed(() => {
  let filtered = proposals.value
  if (search.value) {
    const q = search.value.toLowerCase()
    filtered = filtered.filter(p => p.title?.toLowerCase().includes(q))
  }
  if (reviewerFilter.value) {
    const q = reviewerFilter.value.toLowerCase()
    filtered = filtered.filter(p => p.reviewPivot?.reviewer?.name?.toLowerCase().includes(q))
  }
  if (statusFilter.value === 'pending') {
    filtered = filtered.filter(p => !p.reviewPivot?.overall_score)
  } else if (statusFilter.value === 'completed') {
    filtered = filtered.filter(p => !!p.reviewPivot?.overall_score)
  }
  return filtered
})

async function fetchProposals(page = 1) {
  loading.value = true; error.value = null
  try {
    const { data } = await api.get('/reviewer/proposals', { params: { page } })
    proposals.value = data.data
    Object.assign(pagination, { current_page: data.current_page, last_page: data.last_page, total: data.total })
  } catch (err) {
    error.value = err.response?.data?.message || 'Failed to load review assignments'
  } finally { loading.value = false }
}

onMounted(() => fetchProposals())
</script>

