<template>
  <div class="flex flex-col gap-6 card">
    <!-- Header -->
    <div class="section-header">
      <div>
        <h1 class="section-title">University Campuses</h1>
        <p class="section-subtitle">Manage institutional campus locations and assignments</p>
      </div>
      <button @click="showCreate = true" class="btn btn-primary">
        <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 4v16m8-8H4" /></svg>
        Add Campus
      </button>
    </div>

    <!-- Content -->
    <div v-if="loading" class="card p-8"><LoadingSkeleton :rows="4" /></div>
    <div v-else-if="campuses.length === 0" class="card">
      <EmptyState icon="📍" title="No campuses found" description="Add university campuses to track your institution's locations." action-label="Add Campus" @action="showCreate = true" />
    </div>

    <div v-else class="card overflow-hidden">
      <div class="overflow-x-auto">
        <table class="table-auto">
          <thead>
            <tr>
              <th>Campus Name</th>
              <th>Code</th>
              <th>University</th>
              <th style="text-align: right">Actions</th>
            </tr>
          </thead>
          <tbody>
            <tr v-for="campus in campuses" :key="campus.id" class="group">
              <td class="font-bold text-slate-800 group-hover:text-blue-600 transition">{{ campus.name }}</td>
              <td><span class="badge badge-gray" style="font-size: 10px">{{ campus.code }}</span></td>
              <td class="text-sm text-slate-500">{{ campus.university?.name || 'N/A' }}</td>
              <td style="text-align: right">
                <div class="flex justify-end gap-2">
                  <button @click="editCampus(campus)" class="btn btn-ghost text-blue-600 font-bold" style="padding: 6px 10px; font-size: 11px">Edit</button>
                  <button @click="confirmDelete(campus)" class="btn btn-ghost text-red-500 hover:bg-red-50" style="padding: 6px 10px; font-size: 11px">Delete</button>
                </div>
              </td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>

    <!-- Create/Edit Modal -->
    <Modal :show="showCreate || !!editingCampus" :title="editingCampus ? 'Edit Campus' : 'Add New Campus'" @close="closeModal">
      <form @submit.prevent="saveCampus" class="space-y-5 px-1 py-1">
        <div>
          <label class="block text-[11px] text-slate-500 font-bold uppercase tracking-wider mb-1.5 ml-1">Campus Name *</label>
          <input v-model="form.name" type="text" required class="input" placeholder="e.g., Main Campus" />
        </div>
        <div>
          <label class="block text-[11px] text-slate-500 font-bold uppercase tracking-wider mb-1.5 ml-1">Campus Code *</label>
          <input v-model="form.code" type="text" required class="input" placeholder="e.g., MC" />
        </div>
        <div>
          <label class="block text-[11px] text-slate-500 font-bold uppercase tracking-wider mb-1.5 ml-1">Parent University</label>
          <select v-model="form.university_id" class="input">
            <option value="">Select University</option>
            <option v-for="u in universities" :key="u.id" :value="u.id">{{ u.name }}</option>
          </select>
        </div>
        <div class="flex justify-end gap-3 pt-4">
          <button type="button" @click="closeModal" class="btn btn-secondary">Cancel</button>
          <button type="submit" class="btn btn-primary px-10">{{ editingCampus ? 'Update' : 'Add' }}</button>
        </div>
      </form>
    </Modal>

    <ConfirmDialog :show="showDelete" title="Delete Campus" :message="'Are you sure you want to delete \'' + (deletingCampus?.name) + '\'?'" confirmText="Delete" variant="danger" @confirm="deleteCampus" @cancel="showDelete = false" />
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
const campuses = ref([]); const loading = ref(true); const universities = ref([])
const showCreate = ref(false); const editingCampus = ref(null); const showDelete = ref(false); const deletingCampus = ref(null)
const form = reactive({ name: '', code: '', university_id: '' })

async function fetchData() { loading.value = true; try { const [cRes, uRes] = await Promise.all([api.get('/campuses'), api.get('/universities')]); campuses.value = cRes.data; universities.value = uRes.data } catch (e) {} finally { loading.value = false } }
function editCampus(c) { editingCampus.value = c; Object.assign(form, { name: c.name, code: c.code, university_id: c.university_id || '' }) }
function closeModal() { showCreate.value = false; editingCampus.value = null; Object.assign(form, { name: '', code: '', university_id: '' }) }
function confirmDelete(c) { deletingCampus.value = c; showDelete.value = true }
async function saveCampus() { try { const payload = { ...form, university_id: form.university_id || null }; if (editingCampus.value) await api.put(`/campuses/${editingCampus.value.id}`, payload); else await api.post('/campuses', payload); notif.success(editingCampus.value ? 'Updated!' : 'Created!'); closeModal(); fetchData() } catch (err) { notif.error('Failed') } }
async function deleteCampus() { try { await api.delete(`/campuses/${deletingCampus.value.id}`); notif.success('Deleted!'); showDelete.value = false; fetchData() } catch (err) { notif.error('Failed') } }
onMounted(fetchData)
</script>
