<template>
  <div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8 py-12 animate-fade">
    
    <!-- Loading State -->
    <div v-if="loading" class="space-y-6">
      <div class="h-32 bg-white rounded-2xl shadow-sm border border-slate-100 animate-pulse"></div>
      <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
        <div class="md:col-span-2 h-64 bg-white rounded-2xl shadow-sm border border-slate-100 animate-pulse"></div>
        <div class="h-64 bg-white rounded-2xl shadow-sm border border-slate-100 animate-pulse"></div>
      </div>
    </div>

    <!-- Error State -->
    <div v-else-if="!call" class="card p-16 text-center shadow-sm">
      <div class="w-16 h-16 bg-slate-50 text-slate-400 rounded-full flex items-center justify-center mx-auto mb-6 text-2xl">⚠️</div>
      <h1 class="text-2xl font-black text-slate-800 mb-2">Call Not Found</h1>
      <p class="text-slate-500 mb-8 max-w-sm mx-auto">This research call may have been removed or the link is invalid.</p>
      <router-link to="/calls" class="btn btn-secondary px-8">Back to Calls</router-link>
    </div>

    <!-- Content -->
    <template v-else>
      <router-link to="/calls" class="inline-flex items-center text-sm font-bold text-slate-500 hover:text-brand mb-6 transition-colors">
        ← Back to Funding Opportunities
      </router-link>

      <!-- Main Header Card -->
      <div class="card p-8 md:p-10 mb-8 relative border-t-4 border-t-brand shadow-sm">
        <div class="flex flex-col md:flex-row md:items-start justify-between gap-6">
          <div>
            <div class="flex items-center gap-3 mb-4">
              <span class="px-3 py-1 bg-brand/10 text-brand text-xs font-black capitalize tracking-widest rounded border border-brand/20">
                {{ call.status?.name || 'Open' }}
              </span>
            </div>
            <h1 class="text-3xl md:text-4xl font-black text-slate-900 tracking-tight leading-tight mb-4">
              {{ call.title }}
            </h1>
            <p class="text-sm font-bold text-slate-500 capitalize tracking-widest">
              Host: <span class="text-slate-800">{{ call.university?.name || 'Central Institutional Network' }}</span>
            </p>
          </div>

          <!-- Apply Section -->
          <div class="shrink-0 md:text-right">
            <template v-if="call.status?.name === 'open' || !call.status">
              <router-link :to="`/app/proposals/create?call_id=${call.id}`" class="btn btn-primary px-8 py-3.5 shadow-lg shadow-brand/20 w-full md:w-auto text-base">
                Apply Now
              </router-link>
              <p class="text-xs text-slate-400 font-medium mt-3 text-center md:text-right">Login required to apply</p>
            </template>
            <div v-else class="px-6 py-3 bg-slate-50 border border-slate-200 rounded-xl text-slate-500 font-black text-sm capitalize">
              Submissions Closed
            </div>
          </div>
        </div>
      </div>

      <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
        <!-- Main Description -->
        <div class="lg:col-span-2 space-y-8">
          <div class="card p-8 shadow-sm">
            <h2 class="text-lg font-black text-slate-800 mb-6 flex items-center gap-2 border-b border-slate-100 pb-4">
              Call Details
            </h2>
            <div class="prose prose-slate max-w-none text-slate-600 leading-relaxed font-medium whitespace-pre-wrap">
              {{ call.description }}
            </div>
          </div>
        </div>

        <!-- Sidebar Info -->
        <div class="space-y-6">
          <div class="card p-6 shadow-sm bg-slate-50 border border-slate-100">
            <h3 class="text-xs font-black text-slate-400 uppercase tracking-widest mb-4">Overview</h3>
            <ul class="space-y-4">
              <li>
                <p class="text-[10px] font-black text-slate-400 uppercase tracking-widest leading-none mb-1.5">Deadline</p>
                <div class="flex items-center gap-2 text-slate-800 font-bold">
                  <svg class="w-4 h-4 text-brand" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                  {{ formatDate(call.deadline) }}
                </div>
              </li>
              <li class="pt-4 border-t border-slate-200">
                <p class="text-[10px] font-black text-slate-400 uppercase tracking-widest leading-none mb-1.5">Budget Limit</p>
                <p class="text-lg font-black text-emerald-600 tracking-tight">
                  {{ formatCurrency(call.budget_limit) }}
                </p>
              </li>
              <li class="pt-4 border-t border-slate-200">
                <p class="text-[10px] font-black text-slate-400 uppercase tracking-widest leading-none mb-1.5">Academic Cycle</p>
                <p class="text-sm font-bold text-slate-700">
                  {{ call.academic_year?.name || 'General Cycle' }}
                </p>
              </li>
            </ul>
          </div>
          
          <div class="card p-6 shadow-sm border border-slate-100 relative overflow-hidden group">
            <div class="absolute inset-0 bg-brand/5 opacity-0 group-hover:opacity-100 transition-opacity"></div>
            <h3 class="text-xs font-black text-slate-400 uppercase tracking-widest mb-2 relative z-10">Need Assistance?</h3>
            <p class="text-sm text-slate-500 font-medium mb-4 relative z-10">If you encounter issues during your application process, please contact the ethics and review board.</p>
            <a href="mailto:support@rdrims.local" class="text-brand font-bold text-sm hover:underline relative z-10">Contact Support →</a>
          </div>
        </div>
      </div>
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
    console.error('Failed to load call data')
  } finally {
    loading.value = false
  }
})
</script>
