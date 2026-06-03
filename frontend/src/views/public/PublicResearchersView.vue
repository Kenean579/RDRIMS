<template>
  <div class="flex flex-col gap-5 pb-6 animate-fade">
    <!-- Header -->
    <div class="card p-5 bg-slate-50 border-slate-100 relative overflow-hidden">
      <div class="relative z-10">
        <h1 class="text-xl font-bold text-slate-900 tracking-tight">Researcher Directory</h1>
        <p class="text-slate-500 font-medium mt-1">Connect with experts and principal investigators across our network.</p>
      </div>
      <div class="absolute right-0 top-0 w-32 h-32 bg-brand/5 rounded-full translate-x-8 -translate-y-8"></div>
    </div>

    <!-- Filters -->
    <div class="card p-5 flex flex-col md:flex-row gap-5 items-end">
      <div class="flex-1 w-full relative">
        <label class="block text-[11px] text-slate-500 font-bold capitalize tracking-widest mb-2 ml-1">Search Experts</label>
        <div class="relative group">
          <span class="absolute left-3.5 top-1/2 -translate-y-1/2 text-slate-400">
            <svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
          </span>
          <input v-model="search" type="text" placeholder="Search by name, expertise, or department..." class="input pl-10" />
        </div>
      </div>
    </div>

    <!-- Content -->
    <div v-if="loading" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
      <div v-for="i in 6" :key="i" class="card h-40 animate-pulse"></div>
    </div>

    <div v-else-if="filteredResearchers.length === 0" class="card p-6 text-center text-slate-400 text-xs font-bold capitalize tracking-widest italic">
      No researchers found matching your criteria.
    </div>

    <div v-else class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
      <div v-for="user in filteredResearchers" :key="user.id" class="card p-6 flex flex-col group card-hover relative border-t-4 border-t-brand/10 hover:border-t-brand transition-all">
        <div class="flex items-center gap-4 mb-4">
           <div class="w-12 h-12 rounded-2xl overflow-hidden bg-brand-light border border-brand/20 shrink-0 shadow-sm">
             <img
               v-if="imageUrl(user.profile_image)"
               :src="imageUrl(user.profile_image)"
               :alt="user.name"
               class="w-full h-full object-cover"
             />
             <div v-else class="w-full h-full flex items-center justify-center text-brand font-bold text-sm capitalize">
               {{ user.name.charAt(0) }}
             </div>
           </div>
           <div class="min-w-0">
             <h3 class="text-sm font-bold text-slate-900 leading-tight truncate">{{ user.name }}</h3>
             <p class="text-[10px] font-bold text-slate-400 capitalize tracking-widest mt-1">{{ user.department?.name || 'Researcher' }}</p>
           </div>
        </div>
        
        <div class="flex flex-wrap gap-1.5 mb-6">
          <span v-for="ex in user.expertise?.slice(0, 3)" :key="ex.id" class="px-2 py-0.5 bg-slate-50 text-slate-500 text-[9px] font-bold capitalize tracking-widest rounded border border-slate-100">
            {{ ex.name }}
          </span>
          <span v-if="user.expertise?.length > 3" class="text-[9px] font-bold text-slate-300 self-center">+{{ user.expertise.length - 3 }}</span>
        </div>

        <div class="mt-auto pt-4 border-t border-slate-50 flex items-center justify-between">
           <a :href="`mailto:${user.email}`" class="text-[10px] font-bold text-brand capitalize tracking-widest hover:underline">Contact</a>
           <div class="flex gap-2">
             <a v-if="user.orcid_id" :href="`https://orcid.org/${user.orcid_id}`" target="_blank" class="text-slate-300 hover:text-brand transition-colors" title="ORCID Profile">
               <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                 <path stroke-linecap="round" stroke-linejoin="round" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14" />
               </svg>
             </a>
           </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue'
import { imageUrl } from '@/utils/formatters'
import api from '@/services/api'

const researchers = ref([])
const loading = ref(true)
const search = ref('')

const filteredResearchers = computed(() => {
  return researchers.value.filter(u => {
    return !search.value || 
           u.name?.toLowerCase().includes(search.value.toLowerCase()) || 
           u.expertise?.some(ex => ex.name.toLowerCase().includes(search.value.toLowerCase()))
  })
})

onMounted(async () => {
  try {
    const { data } = await api.get('/public/researchers')
    researchers.value = data.data || data
  } catch (e) {}
  loading.value = false
})
</script>
