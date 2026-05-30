<template>
  <div class="flex flex-col gap-6 card">
    <!-- Header -->
    <div class="section-header">
      <div>
        <h1 class="section-title">Faculties & Colleges</h1>
        <p class="section-subtitle">Manage academic faculties and institutional colleges</p>
      </div>
      <button @click="showCreate = true" class="btn btn-primary">
        <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 4v16m8-8H4" /></svg>
        Add Faculty
      </button>
    </div>

    <!-- Content -->
    <div v-if="loading" class="card p-8 flex justify-center"><div class="animate-spin rounded-full h-8 w-8 border-b-2 border-blue-600"></div></div>
    <div v-else-if="faculties.length === 0" class="card">
      <EmptyState icon="🏫" title="No faculties found" description="Add academic faculties to organize your institution's structure." action-label="Add First Faculty" action-icon="add" @action="showCreate = true" />
    </div>
    <div v-else class="card overflow-hidden">
      <div class="overflow-x-auto">
        <table class="table-auto">
          <thead>
            <tr>
              <th>Faculty Name</th>
              <th>Code</th>
              <th>Campus Alignment</th>
              <th style="text-align: right">Actions</th>
            </tr>
          </thead>
          <tbody>
            <tr v-for="faculty in faculties" :key="faculty.id">
              <td class="font-semibold text-slate-800">{{ faculty.name }}</td>
              <td><span class="badge badge-gray" style="font-size: 10px">{{ faculty.code }}</span></td>
              <td>
                <div class="flex items-center gap-2">
                  <span class="w-2 h-2 rounded-full bg-blue-400"></span>
                  <span class="text-slate-600">{{ faculty.campus?.name || 'Main Campus' }}</span>
                </div>
              </td>
              <td style="text-align: right">
                <div class="flex justify-end gap-2">
                  <button @click="editFaculty(faculty)" class="btn btn-ghost" style="padding: 4px 10px; font-size: 11px;">Edit</button>
                  <button @click="confirmDelete(faculty)" class="btn btn-ghost text-red-600 border-red-50 hover:bg-red-50" style="padding: 4px 10px; font-size: 11px;">Delete</button>
                </div>
              </td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>

    <Modal :show="showCreate || !!editingFaculty" :title="editingFaculty ? 'Edit Faculty Details' : 'Add New Faculty'" @close="closeModal">
      <form @submit.prevent="saveFaculty" class="space-y-4 p-1">
        <div class="space-y-1.5">
          <label class="block text-sm font-semibold text-slate-700">Faculty/College Name *</label>
          <input v-model="form.name" type="text" required placeholder="e.g. Faculty of Technology" class="input" />
        </div>
        <div class="space-y-1.5">
          <label class="block text-sm font-semibold text-slate-700">Abbreviation/Code *</label>
          <input v-model="form.code" type="text" required placeholder="FET" class="input" />
        </div>
        <div class="space-y-1.5">
          <label class="block text-sm font-semibold text-slate-700">Assigned Campus</label>
          <select v-model="form.campus_id" class="input">
            <option value="">Select Campus</option>
            <option v-for="c in campuses" :key="c.id" :value="c.id">{{ c.name }}</option>
          </select>
        </div>
        <div class="flex justify-end gap-3 pt-4 border-t border-slate-100">
          <button type="button" @click="closeModal" class="btn btn-secondary">Discard</button>
          <button type="submit" class="btn btn-primary">{{ editingFaculty ? 'Update' : 'Add' }}</button>
        </div>
      </form>
    </Modal>

    <ConfirmDialog :show="showDelete" title="Delete Faculty" :message="'Delete ' + (deletingFaculty?.name) + '?'" confirmText="Delete" variant="danger" @confirm="deleteFaculty" @cancel="showDelete = false" />
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
const faculties = ref([]); const loading = ref(true); const campuses = ref([])
const showCreate = ref(false); const editingFaculty = ref(null); const showDelete = ref(false); const deletingFaculty = ref(null)
const form = reactive({ name: '', code: '', campus_id: '' })

async function fetchData() {
  loading.value = true
  try { const [fRes, cRes] = await Promise.all([api.get('/faculties'), api.get('/campuses')]); faculties.value = fRes.data; campuses.value = cRes.data }
  catch (e) {} finally { loading.value = false }
}

function editFaculty(f) { editingFaculty.value = f; Object.assign(form, { name: f.name, code: f.code, campus_id: f.campus_id || '' }) }
function closeModal() { showCreate.value = false; editingFaculty.value = null; Object.assign(form, { name: '', code: '', campus_id: '' }) }
function confirmDelete(f) { deletingFaculty.value = f; showDelete.value = true }

async function saveFaculty() { try { const payload = { ...form, campus_id: form.campus_id || null }; if (editingFaculty.value) { await api.put(`/faculties/${editingFaculty.value.id}`, payload) } else { await api.post('/faculties', payload) }; notif.success(editingFaculty.value ? 'Updated!' : 'Created!'); closeModal(); fetchData() } catch (err) { notif.error('Failed') } }
async function deleteFaculty() { try { await api.delete(`/faculties/${deletingFaculty.value.id}`); notif.success('Deleted!'); showDelete.value = false; fetchData() } catch (err) { notif.error('Failed') } }

onMounted(fetchData)
</script>
