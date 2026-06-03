<template>
  <div class="flex flex-col gap-6 card">
    <!-- Header -->
    <div class="section-header">
      <div>
        <h1 class="section-title">Partners & Collaborators</h1>
        <p class="section-subtitle">External organizations, universities, and industry partners contributing to research</p>
      </div>
      <button @click="fetchPartners" class="btn btn-secondary">
        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" /></svg>
        Refresh
      </button>
    </div>

    <!-- Content -->
    <div v-if="loading" class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-4 lg:grid-cols-5 gap-4">
      <div v-for="i in 10" :key="i" class="card p-6 h-40 animate-pulse bg-slate-50/50"></div>
    </div>

    <div v-else-if="partners.length === 0" class="card">
      <EmptyState icon="🤝" title="No partners found" description="Connect with external organizations and industry leaders to expand your research scope." />
    </div>

    <div v-else class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-4 lg:grid-cols-5 gap-5">
      <div v-for="p in partners" :key="p.id" class="card p-6 text-center group card-hover flex flex-col items-center">
        <div class="w-16 h-16 bg-slate-50 rounded-2xl flex items-center justify-center text-3xl group-hover:scale-110 group-hover:bg-blue-50 transition duration-300 mb-4 shadow-sm border border-slate-100">
           {{ getLogo(p) }}
        </div>
        <h3 class="text-sm font-bold text-slate-800 line-clamp-1 group-hover:text-blue-600 transition" :title="p.name">{{ p.name }}</h3>
        <p class="text-[10px] font-bold text-slate-400 mt-2 capitalize tracking-widest">{{ p.type?.name || 'Partner' }}</p>
        
        <div class="mt-4 pt-4 border-t border-slate-50 w-full">
           <a v-if="p.website_url" :href="p.website_url" target="_blank" class="text-[11px] font-bold text-blue-600 hover:text-blue-700 underline-offset-4 hover:underline transition">
              Open Website
           </a>
           <span v-else class="text-[11px] text-slate-300 italic font-medium">No website</span>
        </div>
      </div>
    </div>
  </div>
</template>
<script setup>
import { ref, onMounted } from 'vue'
import api from '@/services/api'
import EmptyState from '@/components/EmptyState.vue'
const loading = ref(true); const partners = ref([])
async function fetchPartners() { try { const { data } = await api.get('/partners'); partners.value = data.data || data } catch (e) {} finally { loading.value = false } }
function getLogo(p) { if (p.type?.name === 'university') return '🎓'; if (p.type?.name === 'industry') return '🏭'; if (p.type?.name === 'ngo') return '🌐'; return '🤝' }
onMounted(fetchPartners)
</script>
