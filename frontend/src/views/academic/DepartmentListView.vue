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

    <!-- Content Wrapper -->
    <div v-if="loading" class="grid grid-cols-1 gap-4">
      <div v-for="i in 3" :key="i" class="card h-24 animate-pulse bg-slate-50/50"></div>
    </div>
    <div v-else-if="departments.length === 0" class="card">
      <EmptyState icon="🏛️" title="No departments found" description="Add academic departments to organize your institution's structure." action-label="Add First Department" action-icon="add" @action="showCreate = true" />
    </div>

    <div v-else class="grid grid-cols-1 lg:grid-cols-2 gap-5">
      <div v-for="dept in departments" :key="dept.id" class="card p-6 flex flex-col sm:flex-row sm:items-center justify-between gap-6 card-hover border-l-4 border-l-orange-500 hover:border-l-orange-600 transition-all">
        <div class="flex items-center gap-4">
          <div class="w-12 h-12 rounded-2xl bg-linear-to-br from-orange-400 to-red-500 text-white flex items-center justify-center font-bold text-xl shadow-lg shadow-orange-500/30">
            {{ dept.name.charAt(0) }}
          </div>
          <div>
            <h3 class="font-bold text-slate-800 text-lg leading-tight mb-1">{{ dept.name }}</h3>
            <div class="flex items-center gap-2">
              <span class="inline-block px-2.5 py-0.5 bg-slate-100 text-slate-500 text-[10px] font-bold capitalize tracking-widest rounded-md border border-slate-200">CODE: {{ dept.code }}</span>
              <span class="inline-block px-2 py-0.5 bg-fuchsia-50 text-fuchsia-600 text-[10px] font-bold capitalize tracking-widest rounded-md border border-fuchsia-100"><i class="fas fa-building mr-1"></i>{{ dept.faculty?.name || 'N/A' }}</span>
            </div>
          </div>
        </div>
        <div class="flex gap-2 shrink-0">
          <button @click="editDept(dept)" class="btn btn-secondary h-9 px-4 text-[11px] font-bold capitalize tracking-widest">
            Edit
          </button>
          <button @click="confirmDelete(dept)" class="btn btn-danger h-9 px-4 text-[11px] font-bold capitalize tracking-widest shadow-lg shadow-rose-500/20">
            Delete
          </button>
        </div>
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
          <label class="block text-sm font-semibold text-slate-700">University</label>
          <select v-model="form.university_id" class="input">
            <option value="">Select University</option>
            <option v-for="u in universities" :key="u.id" :value="u.id">{{ u.name }}</option>
          </select>
        </div>
        <div class="space-y-1.5">
          <label class="block text-sm font-semibold text-slate-700">Campus</label>
          <select v-model="form.campus_id" class="input" :disabled="!form.university_id">
            <option value="">Select Campus</option>
            <option v-for="c in filteredCampuses" :key="c.id" :value="c.id">{{ c.name }}</option>
          </select>
        </div>
        <div class="space-y-1.5">
          <label class="block text-sm font-semibold text-slate-700">Parent Faculty</label>
          <select v-model="form.faculty_id" class="input" :disabled="!form.campus_id">
            <option value="">Select Faculty</option>
            <option v-for="f in filteredFaculties" :key="f.id" :value="f.id">{{ f.name }}</option>
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
import { ref, reactive, computed, watch, onMounted } from 'vue'
import { useNotificationStore } from '@/stores/notification'
import api from '@/services/api'
import Modal from '@/components/Modal.vue'
import ConfirmDialog from '@/components/ConfirmDialog.vue'
import LoadingSkeleton from '@/components/LoadingSkeleton.vue'
import EmptyState from '@/components/EmptyState.vue'

const notif = useNotificationStore()
const departments = ref([]); const loading = ref(true); const faculties = ref([]); const campuses = ref([]); const universities = ref([])
const showCreate = ref(false); const editingDept = ref(null); const showDelete = ref(false); const deletingDept = ref(null)
const form = reactive({ name: '', code: '', university_id: '', campus_id: '', faculty_id: '' })

// Computed
const filteredCampuses = computed(() => campuses.value.filter(c => c.university_id === form.university_id))
const filteredFaculties = computed(() => faculties.value.filter(f => f.campus_id === form.campus_id))

// Watchers
watch(() => form.university_id, () => { form.campus_id = ''; form.faculty_id = '' })
watch(() => form.campus_id, () => { form.faculty_id = '' })

async function fetchData() {
  loading.value = true
  try { 
    const [dRes, fRes, cRes, uRes] = await Promise.all([api.get('/departments'), api.get('/faculties'), api.get('/campuses'), api.get('/universities')])
    departments.value = dRes.data; faculties.value = fRes.data; campuses.value = cRes.data; universities.value = uRes.data 
  }
  catch (e) {} finally { loading.value = false }
}

function editDept(d) {
  let uid = '', cid = ''
  if (d.faculty_id) {
    const fac = faculties.value.find(f => f.id === d.faculty_id)
    if (fac) {
      cid = fac.campus_id; const camp = campuses.value.find(c => c.id === cid)
      if (camp) uid = camp.university_id
    }
  }
  editingDept.value = d; Object.assign(form, { name: d.name, code: d.code, university_id: uid, campus_id: cid, faculty_id: d.faculty_id || '' }) 
}
function closeModal() { showCreate.value = false; editingDept.value = null; Object.assign(form, { name: '', code: '', university_id: '', campus_id: '', faculty_id: '' }) }
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
