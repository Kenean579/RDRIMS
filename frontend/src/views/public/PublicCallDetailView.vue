<template>
  <div class="space-y-12 animate-fade pb-32">
    <!-- Skeleton Overlay -->
    <div v-if="loading" class="max-w-7xl mx-auto px-4 w-full pt-16 space-y-12">
      <div class="h-40 bg-white rounded-[40px] border border-slate-100 animate-pulse shadow-sm"></div>
      <div class="grid grid-cols-1 lg:grid-cols-12 gap-10">
        <div class="lg:col-span-8 h-96 bg-white rounded-[40px] border border-slate-100 animate-pulse"></div>
        <div class="lg:col-span-4 h-64 bg-white rounded-[40px] border border-slate-100 animate-pulse"></div>
      </div>
    </div>

    <!-- Error State -->
    <div v-else-if="!call" class="bg-white rounded-[40px] border border-slate-200 p-24 text-center max-w-4xl mx-auto shadow-sm">
      <div class="w-24 h-24 bg-rose-50 text-rose-500 rounded-full flex items-center justify-center mx-auto mb-8 text-4xl shadow-inner grayscale opacity-50">⚠️</div>
      <h1 class="text-3xl font-black text-slate-800 mb-4 tracking-tight">Entry Not Found</h1>
      <p class="text-slate-500 mb-10 max-w-sm mx-auto font-medium">The specific funding call identifier does not intersect with any current registry entry.</p>
      <router-link to="/calls" class="px-10 py-4 bg-slate-900 text-white rounded-2xl font-black uppercase tracking-widest text-xs shadow-xl active:scale-95 transition-all">Retry Archive Search</router-link>
    </div>

    <template v-else>
      <!-- Premium Hero Header -->
      <section class="bg-white rounded-[40px] border border-slate-200 shadow-sm p-10 md:p-16 relative overflow-hidden group">
        <div class="absolute right-0 top-0 w-96 h-96 bg-brand/5 rounded-full -translate-y-1/3 translate-x-1/4 blur-[100px] pointer-events-none"></div>
        
        <div class="max-w-5xl relative z-10">
          <router-link to="/calls" class="inline-flex items-center gap-3 text-[10px] font-black uppercase tracking-[0.3em] text-brand mb-10 hover:-translate-x-2 transition-transform group/back">
            <svg class="w-4 h-4 group-hover/back:scale-125 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M10 19l-7-7m0 0l7-7m-7 7h18" /></svg>
            Registry Feed
          </router-link>
          
          <h1 class="text-4xl md:text-6xl font-black text-slate-800 tracking-tight leading-tight mb-10">{{ call.title }}</h1>
          
          <div class="flex flex-wrap items-center gap-4">
             <span class="px-5 py-2 bg-slate-900 text-white rounded-2xl text-[10px] font-black uppercase tracking-[0.2em] shadow-xl shadow-slate-900/10">
                Phase: {{ call.status?.name || 'Open Enrollment' }}
             </span>
             <div class="flex items-center gap-3 px-5 py-2 bg-slate-50 border-2 border-slate-100 rounded-2xl shadow-sm italic">
                <svg class="w-4 h-4 text-brand" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M12 8v4l3 3" /></svg>
                <span class="text-[11px] font-black text-slate-600 uppercase tracking-widest">{{ formatDate(call.deadline) }} Deadline</span>
             </div>
             <span v-if="call.academic_year" class="px-4 py-2 bg-indigo-50/50 border border-indigo-100 text-indigo-600 rounded-2xl text-[10px] font-black uppercase tracking-[0.2em]">
                Cycle: {{ call.academic_year.name }}
             </span>
          </div>
        </div>
      </section>

      <!-- Detailed Analytics Content -->
      <section class="max-w-7xl mx-auto w-full">
        <div class="grid grid-cols-1 lg:grid-cols-12 gap-10">
          
          <!-- Narrative Analysis -->
          <div class="lg:col-span-8 space-y-10">
             <div class="bg-white rounded-[40px] border border-slate-200 shadow-sm p-12 md:p-16 relative overflow-hidden">
                <div class="absolute inset-0 bg-[radial-gradient(circle_at_top_right,var(--tw-gradient-stops))] from-slate-50/50 via-transparent to-transparent"></div>
                <h2 class="text-[12px] font-black text-brand uppercase tracking-[0.4em] mb-12 flex items-center gap-4 relative z-10">
                  <span class="w-10 h-px bg-brand/30"></span> Mandate Description
                </h2>
                <div class="prose prose-slate max-w-none text-slate-700 font-medium whitespace-pre-wrap leading-relaxed text-xl relative z-10 italic">
                   "{{ call.description }}"
                </div>
             </div>

             <!-- Grid Stats for Metadata -->
             <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                <div class="p-10 bg-slate-50 rounded-[40px] border border-slate-100 shadow-inner group hover:bg-white hover:border-brand/20 transition-all">
                   <p class="text-[10px] font-black text-slate-400 uppercase tracking-[0.2em] mb-4">Institutional Host</p>
                   <p class="text-xl font-black text-slate-800 tracking-tight leading-tight group-hover:text-brand transition-colors">{{ call.university?.name || 'Global Research Network' }}</p>
                </div>
                <div class="p-10 bg-slate-50 rounded-[40px] border border-slate-100 shadow-inner group hover:bg-white hover:border-brand/20 transition-all">
                   <p class="text-[10px] font-black text-slate-400 uppercase tracking-[0.2em] mb-4">Submission ID</p>
                   <p class="text-xl font-black text-slate-800 tracking-tight group-hover:text-brand transition-colors">#CALL-{{ String(call.id).padStart(4, '0') }}</p>
                </div>
             </div>
          </div>

          <!-- Application Interface -->
          <div class="lg:col-span-4 space-y-8 sticky top-30">
             <div class="bg-emerald-500 rounded-[40px] p-10 text-white shadow-2xl shadow-emerald-500/20 relative overflow-hidden group">
                <div class="absolute right-0 top-0 w-24 h-24 bg-white/10 rounded-bl-[40px]"></div>
                <p class="text-[10px] font-black text-emerald-100 uppercase tracking-[0.4em] mb-4 relative z-10">Strategic Allocation</p>
                <div class="flex flex-col items-start gap-1 relative z-10">
                  <span class="text-[10px] font-black uppercase text-emerald-200">Budget Limit</span>
                  <p class="text-4xl font-black tracking-tighter leading-none">{{ formatCurrency(call.budget_limit) }}</p>
                </div>
                <div class="mt-10 h-1.5 w-full bg-emerald-700/50 rounded-full overflow-hidden">
                   <div class="h-full bg-white rounded-full w-[70%] animate-pulse"></div>
                </div>
             </div>
             
             <div class="bg-white rounded-[40px] border border-slate-200 p-10 shadow-sm text-center relative overflow-hidden group">
                <div class="absolute inset-0 bg-brand/5 opacity-0 group-hover:opacity-100 transition-opacity duration-1000"></div>
                <h2 class="text-[11px] font-black text-slate-400 uppercase tracking-[0.3em] mb-8 relative z-10">Initiate Application</h2>
                
                <div v-if="call.status?.name === 'open' || !call.status" class="relative z-10">
                   <router-link :to="`/proposals/create?call_id=${call.id}`" 
                     class="w-full h-16 bg-slate-900 hover:bg-black text-white rounded-2xl font-black uppercase tracking-widest text-[11px] shadow-2xl shadow-slate-900/20 flex items-center justify-center gap-3 active:scale-95 transition-all mb-6"
                   >
                     Submit Research Entry
                     <svg class="w-5 h-5 opacity-40" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M14 5l7 7m0 0l-7 7m7-7H3"/></svg>
                   </router-link>
                   <div class="bg-slate-50 rounded-2xl p-6 border border-slate-100">
                      <p class="text-[9px] font-black text-slate-400 uppercase tracking-widest leading-relaxed">System requires a verified institutional <span class="text-brand">Researcher Identity</span> to proceed.</p>
                   </div>
                </div>
                <div v-else class="relative z-10">
                   <div class="w-full h-16 bg-slate-50 border-2 border-dashed border-slate-200 rounded-2xl flex items-center justify-center text-slate-300 font-black uppercase tracking-[0.2em] text-[10px]">
                      Enrollment Terminal Closed
                   </div>
                   <p class="mt-4 text-[9px] font-black text-slate-400 uppercase tracking-widest">This entry has been moved to the historical archive.</p>
                </div>
             </div>
          </div>

        </div>
      </section>
    </template>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import { useRoute } from 'vue-router'
import api from '@/services/api'
import { formatCurrency } from '@/utils/formatters'

const route = useRoute()
const call = ref(null)
const loading = ref(true)

function formatDate(val) {
  if (!val) return 'Undetermined'
  return new Date(val).toLocaleDateString(undefined, { weekday: 'long', month: 'long', day: 'numeric', year: 'numeric' })
}

onMounted(async () => {
  try {
    const { data } = await api.get(`/calls/${route.params.id}`)
    call.value = data
  } catch (e) {
    console.error('Parity check failed for the requested call identifier.')
  } finally {
    loading.value = false
  }
})
</script>
