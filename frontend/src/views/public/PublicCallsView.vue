<template>
  <div class="flex flex-col gap-8 pb-12 animate-fade">
    <!-- Header -->
    <div class="card p-8 bg-slate-50 border-slate-100 relative overflow-hidden">
      <div class="relative z-10">
        <h1 class="text-3xl font-black text-slate-900 tracking-tight">Funding Opportunities</h1>
        <p class="text-slate-500 font-medium mt-1">Open grants and research calls across the institutional network.</p>
      </div>
      <div class="absolute right-0 top-0 w-32 h-32 bg-brand/5 rounded-full translate-x-8 -translate-y-8"></div>
    </div>

    <!-- Filters -->
    <div class="card p-5 flex flex-col md:flex-row gap-5 items-end">
      <div class="flex-1 w-full relative">
        <label class="block text-[11px] text-slate-500 font-black capitalize tracking-widest mb-2 ml-1">Search Database</label>
        <div class="relative group">
          <span class="absolute left-3.5 top-1/2 -translate-y-1/2 text-slate-400">
            <svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
          </span>
          <input v-model="search" type="text" placeholder="Search by title or keyword..." class="input pl-10" />
        </div>
      </div>
      <div class="w-full md:w-64">
        <label class="block text-[11px] text-slate-500 font-black capitalize tracking-widest mb-2 ml-1">Call Status</label>
        <select v-model="statusFilter" class="input font-bold">
          <option value="">All Statuses</option>
          <option value="open">Open</option>
          <option value="closed">Closed</option>
        </select>
      </div>
    </div>

    <!-- Content -->
    <div v-if="loading" class="grid grid-cols-1 md:grid-cols-2 gap-6">
      <div v-for="i in 4" :key="i" class="card h-48 animate-pulse"></div>
    </div>

    <div v-else-if="filteredCalls.length === 0" class="card p-12 text-center">
      <p class="text-sm font-black text-slate-400 capitalize tracking-widest italic">No matching calls found.</p>
    </div>

    <div v-else class="grid grid-cols-1 md:grid-cols-2 gap-6">
      <router-link v-for="call in filteredCalls" :key="call.id" :to="`/calls/${call.id}`"
        class="card p-6 flex flex-col group card-hover border-l-4 border-l-transparent hover:border-l-brand transition-all"
      >
        <div class="flex items-center gap-2 mb-4">
          <span class="px-2 py-0.5 bg-brand-light text-brand text-[9px] font-black capitalize tracking-widest rounded border border-brand/10">{{ call.status?.name || 'Open' }}</span>
          <span v-if="isUrgent(call.deadline)" class="px-2 py-0.5 bg-rose-50 text-rose-600 text-[9px] font-black capitalize tracking-widest rounded border border-rose-100">Expiring Soon</span>
        </div>

        <h3 class="text-lg font-black text-slate-900 group-hover:text-brand transition-colors mb-2">{{ call.title }}</h3>
        <p class="text-sm text-slate-500 font-medium line-clamp-2 mb-6 flex-1">{{ call.description }}</p>

        <div class="flex items-center justify-between pt-4 border-t border-slate-50 text-[10px] font-black capitalize tracking-widest text-slate-400">
          <span class="flex items-center gap-1.5">
            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
            Deadline: {{ formatDate(call.deadline) }}
          </span>
          <span class="text-brand">View Details →</span>
        </div>
      </router-link>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue'
import api from '@/services/api'
import { formatDate } from '@/utils/formatters'

const calls = ref([])
const loading = ref(true)
const search = ref('')
const statusFilter = ref('')

const filteredCalls = computed(() => {
  return calls.value.filter(c => {
    const matchSearch = !search.value || c.title?.toLowerCase().includes(search.value.toLowerCase()) || 
                       c.description?.toLowerCase().includes(search.value.toLowerCase())
    const matchStatus = !statusFilter.value || c.status?.name === statusFilter.value
    return matchSearch && matchStatus
  })
})

function isUrgent(dateStr) {
  if (!dateStr) return false
  const diff = new Date(dateStr) - new Date()
  return diff > 0 && diff < 7 * 24 * 60 * 60 * 1000
}

onMounted(async () => {
  try {
    const { data } = await api.get('/calls')
    calls.value = data.data || data
  } catch (e) {}
  loading.value = false
})
</script>
