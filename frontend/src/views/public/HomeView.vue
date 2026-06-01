<template>
  <div class="flex flex-col gap-16 pb-16">
    
    <!-- HERO SECTION -->
    <section class="relative bg-white pt-20 pb-24 overflow-hidden border-b border-slate-100">
      <div class="absolute inset-0 z-0 bg-[radial-gradient(circle_at_top_right,var(--tw-gradient-stops))] from-brand/5 via-transparent to-transparent"></div>
      
      <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 relative z-10 text-center">
        <span class="inline-block py-1 px-3 rounded-full bg-brand/10 text-brand text-xs font-black tracking-widest uppercase mb-6 shadow-sm border border-brand/20">
          Transforming Higher Education
        </span>
        <h1 class="text-5xl md:text-6xl font-black text-slate-900 tracking-tight leading-tight mb-8">
          The Central Hub for <br class="hidden md:block"/>
          <span class="text-transparent bg-clip-text bg-linear-to-r from-brand to-brand-dark">Academic Research</span>
        </h1>
        <p class="mt-4 text-lg text-slate-500 max-w-2xl mx-auto font-medium leading-relaxed mb-10">
          Discover ground-breaking projects, browse the latest publications, explore open research calls, and track our ongoing community impact across universities.
        </p>
        <div class="flex items-center justify-center gap-4">
          <router-link to="/calls" class="px-8 py-3.5 bg-brand text-white font-bold rounded-xl shadow-lg shadow-brand/30 hover:shadow-brand/50 hover:-translate-y-0.5 transition-all">
            Explore Open Calls
          </router-link>
          <router-link to="/publications" class="px-8 py-3.5 bg-white text-slate-700 font-bold rounded-xl shadow-sm border border-slate-200 hover:border-brand hover:text-brand transition-all">
            Browse Publications
          </router-link>
        </div>
      </div>
    </section>

    <!-- STATS BAR -->
    <section class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 -mt-24 relative z-20">
      <div class="bg-white rounded-2xl shadow-xl shadow-slate-200/50 border border-slate-100 p-8">
        <div class="grid grid-cols-2 md:grid-cols-4 gap-8 divide-x divide-slate-100">
          <div class="text-center px-4">
            <p class="text-4xl font-black text-slate-800 mb-2 tracking-tighter">{{ stats.universities }}</p>
            <p class="text-xs font-bold uppercase tracking-widest text-slate-500">Universities</p>
          </div>
          <div class="text-center px-4">
            <p class="text-4xl font-black text-brand mb-2 tracking-tighter">{{ stats.openCalls }}</p>
            <p class="text-xs font-bold uppercase tracking-widest text-slate-500">Open Calls</p>
          </div>
          <div class="text-center px-4">
            <p class="text-4xl font-black text-slate-800 mb-2 tracking-tighter">{{ stats.publications }}</p>
            <p class="text-xs font-bold uppercase tracking-widest text-slate-500">Publications</p>
          </div>
          <div class="text-center px-4">
            <p class="text-4xl font-black text-emerald-500 mb-2 tracking-tighter">{{ stats.communityImpact }}</p>
            <p class="text-xs font-bold uppercase tracking-widest text-slate-500">Problems Solved</p>
          </div>
        </div>
      </div>
    </section>

    <!-- OPEN CALLS SECTION -->
    <section class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 w-full">
      <div class="flex justify-between items-end mb-8">
        <div>
          <h2 class="text-2xl font-black text-slate-800 tracking-tight">Open Research Calls</h2>
          <p class="text-sm text-slate-500 mt-1 font-medium">Opportunities for funding and collaboration</p>
        </div>
        <router-link to="/calls" class="text-sm font-bold text-brand hover:text-brand-dark flex items-center gap-1 group">
          View all 
          <svg class="w-4 h-4 group-hover:translate-x-1 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" /></svg>
        </router-link>
      </div>
      
      <div v-if="loading.calls" class="grid grid-cols-1 md:grid-cols-3 gap-6">
        <LoadingSkeleton v-for="i in 3" :key="i" class="h-48 rounded-2xl" />
      </div>
      <div v-else-if="calls.length === 0" class="bg-white rounded-2xl border border-slate-200 p-12 text-center">
        <EmptyState title="No open calls" message="There are currently no open calls. Check back later." icon="📣" />
      </div>
      <div v-else class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
        <router-link v-for="call in calls" :key="call.id" :to="`/calls/${call.id}`" class="bg-white rounded-2xl border border-slate-200 p-6 shadow-sm hover:shadow-md hover:-translate-y-1 transition-all group relative">
          <div class="absolute top-0 right-0 p-6 flex justify-end">
            <StatusBadge :status="{ name: 'open', label: 'Open' }" />
          </div>
          <p class="text-xs font-bold uppercase tracking-widest text-brand mb-3">{{ call.university?.name || 'Central' }}</p>
          <h3 class="text-lg font-black text-slate-800 leading-tight mb-3 group-hover:text-brand transition-colors pr-16 line-clamp-2">
            {{ call.title }}
          </h3>
          <p class="text-sm text-slate-500 line-clamp-3 mb-6 font-medium leading-relaxed">{{ call.description }}</p>
          
          <div class="flex items-center gap-3 pt-6 border-t border-slate-100">
            <div class="h-10 w-10 rounded-xl bg-rose-50 flex items-center justify-center text-rose-500">
              <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
            </div>
            <div>
              <p class="text-[10px] font-black uppercase tracking-widest text-slate-400">Deadline</p>
              <p class="text-sm font-bold text-slate-700" :class="{ 'text-rose-600': isUrgent(call.deadline) }">
                {{ formatDate(call.deadline) }}
              </p>
            </div>
          </div>
        </router-link>
      </div>
    </section>

    <!-- OTHER SECTIONS TO IMPLEMENT: Events, Publications, Community, Researchers, Partners -->
    
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import api from '@/services/api'
import LoadingSkeleton from '@/components/LoadingSkeleton.vue'
import EmptyState from '@/components/EmptyState.vue'
import StatusBadge from '@/components/StatusBadge.vue'

const stats = ref({
  universities: 0,
  openCalls: 0,
  publications: 0,
  communityImpact: 0
})

const calls = ref([])
const loading = ref({
  stats: true,
  calls: true
})

onMounted(async () => {
  fetchStats()
  fetchCalls()
})

async function fetchStats() {
  try {
    // These could be parallelized
    const [uniRes, callsRes, pubRes, commRes] = await Promise.all([
      api.get('/universities'),
      api.get('/calls?status=open&per_page=1'),
      api.get('/publications?per_page=1'),
      api.get('/community-problems?status=completed&per_page=1')
    ])
    
    // Fallbacks if data structures vary
    stats.value.universities = uniRes.data?.data?.length || uniRes.data?.length || 0
    stats.value.openCalls = callsRes.data?.meta?.total || callsRes.data?.total || 0
    stats.value.publications = pubRes.data?.meta?.total || pubRes.data?.total || 0
    stats.value.communityImpact = commRes.data?.meta?.total || commRes.data?.total || 0
  } catch (err) {
    console.error('Failed to load stats', err)
  }
}

async function fetchCalls() {
  try {
    const res = await api.get('/calls?status=open&per_page=6')
    calls.value = res.data?.data || res.data || []
  } catch(e) {}
  loading.value.calls = false
}

function formatDate(val) {
  if (!val) return 'N/A'
  return new Date(val).toLocaleDateString(undefined, { month: 'short', day: 'numeric', year: 'numeric' })
}

function isUrgent(dateStr) {
  if (!dateStr) return false
  const diffTime = Math.abs(new Date(dateStr) - new Date())
  const diffDays = Math.ceil(diffTime / (1000 * 60 * 60 * 24))
  return diffDays < 7
}
</script>
