<template>
  <div class="flex flex-col gap-5 pb-6 animate-fade">
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
      <div>
        <h1 class="text-xl font-bold text-slate-900">Departments</h1>
        <p class="text-sm text-slate-500 mt-1">Manage academic departments and their faculty placement.</p>
      </div>
      <button type="button" class="btn btn-primary h-11 px-5" @click="openCreate">
        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 4v16m8-8H4" /></svg>
        Add Department
      </button>
    </div>

    <div class="grid grid-cols-1 sm:grid-cols-[180px_1fr] gap-4">
      <div class="card p-5">
        <p class="text-xs font-semibold text-slate-500">Total departments</p>
        <p class="text-3xl font-bold text-slate-900 mt-1">{{ departments.length }}</p>
      </div>
      <div class="card p-5 flex items-end">
        <div class="w-full">
          <label for="department-search" class="block text-xs font-semibold text-slate-500 mb-2">Search</label>
          <div class="relative">
            <svg class="absolute left-3.5 top-1/2 -translate-y-1/2 w-4 h-4 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><circle cx="11" cy="11" r="8" stroke-width="2"/><path d="m21 21-4.35-4.35" stroke-width="2"/></svg>
            <input id="department-search" v-model.trim="searchQuery" class="input pl-10" placeholder="Search by department, code, or faculty..." />
          </div>
        </div>
      </div>
    </div>

    <div v-if="loading" class="grid grid-cols-1 md:grid-cols-2 gap-4">
      <div v-for="item in 4" :key="item" class="card h-28 animate-pulse bg-slate-50"></div>
    </div>

    <div v-else-if="error" class="card p-10 text-center">
      <p class="text-sm font-semibold text-rose-600">{{ error }}</p>
      <button type="button" class="btn btn-secondary mt-4" @click="fetchData">Retry</button>
    </div>

    <EmptyState
      v-else-if="filteredDepartments.length === 0"
      icon="building"
      :title="searchQuery ? 'No matching departments' : 'No departments found'"
      :description="searchQuery ? 'Try a different department, code, or faculty.' : 'Add a department to organize the academic structure.'"
      :action-label="searchQuery ? '' : 'Add First Department'"
      @action="openCreate"
    />

    <div v-else class="grid grid-cols-1 md:grid-cols-2 gap-4">
      <div v-for="dept in filteredDepartments" :key="dept.id" class="card p-5 flex items-start gap-4 hover:border-brand/30 transition-colors">
        <div class="w-11 h-11 rounded-xl bg-brand/10 text-brand flex items-center justify-center font-bold shrink-0">
          {{ dept.name.charAt(0).toUpperCase() }}
        </div>
        <div class="flex-1 min-w-0">
          <div class="flex items-start justify-between gap-3">
            <div class="min-w-0">
              <h2 class="text-sm font-bold text-slate-900 truncate" :title="dept.name">{{ dept.name }}</h2>
              <p class="text-xs text-slate-500 mt-1 truncate">{{ dept.faculty?.name || 'Faculty not assigned' }}</p>
            </div>
            <ActionMenu :actions="[
              { key: 'edit', label: 'Edit', handler: () => editDept(dept) },
              { separator: true },
              { key: 'delete', label: 'Delete', handler: () => confirmDelete(dept) }
            ]" />
          </div>
          <div class="flex flex-wrap gap-2 mt-3">
            <span class="px-2.5 py-1 rounded-md bg-slate-100 text-slate-600 text-xs font-semibold">{{ dept.code }}</span>
            <span v-if="placementLabel(dept)" class="px-2.5 py-1 rounded-md bg-blue-50 text-blue-700 text-xs font-medium">{{ placementLabel(dept) }}</span>
          </div>
        </div>
      </div>
    </div>

    <Modal :show="showCreate || !!editingDept" :title="editingDept ? 'Edit Department' : 'Add Department'" size="md" @close="closeModal">
      <form class="space-y-5" @submit.prevent="saveDept">
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
          <div class="sm:col-span-2">
            <label class="block text-xs font-semibold text-slate-600 mb-2">Department name *</label>
            <input v-model.trim="form.name" required maxlength="255" class="input" placeholder="e.g. Computer Science" />
          </div>
          <div class="sm:col-span-2">
            <label class="block text-xs font-semibold text-slate-600 mb-2">Department code *</label>
            <input v-model.trim="form.code" required maxlength="50" class="input uppercase" placeholder="e.g. CS" />
          </div>
        </div>

        <div class="pt-1">
          <div class="flex items-center justify-between mb-3">
            <p class="text-xs font-semibold text-slate-600">Academic placement *</p>
            <span v-if="editingDept" class="text-xs text-slate-400">Placement cannot be changed after creation</span>
          </div>
          <div class="grid grid-cols-1 sm:grid-cols-2 gap-4" :class="{ 'opacity-60': editingDept }">
            <div>
              <label class="block text-xs text-slate-500 mb-2">University</label>
              <select v-model="form.university_id" required class="input" :disabled="!!editingDept" @change="onUniversityChange">
                <option value="">Select university</option>
                <option v-for="item in universities" :key="item.id" :value="item.id">{{ item.name }}</option>
              </select>
            </div>
            <div>
              <label class="block text-xs text-slate-500 mb-2">Campus</label>
              <select v-model="form.campus_id" required class="input" :disabled="!!editingDept || !form.university_id" @change="onCampusChange">
                <option value="">Select campus</option>
                <option v-for="item in filteredCampuses" :key="item.id" :value="item.id">{{ item.name }}</option>
              </select>
            </div>
            <div class="sm:col-span-2">
              <label class="block text-xs text-slate-500 mb-2">Faculty</label>
              <select v-model="form.faculty_id" required class="input" :disabled="!!editingDept || !form.campus_id">
                <option value="">Select faculty</option>
                <option v-for="item in filteredFaculties" :key="item.id" :value="item.id">{{ item.name }}</option>
              </select>
            </div>
          </div>
        </div>

        <div class="flex justify-end gap-3 pt-5 border-t border-slate-100">
          <button type="button" class="btn btn-secondary" @click="closeModal">Cancel</button>
          <button type="submit" class="btn btn-primary" :disabled="saving">
            {{ saving ? 'Saving...' : editingDept ? 'Save Changes' : 'Add Department' }}
          </button>
        </div>
      </form>
    </Modal>

    <ConfirmDialog :show="showDelete" title="Delete Department" :message="`Delete '${deletingDept?.name || ''}'? This action may fail if the department is still in use.`" confirmText="Delete Department" variant="danger" @confirm="deleteDept" @cancel="closeDelete" />
  </div>
</template>

<script setup>
import { computed, onMounted, reactive, ref } from 'vue'
import api from '@/services/api'
import { useNotificationStore } from '@/stores/notification'
import ActionMenu from '@/components/ActionMenu.vue'
import ConfirmDialog from '@/components/ConfirmDialog.vue'
import EmptyState from '@/components/EmptyState.vue'
import Modal from '@/components/Modal.vue'

const notif = useNotificationStore()
const departments = ref([])
const faculties = ref([])
const campuses = ref([])
const universities = ref([])
const loading = ref(true)
const saving = ref(false)
const error = ref('')
const searchQuery = ref('')
const showCreate = ref(false)
const editingDept = ref(null)
const showDelete = ref(false)
const deletingDept = ref(null)
const form = reactive({ name: '', code: '', university_id: '', campus_id: '', faculty_id: '' })

const filteredCampuses = computed(() => campuses.value.filter(item => Number(item.university_id) === Number(form.university_id)))
const filteredFaculties = computed(() => faculties.value.filter(item => Number(item.campus_id) === Number(form.campus_id)))
const filteredDepartments = computed(() => {
  const query = searchQuery.value.toLocaleLowerCase()
  return departments.value.filter(item => !query || [item.name, item.code, item.faculty?.name].some(value => value?.toLocaleLowerCase().includes(query)))
})

const rows = response => Array.isArray(response.data) ? response.data : (response.data.data || [])

async function fetchData() {
  loading.value = true
  error.value = ''
  try {
    const [departmentResponse, facultyResponse, campusResponse, universityResponse] = await Promise.all([
      api.get('/departments', { timeout: 15000 }),
      api.get('/faculties', { timeout: 15000 }),
      api.get('/campuses', { timeout: 15000 }),
      api.get('/universities', { timeout: 15000 }),
    ])
    departments.value = rows(departmentResponse)
    faculties.value = rows(facultyResponse)
    campuses.value = rows(campusResponse)
    universities.value = rows(universityResponse)
  } catch (err) {
    error.value = err.response?.data?.message || 'Failed to load departments.'
  } finally {
    loading.value = false
  }
}

function placementLabel(department) {
  const faculty = faculties.value.find(item => Number(item.id) === Number(department.faculty_id))
  const campus = campuses.value.find(item => Number(item.id) === Number(faculty?.campus_id))
  return campus?.name || ''
}

function openCreate() {
  editingDept.value = null
  Object.assign(form, { name: '', code: '', university_id: '', campus_id: '', faculty_id: '' })
  showCreate.value = true
}

function editDept(department) {
  const faculty = faculties.value.find(item => Number(item.id) === Number(department.faculty_id))
  const campus = campuses.value.find(item => Number(item.id) === Number(faculty?.campus_id))
  editingDept.value = department
  Object.assign(form, {
    name: department.name,
    code: department.code,
    university_id: campus?.university_id || '',
    campus_id: faculty?.campus_id || '',
    faculty_id: department.faculty_id || '',
  })
}

function onUniversityChange() {
  form.campus_id = ''
  form.faculty_id = ''
}

function onCampusChange() {
  form.faculty_id = ''
}

function closeModal() {
  showCreate.value = false
  editingDept.value = null
  Object.assign(form, { name: '', code: '', university_id: '', campus_id: '', faculty_id: '' })
}

function confirmDelete(department) {
  deletingDept.value = department
  showDelete.value = true
}

function closeDelete() {
  deletingDept.value = null
  showDelete.value = false
}

async function saveDept() {
  saving.value = true
  try {
    const payload = { name: form.name, code: form.code.toUpperCase() }
    if (editingDept.value) {
      await api.put(`/departments/${editingDept.value.id}`, payload)
      notif.success('Department updated.')
    } else {
      payload.faculty_id = form.faculty_id
      await api.post('/departments', payload)
      notif.success('Department added.')
    }
    closeModal()
    await fetchData()
  } catch (err) {
    const errors = err.response?.data?.errors
    notif.error(errors ? Object.values(errors).flat()[0] : (err.response?.data?.message || 'Failed to save department.'))
  } finally {
    saving.value = false
  }
}

async function deleteDept() {
  try {
    await api.delete(`/departments/${deletingDept.value.id}`)
    notif.success('Department deleted.')
    closeDelete()
    await fetchData()
  } catch (err) {
    notif.error(err.response?.data?.message || 'Failed to delete department.')
  }
}

onMounted(fetchData)
</script>
