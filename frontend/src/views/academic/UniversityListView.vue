<template>
  <div class="flex flex-col gap-6 card">
    <!-- Header -->
    <div class="section-header">
      <div>
        <h1 class="section-title">Institutional Management</h1>
        <p class="section-subtitle">Manage universities and multi-tenant configurations</p>
      </div>
      <button @click="showCreate = true" class="btn btn-primary">
        <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 4v16m8-8H4" /></svg>
        Add University
      </button>
    </div>

    <!-- Content -->
    <div v-if="loading" class="card p-8"><LoadingSkeleton :rows="3" /></div>
    <div v-else-if="universities.length === 0" class="card">
      <EmptyState icon="🎓" title="No universities found" description="Add universities to manage multiple institutions." action-label="Add First University" action-icon="add" @action="showCreate = true" />
    </div>
    <div v-else class="grid grid-cols-1 md:grid-cols-2 gap-4">
      <div v-for="uni in universities" :key="uni.id" class="card p-5 flex items-center justify-between card-hover">
        <div class="flex items-center gap-4">
          <div class="w-10 h-10 rounded-xl bg-blue-50 text-blue-600 flex items-center justify-center font-bold text-lg">
            {{ uni.name.charAt(0) }}
          </div>
          <div>
            <h3 class="font-bold text-slate-800">{{ uni.name }}</h3>
            <p class="text-[11px] text-slate-400 font-medium uppercase tracking-wider">Institution Code: {{ uni.code }}</p>
          </div>
        </div>
        <div class="flex gap-2">
          <button @click="editUni(uni)" class="btn btn-ghost" style="padding: 4px 10px; font-size: 11px;">Edit</button>
          <button @click="confirmDelete(uni)" class="btn btn-ghost text-red-600 border-red-50 hover:bg-red-50" style="padding: 4px 10px; font-size: 11px;">Delete</button>
        </div>
      </div>
    </div>

    <!-- Modals -->
    <Modal :show="showCreate || !!editingUni" :title="editingUni ? 'Edit University' : 'Register New University'" @close="closeModal">
      <form @submit.prevent="saveUni" class="space-y-4 p-1">
        <div class="space-y-1.5">
          <label class="block text-sm font-semibold text-slate-700">Institution Name *</label>
          <input v-model="form.name" type="text" required placeholder="University Name" class="input" />
        </div>
        <div class="space-y-1.5">
          <label class="block text-sm font-semibold text-slate-700">Code/Safe-name *</label>
          <input v-model="form.code" type="text" required placeholder="WU" class="input" />
        </div>
        <div class="flex justify-end gap-3 pt-4 border-t border-slate-100">
          <button type="button" @click="closeModal" class="btn btn-secondary">Discard</button>
          <button type="submit" class="btn btn-primary">{{ editingUni ? 'Update' : 'Register' }}</button>
        </div>
      </form>
    </Modal>

    <ConfirmDialog :show="showDelete" title="Delete University" message="Delete this university?" confirmText="Delete" variant="danger" @confirm="deleteUni" @cancel="showDelete = false" />
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

const notif = useNotificationStore()
const universities = ref([]); const loading = ref(true)
const showCreate = ref(false); const editingUni = ref(null); const showDelete = ref(false); const deletingUni = ref(null)
const form = reactive({ name: '', code: '' })

async function fetchUniversities() {
  loading.value = true
  try { const { data } = await api.get('/universities'); universities.value = data }
  catch (e) {} finally { loading.value = false }
}

function editUni(u) { editingUni.value = u; Object.assign(form, { name: u.name, code: u.code }) }
function closeModal() { showCreate.value = false; editingUni.value = null; Object.assign(form, { name: '', code: '' }) }
function confirmDelete(u) { deletingUni.value = u; showDelete.value = true }

async function saveUni() {
  try {
    if (editingUni.value) { await api.put(`/universities/${editingUni.value.id}`, form); notif.success('Updated!') }
    else { await api.post('/universities', form); notif.success('Created!') }
    closeModal(); fetchUniversities()
  } catch (err) { notif.error('Failed') }
}

async function deleteUni() {
  try { await api.delete(`/universities/${deletingUni.value.id}`); notif.success('Deleted!'); showDelete.value = false; fetchUniversities() }
  catch (err) { notif.error('Failed') }
}

onMounted(fetchUniversities)
</script>
