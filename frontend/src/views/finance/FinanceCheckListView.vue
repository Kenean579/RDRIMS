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

    <div v-else class="space-y-4">
      <div v-for="check in checks" :key="check.id" class="card p-5 group card-hover">
        <div class="flex items-start justify-between">
          <div class="flex-1">
            <h3 class="text-base font-bold text-slate-800 group-hover:text-blue-600 transition mb-1">{{ check.proposal?.title }}</h3>
            <div class="flex items-center gap-4 text-[11px] font-bold text-slate-400 uppercase tracking-widest mb-3">
              <span class="flex items-center gap-1.5">
                <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><line x1="12" y1="1" x2="12" y2="23" stroke-width="2"/><path d="M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6" stroke-width="2"/></svg>
                {{ formatCurrency(check.proposal?.budget) }}
              </span>
              <span v-if="check.checker?.name">Checker: {{ check.checker?.name }}</span>
            </div>
            <StatusBadge :status="check.status?.name || 'pending'" />
          </div>
          <div class="flex gap-2">
            <button @click="approveCheck(check)" class="btn btn-primary" style="padding: 6px 14px; font-size: 11px; background: #10b981">
              <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7" /></svg>
              Approve
            </button>
            <button @click="rejectCheck(check)" class="btn btn-danger" style="padding: 6px 14px; font-size: 11px">
              <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M6 18L18 6M6 6l12 12" /></svg>
              Reject
            </button>
          </div>
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
    await api.put(`/finance-checks/${check.id || check.finance_checks?.[0]?.id}`, { status_id: 2 })
    notif.success('Finance check approved!')
    fetchChecks()
  } catch (err) { notif.error('Failed') }
}

function rejectCheck(check) { rejectingCheck.value = check; showReject.value = true }

async function confirmReject() {
  try {
    await api.put(`/finance-checks/${rejectingCheck.value.id || rejectingCheck.value.finance_checks?.[0]?.id}`, { status_id: 3, comments: rejectComment.value })
    notif.success('Rejected!'); showReject.value = false; fetchChecks()
  } catch (err) { notif.error('Failed') }
}

onMounted(fetchChecks)
</script>
