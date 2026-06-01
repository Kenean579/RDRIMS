<template>
  <div class="space-y-8 animate-fade pb-16">
    <!-- Header + Search Bar -->
    <div class="bg-white rounded-3xl border border-slate-200 shadow-sm p-8 relative overflow-hidden">
      <div class="absolute right-0 top-0 w-64 h-64 bg-brand/5 rounded-full -translate-y-1/2 translate-x-1/4"></div>
      <div class="relative z-10 mb-8">
        <h1 class="text-3xl font-black text-slate-800 tracking-tight mb-1">Global Search</h1>
        <p class="text-slate-500 text-sm">Search across proposals, projects, publications, patents, events, and more.</p>
      </div>

      <!-- Search Input -->
      <div class="relative z-10 flex gap-3">
        <div class="flex-1 relative group">
          <svg class="absolute left-5 top-1/2 -translate-y-1/2 w-5 h-5 text-slate-400 group-focus-within:text-brand transition-colors" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
          </svg>
          <input
            v-model="query"
            type="text"
            placeholder="Type a keyword and press Enter…"
            class="w-full bg-slate-50 border-2 border-slate-100 rounded-2xl pl-14 pr-6 py-4 text-sm font-bold text-slate-700 focus:border-brand focus:ring-4 focus:ring-brand/10 outline-none transition-all placeholder:text-slate-400"
            @keyup.enter="performSearch"
          />
        </div>
        <button
          @click="performSearch"
          class="bg-brand text-white px-8 rounded-2xl font-black uppercase tracking-widest text-xs shadow-lg shadow-brand/20 hover:bg-brand-dark hover:scale-105 active:scale-95 transition-all"
          :disabled="loading"
        >
          <svg v-if="loading" class="w-5 h-5 animate-spin" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"/></svg>
          <span v-else>Search</span>
        </button>
      </div>
    </div>

    <!-- Initial State -->
    <div v-if="!searched" class="bg-white rounded-3xl border border-slate-200 shadow-sm p-20 text-center">
      <div class="w-20 h-20 bg-slate-50 text-slate-300 rounded-full flex items-center justify-center mx-auto mb-6 border-2 border-dashed border-slate-200">
        <svg class="w-10 h-10" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" /></svg>
      </div>
      <p class="text-sm font-black text-slate-400 uppercase tracking-widest mb-2">RDRIMS Knowledge Base</p>
      <p class="text-xs text-slate-300 font-medium max-w-xs mx-auto">Enter a keyword above to search across all institutional research records.</p>

      <!-- Category Hints -->
      <div class="flex flex-wrap justify-center gap-3 mt-8">
        <span v-for="cat in categories" :key="cat.label"
          class="flex items-center gap-1.5 px-4 py-2 bg-slate-50 text-slate-400 rounded-xl border border-slate-100 text-[10px] font-black uppercase tracking-widest"
        >
          <span>{{ cat.icon }}</span> {{ cat.label }}
        </span>
      </div>
    </div>

    <!-- No Results -->
    <div v-else-if="results.length === 0 && !loading" class="bg-white rounded-3xl border border-slate-200 shadow-sm p-20 text-center">
      <div class="w-20 h-20 bg-amber-50 text-amber-400 rounded-full flex items-center justify-center mx-auto mb-6 border border-amber-100">
        <svg class="w-10 h-10" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9.172 16.172a4 4 0 015.656 0M9 10h.01M15 10h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
      </div>
      <p class="text-sm font-black text-slate-600 mb-1">No results for <span class="text-brand">"{{ query }}"</span></p>
      <p class="text-xs text-slate-400 font-medium">Try broader keywords or check your spelling.</p>
    </div>

    <!-- Results -->
    <div v-else-if="results.length > 0" class="space-y-4">
      <!-- Count Banner -->
      <div class="flex items-center gap-4 px-2">
        <p class="text-[11px] font-black text-slate-400 uppercase tracking-widest">
          {{ results.length }} result{{ results.length !== 1 ? 's' : '' }} for
          <span class="text-slate-700">"{{ query }}"</span>
        </p>
      </div>

      <!-- Result Cards -->
      <div
        v-for="(result, index) in results"
        :key="index"
        @click="navigateToResult(result)"
        class="group bg-white rounded-3xl border border-slate-200 shadow-sm p-6 cursor-pointer hover:border-brand/30 hover:shadow-xl hover:shadow-brand/5 transition-all"
      >
        <div class="flex items-start gap-5">
          <!-- Icon -->
          <div class="w-12 h-12 rounded-2xl bg-slate-50 border border-slate-100 flex items-center justify-center text-2xl shrink-0 group-hover:border-brand/20 group-hover:bg-brand/5 transition-all">
            {{ getIcon(result.type) }}
          </div>

          <!-- Content -->
          <div class="flex-1 min-w-0">
            <div class="flex items-center gap-3 mb-1.5">
              <span class="px-2.5 py-0.5 bg-brand/10 text-brand rounded-lg text-[10px] font-black uppercase tracking-widest border border-brand/10">
                {{ result.type }}
              </span>
            </div>
            <p class="text-base font-black text-slate-800 group-hover:text-brand transition-colors leading-tight line-clamp-1 mb-1">
              {{ result.title }}
            </p>
            <p v-if="result.description" class="text-xs text-slate-500 font-medium line-clamp-2 leading-relaxed">
              {{ result.description?.substring(0, 180) }}
            </p>
          </div>

          <!-- Arrow -->
          <div class="shrink-0 mt-1">
            <svg class="w-5 h-5 text-slate-300 group-hover:text-brand group-hover:translate-x-1 transition-all" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M13 7l5 5m0 0l-5 5m5-5H6" />
            </svg>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import { useRouter, useRoute } from 'vue-router'
import api from '@/services/api'

const router = useRouter()
const route = useRoute()
const query = ref('')
const results = ref([])
const loading = ref(false)
const searched = ref(false)

const categories = [
  { label: 'Proposals', icon: '📝' },
  { label: 'Projects', icon: '📁' },
  { label: 'Publications', icon: '📄' },
  { label: 'Outputs', icon: '📤' },
  { label: 'Patents', icon: '💡' },
  { label: 'Events', icon: '📅' },
  { label: 'Partners', icon: '🤝' },
  { label: 'Community', icon: '🏘️' },
]

onMounted(() => {
  if (route.query.q) {
    query.value = route.query.q
    performSearch()
  }
})

function getIcon(type) {
  const icons = {
    proposal: '📝', project: '📁', publication: '📄', output: '📤',
    patent: '💡', event: '📅', partner: '🤝', community: '🏘️', user: '👤'
  }
  return icons[type] || '📌'
}

async function performSearch() {
  if (!query.value.trim()) return
  loading.value = true
  searched.value = true
  try {
    const { data } = await api.get('/search', { params: { query: query.value } })
    results.value = data.data || data
  } catch (e) {
    results.value = []
  } finally {
    loading.value = false
  }
}

function navigateToResult(result) {
  const routes = {
    proposal: `/proposals/${result.id}`,
    project: `/projects/${result.id}`,
    publication: `/publications/${result.id}`,
    output: `/outputs/${result.id}`,
    patent: `/patents/${result.id}`,
    event: `/events/${result.id}`,
    partner: `/partners/${result.id}`,
    community: `/community`,
    user: `/users/${result.id}`,
  }
  const path = routes[result.type]
  if (path) router.push(path)
}
</script>
