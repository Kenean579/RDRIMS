<template>
  <div class="flex flex-col gap-8 pb-12 animate-fade card">
    <!-- Header -->
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-6">
      <div>
        <router-link to="/proposals" class="flex items-center gap-2 text-brand font-black uppercase tracking-widest text-[10px] mb-3 hover:translate-x-1 transition-transform inline-block">
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
        <div class="card h-48 animate-pulse bg-slate-50/50 rounded-3xl"></div>
        <div class="card h-96 animate-pulse bg-slate-50/50 rounded-3xl"></div>
      </div>
      <div class="card h-64 animate-pulse bg-slate-50/50 rounded-3xl"></div>
    </div>

    <div v-else-if="error" class="card border-rose-100 bg-rose-50/30 p-12 text-center shadow-xl shadow-rose-500/5 max-w-2xl mx-auto">
       <div class="w-16 h-16 bg-rose-100 text-rose-600 rounded-2xl flex items-center justify-center mx-auto mb-5 text-2xl shadow-inner">⚠️</div>
       <h3 class="text-lg font-black text-slate-800 mb-2 uppercase tracking-tight">Access Error</h3>
       <p class="text-sm text-rose-600 font-bold mb-6 uppercase tracking-widest text-[11px] leading-relaxed">{{ error }}</p>
       <button @click="fetchProposal" class="btn bg-rose-600 hover:bg-rose-700 text-white px-8 h-11 text-[11px] font-black uppercase tracking-widest border-0">Retry</button>
    </div>

    <template v-else>
      <!-- Action Bar -->
      <div class="card p-4 bg-slate-50/50 border border-slate-100 flex flex-wrap gap-4 shadow-inner">
        <button v-if="auth.hasRole('super_admin','research_admin')" @click="checkOriginality" class="btn bg-indigo-600 hover:bg-indigo-700 text-white h-11 px-8 text-[11px] font-black uppercase tracking-widest shadow-lg shadow-indigo-600/20">Check Originality</button>
        <router-link v-if="auth.hasRole('super_admin','research_admin') && proposal.status?.name === 'approved'" :to="`/projects/create-from-proposal/${proposal.id}`" class="btn bg-teal-500 hover:bg-teal-600 text-white h-11 px-8 text-[11px] font-black uppercase tracking-widest shadow-lg shadow-teal-500/20 flex items-center justify-center">Convert To Project</router-link>
        <button v-if="proposal.status?.name === 'draft' && isOwner" @click="submitProposal" class="btn btn-primary h-11 px-8 text-[11px] font-black uppercase tracking-widest shadow-lg shadow-blue-500/20">Submit Final Final</button>
        <button v-if="auth.hasRole('super_admin','research_admin') && canApprove" @click="approveProposal" class="btn bg-emerald-500 hover:bg-emerald-600 text-white h-11 px-8 text-[11px] font-black uppercase tracking-widest shadow-lg shadow-emerald-500/20">Approve</button>
        <button v-if="auth.hasRole('super_admin','research_admin') && canApprove" @click="showReject = true" class="btn bg-rose-600 hover:bg-rose-700 text-white h-11 px-8 text-[11px] font-black uppercase tracking-widest shadow-lg shadow-rose-600/20">Reject</button>
        <button v-if="auth.hasRole('super_admin','research_admin') && proposal.status?.name === 'submitted'" @click="showAssignReviewers = true" class="btn bg-amber-500 hover:bg-amber-600 text-white h-11 px-8 text-[11px] font-black uppercase tracking-widest shadow-lg shadow-amber-500/20">Find Reviews</button>
      </div>

      <div class="grid grid-cols-1 lg:grid-cols-3 gap-8 font-bold">
        <!-- Main Content -->
        <div class="lg:col-span-2 space-y-8">
          <!-- Quick Summary -->
          <div class="card p-8 border-l-4 border-l-brand/20">
            <h2 class="text-xs font-black text-slate-400 uppercase tracking-widest mb-8 flex items-center gap-2">
              <span class="w-1 h-3 bg-brand rounded-full"></span>
              Quick Summary
            </h2>
            <dl class="grid grid-cols-1 sm:grid-cols-2 gap-8 text-sm">
              <div>
                <dt class="text-[10px] font-black text-slate-400 uppercase tracking-widest mb-1.5 ml-1">Title</dt>
                <dd class="text-sm font-black text-slate-800 bg-slate-50 p-4 rounded-2xl border border-slate-100 shadow-inner">{{ proposal.title }}</dd>
              </div>
              <div>
                <dt class="text-[10px] font-black text-slate-400 uppercase tracking-widest mb-1.5 ml-1">Current Status</dt>
                <dd class="p-4 rounded-2xl bg-white border border-slate-100 shadow-sm flex items-center justify-between">
                  <StatusBadge :status="proposal.status?.name" />
                </dd>
              </div>
              <div>
                <dt class="text-[10px] font-black text-slate-400 uppercase tracking-widest mb-1.5 ml-1">Work Type</dt>
                <dd class="p-4 rounded-2xl bg-white border border-slate-100 shadow-sm">
                  <span class="px-2.5 py-1 bg-slate-100 text-slate-600 rounded-lg text-[10px] font-black uppercase tracking-widest">{{ proposal.type?.name || 'N/A' }}</span>
                </dd>
              </div>
              <div>
                <dt class="text-[10px] font-black text-slate-400 uppercase tracking-widest mb-1.5 ml-1">Budget</dt>
                <dd class="p-4 rounded-2xl bg-white border border-slate-100 shadow-sm font-black text-emerald-600">{{ formatCurrency(proposal.budget) }}</dd>
              </div>
              <div>
                <dt class="text-[10px] font-black text-slate-400 uppercase tracking-widest mb-1.5 ml-1">Lead Author</dt>
                <dd class="p-4 rounded-2xl bg-white border border-slate-100 shadow-sm text-slate-700">{{ proposal.submitted_by?.name || 'N/A' }}</dd>
              </div>
              <div>
                <dt class="text-[10px] font-black text-slate-400 uppercase tracking-widest mb-1.5 ml-1">Submitted On</dt>
                <dd class="p-4 rounded-2xl bg-white border border-slate-100 shadow-sm text-slate-700">{{ formatDateTime(proposal.submitted_at) }}</dd>
              </div>
            </dl>
          </div>

          <!-- Research Details -->
          <div class="card p-8">
            <h2 class="text-xs font-black text-slate-400 uppercase tracking-widest mb-8 flex items-center gap-2">
              <span class="w-1 h-3 bg-brand rounded-full"></span>
              Full Details
            </h2>
            <div class="space-y-10">
              <div>
                <p class="text-[10px] font-black text-slate-400 uppercase tracking-widest mb-3 ml-1">Keywords</p>
                <div class="flex flex-wrap gap-2">
                  <span v-for="kw in proposal.keywords?.split(',')" :key="kw" class="px-3 py-1.5 bg-slate-50 text-slate-600 border border-slate-100 rounded-xl text-[10px] font-black uppercase tracking-widest hover:bg-white hover:border-brand transition-colors cursor-default">{{ kw.trim() }}</span>
                </div>
              </div>
              <div>
                <p class="text-[10px] font-black text-slate-400 uppercase tracking-widest mb-3 ml-1">Abstract</p>
                <p class="text-sm font-medium text-slate-600 leading-relaxed italic border-l-4 border-slate-100 pl-6">{{ proposal.abstract }}</p>
              </div>
              <div>
                <p class="text-[10px] font-black text-slate-400 uppercase tracking-widest mb-3 ml-1">Objectives</p>
                <p class="text-sm font-medium text-slate-600 leading-relaxed bg-slate-50 p-6 rounded-3xl border border-slate-100 shadow-inner">{{ proposal.objectives }}</p>
              </div>
              <div>
                <p class="text-[10px] font-black text-slate-400 uppercase tracking-widest mb-3 ml-1">Methodology</p>
                <p class="text-sm font-medium text-slate-600 leading-relaxed bg-slate-50 p-6 rounded-3xl border border-slate-100 shadow-inner">{{ proposal.methodology }}</p>
              </div>
            </div>
          </div>
        </div>

        <div class="flex flex-col gap-8">
          <!-- Team Members -->
          <div class="card p-8">
            <h2 class="text-xs font-black text-slate-400 uppercase tracking-widest mb-6 flex items-center gap-2">
              <span class="w-1 h-3 bg-brand rounded-full"></span>
              Team Members
            </h2>
            <div v-if="proposal.investigators?.length" class="space-y-4">
              <div v-for="inv in proposal.investigators" :key="inv.id" class="flex items-center gap-4 p-4 rounded-2xl bg-slate-50 border border-slate-100 hover:bg-white hover:border-brand hover:shadow-lg transition-all group shadow-sm">
                <div class="w-10 h-10 bg-brand-light text-brand rounded-xl flex items-center justify-center text-xs font-black uppercase shadow-inner group-hover:scale-110 transition-transform">{{ getInitials(inv.user?.name || inv.name) }}</div>
                <div>
                  <p class="text-sm font-black text-slate-800 leading-tight">{{ inv.user?.name || inv.name }}</p>
                  <p class="text-[9px] font-black text-slate-400 uppercase tracking-widest mt-0.5">{{ inv.role?.name || 'Partner' }}</p>
                </div>
              </div>
            </div>
            <p v-else class="text-[10px] font-black text-slate-400 uppercase tracking-widest italic text-center py-6">No members listed yet.</p>
          </div>

          <!-- Reviews -->
          <div class="card p-8 font-bold">
            <h2 class="text-xs font-black text-slate-400 uppercase tracking-widest mb-6 flex items-center gap-2">
              <span class="w-1 h-3 bg-brand rounded-full"></span>
              Reviews
            </h2>
            <div v-if="proposal.reviewers?.length" class="space-y-4">
              <div v-for="r in proposal.reviewers" :key="r.id" class="p-4 bg-slate-50 rounded-2xl border border-slate-100 shadow-inner">
                <div class="flex justify-between items-center mb-2">
                  <p class="text-sm font-black text-slate-800">{{ r.name }}</p>
                  <span v-if="r.pivot?.overall_score" class="px-2 py-0.5 bg-emerald-50 text-emerald-600 text-[10px] font-black rounded-lg border border-emerald-100">{{ r.pivot.overall_score }} / 5</span>
                </div>
                <p class="text-[9px] font-black text-slate-400 uppercase tracking-widest">{{ r.pivot?.overall_score ? 'Review Completed' : 'Waiting for review' }}</p>
              </div>
            </div>
            <p v-else class="text-[10px] font-black text-slate-400 uppercase tracking-widest italic text-center py-6">Not assigned for review yet.</p>
          </div>
        </div>
      </div>
    </template>
    <ConfirmDialog :show="showReject" title="Reject Proposal" message="Provide reason:" confirmText="Reject" variant="danger" @confirm="rejectProposal" @cancel="showReject = false"><template #extra><textarea v-model="rejectComment" rows="3" class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm mt-2" placeholder="Reason..."></textarea></template></ConfirmDialog>
    <div v-if="showAssignReviewers" class="fixed inset-0 z-50 flex items-center justify-center"><div class="fixed inset-0 bg-black/50" @click="showAssignReviewers = false"></div><div class="relative bg-white rounded-lg shadow-xl p-6 w-full max-w-md mx-4"><h3 class="text-lg font-semibold text-gray-800 mb-4">Assign Reviews</h3><div class="space-y-2 max-h-60 overflow-y-auto"><label v-for="r in availableReviewers" :key="r.id" class="flex items-center gap-3 p-2 rounded hover:bg-gray-50 cursor-pointer"><input type="checkbox" :value="r.id" v-model="selectedReviewers" class="rounded border-gray-300 text-blue-600" /><span class="text-sm text-gray-800">{{ r.name }}</span></label></div><div class="flex justify-end gap-3 mt-4"><button @click="showAssignReviewers = false" class="px-4 py-2 text-sm border border-gray-300 rounded-lg hover:bg-gray-50">Cancel</button><button @click="assignReviewers" class="px-4 py-2 text-sm bg-blue-600 text-white rounded-lg hover:bg-blue-700">Assign</button></div></div></div>
  </div>
</template>
<script setup>
import { ref, computed, onMounted } from 'vue'
import { useRoute } from 'vue-router'
import { useAuthStore } from '@/stores/auth'
import { useNotificationStore } from '@/stores/notification'
import api from '@/services/api'
import StatusBadge from '@/components/StatusBadge.vue'
import ConfirmDialog from '@/components/ConfirmDialog.vue'
import { formatCurrency, formatDateTime, getInitials } from '@/utils/formatters'
const route = useRoute(); const auth = useAuthStore(); const notif = useNotificationStore()
const proposal = ref({}); const loading = ref(true); const error = ref(null)
const showReject = ref(false); const rejectComment = ref('')
const showAssignReviewers = ref(false); const selectedReviewers = ref([]); const availableReviewers = ref([])
const isOwner = computed(() => auth.user?.id === proposal.value.submitted_by?.id)
            import { useRouter } from 'vue-router'
            const router = useRouter()
            
            async function checkOriginality() {
              try {
                await api.post('/detection/requests', { detectable_type: 'App\\Models\\Proposal', detectable_id: proposal.value.id });
                notif.success('Detection request submitted!');
              } catch(e) {
                notif.error('Failed to submit detection check');
              }
            }
const canApprove = computed(() => ['submitted','under_review','finance_check'].includes(proposal.value.status?.name))
async function fetchProposal() { loading.value = true; error.value = null; try { const { data } = await api.get(`/proposals/${route.params.id}`); proposal.value = data } catch (err) { error.value = err.response?.data?.message || 'Failed' } finally { loading.value = false } }
async function submitProposal() { try { await api.post(`/proposals/${proposal.value.id}/submit`); notif.success('Submitted!'); fetchProposal() } catch (err) { notif.error(err.response?.data?.message || 'Failed') } }
async function approveProposal() { try { await api.post(`/proposals/${proposal.value.id}/approve`); notif.success('Approved!'); fetchProposal() } catch (err) { notif.error(err.response?.data?.message || 'Failed') } }
async function rejectProposal() { try { await api.post(`/proposals/${proposal.value.id}/reject`, { comment: rejectComment.value }); notif.success('Rejected.'); showReject.value = false; fetchProposal() } catch (err) { notif.error(err.response?.data?.message || 'Failed') } }
async function assignReviewers() { try { await api.post(`/proposals/${proposal.value.id}/assign-reviewers`, { reviewer_ids: selectedReviewers.value }); notif.success('Assigned!'); showAssignReviewers.value = false; fetchProposal() } catch (err) { notif.error(err.response?.data?.message || 'Failed') } }
onMounted(async () => { await fetchProposal(); try { const { data } = await api.get(`/proposals/${route.params.id}/suggest-reviewers`); availableReviewers.value = data.data || data } catch (e) {} })
</script>
