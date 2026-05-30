<template>
  <div class="flex flex-col gap-8 pb-12 animate-fade card">
    <!-- Header -->
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
      <div>
        <h1 class="text-3xl font-black text-slate-900 tracking-tight">Funding Calls</h1>
        <p class="text-slate-500 font-medium mt-1">Open applications for research grants.</p>
      </div>
      <div class="flex items-center gap-3">
        <button v-if="auth.hasRole('super_admin','research_admin')" @click="showCreate = true" class="btn btn-primary h-11 px-6 shadow-lg shadow-blue-500/20">
          <svg class="w-4 h-4 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 4v16m8-8H4" /></svg>
          Create Call
        </button>
        <button @click="fetchCalls" class="btn btn-secondary h-11 px-6 shadow-sm group">
          <svg class="w-4 h-4 mr-1.5 group-hover:rotate-180 transition-transform duration-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" /></svg>
          Refresh
        </button>
      </div>
    </div>

    <!-- Content -->
    <div v-if="loading" class="grid grid-cols-1 md:grid-cols-2 gap-6">
      <div v-for="i in 4" :key="i" class="card p-8 h-64 flex flex-col gap-4 bg-slate-50/50">
        <div class="h-6 w-24 bg-slate-200 rounded-lg animate-pulse"></div>
        <div class="h-8 w-3/4 bg-slate-100 rounded-lg animate-pulse"></div>
        <div class="h-24 w-full bg-slate-100/50 rounded-lg animate-pulse"></div>
      </div>
    </div>

    <div v-else-if="calls.length === 0" class="card">
      <EmptyState icon="📢" title="No calls found" description="There are currently no open calls. We will update you soon." />
    </div>

    <div v-else class="grid grid-cols-1 lg:grid-cols-2 gap-8">
      <div v-for="call in calls" :key="call.id" class="card group card-hover flex flex-col p-8 border-l-4 border-l-brand/20 hover:border-l-brand transition-all">
        <div class="flex flex-col sm:flex-row justify-between items-start gap-4 mb-6">
          <StatusBadge :status="call.status?.name || 'open'" />
          <div class="flex items-center gap-2 text-[10px] font-black text-slate-400 uppercase tracking-widest bg-slate-100 px-3 py-1.5 rounded-xl">
            <svg class="w-4 h-4 text-slate-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" /></svg>
            Ends: {{ formatDate(call.deadline) }}
          </div>
        </div>

        <h3 class="text-xl font-black text-slate-900 group-hover:text-brand transition-colors mb-4 leading-tight">{{ call.title }}</h3>
        <p class="text-sm text-slate-500 font-medium mb-8 flex-1 line-clamp-3 leading-relaxed">{{ call.description }}</p>

        <div class="flex items-center justify-between mt-auto pt-6 border-t border-slate-100">
          <div class="flex items-center gap-2">
            <router-link v-if="auth.hasPermission('submit_proposals')" :to="`/proposals/create?call_id=${call.id}`" class="btn btn-primary shadow-lg shadow-blue-500/20 px-6 text-[11px] font-black uppercase tracking-widest h-10">
              Apply
            </router-link>
            <button @click="viewCall(call)" class="btn btn-ghost hover:bg-slate-100 text-[11px] font-black uppercase tracking-widest h-10 px-5">
              Info
            </button>
            <button v-if="auth.hasRole('super_admin','research_admin')" @click="editCall(call)" class="btn btn-ghost hover:bg-slate-100 text-[11px] font-black uppercase tracking-widest h-10 px-5 text-amber-600 hover:text-amber-700">
              Edit
            </button>
          </div>
          <div class="text-right">
             <p class="text-[9px] font-black text-slate-400 uppercase tracking-widest mb-0.5">Award High</p>
             <p class="text-base font-black text-brand">{{ formatCurrency(call.budget_limit) }}</p>
          </div>
        </div>
      </div>
    </div>

    <!-- Detail Modal -->
    <Modal :show="!!selectedCall && !editingCall" :title="selectedCall?.title" size="lg" @close="selectedCall = null">
      <div v-if="selectedCall" class="flex flex-col gap-8">
        <div class="p-6 bg-slate-50 rounded-2xl border border-slate-100 shadow-inner">
           <h4 class="text-[10px] font-black text-slate-400 uppercase tracking-widest mb-3">About this call</h4>
           <p class="text-base text-slate-700 font-medium whitespace-pre-line leading-relaxed">{{ selectedCall.description }}</p>
        </div>
        <div class="grid grid-cols-1 sm:grid-cols-3 gap-6">
          <div class="p-5 bg-white rounded-2xl border border-slate-100 shadow-sm flex flex-col gap-1 text-center">
             <p class="text-[10px] font-black text-slate-400 uppercase tracking-widest">Award High</p>
             <p class="text-lg font-black text-brand">{{ formatCurrency(selectedCall.budget_limit) }}</p>
          </div>
          <div class="p-5 bg-white rounded-2xl border border-slate-100 shadow-sm flex flex-col gap-1 text-center">
             <p class="text-[10px] font-black text-slate-400 uppercase tracking-widest">Ending Date</p>
             <p class="text-lg font-black text-slate-900">{{ formatDate(selectedCall.deadline) }}</p>
          </div>
          <div class="p-5 bg-white rounded-2xl border border-slate-100 shadow-sm flex flex-col gap-1 text-center">
             <p class="text-[10px] font-black text-slate-400 uppercase tracking-widest">Target Year</p>
             <p class="text-lg font-black text-slate-900">{{ selectedCall.academic_year?.name || '2024' }}</p>
          </div>
        </div>
        <div class="pt-6 border-t border-slate-100 flex justify-end gap-3">
           <button @click="selectedCall = null" class="btn btn-secondary px-8 h-12 text-[11px] font-black uppercase tracking-widest">Close</button>
           <router-link v-if="auth.hasPermission('submit_proposals')" :to="`/proposals/create?call_id=${selectedCall.id}`" @click="selectedCall = null" class="btn btn-primary px-10 h-12 shadow-lg shadow-blue-500/20 text-[11px] font-black uppercase tracking-widest">
              Apply Now
           </router-link>
        </div>
      </div>
    </Modal>

    <!-- Create / Edit Call Modal (Admin Only) -->
    <Modal :show="showCreate || editingCall" :title="editingCall ? 'Edit Funding Call' : 'Create New Funding Call'" size="lg" @close="closeCallModal">
      <form @submit.prevent="saveCall" class="space-y-6">
        <div>
          <label class="block text-[11px] text-slate-500 font-black uppercase tracking-widest mb-2 ml-1">Title *</label>
          <input v-model="callForm.title" type="text" required class="input h-12 font-bold" placeholder="e.g. National Research Innovation Grant 2025" />
        </div>
        <div>
          <label class="block text-[11px] text-slate-500 font-black uppercase tracking-widest mb-2 ml-1">Description *</label>
          <textarea v-model="callForm.description" required rows="4" class="input resize-none pt-3" placeholder="Describe the research areas and what is expected..."></textarea>
        </div>
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-6">
          <div>
            <label class="block text-[11px] text-slate-500 font-black uppercase tracking-widest mb-2 ml-1">Deadline *</label>
            <input v-model="callForm.deadline" type="date" required class="input h-12 font-bold" />
          </div>
          <div>
            <label class="block text-[11px] text-slate-500 font-black uppercase tracking-widest mb-2 ml-1">Budget Limit (ETB)</label>
            <input v-model.number="callForm.budget_limit" type="number" min="0" step="1000" class="input h-12 font-bold" placeholder="500000" />
          </div>
        </div>
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-6">
          <div>
            <label class="block text-[11px] text-slate-500 font-black uppercase tracking-widest mb-2 ml-1">Academic Year</label>
            <select v-model="callForm.academic_year_id" class="input h-12 font-bold">
              <option value="">Select Year</option>
              <option v-for="y in academicYears" :key="y.id" :value="y.id">{{ y.name }}</option>
            </select>
          </div>
          <div>
            <label class="block text-[11px] text-slate-500 font-black uppercase tracking-widest mb-2 ml-1">Status</label>
            <select v-model="callForm.status_id" class="input h-12 font-bold">
              <option value="">Select Status</option>
              <option v-for="s in callStatuses" :key="s.id" :value="s.id">{{ s.name }}</option>
            </select>
          </div>
        </div>
        <div class="flex justify-end gap-4 pt-6 border-t border-slate-100">
          <button type="button" @click="closeCallModal" class="btn btn-secondary px-8 h-11 text-[11px] font-black uppercase tracking-widest">Cancel</button>
          <button type="submit" :disabled="saving" class="btn btn-primary px-10 h-11 shadow-lg shadow-blue-500/20 text-[11px] font-black uppercase tracking-widest">
            {{ saving ? 'Saving...' : (editingCall ? 'Update Call' : 'Create Call') }}
          </button>
        </div>
      </form>
    </Modal>
  </div>
</template>

<script setup>
import { ref, reactive, onMounted } from 'vue'
import { useAuthStore } from '@/stores/auth'
import { useNotificationStore } from '@/stores/notification'
import api from '@/services/api'
import StatusBadge from '@/components/StatusBadge.vue'
import Modal from '@/components/Modal.vue'
import EmptyState from '@/components/EmptyState.vue'
import { formatDate, formatCurrency } from '@/utils/formatters'

const auth = useAuthStore()
const notif = useNotificationStore()
const calls = ref([])
const loading = ref(true)
const saving = ref(false)
const selectedCall = ref(null)
const showCreate = ref(false)
const editingCall = ref(null)
const academicYears = ref([])
const callStatuses = ref([])

const callForm = reactive({
  title: '', description: '', deadline: '', budget_limit: null,
  academic_year_id: '', status_id: ''
})

async function fetchCalls() {
  loading.value = true
  try {
    const { data } = await api.get('/calls')
    calls.value = data.data || data
  } catch (e) {}
  finally { loading.value = false }
}

function viewCall(call) { selectedCall.value = call }

function editCall(call) {
  editingCall.value = call
  Object.assign(callForm, {
    title: call.title,
    description: call.description,
    deadline: call.deadline?.substring(0, 10) || '',
    budget_limit: call.budget_limit,
    academic_year_id: call.academic_year_id || '',
    status_id: call.status_id || ''
  })
}

function closeCallModal() {
  showCreate.value = false
  editingCall.value = null
  Object.assign(callForm, { title: '', description: '', deadline: '', budget_limit: null, academic_year_id: '', status_id: '' })
}

async function saveCall() {
  saving.value = true
  try {
    const payload = { ...callForm, budget_limit: callForm.budget_limit || null, academic_year_id: callForm.academic_year_id || null, status_id: callForm.status_id || null }
    if (editingCall.value) {
      await api.put(`/calls/${editingCall.value.id}`, payload)
      notif.success('Call updated successfully!')
    } else {
      await api.post('/calls', payload)
      notif.success('Funding call created!')
    }
    closeCallModal()
    fetchCalls()
  } catch (err) {
    notif.error(err.response?.data?.message || 'Failed to save call')
  } finally { saving.value = false }
}

onMounted(async () => {
  fetchCalls()
  try {
    const [ys, ss] = await Promise.all([
      api.get('/academic-years'),
      api.get('/lookups/call_statuses')
    ])
    academicYears.value = ys.data
    callStatuses.value = ss.data
  } catch (e) {}
})
</script>
