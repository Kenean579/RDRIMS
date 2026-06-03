<template>
  <div class="flex flex-col gap-8 pb-12 animate-fade card">
    <!-- Header -->
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
      <div>
        <h1 class="text-3xl font-black text-slate-900 tracking-tight">Writing Check</h1>
        <p class="text-slate-500 font-medium mt-1">History of writing checks and original work scans.</p>
      </div>
      <button @click="fetchRequests" class="btn btn-secondary h-11 px-6 shadow-sm group">
        <svg class="w-4 h-4 mr-1.5 group-hover:rotate-180 transition-transform duration-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" /></svg>
        Refresh
      </button>
    </div>

    <!-- Content -->
    <div v-if="loading" class="grid grid-cols-1 md:grid-cols-2 gap-4">
      <div v-for="i in 4" :key="i" class="card h-28 animate-pulse bg-slate-50/50"></div>
    </div>

    <div v-else-if="requests.length === 0" class="card">
      <EmptyState icon="🔍" title="No checks found" description="No writing checks have been done yet." />
    </div>

    <div v-else class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
      <div v-for="req in requests" :key="req.id" class="card p-6 flex flex-col group card-hover relative overflow-hidden border-l-4 border-l-brand hover:border-l-indigo-500 transition-all">
        <div class="flex items-start justify-between mb-4">
          <div class="flex-1 pr-4">
            <h3 class="text-base font-black text-slate-800 leading-tight mb-2">{{ req.service?.name || 'Standard Check' }}</h3>
            <div class="flex items-center gap-2">
              <span class="inline-block px-2 py-0.5 text-slate-500 text-[9px] font-black capitalize tracking-widest rounded-md border border-slate-200">
                {{ req.detectable_type.split('\\').pop() }} #{{ req.detectable_id }}
              </span>
              <span v-if="req.requested_by" class="inline-block px-2 py-0.5 text-blue-600 text-[9px] font-black capitalize tracking-widest rounded-md border border-blue-200 truncate max-w-[120px]">
                <i class="fas fa-user mr-1"></i>{{ req.requested_by?.name || 'User' }}
              </span>
            </div>
          </div>
          <div class="w-12 h-12 rounded-2xl bg-linear-to-br from-indigo-500 to-blue-600 text-white flex items-center justify-center font-black shadow-lg shadow-indigo-500/30 shrink-0">
             <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4" /></svg>
          </div>
        </div>
        
        <div class="mt-auto pt-4 border-t border-slate-100 flex items-center justify-between">
           <div class="flex items-center gap-2">
             <StatusBadge :status="req.status?.name || 'pending'" />
             <div v-if="req.results?.length" class="flex items-center gap-1.5 px-2 py-0.5 rounded-md border border-slate-200">
                <div class="w-1.5 h-1.5 rounded-full" :class="req.results[0].similarity_score > 20 ? 'bg-rose-500' : 'bg-emerald-500'"></div>
                <span class="text-[10px] font-black capitalize tracking-widest" :class="req.results[0].similarity_score > 20 ? 'text-rose-600' : 'text-emerald-600'">
                  {{ req.results[0].similarity_score }}% Match
                </span>
             </div>
           </div>
           
           <button class="w-8 h-8 rounded-full flex items-center justify-center border border-slate-200 text-slate-400 group-hover:border-indigo-300 group-hover:text-indigo-600 transition-all duration-300">
             <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M9 5l7 7-7 7" /></svg>
           </button>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import api from '@/services/api'
import StatusBadge from '@/components/StatusBadge.vue'
import LoadingSkeleton from '@/components/LoadingSkeleton.vue'
import EmptyState from '@/components/EmptyState.vue'

const requests = ref([]); const loading = ref(true)

async function fetchRequests() {
  loading.value = true
  try { const { data } = await api.get('/detection/requests'); requests.value = data.data || data }
  catch (e) {} finally { loading.value = false }
}

onMounted(fetchRequests)
</script>
