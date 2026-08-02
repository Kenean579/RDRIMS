<template>
  <div class="flex flex-col gap-5 pb-6 animate-fade">
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
      <div>
        <h1 class="text-xl font-bold text-slate-900">Campuses</h1>
        <p class="text-sm text-slate-500 mt-1">Manage university campuses and locations.</p>
      </div>
      <button type="button" class="btn btn-primary h-11 px-5" @click="openCreate">
        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 4v16m8-8H4" /></svg>
        Add Campus
      </button>
    </div>

    <div class="grid grid-cols-1 sm:grid-cols-[180px_1fr] gap-4">
      <div class="card p-5">
        <p class="text-xs font-semibold text-slate-500">Total campuses</p>
        <p class="text-3xl font-bold text-slate-900 mt-1">{{ campuses.length }}</p>
      </div>
      <div class="card p-5 flex items-end">
        <div class="w-full">
          <label for="campus-search" class="block text-xs font-semibold text-slate-500 mb-2">Search</label>
          <div class="relative">
            <svg class="absolute left-3.5 top-1/2 -translate-y-1/2 w-4 h-4 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><circle cx="11" cy="11" r="8" stroke-width="2"/><path d="m21 21-4.35-4.35" stroke-width="2"/></svg>
            <input id="campus-search" v-model.trim="searchQuery" class="input pl-10" placeholder="Search by campus, code, or university..." />
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
    <EmptyState v-else-if="filteredCampuses.length === 0" icon="building" :title="searchQuery ? 'No matching campuses' : 'No campuses found'" :description="searchQuery ? 'Try another campus, code, or university.' : 'Add a campus to organize university locations.'" :action-label="searchQuery ? '' : 'Add First Campus'" @action="openCreate" />

    <div v-else class="grid grid-cols-1 md:grid-cols-2 gap-4">
      <div v-for="campus in filteredCampuses" :key="campus.id" class="card p-5 flex items-start gap-4 hover:border-brand/30 transition-colors">
        <div class="w-11 h-11 rounded-xl bg-brand/10 text-brand flex items-center justify-center font-bold shrink-0">{{ campus.name.charAt(0).toUpperCase() }}</div>
        <div class="flex-1 min-w-0">
          <div class="flex items-start justify-between gap-3">
            <div class="min-w-0">
              <h2 class="text-sm font-bold text-slate-900 truncate" :title="campus.name">{{ campus.name }}</h2>
              <p class="text-xs text-slate-500 mt-1 truncate">{{ campus.university?.name || 'University not assigned' }}</p>
            </div>
            <ActionMenu :actions="[
              { key: 'setup', label: 'Assign Campus Admin', handler: () => openSetUpAdmin(campus) },
              { key: 'edit', label: 'Edit', handler: () => editCampus(campus) },
              { separator: true },
              { key: 'delete', label: 'Delete', handler: () => confirmDelete(campus) }
            ]" />
          </div>
          <span class="inline-block mt-3 px-2.5 py-1 rounded-md bg-slate-100 text-slate-600 text-xs font-semibold">{{ campus.code }}</span>
        </div>
      </div>
    </div>

    <Modal :show="showCreate || !!editingCampus" :title="editingCampus ? 'Edit Campus' : 'Add Campus'" size="md" @close="closeModal">
      <form class="space-y-5" @submit.prevent="saveCampus">
        <div>
          <label class="block text-xs font-semibold text-slate-600 mb-2">Campus name *</label>
          <input v-model.trim="form.name" required maxlength="255" class="input" placeholder="e.g. Main Campus" />
        </div>
        <div>
          <label class="block text-xs font-semibold text-slate-600 mb-2">Campus code *</label>
          <input v-model.trim="form.code" required maxlength="50" class="input uppercase" placeholder="e.g. MC" />
        </div>
        <div>
          <div class="flex items-center justify-between mb-2">
            <label class="block text-xs font-semibold text-slate-600">University *</label>
            <span v-if="editingCampus" class="text-xs text-slate-400">Cannot be changed after creation</span>
          </div>
          <select v-model="form.university_id" required class="input" :disabled="!!editingCampus">
            <option value="">Select university</option>
            <option v-for="item in universities" :key="item.id" :value="item.id">{{ item.name }}</option>
          </select>
        </div>
        <div class="flex justify-end gap-3 pt-5 border-t border-slate-100">
          <button type="button" class="btn btn-secondary" @click="closeModal">Cancel</button>
          <button type="submit" class="btn btn-primary" :disabled="saving">{{ saving ? 'Saving...' : editingCampus ? 'Save Changes' : 'Add Campus' }}</button>
        </div>
      </form>
    </Modal>

    <ConfirmDialog :show="showDelete" title="Delete Campus" :message="`Delete '${deletingCampus?.name || ''}'? This may fail if the campus is still in use.`" confirmText="Delete Campus" variant="danger" @confirm="deleteCampus" @cancel="closeDelete" />

    <Modal :show="showSetUpAdmin" title="Assign Campus Admin" size="md" @close="closeAdminModal">
      <form class="space-y-5" @submit.prevent="saveAdmin">
        <div class="p-4 rounded-xl bg-blue-50 border border-blue-100">
          <p class="text-xs text-blue-600 font-semibold">Campus</p>
          <p class="text-sm text-slate-900 font-bold mt-1">{{ targetCampus?.name }}</p>
        </div>
        <div>
          <label class="block text-xs font-semibold text-slate-600 mb-2">Administrator name *</label>
          <input v-model.trim="adminForm.name" required maxlength="255" class="input" placeholder="e.g. Dr. Jane Smith" />
        </div>
        <div>
          <label class="block text-xs font-semibold text-slate-600 mb-2">Email address *</label>
          <input v-model.trim="adminForm.email" required type="email" class="input" placeholder="admin@university.edu" />
        </div>
        <p class="text-xs text-slate-500">A new user receives an activation email. If the email already exists, the campus-admin role is assigned to that user.</p>
        <div class="flex justify-end gap-3 pt-5 border-t border-slate-100">
          <button type="button" class="btn btn-secondary" @click="closeAdminModal">Cancel</button>
          <button type="submit" class="btn btn-primary" :disabled="submittingAdmin">{{ submittingAdmin ? 'Assigning...' : 'Assign Admin' }}</button>
        </div>
      </form>
    </Modal>
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
const campuses = ref([])
const universities = ref([])
const loading = ref(true)
const saving = ref(false)
const error = ref('')
const searchQuery = ref('')
const showCreate = ref(false)
const editingCampus = ref(null)
const showDelete = ref(false)
const deletingCampus = ref(null)
const form = reactive({ name: '', code: '', university_id: '' })
const showSetUpAdmin = ref(false)
const targetCampus = ref(null)
const submittingAdmin = ref(false)
const adminForm = reactive({ name: '', email: '' })

const filteredCampuses = computed(() => {
  const query = searchQuery.value.toLocaleLowerCase()
  return campuses.value.filter(item => !query || [item.name, item.code, item.university?.name].some(value => value?.toLocaleLowerCase().includes(query)))
})
const rows = response => Array.isArray(response.data) ? response.data : (response.data.data || [])

async function fetchData() {
  loading.value = true
  error.value = ''
  try {
    const [campusResponse, universityResponse] = await Promise.all([
      api.get('/campuses', { timeout: 15000 }),
      api.get('/universities', { timeout: 15000 }),
    ])
    campuses.value = rows(campusResponse)
    universities.value = rows(universityResponse)
  } catch (err) {
    error.value = err.response?.data?.message || 'Failed to load campuses.'
  } finally {
    loading.value = false
  }
}

function openCreate() { editingCampus.value = null; Object.assign(form, { name: '', code: '', university_id: '' }); showCreate.value = true }
function editCampus(campus) { editingCampus.value = campus; Object.assign(form, { name: campus.name, code: campus.code, university_id: campus.university_id || '' }) }
function closeModal() { showCreate.value = false; editingCampus.value = null; Object.assign(form, { name: '', code: '', university_id: '' }) }
function confirmDelete(campus) { deletingCampus.value = campus; showDelete.value = true }
function closeDelete() { deletingCampus.value = null; showDelete.value = false }

async function saveCampus() {
  saving.value = true
  try {
    const payload = { name: form.name, code: form.code.toUpperCase() }
    if (editingCampus.value) {
      await api.put(`/campuses/${editingCampus.value.id}`, payload)
      notif.success('Campus updated.')
    } else {
      payload.university_id = form.university_id
      await api.post('/campuses', payload)
      notif.success('Campus added.')
    }
    closeModal()
    await fetchData()
  } catch (err) {
    const errors = err.response?.data?.errors
    notif.error(errors ? Object.values(errors).flat()[0] : (err.response?.data?.message || 'Failed to save campus.'))
  } finally {
    saving.value = false
  }
}

async function deleteCampus() {
  try {
    await api.delete(`/campuses/${deletingCampus.value.id}`)
    notif.success('Campus deleted.')
    closeDelete()
    await fetchData()
  } catch (err) {
    notif.error(err.response?.data?.message || 'Failed to delete campus.')
  }
}

function openSetUpAdmin(campus) { targetCampus.value = campus; Object.assign(adminForm, { name: '', email: '' }); showSetUpAdmin.value = true }
function closeAdminModal() { showSetUpAdmin.value = false; targetCampus.value = null; Object.assign(adminForm, { name: '', email: '' }) }

async function saveAdmin() {
  submittingAdmin.value = true
  try {
    let userId
    try {
      const { data } = await api.post('/users', { name: adminForm.name, email: adminForm.email, university_id: targetCampus.value.university_id, roles: [] })
      userId = data.id
    } catch (createError) {
      if (!createError.response?.data?.errors?.email) throw createError
      const { data } = await api.get('/users', { params: { search: adminForm.email } })
      const existing = (data.data || data).find(user => user.email.toLocaleLowerCase() === adminForm.email.toLocaleLowerCase())
      if (!existing) throw createError
      userId = existing.id
    }
    const { data: roleData } = await api.get('/roles')
    const roles = Array.isArray(roleData) ? roleData : (roleData.data || [])
    const role = roles.find(item => item.name === 'campus_admin')
    if (!role) throw new Error('Campus admin role is not configured.')
    await api.post(`/users/${userId}/roles`, { role_id: role.id, campus_id: targetCampus.value.id })
    notif.success(`Campus admin assigned to ${targetCampus.value.name}.`)
    closeAdminModal()
  } catch (err) {
    notif.error(err.response?.data?.message || err.message || 'Failed to assign campus admin.')
  } finally {
    submittingAdmin.value = false
  }
}

onMounted(fetchData)
</script>
