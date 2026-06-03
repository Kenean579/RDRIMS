<template>
  <div class="flex flex-col gap-6 card">
    <!-- Header -->
    <div class="section-header">
      <div>
        <h1 class="section-title">System Roles Control</h1>
        <p class="section-subtitle">Define access levels and institutional permissions</p>
      </div>
      <button @click="showCreate = true" class="btn btn-primary">
        <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 4v16m8-8H4" /></svg>
        Create Role
      </button>
    </div>

    <!-- Content -->
    <div v-if="loading" class="card p-5">
      <LoadingSkeleton :rows="4" />
    </div>
    
    <div v-else-if="roles.length === 0" class="card">
      <EmptyState icon="🛡️" title="No roles found" description="Establish system roles to manage granular institutional access controls." action-label="Add Admin Role" action-icon="security" @action="showCreate = true" />
    </div>

    <div v-else class="grid grid-cols-1 lg:grid-cols-2 gap-5">
      <div v-for="role in roles" :key="role.id" class="card p-6 flex flex-col group card-hover relative overflow-hidden border-l-4 border-l-violet-500 hover:border-l-violet-600 transition-all">
        <div class="flex items-start justify-between mb-4">
          <div class="flex-1">
            <h3 class="text-lg font-bold text-slate-800 group-hover:text-violet-700 transition leading-tight">{{ role.name }}</h3>
            <p class="text-[10px] text-slate-400 font-bold capitalize tracking-widest mt-1">Effective Permissions: <span class="text-violet-600">{{ role.permissions?.length || 0 }}</span></p>
          </div>
          <div class="w-12 h-12 rounded-2xl bg-linear-to-br from-violet-500 to-fuchsia-600 text-white flex items-center justify-center font-bold shadow-lg shadow-violet-500/30 shrink-0">
             <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z" /></svg>
          </div>
        </div>
        
        <p class="text-sm text-slate-500 line-clamp-2 leading-relaxed mb-6 flex-1">{{ role.description || 'Access level definition for system operations.' }}</p>

        <div class="mt-auto pt-4 border-t border-slate-100 flex items-center justify-between">
           <button @click="openPermissions(role)" class="btn btn-secondary h-9 px-4 text-[11px] font-bold capitalize tracking-widest text-violet-700 bg-violet-50 hover:bg-violet-100 border-0">
             Manage Perms
           </button>
           <div class="flex gap-2">
             <button @click="editRole(role)" class="btn btn-ghost text-slate-500 hover:text-blue-600" style="padding: 6px 10px; font-size: 11px; font-weight: bold;">Edit</button>
             <button @click="confirmDelete(role)" class="btn btn-ghost text-red-500 hover:bg-red-50" style="padding: 6px 10px; font-size: 11px; font-weight: bold;">Delete</button>
           </div>
        </div>
      </div>
    </div>

    <!-- Create/Edit Modal -->
    <Modal :show="showCreate || !!editingRole" :title="editingRole ? 'Modify System Role' : 'Establish New Role'" @close="closeModal">
      <form @submit.prevent="saveRole" class="space-y-5 px-1 py-1">
        <div>
          <label class="block text-[11px] text-slate-500 font-bold capitalize tracking-wider mb-1.5 ml-1">Role Identifier *</label>
          <input v-model="form.name" type="text" required class="input" placeholder="e.g. Finance Officer, Dean" />
        </div>
        <div>
          <label class="block text-[11px] text-slate-500 font-bold capitalize tracking-wider mb-1.5 ml-1">Scope & Responsibility</label>
          <textarea v-model="form.description" rows="3" class="input resize-none" placeholder="Describe the access scope for this role..."></textarea>
        </div>
        <div class="flex justify-end gap-3 pt-4">
          <button type="button" @click="closeModal" class="btn btn-secondary">Discard</button>
          <button type="submit" class="btn btn-primary px-5">{{ editingRole ? 'Update Role' : 'Create Role' }}</button>
        </div>
      </form>
    </Modal>

    <!-- Permissions Modal -->
    <Modal :show="showPermissions" title="Synchronize Role Permissions" size="lg" @close="showPermissions = false">
      <div class="space-y-4 px-1">
        <p class="text-sm text-slate-500 mb-4 px-1">Select the operational capabilities permitted for the <span class="font-bold text-slate-800">{{ syncingRole?.name }}</span> role.</p>
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 max-h-96 overflow-y-auto pr-2 custom-scrollbar">
          <label v-for="perm in allPermissions" :key="perm.id" 
            class="flex items-start gap-3 p-4 rounded-xl border border-slate-100 hover:border-blue-200 hover:bg-blue-50/50 cursor-pointer transition group relative">
            <div class="pt-0.5">
              <input type="checkbox" :value="perm.id" v-model="selectedPermissions" class="w-4 h-4 rounded border-slate-300 text-blue-600 focus:ring-blue-500 transition cursor-pointer" />
            </div>
            <div>
              <p class="text-sm font-bold text-slate-800 group-hover:text-blue-700 transition">{{ perm.name }}</p>
              <p class="text-[11px] text-slate-400 font-medium leading-relaxed mt-1 group-hover:text-slate-500 transition">{{ perm.description || 'No description available for this capability.' }}</p>
            </div>
          </label>
        </div>
      </div>
      <div class="flex justify-end gap-3 mt-5 pt-6 border-t border-slate-50">
        <button @click="showPermissions = false" class="btn btn-secondary">Cancel</button>
        <button @click="syncPermissions" class="btn btn-primary px-5 shadow-lg shadow-blue-500/20">Apply Permission Set</button>
      </div>
    </Modal>

    <ConfirmDialog :show="showDelete" title="Permanently Delete Role" :message="'Are you sure you want to delete the \'' + (deletingRole?.name) + '\' role? Users assigned to this role will lose their associated permissions.'" confirmText="Delete Role Forever" variant="danger" @confirm="deleteRole" @cancel="showDelete = false" />
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
const roles = ref([]); const loading = ref(true)
const allPermissions = ref([])
const showCreate = ref(false); const editingRole = ref(null); const showDelete = ref(false); const deletingRole = ref(null)
const showPermissions = ref(false); const syncingRole = ref(null); const selectedPermissions = ref([])
const form = reactive({ name: '', description: '' })

async function fetchRoles() {
  loading.value = true
  try { const { data } = await api.get('/roles'); roles.value = data }
  catch (e) {} finally { loading.value = false }
}

function editRole(r) { editingRole.value = r; Object.assign(form, { name: r.name, description: r.description || '' }) }
function closeModal() { showCreate.value = false; editingRole.value = null; Object.assign(form, { name: '', description: '' }) }
function confirmDelete(r) { deletingRole.value = r; showDelete.value = true }

function openPermissions(role) {
  syncingRole.value = role
  selectedPermissions.value = role.permissions?.map(p => p.id) || []
  showPermissions.value = true
}

async function saveRole() {
  try {
    if (editingRole.value) { await api.put(`/roles/${editingRole.value.id}`, form); notif.success('Updated!') }
    else { await api.post('/roles', form); notif.success('Created!') }
    closeModal(); fetchRoles()
  } catch (err) { notif.error(err.response?.data?.message || 'Failed') }
}

async function syncPermissions() {
  try {
    await api.post(`/roles/${syncingRole.value.id}/permissions`, { permissions: selectedPermissions.value })
    notif.success('Permissions synced!'); showPermissions.value = false; fetchRoles()
  } catch (err) { notif.error('Failed') }
}

async function deleteRole() {
  try { await api.delete(`/roles/${deletingRole.value.id}`); notif.success('Deleted!'); showDelete.value = false; fetchRoles() }
  catch (err) { notif.error(err.response?.data?.message || 'Failed') }
}

onMounted(async () => {
  await fetchRoles()
  try { const { data } = await api.get('/permissions'); allPermissions.value = data } catch (e) {}
})
</script>
