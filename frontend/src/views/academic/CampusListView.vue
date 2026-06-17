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

    <!-- Content Wrapper -->
    <div v-if="loading" class="grid grid-cols-1 gap-4">
      <div v-for="i in 3" :key="i" class="card h-24 animate-pulse bg-slate-50/50"></div>
    </div>
    <div v-else-if="campuses.length === 0" class="card">
      <EmptyState icon="📍" title="No campuses found" description="Add university campuses to track your institution's locations." action-label="Add Campus" @action="showCreate = true" />
    </div>

    <div v-else class="grid grid-cols-1 lg:grid-cols-2 gap-5">
      <div v-for="campus in campuses" :key="campus.id" class="card p-8 flex flex-col sm:flex-row sm:items-center justify-between gap-6 card-hover transition-all">
        <div class="flex items-center gap-4">
          <div class="w-12 h-12 rounded-2xl bg-slate-100 text-white flex items-center justify-center font-bold text-xl">
            {{ campus.name.charAt(0) }}
          </div>
          <div>
            <h3 class="font-bold text-slate-800 text-lg leading-tight mb-1">{{ campus.name }}</h3>
            <div class="flex items-center gap-2">
              <span class="inline-block px-2.5 py-0.5 bg-slate-100 text-slate-500 text-xs font-medium rounded-md border border-slate-100">CODE: {{ campus.code }}</span>
              <span class="inline-block px-2 py-0.5 bg-blue-50 text-blue-600 text-xs font-medium rounded-md border border-blue-100"><i class="fas fa-university mr-1"></i>{{ campus.university?.name || 'N/A' }}</span>
            </div>
          </div>
        </div>
        <ActionMenu :actions="[
          { key: 'setup', label: 'Set Up Admin', handler: () => openSetUpAdmin(campus) },
          { key: 'edit', label: 'Edit', handler: () => editCampus(campus) },
          { separator: true },
          { key: 'delete', label: 'Delete', handler: () => confirmDelete(campus) }
        ]" />
      </div>
    </div>

    <!-- Create/Edit Modal -->
    <Modal :show="showCreate || !!editingCampus" :title="editingCampus ? 'Edit Campus' : 'Add New Campus'" @close="closeModal">
      <form @submit.prevent="saveCampus" class="space-y-5 px-1 py-1">
        <div>
          <label class="block text-xs text-slate-500 font-medium  tracking-wider mb-1.5 ml-1">Campus Name *</label>
          <input v-model="form.name" type="text" required class="input" placeholder="e.g., Main Campus" />
        </div>
        <div>
          <label class="block text-xs text-slate-500 font-medium  tracking-wider mb-1.5 ml-1">Campus Code *</label>
          <input v-model="form.code" type="text" required class="input" placeholder="e.g., MC" />
        </div>
        <div>
          <label class="block text-xs text-slate-500 font-medium  tracking-wider mb-1.5 ml-1">Parent University</label>
          <select v-model="form.university_id" class="input">
            <option value="">Select University</option>
            <option v-for="u in universities" :key="u.id" :value="u.id">{{ u.name }}</option>
          </select>
        </div>
        <div class="flex justify-end gap-3 pt-4">
          <button type="button" @click="closeModal" class="btn btn-secondary">Cancel</button>
          <button type="submit" class="btn btn-primary px-5">{{ editingCampus ? 'Update' : 'Add' }}</button>
        </div>
      </form>
    </Modal>

    <ConfirmDialog :show="showDelete" title="Delete Campus" :message="'Are you sure you want to delete \'' + (deletingCampus?.name) + '\'?'" confirmText="Delete" variant="danger" @confirm="deleteCampus" @cancel="showDelete = false" />

    <!-- Set Up Admin Modal -->
    <Modal :show="showSetUpAdmin" title="Assign Campus Admin" @close="showSetUpAdmin = false">
      <form @submit.prevent="saveAdmin" class="space-y-6 p-1">
        <div class="p-4 bg-brand/5 border border-brand/10 rounded-2xl mb-4">
           <p class="text-xs text-brand font-bold tracking-wide mb-1">Target Campus</p>
           <h4 class="text-lg font-bold text-slate-800">{{ targetCampus?.name }}</h4>
        </div>

        <div class="space-y-4">
          <div class="space-y-1.5">
            <label class="block text-xs font-bold text-slate-500 ml-1">Admin Full Name *</label>
            <input v-model="adminForm.name" type="text" required placeholder="e.g. Dr. Jane Smith" class="input h-12 font-medium" />
          </div>
          <div class="space-y-1.5">
            <label class="block text-xs font-bold text-slate-500 ml-1">Admin Email *</label>
            <input v-model="adminForm.email" type="email" required placeholder="admin@university.edu" class="input h-12 font-medium" />
          </div>
          <div class="space-y-1.5">
            <label class="block text-xs font-bold text-slate-500 ml-1">Default Password *</label>
            <input v-model="adminForm.password" type="password" required class="input h-12 font-medium" />
          </div>
        </div>

        <div class="p-4 bg-slate-50 rounded-2xl">
          <p class="text-xs text-slate-500 leading-relaxed">
            This user will be granted the <strong>campus_admin</strong> role. They will have control over research data and faculty hierarchies within this campus.
          </p>
        </div>

        <div class="flex justify-end gap-3 pt-6 border-t border-slate-100">
          <button type="button" @click="showSetUpAdmin = false" class="btn btn-secondary h-11 px-6">Discard</button>
          <button type="submit" class="btn btn-primary h-11 px-6" :disabled="submittingAdmin">
            {{ submittingAdmin ? 'Creating...' : 'Create & Assign Role' }}
          </button>
        </div>
      </form>
    </Modal>
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
import ActionMenu from '@/components/ActionMenu.vue'

const notif = useNotificationStore()
const campuses = ref([]); const loading = ref(true); const universities = ref([])
const showCreate = ref(false); const editingCampus = ref(null); const showDelete = ref(false); const deletingCampus = ref(null)
const form = reactive({ name: '', code: '', university_id: '' })

// Admin setup state
const showSetUpAdmin = ref(false); const targetCampus = ref(null); const submittingAdmin = ref(false)
const adminForm = reactive({ name: '', email: '', password: '' })

async function fetchData() { loading.value = true; try { const cRes = await api.get('/campuses'); const uRes = await api.get('/universities'); campuses.value = cRes.data; universities.value = uRes.data } catch (e) {} finally { loading.value = false } }
function editCampus(c) { editingCampus.value = c; Object.assign(form, { name: c.name, code: c.code, university_id: c.university_id || '' }) }
function closeModal() { showCreate.value = false; editingCampus.value = null; Object.assign(form, { name: '', code: '', university_id: '' }) }
function confirmDelete(c) { deletingCampus.value = c; showDelete.value = true }
async function saveCampus() { try { const payload = { ...form, university_id: form.university_id || null }; if (editingCampus.value) await api.put(`/campuses/${editingCampus.value.id}`, payload); else await api.post('/campuses', payload); notif.success(editingCampus.value ? 'Updated!' : 'Created!'); closeModal(); fetchData() } catch (err) { notif.error('Failed') } }
async function deleteCampus() { try { await api.delete(`/campuses/${deletingCampus.value.id}`); notif.success('Deleted!'); showDelete.value = false; fetchData() } catch (err) { notif.error('Failed') } }

function openSetUpAdmin(campus) {
  targetCampus.value = campus
  showSetUpAdmin.value = true
  Object.assign(adminForm, { name: '', email: '', password: '' })
}

async function saveAdmin() {
  submittingAdmin.value = true
  try {
    let userId = null
    // 1. Create User
    try {
      const res = await api.post('/users', {
        ...adminForm,
        password_confirmation: adminForm.password,
        university_id: targetCampus.value.university_id,
        is_active: true
      })
      userId = res.data.id
    } catch (createErr) {
      const errors = createErr.response?.data?.errors
      if (errors?.email) {
        const { data: usersData } = await api.get('/users', { params: { search: adminForm.email } })
        const existing = (usersData.data || usersData).find(u => u.email === adminForm.email)
        if (!existing) throw new Error('Email conflict, user not found.')
        userId = existing.id
        notif.info(`Reassigning role to existing user ${existing.name}`)
      } else throw createErr
    }

    // 2. Assign Campus (Update user model)
    await api.put(`/users/${userId}`, { 
      campus_id: targetCampus.value.id,
      university_id: targetCampus.value.university_id
    })

    // 3. Assign Role (campus_admin)
    const { data: roles } = await api.get('/roles')
    const campusAdminRole = roles.find(r => r.name === 'campus_admin')
    if (campusAdminRole) {
      await api.post(`/users/${userId}/roles`, { role_id: campusAdminRole.id })
    }

    notif.success(`Campus Admin assigned to ${targetCampus.value.name}`)
    showSetUpAdmin.value = false
  } catch (err) {
    notif.error(err.response?.data?.message || err.message || 'Failed to setup admin')
  } finally {
    submittingAdmin.value = false
  }
}
onMounted(fetchData)
</script>
