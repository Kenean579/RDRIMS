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

    <!-- Content Wrapper -->
    <div v-if="loading" class="grid grid-cols-1 gap-4">
      <div v-for="i in 3" :key="i" class="card h-24 animate-pulse bg-slate-50/50"></div>
    </div>
    <div v-else-if="faculties.length === 0" class="card">
      <EmptyState icon="🏫" title="No faculties found" description="Add academic faculties to organize your institution's structure." action-label="Add First Faculty" action-icon="add" @action="showCreate = true" />
    </div>
    
    <div v-else class="grid grid-cols-1 lg:grid-cols-2 gap-5">
      <div v-for="faculty in faculties" :key="faculty.id" class="card p-8 flex flex-col sm:flex-row sm:items-center justify-between gap-6 card-hover transition-all">
        <div class="flex items-center gap-4">
          <div class="w-12 h-12 rounded-2xl bg-slate-100 text-white flex items-center justify-center font-bold text-xl">
            {{ faculty.name.charAt(0) }}
          </div>
          <div>
            <h3 class="font-bold text-slate-800 text-lg leading-tight mb-1">{{ faculty.name }}</h3>
            <div class="flex items-center gap-2">
              <span class="inline-block px-2.5 py-0.5 bg-slate-100 text-slate-500 text-xs font-medium rounded-md border border-slate-100">CODE: {{ faculty.code }}</span>
              <span class="inline-block px-2 py-0.5 bg-emerald-50 text-emerald-600 text-xs font-medium rounded-md border border-emerald-100"><i class="fas fa-map-marker-alt mr-1"></i>{{ faculty.campus?.name || 'Main Campus' }}</span>
            </div>
          </div>
        </div>
        <ActionMenu :actions="[
          { key: 'edit', label: 'Edit', handler: () => editFaculty(faculty) },
          { separator: true },
          { key: 'delete', label: 'Delete', handler: () => confirmDelete(faculty) }
        ]" />
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
          <label class="block text-sm font-semibold text-slate-700">University</label>
          <select v-model="form.university_id" class="input">
            <option value="">Select University</option>
            <option v-for="u in universities" :key="u.id" :value="u.id">{{ u.name }}</option>
          </select>
        </div>
        <div class="space-y-1.5">
          <label class="block text-sm font-semibold text-slate-700">Assigned Campus</label>
          <select v-model="form.campus_id" class="input" :disabled="!form.university_id">
            <option value="">Select Campus</option>
            <option v-for="c in filteredCampuses" :key="c.id" :value="c.id">{{ c.name }}</option>
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
import { ref, reactive, computed, watch, onMounted } from 'vue'
import { useNotificationStore } from '@/stores/notification'
import api from '@/services/api'
import Modal from '@/components/Modal.vue'
import ConfirmDialog from '@/components/ConfirmDialog.vue'
import LoadingSkeleton from '@/components/LoadingSkeleton.vue'
import EmptyState from '@/components/EmptyState.vue'
import ActionMenu from '@/components/ActionMenu.vue'

const notif = useNotificationStore()
const faculties = ref([]); const loading = ref(true); const campuses = ref([]); const universities = ref([])
const showCreate = ref(false); const editingFaculty = ref(null); const showDelete = ref(false); const deletingFaculty = ref(null)
const form = reactive({ name: '', code: '', university_id: '', campus_id: '' })

// Computed
const filteredCampuses = computed(() => campuses.value.filter(c => c.university_id === form.university_id))

// Watchers
watch(() => form.university_id, () => { form.campus_id = '' })

async function fetchData() {
  loading.value = true
  try { 
    const fRes = await api.get('/faculties')
    const cRes = await api.get('/campuses')
    const uRes = await api.get('/universities')
    faculties.value = fRes.data; campuses.value = cRes.data; universities.value = uRes.data 
  }
  catch (e) {} finally { loading.value = false }
}

function editFaculty(f) { 
  let uid = ''
  if (f.campus_id) {
    const camp = campuses.value.find(c => c.id === f.campus_id)
    if (camp) uid = camp.university_id
  }
  editingFaculty.value = f; Object.assign(form, { name: f.name, code: f.code, university_id: uid, campus_id: f.campus_id || '' }) 
}
function closeModal() { showCreate.value = false; editingFaculty.value = null; Object.assign(form, { name: '', code: '', university_id: '', campus_id: '' }) }
function confirmDelete(f) { deletingFaculty.value = f; showDelete.value = true }

async function saveFaculty() { try { const payload = { ...form, campus_id: form.campus_id || null }; if (editingFaculty.value) { await api.put(`/faculties/${editingFaculty.value.id}`, payload) } else { await api.post('/faculties', payload) }; notif.success(editingFaculty.value ? 'Updated!' : 'Created!'); closeModal(); fetchData() } catch (err) { notif.error('Failed') } }
async function deleteFaculty() { try { await api.delete(`/faculties/${deletingFaculty.value.id}`); notif.success('Deleted!'); showDelete.value = false; fetchData() } catch (err) { notif.error('Failed') } }

onMounted(fetchData)
</script>
