<template>
  <div class="flex flex-col gap-8 pb-12 animate-fade">
    <!-- Header -->
    <div class="card p-8 bg-slate-50 border-slate-100 relative overflow-hidden">
      <div class="relative z-10">
        <h1 class="text-3xl font-black text-slate-900 tracking-tight">Research Repository</h1>
        <p class="text-slate-500 font-medium mt-1">Institutional publications, journal papers, and technical reports.</p>
      </div>
      <div class="absolute right-0 top-0 w-32 h-32 bg-brand/5 rounded-full translate-x-8 -translate-y-8"></div>
    </div>

    <!-- Filters -->
    <div class="card p-5 flex flex-col md:flex-row gap-5 items-end">
      <div class="flex-1 w-full relative">
        <label class="block text-[11px] text-slate-500 font-black capitalize tracking-widest mb-2 ml-1">Search Publications</label>
        <div class="relative group">
          <span class="absolute left-3.5 top-1/2 -translate-y-1/2 text-slate-400">
            <svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
          </span>
          <input v-model="search" type="text" placeholder="Search by title, author, or journal..." class="input pl-10" />
        </div>
      </div>
    </div>

    <!-- Content -->
    <div v-if="loading" class="space-y-6">
      <div v-for="i in 3" :key="i" class="card h-40 animate-pulse"></div>
    </div>

    <div v-else-if="filteredPubs.length === 0" class="card p-12 text-center text-slate-400 text-xs font-black capitalize tracking-widest italic">
      No publications found in our repository.
    </div>

    <div v-else class="space-y-6">
      <div v-for="pub in filteredPubs" :key="pub.id" class="card p-6 flex flex-col md:flex-row gap-6 group card-hover relative border-l-4 border-l-transparent hover:border-l-brand transition-all">
        <div class="w-12 h-12 rounded-xl bg-blue-50 text-blue-600 flex items-center justify-center shrink-0">
          <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/></svg>
        </div>
        
        <div class="flex-1 min-w-0">
          <h3 class="text-lg font-black text-slate-800 leading-tight mb-2 group-hover:text-brand transition-colors">{{ pub.title }}</h3>
          <p class="text-sm font-bold text-slate-500 mb-4">{{ pub.authors?.map(a => a.user?.name || a.name).join(', ') }}</p>
          
          <div class="flex flex-wrap items-center gap-4 text-[10px] font-black capitalize tracking-widest text-slate-400">
            <span v-if="pub.journal" class="p-2 bg-slate-50 rounded-lg border border-slate-100 text-slate-600">{{ pub.journal }}</span>
            <span class="flex items-center gap-1.5"><svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg> {{ formatDate(pub.publication_date) }}</span>
            <span v-if="pub.doi" class="text-brand">DOI: {{ pub.doi }}</span>
          </div>
        </div>

        <div class="flex items-end">
          <a v-if="pub.doi || pub.scholar_url" :href="pub.scholar_url || `https://doi.org/${pub.doi}`" target="_blank" class="btn btn-secondary text-[10px] font-black capitalize tracking-widest px-4 h-9">
            Read Online
          </a>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue'
import api from '@/services/api'
import { formatDate } from '@/utils/formatters'

const publications = ref([])
const loading = ref(true)
const search = ref('')

const filteredPubs = computed(() => {
  const q = search.value.toLowerCase()
  return publications.value.filter(p =>
    !q || p.title?.toLowerCase().includes(q) ||
    p.journal?.toLowerCase().includes(q) ||
    p.authors?.some(a => (a.user?.name || a.name || '').toLowerCase().includes(q))
  )
})

onMounted(async () => {
  try {
    const { data } = await api.get('/publications')
    publications.value = data.data || data
  } catch (e) {}
  loading.value = false
})
</script>
