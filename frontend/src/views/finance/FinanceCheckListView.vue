<template>
  <div class="flex flex-col gap-8 animate-fade pb-8">
    <!-- Header -->
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-6 px-1">
      <div>
        <h1 class="text-2xl font-bold text-slate-900 tracking-tight leading-tight">Budget Reviews</h1>
        <p class="text-slate-500 font-medium mt-2 text-xs flex items-center gap-2  tracking-widest">
          <span class="w-2 h-2 rounded-full bg-brand"></span>
          Review and approve proposal budget allocations
        </p>
      </div>
      <div class="flex items-center gap-3">
        <!-- Status filter -->
        <select v-model="statusFilter" @change="fetchChecks" class="input h-10 px-4 text-[12px] font-bold w-48">
          <option value="">All Reviews</option>
          <option v-for="s in financeStatuses" :key="s.id" :value="s.name">{{ s.name }}</option>
        </select>
        <button @click="fetchChecks" class="btn btn-secondary h-10 px-4 group">
          <svg class="w-4 h-4 group-hover:rotate-180 transition-transform duration-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/></svg>
        </button>
      </div>
    </div>

    <!-- Loading -->
    <div v-if="loading" class="grid grid-cols-1 lg:grid-cols-2 gap-6">
      <div v-for="i in 4" :key="i" class="bg-white rounded-3xl h-48 animate-pulse border border-slate-100"></div>
    </div>

    <!-- Empty -->
    <div v-else-if="checks.length === 0" class="card p-16 text-center">
      <div class="w-20 h-20 bg-emerald-50 rounded-3xl flex items-center justify-center mx-auto mb-6 border border-emerald-100">
        <svg class="w-10 h-10 text-emerald-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
      </div>
      <h3 class="text-lg font-bold text-slate-800 mb-2">All clear!</h3>
      <p class="text-sm text-slate-500 font-medium">No budget reviews are pending at the moment.</p>
    </div>

    <!-- Finance Check Cards -->
    <div v-else class="grid grid-cols-1 lg:grid-cols-2 gap-6">
      <div v-for="check in checks" :key="check.id"
        class="bg-white rounded-3xl border border-slate-100 hover:border-emerald-200 hover:shadow-xl hover:shadow-emerald-500/5 transition-all overflow-hidden group flex flex-col"
      >
        <div class="p-6 flex-1">
          <!-- Status row -->
          <div class="flex items-center gap-3 mb-4">
            <span class="px-3 py-1 bg-emerald-50 text-emerald-700 text-xs font-bold rounded-full border border-emerald-100  tracking-wider">Budget Review</span>
            <span
              class="px-3 py-1 rounded-full text-xs font-bold"
              :class="{
                'bg-amber-100 text-amber-700': check.status?.name === 'pending',
                'bg-emerald-100 text-emerald-700': check.status?.name === 'approved',
                'bg-rose-100 text-rose-700': check.status?.name === 'rejected',
              }"
            >{{ check.status?.name || 'pending' }}</span>
          </div>

          <!-- Proposal title -->
          <h3 class="text-base font-bold text-slate-800 group-hover:text-emerald-700 transition-colors leading-tight mb-4">
            {{ check.proposal?.title || 'Untitled Proposal' }}
          </h3>

          <!-- Details grid -->
          <div class="grid grid-cols-2 gap-3">
            <div class="bg-slate-50 rounded-2xl p-3 border border-slate-100">
              <p class="text-xs font-bold text-slate-400  tracking-widest mb-1">Budget</p>
              <p class="text-sm font-bold text-slate-800">{{ formatCurrency(check.proposal?.budget) }}</p>
            </div>
            <div class="bg-slate-50 rounded-2xl p-3 border border-slate-100">
              <p class="text-xs font-bold text-slate-400  tracking-widest mb-1">Reviewer</p>
              <p class="text-sm font-bold text-slate-800 truncate">{{ check.checker?.name || 'Unassigned' }}</p>
            </div>
            <div v-if="check.approved_budget" class="bg-emerald-50 rounded-2xl p-3 border border-emerald-100 col-span-2">
              <p class="text-xs font-bold text-emerald-600  tracking-widest mb-1">Approved Amount</p>
              <p class="text-sm font-bold text-emerald-700">{{ formatCurrency(check.approved_budget) }}</p>
            </div>
            <div v-if="check.comments" class="bg-slate-50 rounded-2xl p-3 border border-slate-100 col-span-2">
              <p class="text-xs font-bold text-slate-400  tracking-widest mb-1">Comments</p>
              <p class="text-xs text-slate-600 font-medium line-clamp-2">{{ check.comments }}</p>
            </div>
          </div>
        </div>

        <!-- Actions footer (only for pending reviews) -->
        <div v-if="check.status?.name === 'pending'" class="px-6 py-4 bg-slate-50/60 border-t border-slate-100 flex gap-3">
          <button
            @click="openApprove(check)"
            class="flex-1 h-10 rounded-2xl bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-bold transition-colors flex items-center justify-center gap-2"
          >
            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"/></svg>
            Approve
          </button>
          <button
            @click="openReject(check)"
            class="flex-1 h-10 rounded-2xl bg-rose-50 hover:bg-rose-100 text-rose-600 text-xs font-bold border border-rose-100 transition-colors flex items-center justify-center gap-2"
          >
            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M6 18L18 6M6 6l12 12"/></svg>
            Reject
          </button>
        </div>
        <div v-else class="px-6 py-4 bg-slate-50/60 border-t border-slate-100">
          <span class="text-xs font-bold text-slate-400  tracking-wider">Review completed</span>
        </div>
      </div>
    </div>

    <!-- Approve Modal -->
    <Modal :show="showApprove" title="Approve Budget" size="sm" @close="showApprove = false">
      <form @submit.prevent="confirmApprove" class="space-y-5">
        <div>
          <label class="block text-xs text-slate-500 font-bold mb-2 ml-1">Approved Budget Amount</label>
          <input v-model.number="approveForm.approved_budget" type="number" step="0.01" class="input h-12 font-bold" :placeholder="approvingCheck?.proposal?.budget" />
          <p class="text-xs text-slate-400 mt-1 ml-1">Leave blank to approve as-is ({{ formatCurrency(approvingCheck?.proposal?.budget) }})</p>
        </div>
        <div>
          <label class="block text-xs text-slate-500 font-bold mb-2 ml-1">Comments (optional)</label>
          <textarea v-model="approveForm.comments" rows="3" class="input resize-none pt-3 font-medium" placeholder="Approval notes..."></textarea>
        </div>
        <div class="flex justify-end gap-3 pt-4 border-t border-slate-100">
          <button type="button" @click="showApprove = false" class="btn btn-secondary px-6 h-11 font-bold text-xs">Cancel</button>
          <button type="submit" class="h-11 px-8 rounded-2xl bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-bold transition-colors">Confirm Approval</button>
        </div>
      </form>
    </Modal>

    <!-- Reject Modal -->
    <Modal :show="showReject" title="Reject Budget Review" size="sm" @close="showReject = false">
      <form @submit.prevent="confirmReject" class="space-y-5">
        <div>
          <label class="block text-xs text-slate-500 font-bold mb-2 ml-1">Reason for Rejection *</label>
          <textarea v-model="rejectForm.comments" required rows="4" class="input resize-none pt-3 font-medium" placeholder="Provide a clear reason..."></textarea>
        </div>
        <div class="flex justify-end gap-3 pt-4 border-t border-slate-100">
          <button type="button" @click="showReject = false" class="btn btn-secondary px-6 h-11 font-bold text-xs">Cancel</button>
          <button type="submit" class="h-11 px-8 rounded-2xl bg-rose-600 hover:bg-rose-700 text-white text-xs font-bold transition-colors">Confirm Rejection</button>
        </div>
      </form>
    </Modal>
  </div>
</template>

<script setup>
import { ref, reactive, onMounted } from 'vue'
import { useNotificationStore } from '@/stores/notification'
import api from '@/services/api'
import Modal from '@/components/Modal.vue'
import { formatCurrency } from '@/utils/formatters'

const notif = useNotificationStore()
const checks = ref([])
const loading = ref(true)
const statusFilter = ref('')
const financeStatuses = ref([])

// Approve flow
const showApprove = ref(false)
const approvingCheck = ref(null)
const approveForm = reactive({ approved_budget: null, comments: '' })

// Reject flow
const showReject = ref(false)
const rejectingCheck = ref(null)
const rejectForm = reactive({ comments: '' })

async function fetchChecks() {
  loading.value = true
  try {
    const params = {}
    if (statusFilter.value) params.status = statusFilter.value
    const { data } = await api.get('/finance-checks', { params })
    checks.value = data.data || data
  } catch (e) {
    notif.error('Failed to load finance checks')
  } finally {
    loading.value = false
  }
}

function openApprove(check) {
  approvingCheck.value = check
  approveForm.approved_budget = null
  approveForm.comments = ''
  showApprove.value = true
}

async function confirmApprove() {
  try {
    const payload = {
      comments: approveForm.comments || null,
    }
    if (approveForm.approved_budget) {
      payload.approved_budget = approveForm.approved_budget
    }
    await api.post(`/finance-checks/${approvingCheck.value.id}/approve`, payload)
    notif.success('Budget approved!')
    showApprove.value = false
    fetchChecks()
  } catch (err) {
    notif.error(err.response?.data?.message || 'Failed to approve')
  }
}

function openReject(check) {
  rejectingCheck.value = check
  rejectForm.comments = ''
  showReject.value = true
}

async function confirmReject() {
  try {
    await api.post(`/finance-checks/${rejectingCheck.value.id}/reject`, {
      comments: rejectForm.comments,
    })
    notif.success('Budget review rejected')
    showReject.value = false
    fetchChecks()
  } catch (err) {
    notif.error(err.response?.data?.message || 'Failed to reject')
  }
}

onMounted(async () => {
  fetchChecks()
  try {
    const { data } = await api.get('/lookups/finance_check_statuses')
    financeStatuses.value = data
  } catch (e) {}
})
</script>
