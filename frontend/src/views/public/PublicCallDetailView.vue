<template>
  <div class="flex flex-col gap-12 pb-16 animate-fade">
    <div v-if="loading" class="max-w-4xl mx-auto px-4 w-full pt-16">
      <div class="h-10 w-3/4 bg-slate-200 rounded-lg animate-pulse mb-6"></div>
      <div class="h-6 w-1/2 bg-slate-100 rounded-lg animate-pulse mb-12"></div>
      <div class="h-64 bg-slate-50 border border-slate-100 rounded-2xl animate-pulse"></div>
    </div>

    <div v-else-if="!call" class="text-center py-24">
      <h1 class="text-2xl font-black text-slate-800 mb-2">Call not found</h1>
      <p class="text-slate-500 mb-6">The research call you are looking for does not exist or has been removed.</p>
      <router-link to="/calls" class="btn btn-primary px-8">Browse Open Calls</router-link>
    </div>

    <template v-else>
      <!-- Hero Header -->
      <section class="bg-white border-b border-slate-100 pt-16 pb-12">
        <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">
          <router-link to="/calls" class="inline-flex items-center gap-1.5 text-xs font-black uppercase tracking-widest text-brand mb-6 hover:-translate-x-1 transition-transform">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M10 19l-7-7m0 0l7-7m-7 7h18" /></svg>
            Back to Calls
          </router-link>
          
          <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-6 mb-6">
            <h1 class="text-3xl md:text-5xl font-black text-slate-900 tracking-tight leading-tight">{{ call.title }}</h1>
          </div>
          
          <div class="flex flex-wrap items-center gap-4 text-xs font-black uppercase tracking-widest">
            <span class="px-3 py-1.5 rounded-full"
              :class="call.status?.name === 'open' ? 'bg-emerald-50 text-emerald-600 border border-emerald-200' : 'bg-slate-100 text-slate-500 border border-slate-200'">
              {{ call.status?.name || 'open' }}
            </span>
            <span class="flex items-center gap-1.5 text-slate-500 bg-slate-50 px-3 py-1.5 rounded-full border border-slate-200">
              <svg class="w-4 h-4 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" /></svg>
              Ends on {{ formatDate(call.deadline) }}
            </span>
            <span v-if="call.academic_year" class="text-slate-400 bg-slate-50 px-3 py-1.5 rounded-full border border-slate-200">
              Target Year: {{ call.academic_year.name }}
            </span>
          </div>
        </div>
      </section>

      <!-- Main Content -->
      <section class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 w-full">
        <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
          
          <!-- Left: Details -->
          <div class="md:col-span-2 space-y-8">
             <div class="bg-white rounded-3xl border border-slate-200 shadow-sm p-8">
                <h2 class="text-xs font-black text-slate-400 uppercase tracking-widest mb-6 flex items-center gap-2">
                  <span class="w-1 h-3 bg-brand rounded-full"></span>
                  Call Description
                </h2>
                <div class="prose prose-slate max-w-none text-slate-700 font-medium whitespace-pre-wrap leading-relaxed">
                   {{ call.description }}
                </div>
             </div>
          </div>

          <!-- Right: Action Panel -->
          <div class="space-y-6">
             <div class="bg-emerald-50 rounded-3xl border border-emerald-100 p-8 shadow-sm">
                <h2 class="text-[10px] font-black text-emerald-600 uppercase tracking-widest mb-2 text-center">Maximum Award Budget</h2>
                <p class="text-3xl font-black text-emerald-800 text-center">{{ formatCurrency(call.budget_limit) }}</p>
             </div>
             
             <div class="bg-white rounded-3xl border border-slate-200 p-8 shadow-sm text-center">
                <h2 class="text-[10px] font-black text-slate-400 uppercase tracking-widest mb-4">Submit Your Application</h2>
                <div v-if="call.status?.name === 'open'">
                   <router-link :to="`/proposals/create?call_id=${call.id}`" class="btn btn-primary w-full justify-center h-12 text-[11px] font-black uppercase tracking-widest shadow-lg shadow-blue-500/20 mb-3">
                     Apply Now
                   </router-link>
                   <p class="text-[10px] font-bold text-slate-400">Requires a registered researcher account to proceed.</p>
                </div>
                <div v-else>
                   <button disabled class="w-full h-12 rounded-xl bg-slate-100 text-slate-400 text-[11px] font-black uppercase tracking-widest cursor-not-allowed border border-slate-200">
                     Call Closed
                   </button>
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
  if (!val) return ''
  return new Date(val).toLocaleDateString(undefined, { weekday: 'long', month: 'long', day: 'numeric', year: 'numeric' })
}

onMounted(async () => {
  try {
    const { data } = await api.get(`/calls/${route.params.id}`)
    call.value = data
  } catch (e) {
    console.error('Call not found')
  } finally {
    loading.value = false
  }
})
</script>
