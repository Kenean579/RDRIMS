<template>
  <div class="flex flex-col gap-12 pb-16">
    <!-- Hero Banner -->
    <section class="bg-white border-b border-slate-100 pt-12 pb-16">
      <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <h1 class="text-4xl md:text-5xl font-black text-slate-900 tracking-tight mb-3">Research Funding Calls</h1>
        <p class="text-lg text-slate-500 font-medium max-w-2xl">Browse open opportunities for research grants, funding, and academic collaboration across universities.</p>
      </div>
    </section>

    <!-- Filters & Content -->
    <section class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 w-full">
      <!-- Search Bar -->
      <div class="flex flex-col sm:flex-row gap-4 mb-8">
        <div class="flex-1 relative">
          <svg class="absolute left-4 top-1/2 -translate-y-1/2 w-5 h-5 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
          <input v-model="search" type="text" placeholder="Search funding calls..."
            class="w-full pl-12 pr-4 py-3.5 bg-white border border-slate-200 rounded-2xl text-sm font-medium focus:ring-2 focus:ring-brand/30 focus:border-brand outline-none shadow-sm transition-all" />
        </div>
        <select v-model="statusFilter"
          class="px-4 py-3.5 bg-white border border-slate-200 rounded-2xl text-sm font-bold text-slate-600 focus:ring-2 focus:ring-brand/30 focus:border-brand outline-none shadow-sm">
          <option value="">All Statuses</option>
          <option value="open">Open</option>
          <option value="closed">Closed</option>
        </select>
      </div>

      <!-- Loading -->
      <div v-if="loading" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
        <div v-for="i in 6" :key="i" class="bg-white rounded-2xl border border-slate-200 p-6 h-56 animate-pulse">
          <div class="h-4 w-20 bg-slate-200 rounded mb-4"></div>
          <div class="h-6 w-3/4 bg-slate-100 rounded mb-3"></div>
          <div class="h-16 w-full bg-slate-50 rounded mb-4"></div>
          <div class="h-4 w-1/2 bg-slate-100 rounded"></div>
        </div>
      </div>

      <!-- Empty -->
      <div v-else-if="filteredCalls.length === 0" class="text-center py-20">
        <div class="h-20 w-20 bg-slate-100 rounded-full flex items-center justify-center mx-auto mb-6 text-3xl">📢</div>
        <h3 class="text-xl font-black text-slate-700 mb-2">No calls found</h3>
        <p class="text-sm text-slate-500 font-medium">Try adjusting your search or check back later for new opportunities.</p>
      </div>

      <!-- Calls Grid -->
      <div v-else class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
        <router-link v-for="call in filteredCalls" :key="call.id" :to="`/calls/${call.id}`"
          class="bg-white rounded-2xl border border-slate-200 p-6 shadow-sm hover:shadow-lg hover:-translate-y-1 transition-all group relative overflow-hidden">
          <!-- Status pill -->
          <div class="flex items-center justify-between mb-4">
            <span class="px-3 py-1 rounded-full text-[10px] font-black uppercase tracking-widest"
              :class="call.status?.name === 'open' ? 'bg-emerald-50 text-emerald-600 border border-emerald-200' : 'bg-slate-100 text-slate-500 border border-slate-200'">
              {{ call.status?.name || 'open' }}
            </span>
            <span v-if="isUrgent(call.deadline)" class="px-2 py-0.5 bg-rose-50 text-rose-600 rounded-full text-[9px] font-black uppercase border border-rose-200">
              Closing Soon
            </span>
          </div>

          <h3 class="text-lg font-black text-slate-800 leading-tight mb-3 group-hover:text-brand transition-colors line-clamp-2">
            {{ call.title }}
          </h3>
          <p class="text-sm text-slate-500 font-medium line-clamp-3 mb-6 leading-relaxed">{{ call.description }}</p>

          <div class="flex items-center gap-3 pt-4 border-t border-slate-100 mt-auto">
            <div class="h-9 w-9 rounded-xl bg-rose-50 flex items-center justify-center text-rose-500 shrink-0">
              <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
            </div>
            <div>
              <p class="text-[9px] font-black uppercase tracking-widest text-slate-400">Deadline</p>
              <p class="text-sm font-bold text-slate-700" :class="{ 'text-rose-600': isUrgent(call.deadline) }">{{ formatDate(call.deadline) }}</p>
            </div>
          </div>
        </router-link>
      </div>
    </section>
  </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue'
import api from '@/services/api'

const calls = ref([])
const loading = ref(true)
const search = ref('')
const statusFilter = ref('')

const filteredCalls = computed(() => {
  return calls.value.filter(c => {
    const matchSearch = !search.value || c.title?.toLowerCase().includes(search.value.toLowerCase())
    const matchStatus = !statusFilter.value || c.status?.name === statusFilter.value
    return matchSearch && matchStatus
  })
})

function formatDate(val) {
  if (!val) return 'N/A'
  return new Date(val).toLocaleDateString(undefined, { month: 'short', day: 'numeric', year: 'numeric' })
}

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
