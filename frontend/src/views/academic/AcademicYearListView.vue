<template>
  <div class="flex flex-col gap-6 card">
    <div class="section-header">
      <div>
        <h1 class="section-title">Academic Years</h1>
        <p class="section-subtitle">Manage institutional academic year periods and timelines</p>
      </div>
      <button @click="showCreate = true" class="btn btn-primary">
        <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 4v16m8-8H4" /></svg>
        Add Year
      </button>
    </div>

    <div v-if="loading" class="card p-5"><LoadingSkeleton :rows="3" /></div>
    <div v-else-if="years.length === 0" class="card">
      <EmptyState icon="📆" title="No academic years" description="Create academic years to organize your institution's timeline." action-label="Add Year" @action="showCreate = true" />
    </div>

    <div v-else class="space-y-4">
      <div v-for="year in years" :key="year.id" class="card p-5 group card-hover flex items-center justify-between">
        <div>
          <div class="flex items-center gap-3 mb-1">
            <h3 class="text-base font-bold text-slate-800 group-hover:text-blue-600 transition">{{ year.name }}</h3>
            <span v-if="year.is_current" class="badge badge-green">Current</span>
          </div>
          <p class="text-[11px] font-bold text-slate-400 capitalize tracking-widest">{{ formatDate(year.start_date) }} – {{ formatDate(year.end_date) }}</p>
        </div>
        <div class="flex gap-2">
          <button v-if="!year.is_current" @click="setCurrent(year)" class="btn btn-ghost text-green-600 font-bold" style="padding: 6px 10px; font-size: 11px">Set Current</button>
          <button @click="editYear(year)" class="btn btn-ghost text-blue-600 font-bold" style="padding: 6px 10px; font-size: 11px">Edit</button>
          <button @click="confirmDelete(year)" class="btn btn-ghost text-red-500 hover:bg-red-50" style="padding: 6px 10px; font-size: 11px">Delete</button>
        </div>
      </div>
    </div>

    <Modal :show="showCreate || !!editingYear" :title="editingYear ? 'Edit Academic Year' : 'Add New Year'" @close="closeModal">
      <form @submit.prevent="saveYear" class="space-y-5 px-1 py-1">
        <div>
          <label class="block text-[11px] text-slate-500 font-bold capitalize tracking-wider mb-1.5 ml-1">Year Name *</label>
          <input v-model="form.name" type="text" required class="input" placeholder="e.g., 2024/2025" />
        </div>
        <div class="grid grid-cols-2 gap-4">
          <div>
            <label class="block text-[11px] text-slate-500 font-bold capitalize tracking-wider mb-1.5 ml-1">Start Date *</label>
            <input v-model="form.start_date" type="date" required class="input" />
          </div>
          <div>
            <label class="block text-[11px] text-slate-500 font-bold capitalize tracking-wider mb-1.5 ml-1">End Date *</label>
            <input v-model="form.end_date" type="date" required class="input" />
          </div>
        </div>
        <div class="flex justify-end gap-3 pt-4">
          <button type="button" @click="closeModal" class="btn btn-secondary">Cancel</button>
          <button type="submit" class="btn btn-primary px-5">{{ editingYear ? 'Update' : 'Add' }}</button>
        </div>
      </form>
    </Modal>

    <ConfirmDialog :show="showDelete" title="Delete Academic Year" message="Are you sure you want to delete this academic year?" confirmText="Delete" variant="danger" @confirm="deleteYear" @cancel="showDelete = false" />
  </div>
</template>

<script setup>
import { ref, reactive, onMounted } from 'vue'
import { useNotificationStore } from '@/stores/notification'
import api from '@/services/api'
import Modal from '@/components/Modal.vue'
import ConfirmDialog from '@/components/ConfirmDialog.vue'
import LoadingSkeleton from '@/components/LoadingSkeleton.vue'
import EmptyState from '@/components/EmptyState.vue'
import { formatDate } from '@/utils/formatters'

const notif = useNotificationStore()
const years = ref([]); const loading = ref(true)
const showCreate = ref(false); const editingYear = ref(null); const showDelete = ref(false); const deletingYear = ref(null)
const form = reactive({ name: '', start_date: '', end_date: '' })

async function fetchYears() {
  loading.value = true
  try { const { data } = await api.get('/academic-years'); years.value = data }
  catch (e) {} finally { loading.value = false }
}

function editYear(y) { editingYear.value = y; Object.assign(form, { name: y.name, start_date: y.start_date, end_date: y.end_date }) }
function closeModal() { showCreate.value = false; editingYear.value = null; Object.assign(form, { name: '', start_date: '', end_date: '' }) }
function confirmDelete(y) { deletingYear.value = y; showDelete.value = true }

async function setCurrent(year) {
  try { await api.post(`/academic-years/${year.id}/set-current`); notif.success('Current year set!'); fetchYears() }
  catch (err) { notif.error('Failed') }
}

async function saveYear() {
  try {
    if (editingYear.value) { await api.put(`/academic-years/${editingYear.value.id}`, form); notif.success('Updated!') }
    else { await api.post('/academic-years', form); notif.success('Created!') }
    closeModal(); fetchYears()
  } catch (err) { notif.error(err.response?.data?.message || 'Failed') }
}

async function deleteYear() {
  try { await api.delete(`/academic-years/${deletingYear.value.id}`); notif.success('Deleted!'); showDelete.value = false; fetchYears() }
  catch (err) { notif.error('Failed') }
}

onMounted(fetchYears)
</script>
