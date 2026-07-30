<template>
  <div class="flex flex-col gap-16 pb-4">
    
    <!-- HERO SECTION -->
    <section class="relative bg-white pt-20 pb-24 overflow-hidden border-b border-slate-100">
      <div class="absolute inset-0 z-0 bg-[radial-gradient(circle_at_top_right,var(--tw-gradient-stops))] from-brand/5 via-transparent to-transparent"></div>
      
      <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-5 relative z-10 text-center">
        <h1 class="text-2xl md:text-xl font-bold text-slate-900 tracking-tight leading-tight mb-6">
          Transforming Higher Education
        </h1>
        <p class="text-xl md:text-2xl font-bold text-slate-800 mb-5">
          The Central Hub for <span class="text-transparent bg-clip-text bg-gradient-to-r from-brand to-indigo-600">Academic Research</span>
        </p>
        <p class="mt-4 text-lg text-slate-500 max-w-2xl mx-auto font-medium leading-relaxed mb-5">
          Discover ground-breaking projects, browse the latest publications, explore open research calls, and track our ongoing community impact across universities.
        </p>
        <div class="flex items-center justify-center gap-4">
          <a href="#calls-section" class="px-5 py-3.5 bg-brand text-white font-bold rounded-2xl hover:shadow-brand/50 hover:-translate-y-0.5 transition-all">
            Explore Open Calls
          </a>
          <router-link to="/publications" class="px-5 py-3.5 bg-white text-slate-700 font-bold rounded-2xl shadow-sm border border-slate-100 hover:border-brand hover:text-brand transition-all">
            Browse Publications
          </router-link>
        </div>
      </div>
    </section>

    <!-- STATS BAR -->
    <section class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-5 -mt-24 relative z-20">
      <div class="bg-white rounded-2xl shadow-xl shadow-slate-200/50 border border-slate-100 p-5">
        <div class="grid grid-cols-2 md:grid-cols-4 gap-5 divide-x divide-slate-100">
          <div class="text-center px-4">
            <p class="text-2xl font-bold text-slate-800 mb-2 tracking-tight">{{ stats.universities }}+</p>
            <p class="text-xs font-medium text-slate-500">Universities</p>
          </div>
          <div class="text-center px-4">
            <p class="text-2xl font-bold text-brand mb-2 tracking-tight">{{ stats.openCalls }}+</p>
            <p class="text-xs font-medium text-slate-500">Open Calls</p>
          </div>
          <div class="text-center px-4">
            <p class="text-2xl font-bold text-slate-800 mb-2 tracking-tight">{{ stats.publications }}+</p>
            <p class="text-xs font-medium text-slate-500">Publications</p>
          </div>
          <div class="text-center px-4">
            <p class="text-2xl font-bold text-emerald-500 mb-2 tracking-tight">{{ stats.problemsSolved }}+</p>
            <p class="text-xs font-medium text-slate-500">Problems Solved</p>
          </div>
        </div>
      </div>
    </section>

  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import api from '@/services/api'

const stats = ref({
  universities: 0,
  openCalls: 0,
  publications: 0,
  problemsSolved: 0
})

const loading = ref(true)

onMounted(async () => {
  await fetchStats()
})

async function fetchStats() {
  loading.value = true
  try {
    // Use Promise.allSettled to handle individual endpoint failures gracefully
    const [uniRes, callsRes, pubRes, commRes] = await Promise.allSettled([
      api.get('/universities').catch(() => ({ data: { data: [] } })),
      api.get('/calls', { params: { status: 'open', per_page: 1 } }).catch(() => ({ data: { meta: { total: 0 } } })),
      api.get('/publications', { params: { per_page: 1 } }).catch(() => ({ data: { meta: { total: 0 } } })),
      api.get('/community-problems', { params: { status: 'completed', per_page: 1 } }).catch(() => ({ data: { meta: { total: 0 } } }))
    ])
    
    stats.value.universities = uniRes.status === 'fulfilled' ? (uniRes.value.data?.data?.length || uniRes.value.data?.length || 0) : 0
    stats.value.openCalls = callsRes.status === 'fulfilled' ? (callsRes.value.data?.meta?.total || callsRes.value.data?.total || 0) : 0
    stats.value.publications = pubRes.status === 'fulfilled' ? (pubRes.value.data?.meta?.total || pubRes.value.data?.total || 0) : 0
    stats.value.problemsSolved = commRes.status === 'fulfilled' ? (commRes.value.data?.meta?.total || commRes.value.data?.total || 0) : 0
  } catch (err) {
    console.error('Failed to load stats', err)
  } finally {
    loading.value = false
  }
}
</script>