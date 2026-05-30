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

    <div v-else class="grid grid-cols-1 md:grid-cols-2 gap-6">
      <div v-for="req in requests" :key="req.id" class="card p-6 group card-hover relative overflow-hidden border-l-4 border-l-brand/20 hover:border-l-brand transition-all">
        <div class="flex items-start justify-between gap-4">
          <div class="space-y-3 flex-1 min-w-0">
            <div>
              <p class="text-[10px] font-black text-slate-400 uppercase tracking-widest mb-1.5">{{ req.detectable_type.split('\\').pop() }} ID #{{ req.detectable_id }}</p>
              <h3 class="text-base font-black text-slate-800 leading-snug truncate">Scan: {{ req.service?.name || 'Standard Check' }}</h3>
            </div>
            
            <div class="flex items-center gap-3">
              <StatusBadge :status="req.status?.name || 'pending'" />
              <div v-if="req.results?.length" class="flex items-center gap-2 px-2.5 py-1 bg-slate-50 rounded-lg border border-slate-100">
                 <div class="w-2 h-2 rounded-full" :class="req.results[0].similarity_score > 20 ? 'bg-rose-500' : 'bg-emerald-500'"></div>
                 <span class="text-[11px] font-black uppercase tracking-widest" :class="req.results[0].similarity_score > 20 ? 'text-rose-600' : 'text-emerald-600'">
                   {{ req.results[0].similarity_score }}% Match
                 </span>
              </div>
            </div>
          </div>
          
          <button class="w-10 h-10 rounded-full flex items-center justify-center bg-slate-50 text-slate-300 group-hover:bg-brand group-hover:text-white transition-all duration-300 shadow-sm">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M9 5l7 7-7 7" /></svg>
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
