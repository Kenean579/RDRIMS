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

    <!-- Content Wrapper -->
    <div v-if="loading" class="grid grid-cols-1 gap-4">
      <div v-for="i in 3" :key="i" class="card h-24 animate-pulse bg-slate-50/50"></div>
    </div>
    
    <div v-else-if="universities.length === 0" class="card">
      <EmptyState icon="🎓" title="No universities found" description="Add universities to manage multiple institutions." action-label="Add First University" action-icon="add" @action="showCreate = true" />
    </div>
    
    <div v-else class="grid grid-cols-1 lg:grid-cols-2 gap-5">
      <div v-for="uni in universities" :key="uni.id" class="card p-8 flex flex-col sm:flex-row sm:items-center justify-between gap-6 card-hover transition-all">
        <div class="flex items-center gap-4">
          <div class="w-12 h-12 rounded-2xl bg-slate-100 flex items-center justify-center overflow-hidden shrink-0 border border-slate-200">
            <img v-if="imageUrl(uni.logo_file)" :src="imageUrl(uni.logo_file)" class="w-full h-full object-contain" />
            <span v-else class="text-slate-400 font-bold text-xl">{{ uni.name.charAt(0) }}</span>
          </div>
          <div>
            <h3 class="font-bold text-slate-800 text-lg leading-tight mb-1">{{ uni.name }}</h3>
            <span class="inline-block px-2.5 py-0.5 bg-slate-100 text-slate-500 text-xs font-medium rounded-md border border-slate-100">CODE: {{ uni.code }}</span>
          </div>
        </div>
        <ActionMenu :actions="[
          { key: 'setup', label: 'Set Up Admin', handler: () => openSetUpAdmin(uni) },
          { key: 'edit', label: 'Edit', handler: () => editUni(uni) },
          { separator: true },
          { key: 'delete', label: 'Delete', handler: () => confirmDelete(uni) }
        ]" />
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
        <div class="space-y-1.5">
          <label class="block text-sm font-semibold text-slate-700">Institution Logo</label>
          <div class="flex items-center gap-4 p-4 bg-slate-50 rounded-2xl border border-slate-100">
            <div class="w-16 h-16 rounded-xl bg-white border border-slate-200 flex items-center justify-center overflow-hidden shrink-0">
               <img v-if="imageUrl(form.logo_file)" :src="imageUrl(form.logo_file)" class="w-full h-full object-contain" />
               <span v-else class="text-xs text-slate-300 font-bold">LOGO</span>
            </div>
            <div class="flex-1">
              <input type="file" accept="image/*" class="hidden" id="uni-logo-input" @change="uploadLogo" />
              <label for="uni-logo-input" class="btn btn-secondary h-9 px-4 text-xs font-bold cursor-pointer">
                 {{ uploadingLogo ? 'Uploading...' : 'Choose Image' }}
              </label>
              <p class="text-[10px] text-slate-400 mt-2">Recommended: PNG/JPG, Square, max 1MB</p>
            </div>
          </div>
        </div>
        <div class="flex justify-end gap-3 pt-4 border-t border-slate-100">
          <button type="button" @click="closeModal" class="btn btn-secondary">Discard</button>
          <button type="submit" class="btn btn-primary" :disabled="uploadingLogo">{{ editingUni ? 'Update' : 'Register' }}</button>
        </div>
      </form>
    </Modal>

    <!-- Set Up Admin Modal -->
    <Modal :show="showSetUpAdmin" title="Assign Research Admin" @close="showSetUpAdmin = false">
      <form @submit.prevent="saveAdmin" class="space-y-6 p-1">
        <div class="p-4 bg-brand/5 border border-brand/10 rounded-2xl mb-4">
           <p class="text-xs text-brand font-bold tracking-wide mb-1">Target University</p>
           <h4 class="text-lg font-bold text-slate-800">{{ targetUni?.name }}</h4>
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
          <div class="p-3 bg-brand/5 border border-brand/10 rounded-xl mb-2 flex items-start gap-2.5">
            <svg class="w-4 h-4 text-brand mt-0.5 shrink-0" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
            <p class="text-xs text-brand leading-relaxed font-semibold">
              No password required. A secure activation email will be sent automatically to the new administrator to set their password.
            </p>
          </div>
        </div>

        <div class="p-4 bg-slate-50 rounded-2xl border border-slate-100 italic">
          <p class="text-[10px] text-slate-500 leading-relaxed">
            This user will be granted the <strong class="text-slate-700">research_admin</strong> role at the university level. They will have full control over institutional research data, including proposals, projects, and department hierarchies.
          </p>
        </div>

        <div class="flex justify-end gap-3 pt-6 border-t border-slate-100">
          <button type="button" @click="showSetUpAdmin = false" class="btn btn-secondary font-bold text-xs">Discard</button>
          <button type="submit" class="btn btn-primary font-bold text-xs" :disabled="submittingAdmin">
             <span v-if="submittingAdmin" class="animate-spin mr-2">◌</span>
             Create & Assign Role
          </button>
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
import ActionMenu from '@/components/ActionMenu.vue'
import { imageUrl } from '@/utils/formatters'

const notif = useNotificationStore()
const universities = ref([]); const loading = ref(true)
const showCreate = ref(false); const editingUni = ref(null); const showDelete = ref(false); const deletingUni = ref(null)
const form = reactive({ name: '', code: '', logo_file_id: null, logo_file: null })
const uploadingLogo = ref(false)

const showSetUpAdmin = ref(false); const targetUni = ref(null); const submittingAdmin = ref(false)
const adminForm = reactive({ name: '', email: '' })

async function fetchUniversities() {
  loading.value = true
  try { 
    const { data } = await api.get('/universities')
    universities.value = (data.data || data).map(u => ({
      ...u,
      logo_file: u.logo_file || u.logoFile
    }))
  }
  catch (e) {} finally { loading.value = false }
}

async function uploadLogo(event) {
  const file = event.target.files?.[0]
  if (!file) return
  uploadingLogo.value = true
  try {
    const fd = new FormData()
    fd.append('file', file)
    fd.append('is_public', '1')
    const { data } = await api.post('/files', fd)
    form.logo_file_id = data.id
    form.logo_file = data
    notif.success('Logo uploaded')
  } catch (e) {
    notif.error('Logo upload failed')
  } finally {
    uploadingLogo.value = false
  }
}

function openSetUpAdmin(uni) {
  targetUni.value = uni
  showSetUpAdmin.value = true
  Object.assign(adminForm, { name: '', email: '' })
}

async function saveAdmin() {
  submittingAdmin.value = true
  try {
    let userId = null

    // Try to create the user; if email exists, find them instead
    try {
      const res = await api.post('/users', {
        name: adminForm.name,
        email: adminForm.email,
        university_id: targetUni.value.id,
        department_id: null,
        is_active: true
      })
      userId = res.data.id
    } catch (createErr) {
      const errors = createErr.response?.data?.errors
      if (errors?.email) {
        // Email already exists — find the existing user and reassign their university + role
        const { data: usersData } = await api.get('/users', { params: { search: adminForm.email } })
        const existing = (usersData.data || usersData).find(u => u.email === adminForm.email)
        if (!existing) throw new Error('Could not find existing user with that email.')
        userId = existing.id
        // Update university link if needed
        await api.put(`/users/${userId}`, { university_id: targetUni.value.id })
        notif.info(`User already exists – reassigning role to ${existing.name}`)
      } else {
        throw createErr
      }
    }

    // Fetch roles from global endpoint
    const { data: rolesData } = await api.get('/roles')
    // Handle both wrapped and unwrapped responses
    const roles = Array.isArray(rolesData) ? rolesData : (rolesData.data || [])
    const researchAdminRole = roles.find(r => r.name === 'research_admin')

    if (researchAdminRole && userId) {
      await api.post(`/users/${userId}/roles`, { role_id: researchAdminRole.id })
    }

    notif.success(`Research Admin assigned for ${targetUni.value.name}`)
    showSetUpAdmin.value = false
  } catch (err) {
    notif.error(err.response?.data?.message || err.message || 'Failed to setup admin')
  } finally {
    submittingAdmin.value = false
  }
}

function editUni(u) { 
  editingUni.value = u; 
  Object.assign(form, { 
    name: u.name, 
    code: u.code, 
    logo_file_id: u.logo_file_id || null, 
    logo_file: u.logo_file || null 
  }) 
}
function closeModal() { 
  showCreate.value = false; 
  editingUni.value = null; 
  Object.assign(form, { name: '', code: '', logo_file_id: null, logo_file: null }) 
}
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