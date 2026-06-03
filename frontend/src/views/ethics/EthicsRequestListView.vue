<template>
  <div class="flex flex-col gap-8 pb-12 animate-fade card">
    <!-- Header -->
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
      <div>
        <h1 class="text-3xl font-black text-slate-900 tracking-tight">Ethics Approval</h1>
        <p class="text-slate-500 font-medium mt-1">Review and approve research projects for ethics.</p>
      </div>
      <button @click="fetchRequests" class="btn btn-secondary h-11 px-6 shadow-sm group">
        <svg class="w-4 h-4 mr-1.5 group-hover:rotate-180 transition-transform duration-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" /></svg>
        Refresh
      </button>
    </div>

    <!-- Content -->
    <div v-if="loading" class="grid grid-cols-1 gap-4">
      <div v-for="i in 3" :key="i" class="card h-24 animate-pulse bg-slate-50/50"></div>
    </div>
    
    <div v-else-if="requests.length === 0" class="card">
      <EmptyState icon="⚖️" title="No requests found" description="All ethics reviews have been completed for now." />
    </div>

    <div v-else class="grid grid-cols-1 lg:grid-cols-2 gap-6">
      <div v-for="req in requests" :key="req.id" class="card p-6 flex flex-col group card-hover relative overflow-hidden border-l-4 border-l-brand hover:border-l-indigo-600 transition-all">
        <div class="flex items-start justify-between mb-4">
          <div class="flex-1 pr-4">
            <div class="flex items-center gap-3 mb-2">
              <span class="px-2.5 py-0.5 bg-slate-100 text-slate-500 text-[10px] font-black capitalize tracking-widest rounded-md border border-slate-200">VER: {{ req.version || 1 }}</span>
              <StatusBadge :status="req.approval_status?.name || 'pending'" />
            </div>
            <h3 class="text-base font-black text-slate-800 leading-tight mb-2 group-hover:text-brand transition-colors">{{ req.proposal?.title }}</h3>
            <p class="text-[10px] font-black text-slate-400 capitalize tracking-widest flex items-center gap-1.5">
              <i class="fas fa-user-edit"></i>
              {{ req.proposal?.authors?.split(',')[0] || 'Researcher' }}
            </p>
          </div>
          <div class="w-12 h-12 rounded-2xl bg-linear-to-br from-indigo-500 to-blue-600 text-white flex items-center justify-center font-black shadow-lg shadow-indigo-500/30 shrink-0">
             <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M3 6l3 1m0 0l-3 9a5.002 5.002 0 006.001 0M6 7l3 9M6 7l6-2m6 2l3-1m-3 1l-3 9a5.002 5.002 0 006.001 0M18 7l3 9m-3-9l-6-2m0-2v2m0 16V5m0 16H9m3 0h3" /></svg>
          </div>
        </div>
        
        <div class="mt-auto pt-4 border-t border-slate-100 flex items-center gap-3">
          <button @click="approveRequest(req)" class="flex-1 btn btn-primary h-10 text-[11px] font-black capitalize tracking-widest bg-emerald-600 hover:bg-emerald-700 shadow-lg shadow-emerald-500/20 border-0">
            Approve
          </button>
          <button @click="rejectRequest(req)" class="flex-1 btn btn-secondary h-10 text-[11px] font-black capitalize tracking-widest text-rose-600 bg-rose-50 hover:bg-rose-100 border-0">
            Reject
          </button>
        </div>
      </div>
    </div>

    <!-- Rejection Dialog -->
    <ConfirmDialog :show="showReject" title="Reject Ethics Request" message="Please explain why this request is being rejected:" confirmText="Confirm Rejection" variant="danger" @confirm="confirmReject" @cancel="showReject = false">
      <template #extra>
        <div class="mt-4">
          <label class="block text-[10px] font-black text-slate-400 capitalize tracking-widest mb-2 ml-1">Rejection Reason</label>
          <textarea v-model="rejectComment" rows="3" class="input p-4 font-semibold text-slate-700 leading-relaxed" placeholder="e.g. Missing participant consent forms..."></textarea>
        </div>
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

const notif = useNotificationStore()
const requests = ref([]); const loading = ref(true)
const showReject = ref(false); const rejectComment = ref(''); const rejectingReq = ref(null)

async function fetchRequests() {
  loading.value = true
  try {
    const { data } = await api.get('/proposals?status=ethics_check')
    requests.value = data.data || data
  } catch (e) {} finally { loading.value = false }
}

async function approveRequest(req) {
  try {
    const existing = req.ethics_requests?.[0]
    if (existing) {
      await api.put(`/ethics-requests/${existing.id}`, { approval_status_id: 2 })
    } else {
      await api.post(`/proposals/${req.id}/ethics-requests`, { approval_status_id: 2 })
    }
    notif.success('Ethics approved!')
    fetchRequests()
  } catch (err) { notif.error('Failed to update ethics request') }
}

function rejectRequest(req) { rejectingReq.value = req; showReject.value = true }

async function confirmReject() {
  try {
    const existing = rejectingReq.value.ethics_requests?.[0]
    if (existing) {
      await api.put(`/ethics-requests/${existing.id}`, { approval_status_id: 3, comments: rejectComment.value })
    } else {
      await api.post(`/proposals/${rejectingReq.value.id}/ethics-requests`, { approval_status_id: 3, comments: rejectComment.value })
    }
    notif.success('Ethics rejected!')
    showReject.value = false; fetchRequests()
  } catch (err) { notif.error('Failed to reject ethics request') }
}

onMounted(fetchRequests)
</script>
