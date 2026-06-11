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
          <div class="w-12 h-12 rounded-2xl bg-slate-100 text-white flex items-center justify-center font-bold text-xl">
            {{ uni.name.charAt(0) }}
          </div>
          <div>
            <h3 class="font-bold text-slate-800 text-lg leading-tight mb-1">{{ uni.name }}</h3>
            <span class="inline-block px-2.5 py-0.5 bg-slate-100 text-slate-500 text-xs font-medium rounded-md border border-slate-100">CODE: {{ uni.code }}</span>
          </div>
        </div>
        <div class="flex gap-2 shrink-0">
          <button @click="openSetUpAdmin(uni)" class="btn btn-ghost border border-brand/20 text-brand hover:bg-brand hover:text-white h-9 px-4 text-xs font-bold">
            Set Up Admin
          </button>
          <button @click="editUni(uni)" class="btn btn-secondary h-9 px-4 text-xs font-medium">
            Edit
          </button>
          <button @click="confirmDelete(uni)" class="btn btn-danger h-9 px-4 text-xs font-medium">
            Delete
          </button>
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

    <!-- Set Up Admin Modal -->
    <Modal :show="showSetUpAdmin" title="Assign Research Admin" @close="showSetUpAdmin = false">
      <form @submit.prevent="saveAdmin" class="space-y-6 p-1">
        <div class="p-4 bg-brand/5 border border-brand/10 rounded-2xl mb-4">
           <p class="text-xs text-brand font-bold uppercase tracking-widest mb-1">Target University</p>
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
          <div class="space-y-1.5">
            <label class="block text-xs font-bold text-slate-500 ml-1">Default Password *</label>
            <input v-model="adminForm.password" type="password" required minlength="8" placeholder="••••••••" class="input h-12 font-bold" />
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

const notif = useNotificationStore()
const universities = ref([]); const loading = ref(true)
const showCreate = ref(false); const editingUni = ref(null); const showDelete = ref(false); const deletingUni = ref(null)
const form = reactive({ name: '', code: '' })

const showSetUpAdmin = ref(false); const targetUni = ref(null); const submittingAdmin = ref(false)
const adminForm = reactive({ name: '', email: '', password: '' })

async function fetchUniversities() {
  loading.value = true
  try { const { data } = await api.get('/universities'); universities.value = Array.isArray(data) ? data : data.data }
  catch (e) {} finally { loading.value = false }
}

function openSetUpAdmin(uni) {
  targetUni.value = uni
  showSetUpAdmin.value = true
  Object.assign(adminForm, { name: '', email: '', password: '' })
}

async function saveAdmin() {
  submittingAdmin.value = true
  try {
    // 1. Create User
    const res = await api.post('/users', {
      ...adminForm,
      university_id: targetUni.value.id,
      department_id: null
    })
    
    // 2. Fetch the research_admin role ID
    const { data: roles } = await api.get('/roles')
    const researchAdminRole = roles.find(r => r.name === 'research_admin')
    
    if (researchAdminRole) {
      // 3. Assign Role
      await api.post(`/users/${res.data.id}/roles`, {
        role_id: researchAdminRole.id
      })
    }
    
    notif.success(`Research Admin created for ${targetUni.value.name}`)
    showSetUpAdmin.value = false
  } catch (err) {
    notif.error(err.response?.data?.message || 'Failed to setup admin')
  } finally {
    submittingAdmin.value = false
  }
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
