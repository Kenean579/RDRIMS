<template>
  <div class="flex flex-col gap-8 pb-12 animate-fade">
    <!-- Header -->
    <div class="card p-8 bg-slate-50 border-slate-100 relative overflow-hidden">
      <div class="relative z-10">
        <h1 class="text-3xl font-black text-slate-900 tracking-tight">Researcher Directory</h1>
        <p class="text-slate-500 font-medium mt-1">Connect with experts and principal investigators across our network.</p>
      </div>
      <div class="absolute right-0 top-0 w-32 h-32 bg-brand/5 rounded-full translate-x-8 -translate-y-8"></div>
    </div>

    <!-- Filters -->
    <div class="card p-5 flex flex-col md:flex-row gap-5 items-end">
      <div class="flex-1 w-full relative">
        <label class="block text-[11px] text-slate-500 font-black uppercase tracking-widest mb-2 ml-1">Search Experts</label>
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

    <div v-else-if="filteredResearchers.length === 0" class="card p-12 text-center text-slate-400 text-xs font-black uppercase tracking-widest italic">
      No researchers found matching your criteria.
    </div>

    <div v-else class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
      <div v-for="user in filteredResearchers" :key="user.id" class="card p-6 flex flex-col group card-hover relative border-t-4 border-t-brand/10 hover:border-t-brand transition-all">
        <div class="flex items-center gap-4 mb-4">
           <div class="w-12 h-12 rounded-2xl bg-brand-light text-brand flex items-center justify-center font-black text-xs uppercase shadow-sm">
             {{ user.name.charAt(0) }}
           </div>
           <div class="min-w-0">
             <h3 class="text-sm font-black text-slate-900 leading-tight truncate">{{ user.name }}</h3>
             <p class="text-[10px] font-black text-slate-400 uppercase tracking-widest mt-1">{{ user.department?.name || 'Researcher' }}</p>
           </div>
        </div>
        
        <div class="flex flex-wrap gap-1.5 mb-6">
          <span v-for="ex in user.expertise?.slice(0, 3)" :key="ex.id" class="px-2 py-0.5 bg-slate-50 text-slate-500 text-[9px] font-black uppercase tracking-widest rounded border border-slate-100">
            {{ ex.name }}
          </span>
          <span v-if="user.expertise?.length > 3" class="text-[9px] font-black text-slate-300 self-center">+{{ user.expertise.length - 3 }}</span>
        </div>

        <div class="mt-auto pt-4 border-t border-slate-50 flex items-center justify-between">
           <a :href="`mailto:${user.email}`" class="text-[10px] font-black text-brand uppercase tracking-widest hover:underline">Contact</a>
           <div class="flex gap-2">
             <a v-if="user.orcid_id" :href="`https://orcid.org/${user.orcid_id}`" target="_blank" class="text-slate-300 hover:text-brand transition-colors"><svg class="w-4 h-4" fill="currentColor" viewBox="0 0 24 24"><path d="M12 0C5.372 0 0 5.372 0 12s5.372 12 12 12 12-5.372 12-12S18.628 0 12 0zM7.369 4.378c.541 0 .942.4c.942.942 0 .541-.4.942-.942.942-.541 0-.942-.4-.942-.942 0-.541.4-.942.942-.942zM4.82 6.404h1.181v13.218H4.819V6.404zm5.507 0h3.585c3.551 0 5.736 2.502 5.736 6.609 0 4.107-2.185 6.609-5.736 6.609h-3.585V6.404zm1.181 1.181v10.856h2.404c2.81 0 4.555-1.921 4.555-5.428 0-3.507-1.745-5.428-4.555-5.428h-2.404z"/></svg></a>
           </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue'
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
    const { data } = await api.get('/users')
    researchers.value = (data.data || data).filter(u => u.roles?.some(r => r.name === 'researcher'))
  } catch (e) {}
  loading.value = false
})
</script>
