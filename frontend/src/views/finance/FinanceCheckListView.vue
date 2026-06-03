<template>
  <div class="flex flex-col gap-6 card">
    <!-- Header -->
    <div class="section-header">
      <div>
        <h1 class="section-title">Budget Reviews</h1>
        <p class="section-subtitle">Review and approve proposal budget allocations</p>
      </div>
      <button @click="fetchChecks" class="btn btn-secondary">
        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" /></svg>
        Sync
      </button>
    </div>

    <!-- Content -->
    <div v-if="loading" class="card p-8"><LoadingSkeleton :rows="5" /></div>
    <div v-else-if="checks.length === 0" class="card">
      <EmptyState icon="💰" title="No finance checks pending" description="Great job! All proposal budget reviews are up to date." />
    </div>

    <div v-else class="grid grid-cols-1 lg:grid-cols-2 gap-6">
      <div v-for="check in checks" :key="check.id" class="card p-6 flex flex-col group card-hover relative overflow-hidden border-l-4 border-l-emerald-500 hover:border-l-emerald-600 transition-all">
        <div class="flex items-start justify-between mb-4">
          <div class="flex-1 pr-4">
            <div class="flex items-center gap-3 mb-2">
              <span class="px-2.5 py-0.5 bg-emerald-50 text-emerald-600 text-[10px] font-black capitalize tracking-widest rounded-md border border-emerald-100">
                Budget Review
              </span>
              <StatusBadge :status="check.status?.name || 'pending'" />
            </div>
            <h3 class="text-base font-black text-slate-800 leading-tight mb-2 group-hover:text-emerald-700 transition-colors">{{ check.proposal?.title }}</h3>
            <div class="flex items-center gap-4 text-[10px] font-black text-slate-400 capitalize tracking-widest">
              <span class="flex items-center gap-1.5">
                <i class="fas fa-coins text-emerald-400"></i>
                {{ formatCurrency(check.proposal?.budget) }}
              </span>
              <span v-if="check.checker?.name" class="flex items-center gap-1.5">
                <i class="fas fa-user-check"></i>
                {{ check.checker?.name }}
              </span>
            </div>
          </div>
          <div class="w-12 h-12 rounded-2xl bg-linear-to-br from-emerald-400 to-teal-600 text-white flex items-center justify-center font-black shadow-lg shadow-emerald-500/30 shrink-0">
             <i class="fas fa-dollar-sign text-xl"></i>
          </div>
        </div>
        
        <div class="mt-auto pt-4 border-t border-slate-100 flex items-center gap-3">
          <button @click="approveCheck(check)" class="flex-1 btn btn-primary h-10 text-[11px] font-black capitalize tracking-widest bg-emerald-600 hover:bg-emerald-700 shadow-lg shadow-emerald-500/20 border-0">
            Approve
          </button>
          <button @click="rejectCheck(check)" class="flex-1 btn btn-secondary h-10 text-[11px] font-black capitalize tracking-widest text-rose-600 bg-rose-50 hover:bg-rose-100 border-0">
            Reject
          </button>
        </div>
      </div>
    </div>

    <ConfirmDialog :show="showReject" title="Reject Budget Review" message="Please provide a reason for rejection:" confirmText="Confirm Rejection" variant="danger" @confirm="confirmReject" @cancel="showReject = false">
      <template #extra>
        <textarea v-model="rejectComment" rows="2" class="input resize-none mt-3" placeholder="Reason for rejection..."></textarea>
      </template>
    </ConfirmDialog>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import { useNotificationStore } from '@/stores/notification'
import api from '@/services/api'
import StatusBadge from '@/components/StatusBadge.vue'
import LoadingSkeleton from '@/components/LoadingSkeleton.vue'
import ConfirmDialog from '@/components/ConfirmDialog.vue'
import EmptyState from '@/components/EmptyState.vue'
import { formatCurrency } from '@/utils/formatters'

const notif = useNotificationStore()
const checks = ref([]); const loading = ref(true)
const showReject = ref(false); const rejectComment = ref(''); const rejectingCheck = ref(null)

async function fetchChecks() {
  loading.value = true
  try {
    const { data } = await api.get('/proposals?status=finance_check')
    checks.value = data.data || data
  } catch (e) {} finally { loading.value = false }
}

async function approveCheck(check) {
  try {
    const existing = check.finance_checks?.[0]
    if (existing) {
      await api.put(`/finance-checks/${existing.id}`, { status_id: 2 })
    } else {
      await api.post(`/proposals/${check.id}/finance-checks`, { status_id: 2 })
    }
    notif.success('Finance check approved!')
    fetchChecks()
  } catch (err) { notif.error('Failed to update finance check') }
}

function rejectCheck(check) { rejectingCheck.value = check; showReject.value = true }

async function confirmReject() {
  try {
    const existing = rejectingCheck.value.finance_checks?.[0]
    if (existing) {
      await api.put(`/finance-checks/${existing.id}`, { status_id: 3, comments: rejectComment.value })
    } else {
      await api.post(`/proposals/${rejectingCheck.value.id}/finance-checks`, { status_id: 3, comments: rejectComment.value })
    }
    notif.success('Finance check rejected!')
    showReject.value = false; fetchChecks()
  } catch (err) { notif.error('Failed to reject finance check') }
}

onMounted(fetchChecks)
</script>
