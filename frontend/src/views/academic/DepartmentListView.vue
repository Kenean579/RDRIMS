<template>
  <div class="flex flex-col gap-6 card">
    <!-- Header -->
    <div class="section-header">
      <div>
        <h1 class="section-title">Academic Departments</h1>
        <p class="section-subtitle">Manage departmental structure and faculty alignment</p>
      </div>
      <button @click="showCreate = true" class="btn btn-primary">
        <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 4v16m8-8H4" /></svg>
        Add Department
      </button>
    </div>

    <!-- Content -->
    <div v-if="loading" class="card p-8 flex justify-center"><div class="animate-spin rounded-full h-8 w-8 border-b-2 border-blue-600"></div></div>
    <div v-else-if="departments.length === 0" class="card">
      <EmptyState icon="🏛️" title="No departments found" description="Add academic departments to organize your institution's structure." action-label="Add First Department" action-icon="add" @action="showCreate = true" />
    </div>
    <div v-else class="card overflow-hidden">
      <div class="overflow-x-auto">
        <table class="table-auto">
          <thead>
            <tr>
              <th>Department Name</th>
              <th>Code</th>
              <th>Parent Faculty</th>
              <th style="text-align: right">Actions</th>
            </tr>
          </thead>
          <tbody>
            <tr v-for="dept in departments" :key="dept.id">
              <td class="font-semibold text-slate-800">{{ dept.name }}</td>
              <td><span class="badge badge-gray" style="font-size: 10px">{{ dept.code }}</span></td>
              <td>{{ dept.faculty?.name || 'N/A' }}</td>
              <td style="text-align: right">
                <div class="flex justify-end gap-2">
                  <button @click="editDept(dept)" class="btn btn-ghost" style="padding: 4px 10px; font-size: 11px;">Edit</button>
                  <button @click="confirmDelete(dept)" class="btn btn-ghost text-red-600 border-red-50 hover:bg-red-50" style="padding: 4px 10px; font-size: 11px;">Delete</button>
                </div>
              </td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>

    <Modal :show="showCreate || !!editingDept" :title="editingDept ? 'Edit Department Details' : 'Add New Department'" @close="closeModal">
      <form @submit.prevent="saveDept" class="space-y-4 p-1">
        <div class="space-y-1.5">
          <label class="block text-sm font-semibold text-slate-700">Department Name *</label>
          <input v-model="form.name" type="text" required placeholder="e.g. Computer Science" class="input" />
        </div>
        <div class="space-y-1.5">
          <label class="block text-sm font-semibold text-slate-700">Department Code *</label>
          <input v-model="form.code" type="text" required placeholder="CS" class="input" />
        </div>
        <div class="space-y-1.5">
          <label class="block text-sm font-semibold text-slate-700">Parent Faculty</label>
          <select v-model="form.faculty_id" class="input">
            <option value="">Select Faculty</option>
            <option v-for="f in faculties" :key="f.id" :value="f.id">{{ f.name }}</option>
          </select>
        </div>
        <div class="flex justify-end gap-3 pt-4 border-t border-slate-100">
          <button type="button" @click="closeModal" class="btn btn-secondary">Discard</button>
          <button type="submit" class="btn btn-primary">{{ editingDept ? 'Update' : 'Add' }}</button>
        </div>
      </form>
    </Modal>

    <ConfirmDialog :show="showDelete" title="Delete Department" message="Delete this department?" confirmText="Delete" variant="danger" @confirm="deleteDept" @cancel="showDelete = false" />
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
const departments = ref([]); const loading = ref(true); const faculties = ref([])
const showCreate = ref(false); const editingDept = ref(null); const showDelete = ref(false); const deletingDept = ref(null)
const form = reactive({ name: '', code: '', faculty_id: '' })

async function fetchData() {
  loading.value = true
  try { const [dRes, fRes] = await Promise.all([api.get('/departments'), api.get('/faculties')]); departments.value = dRes.data; faculties.value = fRes.data }
  catch (e) {} finally { loading.value = false }
}

function editDept(d) { editingDept.value = d; Object.assign(form, { name: d.name, code: d.code, faculty_id: d.faculty_id || '' }) }
function closeModal() { showCreate.value = false; editingDept.value = null; Object.assign(form, { name: '', code: '', faculty_id: '' }) }
function confirmDelete(d) { deletingDept.value = d; showDelete.value = true }

async function saveDept() {
  try {
    const payload = { ...form, faculty_id: form.faculty_id || null }
    if (editingDept.value) { await api.put(`/departments/${editingDept.value.id}`, payload); notif.success('Updated!') }
    else { await api.post('/departments', payload); notif.success('Added!') }
    closeModal(); fetchData()
  } catch (err) { notif.error('Failed') }
}

async function deleteDept() {
  try { await api.delete(`/departments/${deletingDept.value.id}`); notif.success('Deleted!'); showDelete.value = false; fetchData() }
  catch (err) { notif.error('Failed') }
}

onMounted(fetchData)
</script>
