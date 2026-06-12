<template>
  <div class="flex flex-col gap-5 pb-6 animate-fade card">
    <!-- Header -->
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-6">
      <div>
        <router-link to="/app/proposals" class="flex items-center gap-2 text-brand font-bold text-xs mb-3 hover:translate-x-1 transition-transform">
          <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M10 19l-7-7m0 0l7-7m-7 7h18" /></svg>
          Back to list
        </router-link>
        <h1 class="text-xl font-bold text-slate-900 tracking-tight leading-tight max-w-2xl">{{ proposal.title || 'Submission View' }}</h1>
        <p class="text-slate-500 font-medium mt-1 text-xs">Submission details and tracking.</p>
      </div>
      <div v-if="!loading" class="flex items-center gap-3">
        <StatusBadge :status="proposal.status?.name" size="lg" />
      </div>
    </div>

    <div v-if="loading" class="grid grid-cols-1 lg:grid-cols-3 gap-5">
      <div class="lg:col-span-2 space-y-8">
        <div class="card h-48 animate-pulse bg-slate-50/50"></div>
        <div class="card h-96 animate-pulse bg-slate-50/50"></div>
      </div>
      <div class="card h-64 animate-pulse bg-slate-50/50"></div>
    </div>

    <div v-else-if="error" class="card border-rose-100 bg-rose-50/30 p-8 text-center shadow-xl shadow-rose-500/5 max-w-2xl mx-auto text-xs">
       <div class="w-16 h-16 bg-rose-100 text-rose-600 rounded-2xl flex items-center justify-center mx-auto mb-5 text-2xl">⚠️</div>
       <p class="text-rose-600 mb-6">{{ error }}</p>
       <button @click="fetchProposal" class="btn bg-rose-600 text-white px-5 h-11 border-0">Retry</button>
    </div>

    <template v-else>
      <!-- Action Bar -->
      <div class="card p-4 bg-slate-50/50 border border-slate-100 flex flex-wrap gap-4">
        <!-- Edit Logic: Draft Only + Owner/Admin -->
        <router-link v-if="canEditProposal" :to="`/app/proposals/${proposal.id}/edit`" class="btn bg-white border border-slate-100 text-slate-700 hover:text-brand hover:border-brand h-11 px-5 text-xs shadow-sm flex items-center gap-2">
          <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 002 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path></svg>
          Edit Details
        </router-link>

        <!-- Plagiarism Check: Admins Only -->
        <button v-if="canManageProposal && proposal.status?.name === 'submitted'" @click="runChecks" :disabled="isChecking" class="btn bg-indigo-600 hover:bg-indigo-700 text-white h-11 px-5 text-xs">
          <svg v-if="isChecking" class="w-4 h-4 mr-1.5 animate-spin" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"/></svg>
          <svg v-else class="w-4 h-4 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
          {{ isChecking ? 'Running Checks...' : 'Run Automated Checks' }}
        </button>
        
        <!-- Registration as Project: Approved Only + Admin -->
        <router-link v-if="canConvertToProject" :to="`/app/projects/create-from-proposal/${proposal.id}`" class="btn bg-teal-500 hover:bg-teal-600 text-white h-11 px-5 text-xs flex items-center justify-center">Convert To Project</router-link>
        
        <!-- Submit: Owner Only + Draft Only -->
        <button v-if="proposal.status?.name === 'draft' && isOwner" @click="submitProposal" class="btn btn-primary h-11 px-5 text-xs">Submit Final</button>
        
        <!-- Admin Workflow: Submitted / Under Review / Processing -->
        <template v-if="canAssignReviewers">
          <button @click="showAssignReviewers = true" class="btn bg-amber-500 hover:bg-amber-600 text-white h-11 px-5 text-xs">Assign Reviewers</button>
          <button v-if="proposal.status?.name === 'submitted'" @click="sendToFinance" class="btn bg-slate-700 hover:bg-slate-800 text-white h-11 px-5 text-xs">Send to Finance</button>
          <button v-if="proposal.status?.name === 'finance_check'" @click="generateEthics" class="btn bg-slate-700 hover:bg-slate-800 text-white h-11 px-5 text-xs">Generate Ethics IRB</button>
          
          <button v-if="canApprove" @click="approveProposal" class="btn bg-emerald-500 hover:bg-emerald-600 text-white h-11 px-5 text-xs">Final Approve</button>
          <button v-if="canApprove" @click="showReject = true" class="btn bg-rose-600 hover:bg-rose-700 text-white h-11 px-5 text-xs">Reject</button>
        </template>

        <!-- Finance Officer Check -->
        <template v-if="auth.hasRole('finance_officer') && proposal.status?.name === 'finance_check'">
          <button @click="verifyBudget" class="btn bg-emerald-500 hover:bg-emerald-600 text-white h-11 px-5 text-xs">Verify Budget</button>
          <button @click="showReject = true; rejectSource = 'finance'" class="btn bg-rose-600 hover:bg-rose-700 text-white h-11 px-5 text-xs">Budget Revision</button>
        </template>

        <!-- Ethics Officer Check -->
        <template v-if="auth.hasRole('ethics_officer') && proposal.status?.name === 'ethics_pending'">
          <button @click="verifyEthics" class="btn bg-emerald-500 hover:bg-emerald-600 text-white h-11 px-5 text-xs">Ethical Clearance</button>
          <button @click="showReject = true; rejectSource = 'ethics'" class="btn bg-rose-600 hover:bg-rose-700 text-white h-11 px-5 text-xs">Ethics Rejection</button>
        </template>
      </div>

      <!-- Tabs -->
      <div class="card p-2 bg-slate-50/50 border border-slate-100 flex flex-wrap gap-2">
        <button v-for="t in tabs" :key="t.key" @click="activeTab = t.key"
          class="px-5 py-2.5 rounded-xl text-xs font-bold transition-all"
          :class="activeTab === t.key ? 'bg-brand text-white shadow-md shadow-brand/20' : 'bg-white text-slate-600 hover:text-brand border border-slate-200'">
          {{ t.label }}
        </button>
      </div>

      <div class="grid grid-cols-1 lg:grid-cols-3 gap-5 font-bold">
        <!-- Main Content -->
        <div class="lg:col-span-2 space-y-8">
          <!-- Quick Summary -->
          <div class="card p-8">
            <h2 class="text-xs font-medium text-slate-400 mb-5 flex items-center gap-2">
              <span class="w-1 h-3 bg-brand rounded-full"></span>
              Institutional Metadata
            </h2>
            <dl class="grid grid-cols-1 sm:grid-cols-2 gap-5 text-sm">
              <div>
                <dt class="text-xs font-medium text-slate-400 mb-1.5 ml-1">Research Title</dt>
                <dd class="text-sm font-bold text-slate-800 bg-slate-50 p-4 rounded-2xl border border-slate-100">{{ proposal.title }}</dd>
              </div>
               <div>
                <dt class="text-xs font-medium text-slate-400 mb-1.5 ml-1">Year & Allocation</dt>
                <dd class="p-4 rounded-2xl bg-white border border-slate-100 flex items-center justify-between">
                  <span class="text-slate-700">{{ proposal.academic_year?.name || 'N/A' }}</span>
                  <span class="text-emerald-600 font-bold">{{ formatCurrency(proposal.budget) }}</span>
                </dd>
              </div>
              <div>
                <dt class="text-xs font-medium text-slate-400 mb-1.5 ml-1">Work Type</dt>
                <dd class="p-4 rounded-2xl bg-white border border-slate-100">
                  <span class="px-2.5 py-1 bg-slate-100 text-slate-600 rounded-2xl text-xs font-medium">{{ proposal.type?.name || 'N/A' }}</span>
                </dd>
              </div>
              <div>
                <dt class="text-xs font-medium text-slate-400 mb-1.5 ml-1">Principal Investigator</dt>
                <dd class="p-4 rounded-2xl bg-white border border-slate-100 text-slate-700 flex items-center gap-2">
                  {{ proposal.submitted_by?.name || 'N/A' }}
                  <span v-if="proposal.submitted_by?.department" class="text-xs font-medium text-slate-400">({{ proposal.submitted_by.department.name }})</span>
                </dd>
              </div>
            </dl>
          </div>

          <!-- Research Details -->
          <div class="card p-8">
            <h2 class="text-xs font-medium text-slate-400 mb-5 flex items-center gap-2">
              <span class="w-1 h-3 bg-brand rounded-full"></span>
              Methodology & Abstract
            </h2>
            <div class="space-y-10">
              <div>
                <p class="text-xs font-medium text-slate-400 mb-3 ml-1">Domain Keywords</p>
                <div class="flex flex-wrap gap-2">
                  <span v-for="kw in proposal.keywords?.split(',')" :key="kw" class="px-3 py-1.5 bg-slate-50 text-slate-600 border border-slate-100 rounded-2xl text-xs font-medium hover:bg-white hover:border-brand transition-colors cursor-default">{{ kw.trim() }}</span>
                </div>
              </div>
              <div>
                <p class="text-xs font-medium text-slate-400 mb-3 ml-1">Research Abstract</p>
                <p class="text-sm font-medium text-slate-600 leading-relaxed italic border-l-4 border-slate-100 pl-6">{{ proposal.abstract }}</p>
              </div>
              <div v-if="proposal.objectives">
                <p class="text-xs font-medium text-slate-400 mb-3 ml-1">Specific Objectives</p>
                <pre class="whitespace-pre-wrap font-inter text-sm text-slate-600 bg-slate-50 p-6 rounded-2xl border border-slate-100 leading-relaxed">{{ proposal.objectives }}</pre>
              </div>
               <div v-if="proposal.methodology">
                <p class="text-xs font-medium text-slate-400 mb-3 ml-1">Methodology</p>
                <div class="text-sm font-medium text-slate-600 leading-relaxed">{{ proposal.methodology }}</div>
              </div>
            </div>
          </div>
        </div>

        <!-- Sidebar -->
        <div class="flex flex-col gap-5">
          <!-- Research Team -->
          <div class="card p-8">
            <h2 class="text-xs font-medium text-slate-400 mb-6 flex items-center gap-2">
              <span class="w-1 h-3 bg-brand rounded-full"></span>
              Research Team
            </h2>
            <div v-if="proposal.investigators?.length" class="space-y-4">
              <div v-for="inv in proposal.investigators" :key="inv.id" class="flex items-start gap-4 p-4 rounded-2xl bg-slate-50 border border-slate-100 hover:bg-white hover:border-brand transition-all group shadow-sm">
                <div class="w-10 h-10 bg-brand-light text-brand rounded-2xl flex items-center justify-center text-xs font-bold  group-hover:scale-110 overflow-hidden transition-transform shrink-0">
                  <img v-if="imageUrl(inv.user?.profile_image)" :src="imageUrl(inv.user?.profile_image)" class="w-full h-full object-cover"/>
                  <span v-else>{{ getInitials(inv.user?.name || inv.name) }}</span>
                </div>
                <div class="min-w-0">
                  <p class="text-sm font-bold text-slate-800 leading-tight truncate">{{ inv.user?.name || inv.name }}</p>
                  <p class="text-xs font-medium text-slate-400 mt-0.5">{{ inv.role?.name || 'Researcher' }}</p>
                  
                  <div class="mt-3 space-y-1">
                    <p v-if="inv.email || inv.user?.email" class="text-xs text-slate-500 font-medium flex items-center gap-1.5">
                      <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/></svg>
                      {{ inv.email || inv.user?.email }}
                    </p>
                    <p v-if="inv.institution" class="text-xs text-slate-500 font-medium flex items-center gap-1.5 italic">
                      <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/></svg>
                      {{ inv.institution }}
                    </p>
                  </div>
                </div>
              </div>
            </div>
            <p v-else class="text-xs font-medium text-slate-400 italic text-center py-6">No co-investigators.</p>
          </div>

          <!-- Document Management -->
          <div class="card p-8">
            <h2 class="text-xs font-medium text-slate-400 mb-6 flex items-center gap-2">
              <span class="w-1 h-3 bg-brand rounded-full"></span>
              Main Document
            </h2>
            
            <div v-if="proposal.file" class="p-4 bg-emerald-50 rounded-2xl border border-emerald-100 flex items-center justify-between group">
              <div class="flex items-center gap-3 min-w-0">
                <div class="w-10 h-10 bg-emerald-100 text-emerald-600 rounded-2xl flex items-center justify-center text-xl shrink-0 group-hover:scale-110 transition-transform">📄</div>
                <div class="min-w-0">
                   <p class="text-sm font-bold text-slate-800 truncate">{{ proposal.file.original_filename }}</p>
                   <p class="text-xs font-medium text-emerald-600 mt-0.5">Formal Proposal Attached</p>
                </div>
              </div>
              <a :href="`/api/files/${proposal.file.id}/download`" target="_blank" class="p-2 text-slate-400 hover:text-brand transition-colors">
                 <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M4 16v1a2 2 0 002 2h2m4-4V4m0 0l4 4m-4-4l-4 4m5 6h2a2 2 0 002-2v-5a2 2 0 00-2-2H9"></path></svg>
              </a>
            </div>

            <div v-if="proposal.status?.name === 'draft' || !proposal.file || auth.hasRole('super_admin')" class="mt-4">
              <label class="block">
                <span class="sr-only">Choose proposal file</span>
                <input type="file" @change="onFileSelected" accept=".pdf,.doc,.docx" class="block w-full text-xs text-slate-500 file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0 file:text-xs file:font-bold file:bg-brand/10 file:text-brand hover:file:bg-brand/20 cursor-pointer"/>
              </label>
              <button v-if="selectedFile" @click="uploadDocument" :disabled="uploading" class="btn btn-primary w-full mt-4 h-10 text-xs tracking-widest ">
                <span v-if="uploading">Uploading...</span>
                <span v-else>Attach Document</span>
              </button>
            </div>
          </div>

          <!-- Reviews Progress -->
          <div class="card p-8">
            <h2 class="text-xs font-medium text-slate-400 mb-6 flex items-center gap-2">
              <span class="w-1 h-3 bg-amber-500 rounded-full"></span>
              Status & Reviews
            </h2>
            
            <div class="space-y-6">
              <!-- Internal Reviewers -->
              <div v-if="proposal.reviewers?.length" class="space-y-3">
                 <p class="text-xs font-medium text-slate-400">Internal Peer Reviewers</p>
                 <div v-for="r in proposal.reviewers" :key="r.id" class="p-4 bg-slate-50 rounded-2xl border border-slate-100 relative group">
                    <div class="flex justify-between items-center mb-1">
                      <p class="text-sm font-bold text-slate-700">{{ r.name }}</p>
                      <span v-if="r.pivot?.overall_score" class="text-brand font-bold">{{ r.pivot.overall_score }}/5</span>
                    </div>
                    <p class="text-xs font-medium  text-slate-400 tracking-widest">{{ r.pivot?.overall_score ? 'Feedback Provided' : 'Under Review' }}</p>
                 </div>
              </div>
              <p v-else class="text-xs font-medium text-slate-400  italic">No reviewers assigned.</p>

              <hr class="border-slate-50" />

              <!-- External Checks -->
              <div class="space-y-4">
                 <p class="text-xs font-medium text-slate-400">Procedural Status</p>
                 <div v-if="proposal.status?.name === 'checking'" class="flex items-center justify-between p-3 rounded-2xl bg-indigo-50 border border-indigo-100">
                    <span class="text-xs font-medium text-indigo-700 flex items-center gap-2">
                       <svg class="animate-spin w-3 h-3" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"/></svg>
                       Automated Checks Running...
                    </span>
                 </div>
                 <div v-if="proposal.originality_score" class="flex items-center justify-between p-3 rounded-2xl bg-slate-50 border border-slate-100">
                    <span class="text-xs font-medium text-slate-600">Originality Score</span>
                    <a v-if="proposal.plagiarism_report_url" :href="proposal.plagiarism_report_url" target="_blank" class="text-xs font-bold px-3 py-1 rounded-lg hover:underline transition-colors cursor-pointer" :class="proposal.originality_score > 90 ? 'bg-emerald-50 text-emerald-600' : 'bg-rose-50 text-rose-600'">
                      {{ proposal.originality_score }}% (View)
                    </a>
                    <span v-else class="text-xs font-bold px-3 py-1 rounded-lg" :class="proposal.originality_score > 90 ? 'bg-emerald-50 text-emerald-600' : 'bg-rose-50 text-rose-600'">
                      {{ proposal.originality_score }}%
                    </span>
                 </div>
                 <div class="flex items-center justify-between p-3 rounded-2xl bg-slate-50 border border-slate-100">
                    <span class="text-xs font-medium  text-slate-600">Ethics Clearance</span>
                    <StatusBadge :status="proposal.ethics_status || 'not_requested'" size="sm" />
                 </div>
                 <div class="flex items-center justify-between p-3 rounded-2xl bg-slate-50 border border-slate-100">
                    <span class="text-xs font-medium  text-slate-600">Finance Audit</span>
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
             <p class="text-xs font-medium text-slate-400 mb-4">Eligible Reviewers (Expertise Match)</p>
             <div class="space-y-2 max-h-60 overflow-y-auto pr-2">
                <label v-for="r in availableReviewers" :key="r.user ? r.user.id : r.id" class="flex items-center justify-between p-3 bg-white rounded-2xl border border-slate-100 hover:border-brand cursor-pointer group transition-all">
                  <div class="flex items-center gap-3">
                    <input type="checkbox" :value="r.user ? r.user.id : r.id" v-model="selectedReviewers" class="w-4 h-4 rounded text-brand focus:ring-brand border-slate-300" />
                    <div class="min-w-0">
                      <p class="text-sm font-bold text-slate-800 group-hover:text-brand">{{ r.user ? r.user.name : r.name }}</p>
                      <p class="text-xs font-medium text-slate-400 ">{{ r.user ? r.user.email : r.email }}</p>
                    </div>
                  </div>
                  <div v-if="r.match_percentage" class="px-2 py-1 bg-brand/10 text-brand text-xs font-medium rounded flex flex-col items-center justify-center">
                    <span>{{ r.match_percentage }}% Match</span>
                    <span v-if="r.matched_keywords?.length" class="text-[8px] opacity-70">
                      via {{ r.matched_keywords.slice(0, 2).join(', ') }}
                    </span>
                  </div>
                </label>
             </div>
             <p v-if="availableReviewers.length === 0" class="text-xs text-slate-400 py-4 text-center italic font-medium">No reviewers available or configured.</p>
          </div>
          <div class="flex justify-end gap-3 pt-6 border-t border-slate-100">
             <button @click="showAssignReviewers = false" class="btn btn-secondary px-6">Cancel</button>
             <button @click="assignReviewers" class="btn btn-primary px-5">Assign Selection</button>
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
import { formatCurrency, formatDateTime, getInitials, imageUrl } from '@/utils/formatters'

const route = useRoute(); const router = useRouter(); const auth = useAuthStore(); const notif = useNotificationStore()
const proposal = ref({}); const loading = ref(true); const error = ref(null)
const showReject = ref(false); const rejectComment = ref('')
const showAssignReviewers = ref(false); const selectedReviewers = ref([]); const availableReviewers = ref([])
const selectedFile = ref(null); const uploading = ref(false)
const showDetectionServiceModal = ref(false); const detectionServices = ref([]); const selectedService = ref(null)
const rejectSource = ref('admin') // 'admin', 'finance', or 'ethics'

const isOwner = computed(() => auth.user?.id === proposal.value.submitted_by?.id)
const canApprove = computed(() => ['submitted','under_review','finance_check'].includes(proposal.value.status?.name))

// Hierarchical management rights
const canManageProposal = computed(() => {
  return auth.hasRole('super_admin', 'research_admin', 'campus_admin', 'faculty_admin', 'department_head', 'director')
})

const canEditProposal = computed(() => {
  if (auth.hasRole('super_admin')) return true
  if (auth.hasRole('research_admin') && auth.userScope === 'university') return true
  if (auth.hasRole('campus_admin') && auth.userScope === 'campus') return true
  if (auth.hasRole('faculty_admin') && auth.userScope === 'faculty') return true
  if (auth.hasRole('department_head') && auth.userScope === 'department') return true
  if (auth.hasRole('director') && auth.userScope === 'research_center') return true
  return isOwner.value && proposal.value.status?.name === 'draft'
})

const canAssignReviewers = computed(() => {
  return auth.hasRole('super_admin', 'research_admin', 'campus_admin', 'faculty_admin', 'department_head', 'director') &&
         ['submitted','under_review','finance_check','ethics_pending'].includes(proposal.value.status?.name)
})

const canConvertToProject = computed(() => {
  return auth.hasRole('super_admin', 'research_admin', 'campus_admin', 'faculty_admin', 'department_head', 'director') &&
         proposal.value.status?.name === 'approved'
})

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
  if (!proposal.value.file_id) {
    notif.error('No document attached for plagiarism check')
    return
  }
  try {
    // Fetch available detection services
    const { data } = await api.get('/detection/services')
    detectionServices.value = data.data || data
    selectedService.value = null
    showDetectionServiceModal.value = true
  } catch(e) {
    notif.error('Failed to load detection services')
  }
}

function getServiceDescription(serviceName) {
  const descriptions = {
    turnitin: 'Industry-standard plagiarism detection - checks against academic databases and web sources.',
    copyleaks: 'Advanced similarity detection - checks against internet sources and academic repositories.',
    gptzero: 'AI content detection - identifies AI-generated text and writing assistance.',
    local_similarity: 'Basic text similarity check - compares against internal proposals.',
    plagiarismcheck: 'External plagiarism detection - checks against web sources and internal databases.'
  }
  return descriptions[serviceName] || 'Similarity detection service.'
}

const isChecking = ref(false)

async function runChecks() {
  isChecking.value = true
  try {
    const { data } = await api.post(`/proposals/${proposal.value.id}/check`)
    notif.success(data.message || 'Background checks initiated')
    fetchProposal()
  } catch(e) {
    notif.error(e.response?.data?.message || 'Failed to initiate checks')
  } finally {
    isChecking.value = false
  }
}

async function submitProposal() {
  try {
    await api.post(`/proposals/${proposal.value.id}/submit`)
    notif.success('Formally submitted for review')
    fetchProposal()
  } catch (err) {
    const data = err.response?.data
    let msg = data?.message || 'Submission failed'
    if (data?.errors) {
      const firstKey = Object.keys(data.errors)[0]
      if (firstKey) msg = data.errors[firstKey][0]
    }
    notif.error(msg)
  }
}

async function approveProposal() {
  try {
    await api.post(`/proposals/${proposal.value.id}/approve`)
    notif.success('Proposal indexed as approved')
    fetchProposal()
  } catch (err) {
    notif.error(err.response?.data?.message || 'Approval failed')
  }
}

async function rejectProposal() {
  if (!rejectComment.value) { notif.error('Reason required'); return }
  try {
     if (rejectSource.value === 'finance') {
        const check = proposal.value.finance_checks?.find(c => c.status?.name === 'pending')
        if (!check) return
        await api.put(`/finance-checks/${check.id}`, { status: 'rejected', comments: rejectComment.value })
     } else if (rejectSource.value === 'ethics') {
        const req = proposal.value.ethics_requests?.find(r => r.status?.name === 'pending')
        if (!req) return
        await api.post(`/ethics-requests/${req.id}/decision`, { decision: 'rejected', comments: rejectComment.value })
     } else {
        await api.post(`/proposals/${proposal.value.id}/reject`, { comment: rejectComment.value })
     }
    notif.success('Decision recorded successfully')
    showReject.value = false; fetchProposal()
  } catch (err) {
    notif.error(err.response?.data?.message || 'Action failed')
  }
}

async function verifyBudget() {
  try {
    const check = proposal.value.finance_checks?.find(c => c.status?.name === 'pending')
    if (!check) return
    await api.put(`/finance-checks/${check.id}`, { status: 'approved' })
    notif.success('Budget verified')
    fetchProposal()
  } catch(e) { notif.error('Verification failed') }
}

async function verifyEthics() {
  try {
    const req = proposal.value.ethics_requests?.find(r => r.status?.name === 'pending')
    if (!req) return
    await api.post(`/ethics-requests/${req.id}/decision`, { decision: 'approved' })
    notif.success('Ethical clearance granted')
    fetchProposal()
  } catch(e) { notif.error('Clearance failed') }
}

async function assignReviewers() {
  if (selectedReviewers.value.length === 0) { notif.error('Select at least one'); return }
  try {
    await api.post(`/proposals/${proposal.value.id}/assign-reviewers`, { reviewer_ids: selectedReviewers.value })
    notif.success('Peer assignment successful')
    showAssignReviewers.value = false; fetchProposal()
  } catch (err) {
    notif.error(err.response?.data?.message || 'Assignment failed')
  }
}

async function sendToFinance() {
  try {
    await api.post(`/proposals/${proposal.value.id}/finance-checks`)
    notif.success('Budget evaluation requested')
    fetchProposal()
  } catch (e) { notif.error('Request failed') }
}

async function generateEthics() {
  try {
    await api.post(`/proposals/${proposal.value.id}/ethics-requests`)
    notif.success('IRB Ethics PDF auto-generated')
    fetchProposal()
  } catch (e) { notif.error('Generation failed') }
}



function onFileSelected(event) {
  selectedFile.value = event.target.files[0]
}

async function uploadDocument() {
  if (!selectedFile.value) return
  uploading.value = true
  const formData = new FormData()
  formData.append('file', selectedFile.value)
  formData.append('parent_type', 'proposal')
  formData.append('parent_id', proposal.value.id)
  formData.append('is_public', '0')
  
  try {
    // Step 1: Upload the file and get its ID
    const { data: uploadedFile } = await api.post('/files', formData)
    // Step 2: Attach the file to the proposal
    await api.post(`/proposals/${proposal.value.id}/files`, { file_id: uploadedFile.id })
    notif.success('Research document attached successfully')
    selectedFile.value = null
    fetchProposal()
  } catch (e) {
    notif.error(e.response?.data?.message || 'Upload failed')
  } finally {
    uploading.value = false
  }
}

onMounted(async () => {
  await fetchProposal()
  try {
    if (auth.hasPermission('assign_reviewers') || auth.hasRole('super_admin', 'research_admin', 'campus_admin', 'faculty_admin', 'director', 'department_head')) {
      const { data } = await api.get(`/proposals/${route.params.id}/suggest-reviewers`)
      availableReviewers.value = data.data || data
    }
  } catch (e) {}
})
</script>
