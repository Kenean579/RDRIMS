<template>
  <div class="flex flex-col gap-8 pb-12 animate-fade card">
    <!-- Header -->
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
      <div>
        <h1 class="text-3xl font-black text-slate-900 tracking-tight">Help Desk</h1>
        <p class="text-slate-500 font-medium mt-1">Report a problem or help solve existing ones.</p>
      </div>
      <button @click="showCreate = true" class="btn btn-primary h-11 px-8 shadow-lg shadow-blue-500/20">
        <svg class="w-4 h-4 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M12 4v16m8-8H4" /></svg>
        Report Issue
      </button>
    </div>

    <!-- Content -->
    <div v-if="loading" class="grid grid-cols-1 md:grid-cols-2 gap-6">
      <div v-for="i in 4" :key="i" class="card h-40 animate-pulse bg-slate-50/50"></div>
    </div>
    <div v-else-if="error" class="card border-rose-100 bg-rose-50/30 p-12 text-center shadow-xl shadow-rose-500/5">
       <div class="w-16 h-16 bg-rose-100 text-rose-600 rounded-2xl flex items-center justify-center mx-auto mb-5 text-2xl shadow-inner">⚠️</div>
       <h3 class="text-lg font-black text-slate-800 mb-2 uppercase tracking-tight">System Error</h3>
       <p class="text-sm text-rose-600 font-bold mb-6 uppercase tracking-widest text-[11px] leading-relaxed">{{ error }}</p>
       <button @click="fetchProblems" class="btn bg-rose-600 hover:bg-rose-700 text-white px-8 h-11 text-[11px] font-black uppercase tracking-widest border-0">Retry</button>
    </div>
    <div v-else-if="problems.length === 0" class="card">
      <EmptyState icon="🏘️" title="No issues reported" description="Know about a problem in your area? Let us know so we can help." action-label="Report Issue" @action="showCreate = true" />
    </div>

    <div v-else class="grid grid-cols-1 md:grid-cols-2 gap-6">
      <div v-for="p in problems" :key="p.id" class="card group card-hover flex flex-col border-l-4 border-l-brand/20 hover:border-l-brand transition-all overflow-hidden font-bold">
        <div class="p-6 flex-1">
          <div class="flex items-center justify-between gap-4 mb-4">
            <h3 class="text-lg font-black text-slate-800 group-hover:text-brand transition-colors leading-tight">{{ p.title }}</h3>
            <StatusBadge :status="p.status?.name || 'open'" />
          </div>
          <p class="text-sm text-slate-500 font-medium leading-relaxed mb-6 line-clamp-2 italic">{{ p.description }}</p>
          
          <div class="flex flex-col gap-3">
            <div class="flex items-center gap-2.5 text-[10px] font-black text-slate-400 uppercase tracking-widest bg-slate-50 p-2.5 rounded-xl border border-slate-100 shadow-inner">
              <svg class="w-4 h-4 text-brand" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" /><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z" /></svg>
              {{ p.location }}
            </div>
            <div v-if="p.claimed_by" class="flex items-center gap-2.5 text-[10px] font-black text-slate-400 uppercase tracking-widest bg-slate-50 p-2.5 rounded-xl border border-slate-100 shadow-inner">
              <svg class="w-4 h-4 text-emerald-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" /></svg>
              Helper: {{ p.claimed_by?.name }}
            </div>
            <div v-if="p.rating" class="flex items-center gap-2 text-[10px] font-black text-slate-400 uppercase tracking-widest bg-amber-50/50 p-2.5 rounded-xl border border-amber-100 shadow-inner">
              <span class="text-amber-500 text-lg">★</span>
              Rating: {{ p.rating }} / 5
            </div>
          </div>
        </div>

        <div class="px-6 py-4 bg-slate-50/50 border-t border-slate-100 flex gap-2">
          <button v-if="p.status?.name === 'open'" @click="claimProblem(p)" class="flex-1 btn btn-primary justify-center text-[11px] font-black uppercase tracking-widest h-10 shadow-lg shadow-blue-500/20">Help Out</button>
          <button v-if="p.status?.name === 'claimed' && p.claimed_by?.id === auth.user?.id" @click="completeProblem(p)" class="flex-1 btn btn-primary justify-center text-[11px] font-black uppercase tracking-widest h-10 shadow-lg shadow-blue-500/20">Finish</button>
          <button v-if="p.status?.name === 'completed' && !p.feedback" @click="openFeedback(p)" class="flex-1 btn btn-secondary justify-center text-[11px] font-black uppercase tracking-widest h-10 px-4">Give Feedback</button>
          <span v-if="p.status?.name === 'completed' && p.feedback" class="flex-1 text-center py-2 text-[10px] font-black uppercase tracking-widest text-emerald-600 bg-emerald-50 rounded-xl border border-emerald-100">Task Completed ✓</span>
        </div>
      </div>
    </div>

    <!-- Submit Final Modal -->
    <Modal :show="showCreate" title="Report an Issue" size="md" @close="showCreate = false">
      <form @submit.prevent="submitProblem" class="space-y-6">
        <div>
          <label class="block text-[11px] text-slate-500 font-black uppercase tracking-widest mb-2 ml-1">What is the problem? *</label>
          <input v-model="form.title" type="text" required class="input h-12 font-bold" placeholder="Give it a clear title..." />
        </div>
        <div>
          <label class="block text-[11px] text-slate-500 font-black uppercase tracking-widest mb-2 ml-1">More Details *</label>
          <textarea v-model="form.description" required rows="4" class="input resize-none pt-4 font-bold" placeholder="Explain what is happening..."></textarea>
        </div>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
          <div>
            <label class="block text-[11px] text-slate-500 font-black uppercase tracking-widest mb-2 ml-1">Where is it? *</label>
            <input v-model="form.location" type="text" required class="input h-12 font-bold" placeholder="e.g. South Wollo" />
          </div>
          <div>
            <label class="block text-[11px] text-slate-500 font-black uppercase tracking-widest mb-2 ml-1">Phone / Email</label>
            <input v-model="form.contact_info" type="text" class="input h-12 font-bold" placeholder="How to reach you" />
          </div>
        </div>
        <div class="flex justify-end gap-3 pt-6 border-t border-slate-100">
          <button type="button" @click="showCreate = false" class="btn btn-secondary px-8 h-12 text-[11px] font-black uppercase tracking-widest">Cancel</button>
          <button type="submit" class="btn btn-primary px-12 h-12 text-[11px] font-black uppercase tracking-widest shadow-xl shadow-blue-500/20">Submit Final Report</button>
        </div>
      </form>
    </Modal>

    <!-- Feedback Modal -->
    <Modal :show="showFeedback" title="Leave a Review" @close="showFeedback = false">
      <form @submit.prevent="submitFeedback" class="space-y-6">
        <div>
          <label class="block text-[11px] text-slate-500 font-black uppercase tracking-widest mb-3 ml-1">How would you rate the help?</label>
          <div class="flex justify-between px-4 py-6 bg-slate-50 rounded-2xl border border-slate-100 shadow-inner">
            <button v-for="i in 5" :key="i" type="button" @click="feedbackForm.rating = i" class="text-3xl transition-all duration-300 transform" :class="i <= feedbackForm.rating ? 'scale-110 grayscale-0' : 'scale-90 grayscale opacity-40'">
              {{ i <= feedbackForm.rating ? '⭐' : '☆' }}
            </button>
          </div>
        </div>
        <div>
          <label class="block text-[11px] text-slate-500 font-black uppercase tracking-widest mb-2 ml-1">Share your experience *</label>
          <textarea v-model="feedbackForm.feedback" required rows="3" class="input resize-none pt-4 font-bold" placeholder="Helpful comments..."></textarea>
        </div>
        <div class="flex justify-end gap-3 pt-6 border-t border-slate-100">
          <button type="button" @click="showFeedback = false" class="btn btn-secondary px-8 h-12 text-[11px] font-black uppercase tracking-widest">Cancel</button>
          <button type="submit" class="btn btn-primary px-12 h-12 text-[11px] font-black uppercase tracking-widest shadow-xl shadow-blue-500/20">Send Review</button>
        </div>
      </form>
    </Modal>  </div>
</template>

<script setup>
import { ref, reactive, onMounted } from 'vue'
import { useAuthStore } from '@/stores/auth'
import { useNotificationStore } from '@/stores/notification'
import api from '@/services/api'
import StatusBadge from '@/components/StatusBadge.vue'
import Modal from '@/components/Modal.vue'
import LoadingSkeleton from '@/components/LoadingSkeleton.vue'
import EmptyState from '@/components/EmptyState.vue'

const auth = useAuthStore(); const notif = useNotificationStore()
const problems = ref([]); const loading = ref(true); const error = ref(null)
const showCreate = ref(false); const showFeedback = ref(false); const feedbackProblem = ref(null)
const form = reactive({ title: '', description: '', location: '', contact_info: '' })
const feedbackForm = reactive({ rating: 3, feedback: '' })

async function fetchProblems() {
  loading.value = true; error.value = null
  try { const { data } = await api.get('/community-problems'); problems.value = data.data || data }
  catch (err) { error.value = err.response?.data?.message || 'Failed' } finally { loading.value = false }
}

async function submitProblem() {
  try { await api.post('/community-problems', form); notif.success('Submitted!'); showCreate.value = false; fetchProblems() }
  catch (err) { notif.error(err.response?.data?.message || 'Failed') }
}

async function claimProblem(p) {
  try { await api.post(`/community-problems/${p.id}/claim`); notif.success('Claimed!'); fetchProblems() }
  catch (err) { notif.error(err.response?.data?.message || 'Failed') }
}

async function completeProblem(p) {
  try { await api.post(`/community-problems/${p.id}/complete`); notif.success('Completed!'); fetchProblems() }
  catch (err) { notif.error(err.response?.data?.message || 'Failed') }
}

function openFeedback(p) { feedbackProblem.value = p; feedbackForm.rating = 3; feedbackForm.feedback = ''; showFeedback.value = true }

async function submitFeedback() {
  try { await api.post(`/community-problems/${feedbackProblem.value.id}/feedback`, feedbackForm); notif.success('Feedback added!'); showFeedback.value = false; fetchProblems() }
  catch (err) { notif.error(err.response?.data?.message || 'Failed') }
}

onMounted(fetchProblems)
</script>
