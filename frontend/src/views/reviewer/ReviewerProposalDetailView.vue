<template>
  <div class="space-y-6 animate-fade pb-8">
    
    <!-- Meta Header -->
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 py-2 border-b border-slate-100">
      <div>
        <router-link to="/app/reviewer/proposals" class="flex items-center gap-2 text-brand font-bold text-xs mb-3 hover:-translate-x-1 transition-transform">
          <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M14 5l-7 7m0 0l7 7m-7-7h18" /></svg>
          Back to My Reviews
        </router-link>
        <h1 class="text-xl font-black text-slate-900 tracking-tight leading-none ">
          Review Proposal
        </h1>
        <p class="text-xs font-bold text-rose-500 mt-2  tracking-widest flex items-center gap-2">
          <span class="w-2 h-2 rounded-full bg-rose-500 shadow-lg shadow-rose-200 animate-pulse"></span>
          Double-blind review — author identity is confidential
        </p>
      </div>
      <div v-if="!loading && proposal.id" class="flex items-center gap-3">
         <span class="px-4 py-2 bg-slate-100 text-slate-600 rounded-xl text-xs font-black  tracking-widest">
           Ref: RDRIMS-{{ proposal.id }}-BLIND
         </span>
         <StatusBadge :status="proposal.status?.name" size="lg" />
      </div>
    </div>

    <div v-if="loading" class="grid grid-cols-1 lg:grid-cols-3 gap-6">
      <div class="lg:col-span-2 space-y-6">
        <div class="card h-64 animate-pulse"></div>
        <div class="card h-96 animate-pulse"></div>
      </div>
      <div class="card h-80 animate-pulse"></div>
    </div>

    <div v-else-if="error" class="card border-rose-100 bg-rose-50/30 p-12 text-center">
       <div class="w-20 h-20 bg-rose-100 text-rose-600 rounded-3xl flex items-center justify-center mx-auto mb-6 text-3xl">⚠️</div>
       <h3 class="text-lg font-black text-slate-800 mb-2">Access Resticted</h3>
       <p class="text-slate-500 text-sm mb-8 font-medium">{{ error }}</p>
       <button @click="fetchProposal" class="btn bg-rose-600 text-white px-8 h-12 text-xs  font-black tracking-widest shadow-xl shadow-rose-200">Retry Authentication</button>
    </div>

    <template v-else>
      <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        
        <!-- Left: Anonymized Protocol Data -->
        <div class="lg:col-span-2 space-y-6">
          
          <!-- Core Data -->
          <div class="card p-8">
            <h2 class="text-xs font-black text-slate-800  tracking-widest mb-8 flex items-center gap-3">
              <span class="w-1.5 h-4 bg-brand rounded-full"></span>
              Proposal Details
            </h2>
            
            <div class="space-y-10">
              <div>
                <p class="text-xs font-bold text-slate-400  tracking-widest mb-3 ml-1">Title</p>
                <div class="p-5 rounded-2xl bg-slate-50 border border-slate-100 font-bold text-slate-700 leading-relaxed">
                  {{ proposal.title }}
                </div>
              </div>

              <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                 <div>
                    <p class="text-xs font-bold text-slate-400  tracking-widest mb-3 ml-1">Type</p>
                    <div class="p-4 rounded-xl bg-white border border-slate-100 text-sm font-bold text-slate-600">
                      {{ proposal.type?.name || 'Standard Protocol' }}
                    </div>
                 </div>
                 <div>
                    <p class="text-xs font-bold text-slate-400  tracking-widest mb-3 ml-1">Requested Budget</p>
                    <div class="p-4 rounded-xl bg-emerald-50 border border-emerald-100 text-sm font-black text-emerald-600">
                      {{ formatCurrency(proposal.budget) }}
                    </div>
                 </div>
              </div>

              <div>
                <p class="text-xs font-bold text-slate-400  tracking-widest mb-3 ml-1">Expertise Tags</p>
                <div class="flex flex-wrap gap-2">
                   <span v-for="kw in keywordsList" :key="kw" class="px-4 py-2 bg-white border border-slate-200 rounded-xl text-xs font-black text-slate-500  tracking-widest">
                     {{ kw }}
                   </span>
                </div>
              </div>

              <div>
                <p class="text-xs font-bold text-slate-400  tracking-widest mb-3 ml-1">Abstract</p>
                <p class="text-sm font-medium text-slate-600 leading-loose italic pl-6 border-l-4 border-slate-200">
                  {{ proposal.abstract }}
                </p>
              </div>
            </div>
          </div>

          <!-- Extended Details -->
          <div class="card p-8">
            <h2 class="text-xs font-black text-slate-800  tracking-widest mb-8 flex items-center gap-3">
              <span class="w-1.5 h-4 bg-brand rounded-full"></span>
              Objectives & Methodology
            </h2>
            <div class="space-y-12">
               <div v-if="proposal.objectives">
                 <p class="text-xs font-bold text-slate-400  tracking-widest mb-4">Objectives</p>
                 <div class="whitespace-pre-line text-sm font-medium text-slate-600 leading-loose p-6 rounded-2xl bg-slate-50 border border-slate-100">
                   {{ proposal.objectives }}
                 </div>
               </div>
               <div v-if="proposal.methodology">
                 <p class="text-xs font-bold text-slate-400  tracking-widest mb-4">Methodology</p>
                 <div class="text-sm font-medium text-slate-600 leading-loose">
                   {{ proposal.methodology }}
                 </div>
               </div>
            </div>
          </div>
        </div>

        <!-- Right: Assessment Console -->
        <div class="space-y-6">
          
          <!-- Documentation -->
          <div class="card p-6">
             <h3 class="text-xs font-black text-slate-800  tracking-widest mb-5">Primary File</h3>
             <div v-if="proposal.file" class="p-4 bg-brand/5 border border-brand/10 rounded-2xl flex items-center justify-between group">
               <div class="flex items-center gap-3 min-w-0">
                  <div class="w-10 h-10 bg-brand text-white rounded-xl flex items-center justify-center text-xl shadow-lg transition-transform group-hover:rotate-6">📄</div>
                  <div class="min-w-0">
                    <p class="text-xs font-black text-slate-800 truncate">ANONYMIZED_MS.pdf</p>
                    <p class="text-xs font-bold text-brand  tracking-tighter">Verified Protocol</p>
                  </div>
               </div>
               <a :href="`/api/files/${proposal.file.id}/download`" target="_blank" class="p-2.5 text-brand bg-white border border-brand/20 rounded-xl hover:shadow-brand/5 transition-all">
                  <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path d="M4 16v1a2 2 0 002 2h12a2 2 0 002-2v-1m-4-4l-4 4m0 0l-4-4m4 4V4" stroke-width="2.5"/></svg>
               </a>
             </div>
             <div v-else class="text-center py-4">
                <p class="text-xs font-bold text-rose-400 italic">No manuscript attached.</p>
             </div>
          </div>

           <!-- Evaluation Controller -->
           <div class="card overflow-hidden">
              <div class="px-6 py-4 bg-slate-900 border-b border-slate-800 flex items-center justify-between gap-3">
                 <div class="flex items-center gap-3">
                   <h3 class="text-xs font-black text-white  tracking-widest">Submit Review</h3>
                   <span class="px-2 py-0.5 bg-brand text-white text-[8px] font-black rounded ">Secure</span>
                 </div>
                 <div class="flex items-center gap-2">
                   <button type="button" @click="exportScores" class="px-3 py-1.5 bg-white/10 text-white text-[10px] font-black rounded hover:bg-white/20 transition-colors">
                     Export Excel
                   </button>
                   <label class="px-3 py-1.5 bg-white/10 text-white text-[10px] font-black rounded hover:bg-white/20 transition-colors cursor-pointer">
                     Import Excel
                     <input type="file" accept=".xlsx,.xls,.csv" class="hidden" @change="importScores" />
                   </label>
                 </div>
              </div>

             <div v-if="alreadyReviewed" class="p-8 text-center bg-emerald-50/50">
                <div class="w-16 h-16 bg-emerald-500 text-white rounded-full flex items-center justify-center mx-auto mb-4 shadow-xl">
                  <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path d="M5 13l4 4L19 7" stroke-width="3"/></svg>
                </div>
                <h4 class="text-sm font-black text-slate-800 mb-1">Decision Locked</h4>
                <p class="text-xs font-bold text-slate-400 mb-6 ">Assigned Score: {{ existingScore }}/5.0</p>
                <div class="p-3 bg-white border border-slate-100 rounded-2xl text-xs font-bold text-slate-500 italic">
                   "{{ proposal.reviewPivot?.overall_comments || 'No comment provided' }}"
                </div>
             </div>

             <div v-else class="p-6 space-y-6">
                <!-- Action Hub -->

                <hr class="border-slate-50"/>

                <form @submit.prevent="submitReview" class="space-y-5">
                   <div v-for="c in reviewCriteria" :key="c.id" class="space-y-2">
                      <div class="flex justify-between items-end">
                        <label class="text-xs font-black text-slate-500  tracking-widest">{{ c.name }}</label>
                        <span class="text-xs font-black text-brand">{{ scores[c.id] || 0 }}<span class="text-slate-300">/{{ c.max_score }}</span></span>
                      </div>
                      <input type="range" v-model.number="scores[c.id]" :max="c.max_score" min="0" step="0.5" class="w-full h-1.5 bg-slate-100 rounded-lg appearance-none cursor-pointer accent-brand"/>
                   </div>

                   <div class="space-y-2 pt-2">
                      <label class="text-xs font-black text-slate-800  tracking-widest">Decision</label>
                      <select v-model="decisionId" required class="w-full bg-slate-50 border border-slate-100 rounded-xl px-4 py-3 text-xs font-bold text-slate-700 outline-none focus:ring-2 focus:ring-brand/20">
                         <option value="">Select a decision</option>
                         <option v-for="d in reviewDecisions" :key="d.id" :value="d.id">{{ formatStatusName(d.name) }}</option>
                      </select>
                   </div>

                   <div class="space-y-2 pt-2">
                       <label class="text-xs font-black text-slate-800  tracking-widest">Comments</label>
                       <textarea v-model="overallComments" required rows="4" class="w-full bg-slate-50 border border-slate-100 rounded-xl px-4 py-3 text-xs font-medium text-slate-600 outline-none focus:ring-2 focus:ring-brand/20 resize-none" placeholder="Provide your feedback and evaluation comments..."></textarea>
                   </div>

                   <button type="submit" :disabled="submitting" class="w-full py-4 bg-brand text-white rounded-2xl text-xs font-black  tracking-widest shadow-xl shadow-brand/20 hover:scale-[1.02] active:scale-95 transition-all">
                       {{ submitting ? 'Submitting...' : 'Submit Review' }}
                   </button>
                </form>
             </div>
          </div>
        </div>

      </div>
    </template>
  </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue'
import { useRoute } from 'vue-router'
import { useAuthStore } from '@/stores/auth'
import { useNotificationStore } from '@/stores/notification'
import api from '@/services/api'
import StatusBadge from '@/components/StatusBadge.vue'
import { formatCurrency } from '@/utils/formatters'
import { formatStatusName } from '@/utils/colors'
import * as XLSX from 'xlsx'

const route = useRoute()
const auth = useAuthStore()
const notif = useNotificationStore()

const proposal = ref({})
const loading = ref(true)
const error = ref(null)
const reviewCriteria = ref([])
const reviewDecisions = ref([])
const scores = ref({})
const overallComments = ref('')
const decisionId = ref('')
const submitting = ref(false)

const keywordsList = computed(() => {
  if (!proposal.value.keywords) return []
  if (Array.isArray(proposal.value.keywords)) return proposal.value.keywords
  return proposal.value.keywords.split(',').map(k => k.trim())
})

const alreadyReviewed = computed(() => proposal.value.reviewPivot?.overall_score !== null)
const existingScore = computed(() => proposal.value.reviewPivot?.overall_score)

async function fetchProposal() {
  loading.value = true; error.value = null
  try {
    const { data } = await api.get(`/reviewer/proposals/${route.params.id}`)
    proposal.value = data
  } catch (err) {
    error.value = err.response?.data?.message || 'You are not assigned as a reviewer for this proposal'
  } finally { loading.value = false }
}



async function submitReview() {
  submitting.value = true
  try {
    const totalMax = reviewCriteria.value.reduce((acc, c) => acc + c.max_score, 0)
    const currentTotal = Object.values(scores.value).reduce((acc, s) => acc + (s || 0), 0)
    const overall_score = totalMax > 0 ? (currentTotal / totalMax) * 5 : 0

    const payload = {
      scores: reviewCriteria.value.map(c => ({
        criterion_id: c.id,
        score: scores.value[c.id] || 0,
        comments: ''
      })),
      overall_score,
      overall_comments: overallComments.value,
      decision_id: decisionId.value
    }
    await api.post(`/reviewer/proposals/${route.params.id}/review`, payload)
    notif.success('Review submitted successfully!')
    await fetchProposal()
  } catch (err) {
    notif.error('Failed to submit review. Please try again.')
  } finally { submitting.value = false }
}

onMounted(async () => {
  await fetchProposal()
  try {
    const [criteriaRes, decisionsRes] = await Promise.all([
      api.get('/review-criteria'),
      api.get('/lookups/review_decisions')
    ])
    reviewCriteria.value = criteriaRes.data.filter(c => c.is_active)
    reviewDecisions.value = decisionsRes.data
    reviewCriteria.value.forEach(c => { scores.value[c.id] = 0 })
  } catch (e) { }
})
</script>
