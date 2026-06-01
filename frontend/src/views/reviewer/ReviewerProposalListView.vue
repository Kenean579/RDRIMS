<template>
  <div class="space-y-6">
    <!-- Header -->
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-6 bg-white p-8 rounded-3xl border border-slate-200 shadow-sm relative overflow-hidden">
      <div class="absolute right-0 top-0 w-64 h-64 bg-brand/5 rounded-full -translate-y-1/2 translate-x-1/3"></div>
      <div class="relative z-10">
        <h1 class="text-3xl font-black text-slate-800 tracking-tight mb-2">My Review Assignments</h1>
        <p class="text-slate-500 max-w-xl text-sm leading-relaxed">
          Evaluate and score research proposals assigned to you. Your expertise helps maintain the highest standards of academic excellence.
        </p>
      </div>
      <div class="flex items-center gap-2 relative z-10">
        <div class="bg-brand/10 px-4 py-2 rounded-2xl border border-brand/20">
          <p class="text-[10px] font-black text-brand uppercase tracking-widest mb-0.5">Assigned Count</p>
          <p class="text-xl font-black text-brand-dark">{{ pagination.total }}</p>
        </div>
      </div>
    </div>

    <!-- Filters & Stats -->
    <div class="grid grid-cols-1 md:grid-cols-12 gap-6 items-start">
      <div class="md:col-span-12 flex flex-wrap items-center gap-3 bg-slate-50 p-2 rounded-2xl border border-slate-200">
        <button 
          v-for="status in ['all', 'pending', 'reviewed']" 
          :key="status"
          @click="currentTab = status; fetchProposals(1)"
          :class="[
            'px-6 py-2.5 rounded-xl text-xs font-black uppercase tracking-widest transition-all',
            currentTab === status 
              ? 'bg-brand text-white shadow-lg shadow-brand/30' 
              : 'text-slate-500 hover:bg-white hover:text-brand'
          ]"
        >
          {{ status }}
        </button>
      </div>

      <!-- Main List -->
      <div class="md:col-span-12 space-y-4">
        <div v-if="loading" class="grid grid-cols-1 md:grid-cols-2 gap-6">
          <div v-for="i in 4" :key="i" class="bg-white rounded-3xl p-6 border border-slate-200 animate-pulse">
            <div class="h-4 bg-slate-100 rounded-full w-3/4 mb-4"></div>
            <div class="h-10 bg-slate-50 rounded-2xl mb-4"></div>
            <div class="flex gap-2">
              <div class="h-8 bg-slate-50 rounded-xl w-24"></div>
              <div class="h-8 bg-slate-50 rounded-xl w-24"></div>
            </div>
          </div>
        </div>

        <div v-else-if="error" class="bg-rose-50 border border-rose-200 rounded-3xl p-12 text-center">
          <div class="w-16 h-16 bg-rose-100 text-rose-600 rounded-2xl flex items-center justify-center mx-auto mb-4">
            <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" /></svg>
          </div>
          <h3 class="text-xl font-black text-rose-800 mb-2">Evaluation Data Unavailable</h3>
          <p class="text-rose-600/70 mb-6 text-sm">{{ error }}</p>
          <button @click="fetchProposals(1)" class="btn bg-rose-600 text-white hover:bg-rose-700">Retry Fetch</button>
        </div>

        <div v-else-if="proposals.length === 0" class="bg-white rounded-[40px] border-2 border-dashed border-slate-200 p-20 text-center">
           <div class="w-20 h-20 bg-slate-50 text-slate-300 rounded-3xl flex items-center justify-center mx-auto mb-6">
             <svg class="w-10 h-10" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
           </div>
           <h3 class="text-2xl font-black text-slate-400">All Caught Up!</h3>
           <p class="text-slate-400 mt-2">No {{ currentTab !== 'all' ? currentTab : '' }} review assignments found at the moment.</p>
        </div>

        <div v-else class="grid grid-cols-1 md:grid-cols-2 gap-6">
          <div 
            v-for="p in proposals" 
            :key="p.id"
            @click="$router.push(`/reviewer/proposals/${p.id}`)"
            class="group bg-white rounded-3xl border border-slate-200 p-6 hover:shadow-2xl hover:shadow-brand/10 hover:-translate-y-1 transition-all cursor-pointer relative overflow-hidden"
          >
            <div class="absolute right-0 top-0 w-24 h-24 bg-brand/5 rounded-bl-[100px] -mr-12 -mt-12 transition-transform group-hover:scale-150"></div>
            
            <div class="flex items-start justify-between mb-4 relative z-10">
              <div class="flex items-center gap-3">
                <div class="w-10 h-10 bg-slate-100 rounded-xl flex items-center justify-center text-slate-400 font-black text-xs">
                  #{{ p.id }}
                </div>
                <div>
                  <p class="text-[10px] font-black uppercase tracking-widest text-slate-400 mb-0.5">Review Assignment</p>
                  <p class="text-xs font-black text-slate-600">Assigned {{ formatDate(p.reviewPivot?.assigned_at) }}</p>
                </div>
              </div>
              <StatusBadge :status="p.status?.name || 'draft'" />
            </div>

            <h3 class="text-lg font-black text-slate-800 line-clamp-2 mb-4 group-hover:text-brand transition-colors h-14">
              {{ p.title }}
            </h3>

            <div class="flex items-center justify-between pt-4 border-t border-slate-100 mt-auto">
              <div class="flex items-center gap-4">
                <div>
                  <p class="text-[10px] font-black uppercase tracking-widest text-slate-400 mb-0.5">Score</p>
                  <p :class="['text-sm font-black', p.reviewPivot?.overall_score ? 'text-brand' : 'text-slate-300']">
                    {{ p.reviewPivot?.overall_score || 'Pending' }}
                  </p>
                </div>
                <div class="h-8 w-px bg-slate-100"></div>
                <div>
                  <p class="text-[10px] font-black uppercase tracking-widest text-slate-400 mb-0.5">Budget</p>
                  <p class="text-sm font-black text-slate-700">{{ formatCurrency(p.budget) }}</p>
                </div>
              </div>
              
              <div class="w-8 h-8 rounded-full bg-slate-50 flex items-center justify-center text-slate-400 group-hover:bg-brand group-hover:text-white transition-all">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M9 5l7 7-7 7" /></svg>
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- Pagination -->
      <div class="md:col-span-12 flex justify-center mt-6">
        <Pagination 
          :current-page="pagination.current_page" 
          :total-pages="pagination.last_page"
          :total="pagination.total" 
          @page-change="fetchProposals" 
        />
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, reactive, onMounted } from 'vue'
import api from '@/services/api'
import StatusBadge from '@/components/StatusBadge.vue'
import Pagination from '@/components/Pagination.vue'
import { formatDate, formatCurrency } from '@/utils/formatters'

const loading = ref(true)
const error = ref(null)
const proposals = ref([])
const currentTab = ref('all')
const pagination = reactive({ current_page: 1, last_page: 1, total: 0 })

async function fetchProposals(page = 1) {
  loading.value = true; error.value = null
  try {
    const params = { page }
    if (currentTab.value !== 'all') {
      params.review_status = currentTab.value
    }
    const { data } = await api.get('/reviewer/proposals', { params })
    proposals.value = data.data
    Object.assign(pagination, { 
      current_page: data.current_page, 
      last_page: data.last_page, 
      total: data.total 
    })
  } catch (err) {
    error.value = err.response?.data?.message || 'Failed to load reviews'
  } finally { loading.value = false }
}

onMounted(() => fetchProposals())
</script>

