<template>
  <div class="flex flex-col gap-8 pb-12 animate-fade card">
    <!-- Header -->
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
      <div>
        <h1 class="text-3xl font-black text-slate-900 tracking-tight">Patents</h1>
        <p class="text-slate-500 font-medium mt-1">Manage patented research and new inventions.</p>
      </div>
      <button @click="showCreate = true" class="btn btn-primary h-11 px-6 shadow-lg shadow-blue-500/20">
        <svg class="w-4 h-4 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 4v16m8-8H4" /></svg>
        Add New Patent
      </button>
    </div>

    <!-- Content -->
    <div v-if="loading" class="card p-24 flex flex-col justify-center items-center gap-4">
      <div class="animate-spin rounded-full h-10 w-10 border-b-2 border-brand"></div>
      <p class="text-xs font-bold text-slate-400 uppercase tracking-widest">Loading patents...</p>
    </div>
    
    <div v-else-if="error" class="card border-red-100 bg-red-50/30 p-16 text-center">
      <p class="text-sm text-red-600 font-bold uppercase tracking-widest">{{ error }}</p>
      <button @click="fetchPatents" class="btn btn-ghost mt-4 text-xs font-bold uppercase">Retry</button>
    </div>
    
    <div v-else-if="patents.length === 0" class="card">
      <EmptyState icon="💡" title="No patents found" description="Protect your work. Add your research patents here." action-label="Add First Patent" @action="showCreate = true" />
    </div>

    <div v-else class="grid grid-cols-1 md:grid-cols-2 gap-6">
      <div v-for="patent in patents" :key="patent.id" class="card p-6 flex flex-col group card-hover relative border-l-4 border-l-transparent hover:border-l-brand transition-all">
        <div class="flex items-start justify-between mb-5">
          <div class="flex-1 min-w-0">
            <div class="flex items-center gap-3 mb-2">
              <h3 class="text-lg font-black text-slate-900 group-hover:text-brand transition-colors line-clamp-1">{{ patent.title }}</h3>
              <StatusBadge :status="patent.status?.name || 'pending'" />
            </div>
            <p class="text-[10px] font-black text-slate-400 uppercase tracking-widest">Inventors: {{ patent.inventors }}</p>
          </div>
          <div class="w-10 h-10 bg-slate-50 rounded-xl flex items-center justify-center text-slate-400 group-hover:text-brand group-hover:bg-brand-light transition-all duration-300 shadow-sm shrink-0">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><circle cx="12" cy="12" r="10" stroke-width="2.5"/><line x1="12" y1="8" x2="12" y2="12" stroke-width="2.5"/><line x1="12" y1="16" x2="12.01" y2="16" stroke-width="2.5"/></svg>
          </div>
        </div>

        <div class="flex flex-wrap items-center gap-4 text-[10px] font-bold text-slate-500 uppercase tracking-tight mb-6">
          <span class="flex items-center gap-1.5 px-2 py-1 bg-slate-100 rounded-lg">
            <svg class="w-3.5 h-3.5 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><rect x="3" y="4" width="18" height="18" rx="2" ry="2" stroke-width="2.5"/><line x1="16" y1="2" x2="16" y2="6" stroke-width="2.5"/><line x1="8" y1="2" x2="8" y2="6" stroke-width="2.5"/></svg>
            Filed: {{ formatDate(patent.filing_date) }}
          </span>
          <span v-if="patent.patent_number" class="font-mono bg-slate-100 px-2 py-1 rounded-lg text-slate-500">ID: {{ patent.patent_number }}</span>
        </div>

        <div class="mt-auto pt-5 border-t border-slate-50 flex items-center justify-between">
          <span class="text-[10px] font-black text-brand uppercase tracking-widest bg-brand-light px-2.5 py-1 rounded-lg">{{ patent.licenses?.length || 0 }} Licenses</span>
          <div class="flex gap-2">
            <button @click="editPatent(patent)" class="btn btn-ghost hover:bg-slate-100 text-[11px] font-black uppercase tracking-widest py-2">Edit</button>
            <button @click="confirmDelete(patent)" class="btn btn-ghost text-red-500 hover:bg-red-50 text-[11px] font-black uppercase tracking-widest py-2">Delete</button>
          </div>
        </div>
      </div>
    </div>

    <!-- Create/Edit Modal -->
    <Modal :show="showCreate || !!editingPatent" :title="editingPatent ? 'Edit Patent' : 'Add New Patent'" @close="closeModal">
      <form @submit.prevent="savePatent" class="space-y-6">
        <div>
          <label class="block text-[11px] text-slate-500 font-black uppercase tracking-widest mb-2 ml-1">Patent Title *</label>
          <input v-model="form.title" type="text" required class="input h-12 font-bold" placeholder="e.g. New Solar Panel Tech" />
        </div>
        <div>
          <label class="block text-[11px] text-slate-500 font-black uppercase tracking-widest mb-2 ml-1">Inventors *</label>
          <input v-model="form.inventors" type="text" required class="input h-12 font-bold" placeholder="Names (e.g. John Doe, Sarah Smith)" />
        </div>
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-6">
          <div>
            <label class="block text-[11px] text-slate-500 font-black uppercase tracking-widest mb-2 ml-1">Filing Date *</label>
            <input v-model="form.filing_date" type="date" required class="input h-12 font-bold" />
          </div>
          <div>
            <label class="block text-[11px] text-slate-500 font-black uppercase tracking-widest mb-2 ml-1">Patent Number</label>
            <input v-model="form.patent_number" type="text" class="input h-12 font-bold" placeholder="e.g. PAT-12345" />
          </div>
        </div>
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-6">
          <div>
            <label class="block text-[11px] text-slate-500 font-black uppercase tracking-widest mb-2 ml-1">Status</label>
            <select v-model="form.status_id" class="input h-12 font-bold">
              <option v-for="s in patentStatuses" :key="s.id" :value="s.id">{{ formatStatusName(s.name) }}</option>
            </select>
          </div>
          <div>
            <label class="block text-[11px] text-slate-500 font-black uppercase tracking-widest mb-2 ml-1">Project</label>
            <select v-model="form.project_id" class="input h-12 font-bold">
              <option value="">None</option>
              <option v-for="p in projects" :key="p.id" :value="p.id">{{ p.title }}</option>
            </select>
          </div>
        </div>
        <div class="flex justify-end gap-3 pt-6 mt-6 border-t border-slate-100">
          <button type="button" @click="closeModal" class="btn btn-secondary px-6">Cancel</button>
          <button type="submit" class="btn btn-primary px-10 shadow-lg shadow-blue-500/20">{{ editingPatent ? 'Save Changes' : 'Save Patent' }}</button>
        </div>
      </form>
    </Modal>

    <ConfirmDialog :show="showDelete" title="Delete Patent" message="Are you sure you want to delete this patent record? This cannot be undone." confirmText="Delete Now" variant="danger" @confirm="deletePatent" @cancel="showDelete = false" />
  </div>
</template>

<script setup>
import { ref, reactive, onMounted } from 'vue'
import { useNotificationStore } from '@/stores/notification'
import api from '@/services/api'
import StatusBadge from '@/components/StatusBadge.vue'
import Modal from '@/components/Modal.vue'
import ConfirmDialog from '@/components/ConfirmDialog.vue'
import LoadingSkeleton from '@/components/LoadingSkeleton.vue'
import EmptyState from '@/components/EmptyState.vue'
import { formatDate } from '@/utils/formatters'
import { formatStatusName } from '@/utils/colors'

const notif = useNotificationStore()
const patents = ref([]); const loading = ref(true); const error = ref(null)
const patentStatuses = ref([]); const projects = ref([])
const showCreate = ref(false); const editingPatent = ref(null); const showDelete = ref(false); const deletingOutput = ref(null)
const form = reactive({ title: '', inventors: '', filing_date: '', patent_number: '', status_id: 1, project_id: '' })

async function fetchPatents() {
  loading.value = true; error.value = null
  try { const { data } = await api.get('/patents'); patents.value = data.data || data }
  catch (err) { error.value = err.response?.data?.message || 'Failed' } finally { loading.value = false }
}

function editPatent(p) { editingPatent.value = p; Object.assign(form, { title: p.title, inventors: p.inventors, filing_date: p.filing_date, patent_number: p.patent_number || '', status_id: p.status_id, project_id: p.project_id || '' }) }
function closeModal() { showCreate.value = false; editingPatent.value = null; Object.assign(form, { title: '', inventors: '', filing_date: '', patent_number: '', status_id: 1, project_id: '' }) }
function confirmDelete(p) { deletingOutput.value = p; showDelete.value = true }

async function savePatent() {
  try {
    const payload = { ...form, project_id: form.project_id || null }
    if (editingPatent.value) { await api.put(`/patents/${editingPatent.value.id}`, payload); notif.success('Updated!') }
    else { await api.post('/patents', payload); notif.success('Added!') }
    closeModal(); fetchPatents()
  } catch (err) { notif.error(err.response?.data?.message || 'Failed') }
}

async function deletePatent() {
  try { await api.delete(`/patents/${deletingOutput.value.id}`); notif.success('Deleted!'); showDelete.value = false; fetchPatents() }
  catch (err) { notif.error(err.response?.data?.message || 'Failed') }
}

onMounted(async () => {
  await fetchPatents()
  try { const [ss, ps] = await Promise.all([api.get('/lookups/patent_statuses'), api.get('/projects',{params:{per_page:100}})]); patentStatuses.value = ss.data; projects.value = ps.data.data || ps.data } catch (e) {}
})
</script>
