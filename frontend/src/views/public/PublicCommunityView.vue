<template>
  <div class="flex flex-col gap-12 pb-16">
    <section class="bg-white border-b border-slate-100 pt-12 pb-16">
      <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <h1 class="text-4xl md:text-5xl font-black text-slate-900 tracking-tight mb-3">Community Impact</h1>
        <p class="text-lg text-slate-500 font-medium max-w-2xl">Track how academic research addresses real-world community challenges and creates lasting impact.</p>
      </div>
    </section>

    <section class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 w-full">
      <!-- Status Filter Tabs -->
      <div class="flex items-center gap-2 mb-8 overflow-x-auto pb-2">
        <button v-for="tab in tabs" :key="tab.value" @click="activeTab = tab.value"
          class="px-5 py-2.5 rounded-xl text-sm font-bold whitespace-nowrap transition-all"
          :class="activeTab === tab.value
            ? 'bg-brand text-white shadow-md shadow-brand/30'
            : 'text-slate-500 hover:bg-slate-100'">
          {{ tab.label }}
        </button>
      </div>

      <div v-if="loading" class="space-y-4">
        <div v-for="i in 4" :key="i" class="bg-white rounded-2xl border border-slate-200 p-6 animate-pulse flex gap-6">
          <div class="w-14 h-14 bg-slate-200 rounded-2xl shrink-0"></div>
          <div class="flex-1 space-y-3">
            <div class="h-5 w-2/3 bg-slate-100 rounded"></div>
            <div class="h-4 w-full bg-slate-50 rounded"></div>
            <div class="h-3 w-1/4 bg-slate-100 rounded"></div>
          </div>
        </div>
      </div>

      <div v-else-if="filteredProblems.length === 0" class="text-center py-20">
        <div class="h-20 w-20 bg-slate-100 rounded-full flex items-center justify-center mx-auto mb-6 text-3xl">🌍</div>
        <h3 class="text-xl font-black text-slate-700 mb-2">No community problems found</h3>
        <p class="text-sm text-slate-500 font-medium">Community-engaged research projects will appear here.</p>
      </div>

      <div v-else class="space-y-4">
        <div v-for="problem in filteredProblems" :key="problem.id"
          class="bg-white rounded-2xl border border-slate-200 p-6 shadow-sm hover:shadow-md transition-all group flex gap-6">
          <div class="h-14 w-14 rounded-2xl flex items-center justify-center shrink-0 text-2xl"
            :class="statusIcon(problem.status?.name).bg">
            {{ statusIcon(problem.status?.name).icon }}
          </div>
          <div class="flex-1 min-w-0">
            <div class="flex items-center gap-3 mb-2">
              <h3 class="text-lg font-black text-slate-800 group-hover:text-brand transition-colors">{{ problem.title }}</h3>
              <span class="px-2.5 py-0.5 rounded-full text-[9px] font-black uppercase tracking-widest shrink-0"
                :class="statusColor(problem.status?.name)">
                {{ problem.status?.name || 'open' }}
              </span>
            </div>
            <p class="text-sm text-slate-500 font-medium line-clamp-2 mb-3 leading-relaxed">{{ problem.description }}</p>
            <div class="flex flex-wrap items-center gap-4 text-[10px] font-black text-slate-400 uppercase tracking-widest">
              <span v-if="problem.location" class="flex items-center gap-1">
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/></svg>
                {{ problem.location }}
              </span>
              <span v-if="problem.created_at">
                {{ formatDate(problem.created_at) }}
              </span>
            </div>
          </div>
        </div>
      </div>
    </section>
  </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue'
import api from '@/services/api'

const problems = ref([])
const loading = ref(true)
const activeTab = ref('')

const tabs = [
  { label: 'All', value: '' },
  { label: 'Open', value: 'open' },
  { label: 'In Progress', value: 'in_progress' },
  { label: 'Completed', value: 'completed' },
]

const filteredProblems = computed(() => {
  if (!activeTab.value) return problems.value
  return problems.value.filter(p => p.status?.name === activeTab.value)
})

function statusColor(name) {
  const map = {
    open: 'bg-amber-50 text-amber-600 border border-amber-200',
    in_progress: 'bg-blue-50 text-blue-600 border border-blue-200',
    completed: 'bg-emerald-50 text-emerald-600 border border-emerald-200',
  }
  return map[name] || 'bg-slate-100 text-slate-500 border border-slate-200'
}

function statusIcon(name) {
  const map = {
    open: { icon: '🔍', bg: 'bg-amber-50' },
    in_progress: { icon: '⚙️', bg: 'bg-blue-50' },
    completed: { icon: '✅', bg: 'bg-emerald-50' },
  }
  return map[name] || { icon: '📋', bg: 'bg-slate-50' }
}

function formatDate(val) {
  if (!val) return ''
  return new Date(val).toLocaleDateString(undefined, { month: 'short', day: 'numeric', year: 'numeric' })
}

onMounted(async () => {
  try {
    const { data } = await api.get('/community-problems')
    problems.value = data.data || data
  } catch (e) {}
  loading.value = false
})
</script>
