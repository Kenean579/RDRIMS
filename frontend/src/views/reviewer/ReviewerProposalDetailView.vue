<template>
  <div class="space-y-6 max-w-[1600px] mx-auto">
    <!-- Header -->
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-6">
      <div>
        <router-link to="/reviewer/proposals" class="flex items-center gap-2 text-brand font-black uppercase tracking-widest text-[10px] mb-3 hover:translate-x-1 transition-transform">
          <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M10 19l-7-7m0 0l7-7m-7 7h18" /></svg>
          Back to Review Assignments
        </router-link>
        <h1 class="text-3xl font-black text-slate-800 tracking-tight">Evaluate Proposal</h1>
        <div class="flex items-center gap-3 mt-2">
          <div class="flex items-center gap-1.5 px-3 py-1 bg-amber-50 text-amber-600 rounded-full border border-amber-100 shadow-sm">
            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l18 18" /></svg>
            <span class="text-[10px] font-black uppercase tracking-widest">Anonymized Blind Review</span>
          </div>
          <span class="text-slate-300">•</span>
          <span class="text-slate-500 font-black text-xs uppercase tracking-widest">ID: #PRO-{{ String(proposal.id).padStart(4, '0') }}</span>
        </div>
      </div>
      
      <div v-if="alreadyReviewed" class="bg-emerald-50 border border-emerald-200 rounded-2xl px-6 py-4 flex items-center gap-4 shadow-sm">
        <div class="w-12 h-12 bg-emerald-500 text-white rounded-xl flex items-center justify-center shadow-lg shadow-emerald-500/30">
          <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7" /></svg>
        </div>
        <div>
          <p class="text-[10px] font-black text-emerald-600 uppercase tracking-widest">Evaluation Submitted</p>
          <p class="text-lg font-black text-emerald-900 leading-none mt-1">Score: {{ existingScore }} / 100</p>
        </div>
      </div>
    </div>

    <div v-if="loading" class="grid grid-cols-1 md:grid-cols-12 gap-8">
      <div class="md:col-span-8 bg-white rounded-3xl p-10 border border-slate-200 animate-pulse">
        <div class="h-8 bg-slate-100 rounded-full w-1/2 mb-8"></div>
        <div class="space-y-6">
          <div v-for="i in 4" :key="i" class="h-4 bg-slate-50 rounded-full w-full"></div>
        </div>
      </div>
      <div class="md:col-span-4 bg-white rounded-3xl p-8 border border-slate-200 animate-pulse">
        <div class="h-6 bg-slate-100 rounded-full w-3/4 mb-6"></div>
        <div class="space-y-8">
          <div v-for="i in 3" :key="i" class="h-20 bg-slate-50 rounded-2xl"></div>
        </div>
      </div>
    </div>

    <div v-else-if="error" class="bg-rose-50 border border-rose-200 rounded-3xl p-12 text-center max-w-2xl mx-auto">
      <div class="w-16 h-16 bg-rose-100 text-rose-600 rounded-2xl flex items-center justify-center mx-auto mb-4">
        <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" /></svg>
      </div>
      <h3 class="text-xl font-black text-rose-800 mb-2">Failed to Load Proposal</h3>
      <p class="text-rose-600/70 mb-6 text-sm">{{ error }}</p>
      <button @click="fetchProposal" class="btn bg-rose-600 text-white hover:bg-rose-700">Retry Fetch</button>
    </div>

    <template v-else>
      <div class="grid grid-cols-1 md:grid-cols-12 gap-8 items-start">
        <!-- Left Column: Proposal Content -->
        <div class="md:col-span-7 lg:col-span-8 space-y-8">
          <div class="bg-white rounded-[40px] border border-slate-200 shadow-sm overflow-hidden p-8 md:p-12 relative">
            <div class="absolute right-0 top-0 w-96 h-96 bg-brand/5 rounded-full -translate-y-1/2 translate-x-1/2 z-0"></div>
            
            <div class="relative z-10 space-y-10">
              <!-- Title & Meta -->
              <div>
                <h2 class="text-3xl font-black text-slate-800 mb-6 leading-tight">{{ proposal.title }}</h2>
                <div class="flex flex-wrap gap-3">
                  <div class="px-4 py-2 bg-slate-100 rounded-2xl border border-slate-200">
                    <p class="text-[10px] font-black uppercase tracking-widest text-slate-400 mb-0.5">Category</p>
                    <p class="text-sm font-black text-slate-700 uppercase">{{ proposal.type?.name || 'N/A' }}</p>
                  </div>
                  <div class="px-4 py-2 bg-slate-100 rounded-2xl border border-slate-200">
                    <p class="text-[10px] font-black uppercase tracking-widest text-slate-400 mb-0.5">Budget Estimate</p>
                    <p class="text-sm font-black text-slate-700">{{ formatCurrency(proposal.budget) }}</p>
                  </div>
                </div>
              </div>

              <!-- Abstract -->
              <div>
                <h3 class="text-[12px] font-black text-brand uppercase tracking-[0.2em] mb-4 flex items-center gap-3">
                  <span class="w-8 h-px bg-brand/30"></span> Abstract
                </h3>
                <p class="text-slate-600 leading-relaxed text-lg italic whitespace-pre-line bg-brand/5 p-8 rounded-3xl border border-brand/10">
                  {{ proposal.abstract }}
                </p>
              </div>

              <!-- Methodology & Objectives -->
              <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
                <div class="space-y-4">
                  <h3 class="text-[12px] font-black text-slate-800 uppercase tracking-[0.2em] mb-4">Core Objectives</h3>
                  <div class="text-slate-600 text-sm leading-relaxed whitespace-pre-line prose max-w-none">
                    {{ proposal.objectives }}
                  </div>
                </div>
                <div class="space-y-4">
                  <h3 class="text-[12px] font-black text-slate-800 uppercase tracking-[0.2em] mb-4">Methodology</h3>
                  <div class="text-slate-600 text-sm leading-relaxed whitespace-pre-line prose max-w-none">
                    {{ proposal.methodology }}
                  </div>
                </div>
              </div>

              <!-- Keywords -->
              <div>
                <h3 class="text-[12px] font-black text-slate-400 uppercase tracking-[0.2em] mb-4">Thematic Keywords</h3>
                <div class="flex flex-wrap gap-2">
                  <span 
                    v-for="tag in String(proposal.keywords || '').split(',')" 
                    :key="tag"
                    class="px-4 py-1.5 bg-slate-50 text-slate-600 rounded-xl border border-slate-200 text-xs font-black"
                  >
                    {{ tag.trim() }}
                  </span>
                </div>
              </div>

              <!-- Documents -->
              <div v-if="proposal.proposal_file" class="pt-8 border-t border-slate-100">
                <a 
                  :href="`/api/files/${proposal.proposal_file_id}/download`" 
                  target="_blank"
                  class="flex items-center justify-between p-6 bg-slate-900 text-white rounded-3xl hover:bg-brand transition-all group overflow-hidden relative"
                >
                  <div class="absolute right-0 top-0 w-32 h-32 bg-white/10 rounded-full translate-x-1/2 -translate-y-1/2 transition-transform group-hover:scale-150"></div>
                  <div class="flex items-center gap-4 relative z-10">
                    <div class="w-12 h-12 bg-white/20 rounded-2xl flex items-center justify-center">
                      <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" /></svg>
                    </div>
                    <div>
                      <p class="text-xs font-black uppercase tracking-widest text-white/50">Full Manuscript</p>
                      <p class="font-black">Download Proposal Documentation (PDF)</p>
                    </div>
                  </div>
                  <svg class="w-6 h-6 text-white/30 group-hover:text-white transition-colors relative z-10" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M14 5l7 7-7 7" /></svg>
                </a>
              </div>
            </div>
          </div>
        </div>

        <!-- Right Column: Review Form -->
        <div class="md:col-span-5 lg:col-span-4 space-y-6 sticky top-24">
          <div class="bg-white rounded-3xl border-2 border-slate-200 shadow-xl shadow-brand/5 overflow-hidden">
            <div class="bg-slate-50 border-b border-slate-200 p-6 flex items-center justify-between">
              <h3 class="text-sm font-black text-slate-800 uppercase tracking-widest">Scoring & Feedback</h3>
              <div v-if="!alreadyReviewed" class="w-2 h-2 rounded-full bg-brand animate-pulse"></div>
            </div>

            <div v-if="alreadyReviewed" class="p-8 text-center space-y-6">
               <div class="w-24 h-24 bg-emerald-50 text-emerald-500 rounded-full flex items-center justify-center mx-auto border-4 border-emerald-100 italic font-black text-3xl">
                 {{ existingScore }}
               </div>
               <div>
                 <p class="text-xl font-black text-slate-800">Review Completed</p>
                 <p class="text-slate-500 text-sm mt-2">You have finalized this review. Thank you for your contribution to academic integrity.</p>
               </div>
               <div class="pt-6 border-t border-slate-100 flex flex-col gap-3">
                 <div class="flex items-center justify-between text-sm px-4 py-3 bg-slate-50 rounded-2xl border border-slate-100">
                    <span class="text-slate-500 font-bold">Decision:</span>
                    <span class="font-black text-brand">{{ formatStatusName(proposal.reviewPivot?.decision?.name || 'Approved') }}</span>
                 </div>
               </div>
            </div>

            <form v-else @submit.prevent="submitReview" class="p-8 space-y-8">
              <!-- Dynamic Criteria -->
              <div class="space-y-6">
                <div v-for="criterion in reviewCriteria" :key="criterion.id" class="group">
                  <div class="flex items-center justify-between mb-3">
                    <label class="text-[11px] font-black uppercase tracking-widest text-slate-500">{{ criterion.name }}</label>
                    <span class="text-[10px] font-black text-slate-400 bg-slate-50 px-2 py-0.5 rounded-lg border border-slate-100">MAX {{ criterion.max_score }}</span>
                  </div>
                  <p class="text-[10px] text-slate-400 mb-3 italic leading-relaxed">{{ criterion.description }}</p>
                  
                  <div class="flex gap-3">
                    <div class="relative w-24">
                      <input 
                        v-model.number="scores[criterion.id]" 
                        type="number" 
                        :max="criterion.max_score" 
                        min="0"
                        class="w-full bg-slate-50 border-2 border-slate-200 rounded-2xl px-4 py-3 text-sm font-black text-brand focus:border-brand focus:ring-4 focus:ring-brand/10 outline-none transition-all placeholder:text-slate-300"
                        placeholder="0" 
                        required 
                      />
                    </div>
                    <input 
                      v-model="comments[criterion.id]" 
                      type="text"
                      class="flex-1 bg-slate-50 border-2 border-slate-200 rounded-2xl px-4 py-3 text-sm focus:border-brand focus:ring-4 focus:ring-brand/10 outline-none transition-all placeholder:text-slate-400"
                      placeholder="Specific feedback..." 
                    />
                  </div>
                </div>
              </div>

              <!-- Overall Scoring -->
              <div class="pt-8 border-t border-slate-100 space-y-6">
                <div class="grid grid-cols-2 gap-4">
                  <div class="space-y-2">
                    <label class="text-[10px] font-black uppercase tracking-[0.2em] text-slate-400">Weighted Average</label>
                    <input 
                      v-model.number="overallScore" 
                      type="number" 
                      step="0.01" 
                      max="100"
                      min="0"
                      required
                      class="w-full bg-brand/5 border-2 border-brand/20 rounded-2xl px-4 py-4 text-xl font-black text-brand focus:border-brand-dark focus:ring-4 focus:ring-brand/10 outline-none transition-all" 
                      placeholder="0.00"
                    />
                  </div>
                  <div class="space-y-2">
                    <label class="text-[10px] font-black uppercase tracking-[0.2em] text-slate-400">Final Recommendation</label>
                    <select 
                      v-model="decisionId" 
                      required
                      class="w-full bg-slate-100 border-2 border-slate-200 rounded-2xl px-4 py-4 text-sm font-black text-slate-700 focus:border-brand focus:ring-4 focus:ring-brand/10 outline-none transition-all appearance-none cursor-pointer"
                    >
                      <option value="" disabled>Select Result</option>
                      <option v-for="d in reviewDecisions" :key="d.id" :value="d.id">{{ formatStatusName(d.name) }}</option>
                    </select>
                  </div>
                </div>

                <div class="space-y-2">
                  <label class="text-[10px] font-black uppercase tracking-[0.2em] text-slate-400">General Comments to Board</label>
                  <textarea 
                    v-model="overallComments" 
                    rows="4"
                    class="w-full bg-slate-50 border-2 border-slate-200 rounded-2xl px-4 py-3 text-sm focus:border-brand focus:ring-4 focus:ring-brand/10 outline-none transition-all resize-none placeholder:text-slate-400"
                    placeholder="Provide a summary of your decision..."
                  ></textarea>
                </div>
              </div>

              <!-- Submit Action -->
              <button 
                type="submit" 
                :disabled="submitting"
                class="w-full py-5 bg-slate-900 text-white rounded-[24px] font-black uppercase tracking-widest text-xs hover:bg-brand hover:shadow-xl hover:shadow-brand/30 transition-all active:scale-[0.98] disabled:opacity-50 flex items-center justify-center gap-3 group"
              >
                <svg v-if="submitting" class="animate-spin h-5 w-5 text-white" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>
                <span v-else>{{ alreadyReviewed ? 'Update Review' : 'Authorize & Submit Final Review' }}</span>
                <svg v-if="!submitting" class="w-4 h-4 group-hover:translate-x-1 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M14 5l7 7-7 7" /></svg>
              </button>
            </form>
          </div>
          
          <div class="bg-amber-50 rounded-3xl p-6 border border-amber-200">
             <div class="flex gap-3">
               <div class="w-8 h-8 bg-amber-100 text-amber-600 rounded-xl flex items-center justify-center grow-0 shrink-0">
                 <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
               </div>
               <p class="text-xs text-amber-900/70 font-medium leading-relaxed">
                 By submitting this review, you certify that you have no conflict of interest regarding this proposal and that your evaluation is based solely on the scientific merit and feasibility of the work.
               </p>
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
import { useNotificationStore } from '@/stores/notification'
import api from '@/services/api'
import { formatCurrency } from '@/utils/formatters'
import { formatStatusName } from '@/utils/colors'

const route = useRoute()
const notif = useNotificationStore()

const proposal = ref({})
const loading = ref(true)
const error = ref(null)
const reviewCriteria = ref([])
const reviewDecisions = ref([])
const scores = ref({})
const comments = ref({})
const overallScore = ref(null)
const overallComments = ref('')
const decisionId = ref('')
const submitting = ref(false)

const alreadyReviewed = computed(() => proposal.value.reviewPivot?.overall_score !== null)
const existingScore = computed(() => proposal.value.reviewPivot?.overall_score)

async function fetchProposal() {
  loading.value = true; error.value = null
  try {
    const { data } = await api.get(`/reviewer/proposals/${route.params.id}`)
    proposal.value = data
  } catch (err) {
    error.value = err.response?.data?.message || 'Failed to load proposal'
  } finally { loading.value = false }
}

async function submitReview() {
  submitting.value = true
  try {
    const payload = {
      scores: reviewCriteria.value.map(c => ({
        criterion_id: c.id,
        score: scores.value[c.id] || 0,
        comments: comments.value[c.id] || null
      })),
      overall_score: overallScore.value,
      overall_comments: overallComments.value,
      decision_id: decisionId.value
    }
    await api.post(`/reviewer/proposals/${route.params.id}/review`, payload)
    notif.success('Review submitted successfully!')
    await fetchProposal()
  } catch (err) {
    notif.error(err.response?.data?.message || 'Failed to submit review')
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
  } catch (e) { /* ignore */ }
})
</script>

