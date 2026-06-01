<template>
  <div class="flex flex-col gap-12 pb-16">
    <section class="bg-white border-b border-slate-100 pt-12 pb-16">
      <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <h1 class="text-4xl md:text-5xl font-black text-slate-900 tracking-tight mb-3">Research Publications</h1>
        <p class="text-lg text-slate-500 font-medium max-w-2xl">Explore published research papers, journals, and conference proceedings from our academic community.</p>
      </div>
    </section>

    <section class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 w-full">
      <div class="flex flex-col sm:flex-row gap-4 mb-8">
        <div class="flex-1 relative">
          <svg class="absolute left-4 top-1/2 -translate-y-1/2 w-5 h-5 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
          <input v-model="search" type="text" placeholder="Search publications by title, author, or keyword..."
            class="w-full pl-12 pr-4 py-3.5 bg-white border border-slate-200 rounded-2xl text-sm font-medium focus:ring-2 focus:ring-brand/30 focus:border-brand outline-none shadow-sm transition-all" />
        </div>
      </div>

      <div v-if="loading" class="space-y-4">
        <div v-for="i in 5" :key="i" class="bg-white rounded-2xl border border-slate-200 p-6 animate-pulse flex gap-6">
          <div class="w-12 h-12 bg-slate-200 rounded-xl shrink-0"></div>
          <div class="flex-1 space-y-3">
            <div class="h-5 w-3/4 bg-slate-100 rounded"></div>
            <div class="h-4 w-1/2 bg-slate-50 rounded"></div>
            <div class="h-3 w-1/4 bg-slate-100 rounded"></div>
          </div>
        </div>
      </div>

      <div v-else-if="filteredPubs.length === 0" class="text-center py-20">
        <div class="h-20 w-20 bg-slate-100 rounded-full flex items-center justify-center mx-auto mb-6 text-3xl">📄</div>
        <h3 class="text-xl font-black text-slate-700 mb-2">No publications found</h3>
        <p class="text-sm text-slate-500 font-medium">Try adjusting your search terms.</p>
      </div>

      <div v-else class="space-y-4">
        <div v-for="pub in filteredPubs" :key="pub.id"
          class="bg-white rounded-2xl border border-slate-200 p-6 shadow-sm hover:shadow-md transition-all group flex gap-6">
          <div class="h-12 w-12 bg-indigo-50 border border-indigo-100 rounded-xl flex items-center justify-center text-indigo-500 shrink-0 group-hover:scale-110 transition-transform">
            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/></svg>
          </div>
          <div class="flex-1 min-w-0">
            <h3 class="text-lg font-black text-slate-800 leading-tight mb-2 group-hover:text-brand transition-colors">{{ pub.title }}</h3>
            <p v-if="pub.authors?.length" class="text-sm text-slate-500 font-medium mb-2">
              {{ pub.authors.map(a => a.user?.name || a.name).join(', ') }}
            </p>
            <div class="flex flex-wrap items-center gap-3 text-[10px] font-black uppercase tracking-widest text-slate-400">
              <span v-if="pub.journal" class="px-2.5 py-1 bg-slate-50 rounded-lg border border-slate-100">{{ pub.journal }}</span>
              <span v-if="pub.published_at">{{ formatDate(pub.published_at) }}</span>
              <span v-if="pub.doi" class="text-brand">DOI: {{ pub.doi }}</span>
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

const publications = ref([])
const loading = ref(true)
const search = ref('')

const filteredPubs = computed(() => {
  if (!search.value) return publications.value
  const q = search.value.toLowerCase()
  return publications.value.filter(p =>
    p.title?.toLowerCase().includes(q) ||
    p.journal?.toLowerCase().includes(q) ||
    p.authors?.some(a => (a.user?.name || a.name || '').toLowerCase().includes(q))
  )
})

function formatDate(val) {
  if (!val) return ''
  return new Date(val).toLocaleDateString(undefined, { month: 'short', day: 'numeric', year: 'numeric' })
}

onMounted(async () => {
  try {
    const { data } = await api.get('/publications')
    publications.value = data.data || data
  } catch (e) {}
  loading.value = false
})
</script>
