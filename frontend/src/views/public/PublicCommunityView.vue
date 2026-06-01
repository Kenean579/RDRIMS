<template>
  <div class="space-y-12 animate-fade pb-32">
    <!-- Premium Header -->
    <section class="bg-white rounded-[40px] border border-slate-200 shadow-sm p-10 md:p-16 relative overflow-hidden group">
      <div class="absolute right-0 top-0 w-96 h-96 bg-emerald-500/5 rounded-full -translate-y-1/2 translate-x-1/2 blur-[80px]"></div>
      <div class="relative z-10 max-w-3xl">
        <h1 class="text-4xl md:text-5xl font-black text-slate-800 tracking-tight leading-tight mb-6">Resilience Intelligence</h1>
        <p class="text-xl text-slate-500 font-medium leading-relaxed">Documenting the tangible outcomes of academic research applied to community-critical challenges.</p>
      </div>
    </section>

    <!-- Operational State Navigation -->
    <div class="bg-white rounded-[32px] border border-slate-200 p-6 shadow-sm relative z-20 flex items-center gap-3 overflow-x-auto custom-scrollbar">
      <button v-for="tab in tabs" :key="tab.value" @click="activeTab = tab.value"
        class="px-8 py-3.5 rounded-2xl text-[10px] font-black uppercase tracking-[0.2em] whitespace-nowrap transition-all"
        :class="activeTab === tab.value
          ? 'bg-slate-900 text-white shadow-2xl shadow-slate-900/20'
          : 'text-slate-400 hover:bg-slate-50 hover:text-slate-600'">
        {{ tab.label }}
      </button>
    </div>

    <!-- Impact Feed -->
    <div v-if="loading" class="space-y-8">
      <div v-for="i in 4" :key="i" class="bg-white rounded-[40px] border border-slate-100 p-12 animate-pulse flex gap-10">
        <div class="w-20 h-20 bg-slate-100 rounded-3xl shrink-0"></div>
        <div class="flex-1 space-y-4">
          <div class="h-6 w-3/4 bg-slate-50 rounded"></div>
          <div class="h-4 w-full bg-slate-50 rounded"></div>
        </div>
      </div>
    </div>

    <div v-else-if="filteredProblems.length === 0" class="bg-white rounded-[40px] border border-slate-200 p-24 text-center">
      <div class="w-24 h-24 bg-slate-50 rounded-full flex items-center justify-center mx-auto mb-8 text-4xl shadow-inner grayscale opacity-50">🌍</div>
      <h3 class="text-2xl font-black text-slate-800 mb-2">Impact Registry Vacant</h3>
      <p class="text-sm text-slate-400 font-medium">Challenges matching this operational state are not currently documented in the public ledger.</p>
    </div>

    <div v-else class="space-y-8">
      <div v-for="problem in filteredProblems" :key="problem.id"
        class="bg-white rounded-[40px] border border-slate-200 p-10 md:p-12 shadow-sm hover:shadow-2xl hover:shadow-brand/5 transition-all group flex flex-col md:flex-row gap-10 relative overflow-hidden"
      >
        <div class="absolute inset-0 bg-brand/5 opacity-0 group-hover:opacity-100 transition-opacity duration-1000"></div>
        
        <!-- Status Iconography -->
        <div class="h-20 w-20 rounded-[32px] flex items-center justify-center shrink-0 text-3xl shadow-inner relative z-10"
          :class="statusIcon(problem.status?.name).bg">
          {{ statusIcon(problem.status?.name).icon }}
        </div>

        <div class="flex-1 min-w-0 relative z-10 space-y-6">
          <div class="flex flex-wrap items-center gap-4">
            <h3 class="text-2xl font-black text-slate-800 group-hover:text-brand transition-colors tracking-tight">{{ problem.title }}</h3>
            <span class="px-4 py-1.5 rounded-xl text-[9px] font-black uppercase tracking-[0.2em] border shadow-sm"
              :class="statusColor(problem.status?.name)">
              {{ problem.status?.name || 'In-Queue' }}
            </span>
          </div>
          
          <p class="text-lg text-slate-500 font-medium line-clamp-3 leading-relaxed italic border-l-4 border-slate-50 pl-8 group-hover:border-brand/20 transition-all">
             "{{ problem.description }}"
          </p>

          <div class="flex flex-wrap items-center gap-8 pt-6 border-t border-slate-50">
            <div v-if="problem.location" class="flex items-center gap-3">
               <div class="w-8 h-8 rounded-lg bg-slate-50 border border-slate-100 flex items-center justify-center text-slate-400">
                  <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/></svg>
               </div>
               <span class="text-[10px] font-black text-slate-400 uppercase tracking-widest">{{ problem.location }}</span>
            </div>
            <div class="flex items-center gap-3">
               <div class="w-8 h-8 rounded-lg bg-slate-50 border border-slate-100 flex items-center justify-center text-slate-400">
                  <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" /></svg>
               </div>
               <span class="text-[10px] font-black text-slate-400 uppercase tracking-widest">{{ formatDate(problem.created_at) }}</span>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue'
import api from '@/services/api'

const problems = ref([])
const loading = ref(true)
const activeTab = ref('')

const tabs = [
  { label: 'Comprehensive Repository', value: '' },
  { label: 'Active Challenges', value: 'open' },
  { label: 'Intervention Stage', value: 'in_progress' },
  { label: 'Solved / Outcome Verified', value: 'completed' },
]

const filteredProblems = computed(() => {
  if (!activeTab.value) return problems.value
  return problems.value.filter(p => p.status?.name === activeTab.value)
})

function statusColor(name) {
  const map = {
    open: 'bg-amber-50 text-amber-600 border-amber-100',
    in_progress: 'bg-indigo-50 text-indigo-600 border-indigo-100',
    completed: 'bg-emerald-50 text-emerald-600 border-emerald-100',
  }
  return map[name] || 'bg-slate-50 text-slate-400 border-slate-100'
}

function statusIcon(name) {
  const map = {
    open: { icon: '🔍', bg: 'bg-amber-50' },
    in_progress: { icon: '⚙️', bg: 'bg-indigo-50' },
    completed: { icon: '✅', bg: 'bg-emerald-50' },
  }
  return map[name] || { icon: '📋', bg: 'bg-slate-50' }
}

function formatDate(val) {
  if (!val) return 'Legacy Record'
  return new Date(val).toLocaleDateString(undefined, { month: 'short', day: 'numeric', year: 'numeric' })
}

onMounted(async () => {
  try {
    const { data } = await api.get('/community-problems')
    problems.value = data.data || data
  } catch (e) {
    console.error('Impact registry synchronization failure.')
  }
  loading.value = false
})
</script>

<style scoped>
.custom-scrollbar::-webkit-scrollbar {
  height: 4px;
}
.custom-scrollbar::-webkit-scrollbar-track {
  background: transparent;
}
.custom-scrollbar::-webkit-scrollbar-thumb {
  background: #f1f5f9;
  border-radius: 10px;
}
</style>
