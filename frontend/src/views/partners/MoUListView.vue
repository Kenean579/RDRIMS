<template>
  <div class="flex flex-col gap-6 card">
    <div class="mb-2">
      <router-link to="/app/partners" class="inline-flex items-center text-sm font-bold text-slate-500 hover:text-blue-600 transition">
        <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M10 19l-7-7m0 0l7-7m-7 7h18" /></svg>
        Back to Partners
      </router-link>
    </div>

    <div class="section-header">
      <div>
        <h1 class="section-title">Memoranda of Understanding</h1>
        <p class="section-subtitle">Manage MoUs for <span class="font-bold text-slate-700">{{ partner?.name || 'Partner' }}</span></p>
      </div>
      <button @click="showCreate = true" class="btn btn-primary">
        <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 4v16m8-8H4" /></svg>
        Add MoU
      </button>
    </div>

    <div v-if="loading" class="card p-8"><LoadingSkeleton :rows="3" /></div>
    <div v-else-if="mous.length === 0" class="card">
      <EmptyState icon="📜" title="No MoUs found" description="A Memorandum of Understanding (MoU) defines the formal agreement between your institution and this partner." action-label="Add MoU" @action="showCreate = true" />
    </div>

    <div v-else class="space-y-4">
      <div v-for="m in mous" :key="m.id" class="card p-5 group card-hover flex items-center justify-between">
        <div class="flex items-center gap-4">
          <div class="w-10 h-10 bg-slate-50 rounded-xl flex items-center justify-center text-slate-400 group-hover:text-blue-600 group-hover:bg-blue-50 transition duration-300">
             <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" /></svg>
          </div>
          <div>
            <h3 class="text-base font-bold text-slate-800 group-hover:text-blue-600 transition mb-1">MoU Agreement</h3>
            <p class="text-[11px] font-bold text-slate-400 uppercase tracking-widest">{{ formatDate(m.start_date) }} – {{ formatDate(m.end_date) }}</p>
          </div>
        </div>
        <div class="flex gap-2">
          <button @click="editMoU(m)" class="btn btn-ghost text-blue-600 font-bold" style="padding: 6px 10px; font-size: 11px">Edit</button>
          <button @click="confirmDelete(m)" class="btn btn-ghost text-red-500 hover:bg-red-50" style="padding: 6px 10px; font-size: 11px">Delete</button>
        </div>
      </div>
    </div>

    <Modal :show="showCreate || !!editingMoU" :title="editingMoU ? 'Edit MoU Dates' : 'Add New MoU'" @close="closeModal">
      <form @submit.prevent="saveMoU" class="space-y-5 px-1 py-1">
        <div class="grid grid-cols-2 gap-4">
          <div>
            <label class="block text-[11px] text-slate-500 font-bold uppercase tracking-wider mb-1.5 ml-1">Start Date *</label>
            <input v-model="form.start_date" type="date" required class="input" />
          </div>
          <div>
             <label class="block text-[11px] text-slate-500 font-bold uppercase tracking-wider mb-1.5 ml-1">End Date *</label>
             <input v-model="form.end_date" type="date" required class="input" />
          </div>
        </div>
        <div class="flex justify-end gap-3 pt-4">
          <button type="button" @click="closeModal" class="btn btn-secondary">Cancel</button>
          <button type="submit" class="btn btn-primary px-10">{{ editingMoU ? 'Update' : 'Add MoU' }}</button>
        </div>
      </form>
    </Modal>

    <ConfirmDialog :show="showDelete" title="Delete MoU" message="Are you sure you want to delete this MoU? This action cannot be undone." confirmText="Delete" variant="danger" @confirm="deleteMoU" @cancel="showDelete = false" />
  </div>
</template>

<script setup>
import { ref, reactive, onMounted } from 'vue'
import { useRoute } from 'vue-router'
import { useNotificationStore } from '@/stores/notification'
import api from '@/services/api'
import Modal from '@/components/Modal.vue'
import ConfirmDialog from '@/components/ConfirmDialog.vue'
import LoadingSkeleton from '@/components/LoadingSkeleton.vue'
import EmptyState from '@/components/EmptyState.vue'
import { formatDate } from '@/utils/formatters'

const route = useRoute(); const notif = useNotificationStore()
const partner = ref({}); const mous = ref([]); const loading = ref(true)
const showCreate = ref(false); const editingMoU = ref(null); const showDelete = ref(false); const deletingMoU = ref(null)
const form = reactive({ start_date: '', end_date: '' })

async function fetchData() {
  loading.value = true
  try {
    const [pRes, mRes] = await Promise.all([api.get(`/partners/${route.params.id}`), api.get(`/partners/${route.params.id}/mo-us`)])
    partner.value = pRes.data; mous.value = mRes.data.data || mRes.data
  } catch (e) {} finally { loading.value = false }
}

function editMoU(m) { editingMoU.value = m; Object.assign(form, { start_date: m.start_date, end_date: m.end_date }) }
function closeModal() { showCreate.value = false; editingMoU.value = null; Object.assign(form, { start_date: '', end_date: '' }) }
function confirmDelete(m) { deletingMoU.value = m; showDelete.value = true }

async function saveMoU() {
  try {
    if (editingMoU.value) { await api.put(`/mo-us/${editingMoU.value.id}`, form); notif.success('Updated!') }
    else { await api.post(`/partners/${route.params.id}/mo-us`, form); notif.success('Added!') }
    closeModal(); fetchData()
  } catch (err) { notif.error(err.response?.data?.message || 'Failed') }
}

async function deleteMoU() {
  try { await api.delete(`/mo-us/${deletingMoU.value.id}`); notif.success('Deleted!'); showDelete.value = false; fetchData() }
  catch (err) { notif.error(err.response?.data?.message || 'Failed') }
}

onMounted(fetchData)
</script>
