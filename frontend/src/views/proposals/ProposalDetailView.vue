<template>
  <div class="flex flex-col gap-8 pb-12 animate-fade card">
    <!-- Header -->
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-6">
      <div>
        <router-link to="/app/proposals" class="flex items-center gap-2 text-brand font-black uppercase tracking-widest text-[10px] mb-3 hover:translate-x-1 transition-transform">
          <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M10 19l-7-7m0 0l7-7m-7 7h18" /></svg>
          Back to list
        </router-link>
        <h1 class="text-3xl font-black text-slate-900 tracking-tight leading-tight max-w-2xl">{{ proposal.title || 'Submission View' }}</h1>
        <p class="text-slate-500 font-medium mt-1 uppercase tracking-widest text-[9px]">Submission details and tracking.</p>
      </div>
      <div v-if="!loading" class="flex items-center gap-3">
        <StatusBadge :status="proposal.status?.name" size="lg" />
      </div>
    </div>

    <div v-if="loading" class="grid grid-cols-1 lg:grid-cols-3 gap-8">
      <div class="lg:col-span-2 space-y-8">
        <div class="card h-48 animate-pulse bg-slate-50/50"></div>
        <div class="card h-96 animate-pulse bg-slate-50/50"></div>
      </div>
      <div class="card h-64 animate-pulse bg-slate-50/50"></div>
    </div>

    <div v-else-if="error" class="card border-rose-100 bg-rose-50/30 p-12 text-center shadow-xl shadow-rose-500/5 max-w-2xl mx-auto font-bold uppercase tracking-widest text-xs">
       <div class="w-16 h-16 bg-rose-100 text-rose-600 rounded-2xl flex items-center justify-center mx-auto mb-5 text-2xl shadow-inner">⚠️</div>
       <p class="text-rose-600 mb-6">{{ error }}</p>
       <button @click="fetchProposal" class="btn bg-rose-600 text-white px-8 h-11 border-0">Retry</button>
    </div>

    <template v-else>
      <!-- Action Bar -->
      <div class="card p-4 bg-slate-50/50 border border-slate-100 flex flex-wrap gap-4 shadow-inner font-black uppercase tracking-widest">
        <button v-if="auth.hasRole('super_admin','research_admin')" @click="checkOriginality" class="btn bg-indigo-600 hover:bg-indigo-700 text-white h-11 px-8 text-[11px] shadow-lg shadow-indigo-600/20">Check Originality</button>
        <router-link v-if="auth.hasRole('super_admin','research_admin') && proposal.status?.name === 'approved'" :to="`/app/projects/create-from-proposal/${proposal.id}`" class="btn bg-teal-500 hover:bg-teal-600 text-white h-11 px-8 text-[11px] shadow-lg shadow-teal-500/20 flex items-center justify-center">Convert To Project</router-link>
        <button v-if="proposal.status?.name === 'draft' && isOwner" @click="submitProposal" class="btn btn-primary h-11 px-8 text-[11px] shadow-lg shadow-blue-500/20">Submit Final</button>
        <button v-if="auth.hasRole('super_admin','research_admin') && canApprove" @click="approveProposal" class="btn bg-emerald-500 hover:bg-emerald-600 text-white h-11 px-8 text-[11px] shadow-lg shadow-emerald-500/20">Approve</button>
        <button v-if="auth.hasRole('super_admin','research_admin') && canApprove" @click="showReject = true" class="btn bg-rose-600 hover:bg-rose-700 text-white h-11 px-8 text-[11px] shadow-lg shadow-rose-600/20">Reject</button>
        <button v-if="auth.hasRole('super_admin','research_admin') && (proposal.status?.name === 'submitted' || proposal.status?.name === 'under_review')" @click="showAssignReviewers = true" class="btn bg-amber-500 hover:bg-amber-600 text-white h-11 px-8 text-[11px] shadow-lg shadow-amber-500/20">Assign Peer Reviewers</button>
      </div>

      <div class="grid grid-cols-1 lg:grid-cols-3 gap-8 font-bold">
        <!-- Main Content -->
        <div class="lg:col-span-2 space-y-8">
          <!-- Quick Summary -->
          <div class="card p-8 border-l-4 border-l-brand/20">
            <h2 class="text-xs font-black text-slate-400 uppercase tracking-widest mb-8 flex items-center gap-2">
              <span class="w-1 h-3 bg-brand rounded-full"></span>
              Institutional Metadata
            </h2>
            <dl class="grid grid-cols-1 sm:grid-cols-2 gap-8 text-sm">
              <div>
                <dt class="text-[10px] font-black text-slate-400 uppercase tracking-widest mb-1.5 ml-1">Research Title</dt>
                <dd class="text-sm font-black text-slate-800 bg-slate-50 p-4 rounded-xl border border-slate-100">{{ proposal.title }}</dd>
              </div>
               <div>
                <dt class="text-[10px] font-black text-slate-400 uppercase tracking-widest mb-1.5 ml-1">Year & Allocation</dt>
                <dd class="p-4 rounded-xl bg-white border border-slate-100 flex items-center justify-between">
                  <span class="text-slate-700">{{ proposal.academic_year?.name || 'N/A' }}</span>
                  <span class="text-emerald-600 font-black">{{ formatCurrency(proposal.budget) }}</span>
                </dd>
              </div>
              <div>
                <dt class="text-[10px] font-black text-slate-400 uppercase tracking-widest mb-1.5 ml-1">Work Type</dt>
                <dd class="p-4 rounded-xl bg-white border border-slate-100">
                  <span class="px-2.5 py-1 bg-slate-100 text-slate-600 rounded-lg text-[10px] font-black uppercase tracking-widest">{{ proposal.type?.name || 'N/A' }}</span>
                </dd>
              </div>
              <div>
                <dt class="text-[10px] font-black text-slate-400 uppercase tracking-widest mb-1.5 ml-1">Principal Investigator</dt>
                <dd class="p-4 rounded-xl bg-white border border-slate-100 text-slate-700 flex items-center gap-2">
                  {{ proposal.submitted_by?.name || 'N/A' }}
                  <span v-if="proposal.submitted_by?.department" class="text-[10px] font-black text-slate-400">({{ proposal.submitted_by.department.name }})</span>
                </dd>
              </div>
            </dl>
          </div>

          <!-- Research Details -->
          <div class="card p-8">
            <h2 class="text-xs font-black text-slate-400 uppercase tracking-widest mb-8 flex items-center gap-2">
              <span class="w-1 h-3 bg-brand rounded-full"></span>
              Methodology & Abstract
            </h2>
            <div class="space-y-10">
              <div>
                <p class="text-[10px] font-black text-slate-400 uppercase tracking-widest mb-3 ml-1">Domain Keywords</p>
                <div class="flex flex-wrap gap-2">
                  <span v-for="kw in proposal.keywords?.split(',')" :key="kw" class="px-3 py-1.5 bg-slate-50 text-slate-600 border border-slate-100 rounded-xl text-[10px] font-black uppercase tracking-widest hover:bg-white hover:border-brand transition-colors cursor-default">{{ kw.trim() }}</span>
                </div>
              </div>
              <div>
                <p class="text-[10px] font-black text-slate-400 uppercase tracking-widest mb-3 ml-1">Research Abstract</p>
                <p class="text-sm font-medium text-slate-600 leading-relaxed italic border-l-4 border-slate-100 pl-6">{{ proposal.abstract }}</p>
              </div>
              <div v-if="proposal.objectives">
                <p class="text-[10px] font-black text-slate-400 uppercase tracking-widest mb-3 ml-1">Specific Objectives</p>
                <pre class="whitespace-pre-wrap font-inter text-sm text-slate-600 bg-slate-50 p-6 rounded-2xl border border-slate-100 leading-relaxed">{{ proposal.objectives }}</pre>
              </div>
               <div v-if="proposal.methodology">
                <p class="text-[10px] font-black text-slate-400 uppercase tracking-widest mb-3 ml-1">Methodology</p>
                <div class="text-sm font-medium text-slate-600 leading-relaxed">{{ proposal.methodology }}</div>
              </div>
            </div>
          </div>
        </div>

        <!-- Sidebar -->
        <div class="flex flex-col gap-8">
          <!-- Research Team -->
          <div class="card p-8">
            <h2 class="text-xs font-black text-slate-400 uppercase tracking-widest mb-6 flex items-center gap-2">
              <span class="w-1 h-3 bg-brand rounded-full"></span>
              Research Team
            </h2>
            <div v-if="proposal.investigators?.length" class="space-y-4">
              <div v-for="inv in proposal.investigators" :key="inv.id" class="flex items-start gap-4 p-4 rounded-2xl bg-slate-50 border border-slate-100 hover:bg-white hover:border-brand transition-all group shadow-sm">
                <div class="w-10 h-10 bg-brand-light text-brand rounded-xl flex items-center justify-center text-xs font-black uppercase shadow-inner group-hover:scale-110 transition-transform shrink-0">{{ getInitials(inv.user?.name || inv.name) }}</div>
                <div class="min-w-0">
                  <p class="text-sm font-black text-slate-800 leading-tight truncate">{{ inv.user?.name || inv.name }}</p>
                  <p class="text-[9px] font-black text-slate-400 uppercase tracking-widest mt-0.5">{{ inv.role?.name || 'Researcher' }}</p>
                  
                  <div class="mt-3 space-y-1">
                    <p v-if="inv.email || inv.user?.email" class="text-[10px] text-slate-500 font-bold flex items-center gap-1.5">
                      <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/></svg>
                      {{ inv.email || inv.user?.email }}
                    </p>
                    <p v-if="inv.institution" class="text-[10px] text-slate-500 font-bold flex items-center gap-1.5 italic">
                      <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/></svg>
                      {{ inv.institution }}
                    </p>
                  </div>
                </div>
              </div>
            </div>
            <p v-else class="text-[10px] font-black text-slate-400 uppercase tracking-widest italic text-center py-6">No co-investigators.</p>
          </div>

          <!-- Reviews Progress -->
          <div class="card p-8">
            <h2 class="text-xs font-black text-slate-400 uppercase tracking-widest mb-6 flex items-center gap-2">
              <span class="w-1 h-3 bg-amber-500 rounded-full"></span>
              Status & Reviews
            </h2>
            
            <div class="space-y-6">
              <!-- Internal Reviewers -->
              <div v-if="proposal.reviewers?.length" class="space-y-3">
                 <p class="text-[10px] font-black text-slate-400 uppercase tracking-widest">Internal Peer Reviewers</p>
                 <div v-for="r in proposal.reviewers" :key="r.id" class="p-4 bg-slate-50 rounded-xl border border-slate-100 relative group">
                    <div class="flex justify-between items-center mb-1">
                      <p class="text-sm font-black text-slate-700">{{ r.name }}</p>
                      <span v-if="r.pivot?.overall_score" class="text-brand font-black">{{ r.pivot.overall_score }}/5</span>
                    </div>
                    <p class="text-[9px] font-black uppercase text-slate-400 tracking-widest">{{ r.pivot?.overall_score ? 'Feedback Provided' : 'Under Review' }}</p>
                 </div>
              </div>
              <p v-else class="text-[10px] font-black text-slate-400 uppercase italic">No reviewers assigned.</p>

              <hr class="border-slate-50" />

              <!-- External Checks -->
              <div class="space-y-4">
                 <p class="text-[10px] font-black text-slate-400 uppercase tracking-widest">Procedural Status</p>
                 <div class="flex items-center justify-between p-3 rounded-lg bg-slate-50 border border-slate-100 shadow-inner">
                    <span class="text-[10px] font-black uppercase text-slate-600">Ethics Clearance</span>
                    <StatusBadge :status="proposal.ethics_status || 'not_requested'" size="sm" />
                 </div>
                 <div class="flex items-center justify-between p-3 rounded-lg bg-slate-50 border border-slate-100 shadow-inner">
                    <span class="text-[10px] font-black uppercase text-slate-600">Finance Audit</span>
                    <StatusBadge :status="proposal.finance_status || 'pending'" size="sm" />
                 </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </template>

    <ConfirmDialog :show="showReject" title="Reject Submission" message="Please explain the decision to the author:" confirmText="Confirm Reject" variant="danger" @confirm="rejectProposal" @cancel="showReject = false">
      <template #extra><textarea v-model="rejectComment" rows="3" class="input resize-none mt-4 bg-slate-50 p-4" placeholder="Decision summary..."></textarea></template>
    </ConfirmDialog>

    <Modal :show="showAssignReviewers" title="Assign Peer Reviewers" @close="showAssignReviewers = false">
       <div class="space-y-6">
          <div class="p-4 bg-slate-50 rounded-2xl border border-slate-100">
             <p class="text-[10px] font-black text-slate-400 uppercase tracking-widest mb-4">Eligible Reviewers (Expertise Match)</p>
             <div class="space-y-2 max-h-60 overflow-y-auto pr-2">
                <label v-for="r in availableReviewers" :key="r.id" class="flex items-center gap-3 p-3 bg-white rounded-xl border border-slate-100 hover:border-brand cursor-pointer group transition-all">
                  <input type="checkbox" :value="r.id" v-model="selectedReviewers" class="w-4 h-4 rounded text-brand focus:ring-brand border-slate-300" />
                  <div class="min-w-0">
                    <p class="text-sm font-black text-slate-800 group-hover:text-brand">{{ r.name }}</p>
                    <p class="text-[9px] font-bold text-slate-400 uppercase">{{ r.email }}</p>
                  </div>
                </label>
             </div>
          </div>
          <div class="flex justify-end gap-3 pt-6 border-t border-slate-100">
             <button @click="showAssignReviewers = false" class="btn btn-secondary px-6">Cancel</button>
             <button @click="assignReviewers" class="btn btn-primary px-8">Assign Selection</button>
          </div>
       </div>
    </Modal>
  </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import { useAuthStore } from '@/stores/auth'
import { useNotificationStore } from '@/stores/notification'
import api from '@/services/api'
import StatusBadge from '@/components/StatusBadge.vue'
import Modal from '@/components/Modal.vue'
import ConfirmDialog from '@/components/ConfirmDialog.vue'
import { formatCurrency, formatDateTime, getInitials } from '@/utils/formatters'

const route = useRoute(); const router = useRouter(); const auth = useAuthStore(); const notif = useNotificationStore()
const proposal = ref({}); const loading = ref(true); const error = ref(null)
const showReject = ref(false); const rejectComment = ref('')
const showAssignReviewers = ref(false); const selectedReviewers = ref([]); const availableReviewers = ref([])

const isOwner = computed(() => auth.user?.id === proposal.value.submitted_by?.id)
const canApprove = computed(() => ['submitted','under_review','finance_check'].includes(proposal.value.status?.name))

async function fetchProposal() {
  loading.value = true; error.value = null
  try {
    const { data } = await api.get(`/proposals/${route.params.id}`)
    proposal.value = data
  } catch (err) {
    error.value = err.response?.data?.message || 'Unauthorized access or network failure'
  } finally {
    loading.value = false
  }
}

async function checkOriginality() {
  try {
    await api.post('/detection/requests', { detectable_type: 'App\\Models\\Proposal', detectable_id: proposal.value.id })
    notif.success('Strategic detection request submitted')
  } catch(e) {
    notif.error('Failed to initialize check')
  }
}

async function submitProposal() {
  try {
    await api.post(`/proposals/${proposal.value.id}/submit`)
    notif.success('Formally submitted for review')
    fetchProposal()
  } catch (err) { notif.error('Recall failed') }
}

async function approveProposal() {
  try {
    await api.post(`/proposals/${proposal.value.id}/approve`)
    notif.success('Proposal indexed as approved')
    fetchProposal()
  } catch (err) { notif.error('Action failed') }
}

async function rejectProposal() {
  if (!rejectComment.value) { notif.error('Reason required'); return }
  try {
    await api.post(`/proposals/${proposal.value.id}/reject`, { comment: rejectComment.value })
    notif.success('Handled as rejected')
    showReject.value = false; fetchProposal()
  } catch (err) { notif.error('Action failed') }
}

async function assignReviewers() {
  if (selectedReviewers.value.length === 0) { notif.error('Select at least one'); return }
  try {
    await api.post(`/proposals/${proposal.value.id}/assign-reviewers`, { reviewer_ids: selectedReviewers.value })
    notif.success('Peer assignment successful')
    showAssignReviewers.value = false; fetchProposal()
  } catch (err) { notif.error('Assignment failed') }
}

onMounted(async () => {
  await fetchProposal()
  try {
    const { data } = await api.get(`/proposals/${route.params.id}/suggest-reviewers`)
    availableReviewers.value = data.data || data
  } catch (e) {}
})
</script>
