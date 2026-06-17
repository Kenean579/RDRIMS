<template>
  <div class="flex flex-col gap-6 animate-fade">
    <!-- Header -->
    <div class="section-header">
      <div>
        <h1 class="section-title">System Roles Control</h1>
        <p class="section-subtitle">Define access levels and institutional permissions</p>
      </div>
      <button @click="showCreate = true" class="btn btn-primary" aria-label="Create a new system role">
        <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 4v16m8-8H4" /></svg>
        Create Role
      </button>
    </div>

    <!-- Loading State -->
    <div v-if="loading" class="grid grid-cols-1 md:grid-cols-2 gap-5">
      <div v-for="i in 4" :key="i" class="bg-white border border-slate-200 rounded-2xl p-6 animate-pulse">
        <div class="flex items-start justify-between mb-4">
          <div class="flex items-center gap-3">
            <div class="w-11 h-11 rounded-xl bg-slate-100"></div>
            <div>
              <div class="h-4 w-32 bg-slate-100 rounded-lg mb-2"></div>
              <div class="h-3 w-20 bg-slate-50 rounded-lg"></div>
            </div>
          </div>
          <div class="w-8 h-8 bg-slate-50 rounded-lg"></div>
        </div>
        <div class="h-3 w-full bg-slate-50 rounded-lg mb-2"></div>
        <div class="h-3 w-2/3 bg-slate-50 rounded-lg"></div>
      </div>
    </div>

    <!-- Empty State -->
    <div v-else-if="roles.length === 0" class="bg-white border border-slate-200 rounded-2xl">
      <EmptyState icon="🛡️" title="No roles found" description="Establish system roles to manage granular institutional access controls." action-label="Add Admin Role" action-icon="security" @action="showCreate = true" />
    </div>

    <!-- Role Cards Grid -->
    <div v-else class="grid grid-cols-1 md:grid-cols-2 gap-5">
      <div
        v-for="role in roles"
        :key="role.id"
        class="bg-white border border-slate-200 rounded-2xl p-6 group hover:border-blue-200 hover:shadow-lg transition-all duration-300 relative"
        tabindex="0"
        :aria-label="'Role: ' + role.name"
      >
        <!-- Card Header -->
        <div class="flex items-start justify-between mb-3">
          <div class="flex items-center gap-3">
            <div class="w-11 h-11 rounded-xl bg-blue-50 text-blue-600 flex items-center justify-center shrink-0">
              <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z" /></svg>
            </div>
            <div>
              <h3 class="text-lg font-semibold text-slate-900 leading-tight">{{ role.name }}</h3>
              <p class="text-sm text-slate-500 mt-0.5">
                Effective Permissions: <span class="font-medium text-blue-600">{{ role.permissions?.length || 0 }}</span>
              </p>
            </div>
          </div>

          <!-- Three Dot Menu -->
          <ActionMenu
            :actions="getRoleActions(role)"
            align="right"
            size="sm"
          />
        </div>

        <!-- Description -->
        <p class="text-sm text-slate-500 line-clamp-2 leading-relaxed mb-4">{{ role.description || 'Access level definition for system operations.' }}</p>

        <!-- Permissions Badge -->
        <div class="flex items-center gap-2">
          <span class="bg-blue-50 text-blue-700 rounded-full px-3 py-1 text-xs font-medium">
            {{ role.permissions?.length || 0 }} permissions
          </span>
        </div>
      </div>
    </div>

    <!-- Create/Edit Modal -->
    <Modal :show="showCreate || !!editingRole" :title="editingRole ? 'Modify System Role' : 'Establish New Role'" @close="closeModal">
      <form @submit.prevent="saveRole" class="space-y-5 px-1 py-1">
        <div>
          <label class="block text-xs font-medium text-slate-500 tracking-wider mb-1.5 ml-1" for="role-name-input">Role Identifier *</label>
          <input id="role-name-input" v-model="form.name" type="text" required class="input" placeholder="e.g. Finance Officer, Dean" aria-required="true" />
        </div>
        <div>
          <label class="block text-xs font-medium text-slate-500 tracking-wider mb-1.5 ml-1" for="role-desc-input">Scope & Responsibility</label>
          <textarea id="role-desc-input" v-model="form.description" rows="3" class="input resize-none" placeholder="Describe the access scope for this role..."></textarea>
        </div>
        <div class="flex justify-end gap-3 pt-4 border-t border-slate-200">
          <button type="button" @click="closeModal" class="bg-slate-100 hover:bg-slate-200 text-slate-700 px-5 py-2.5 rounded-xl text-sm font-medium transition-colors duration-200">Discard</button>
          <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white px-5 py-2.5 rounded-xl text-sm font-medium transition-colors duration-200 shadow-sm">{{ editingRole ? 'Update Role' : 'Create Role' }}</button>
        </div>
      </form>
    </Modal>

    <!-- Permissions Modal -->
    <Modal :show="showPermissions" title="Synchronize Role Permissions" size="lg" @close="showPermissions = false">
      <div class="space-y-4 px-1">
        <p class="text-sm text-slate-500 mb-4 px-1">Select the operational capabilities permitted for the <span class="font-semibold text-slate-900">{{ syncingRole?.name }}</span> role.</p>
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 max-h-96 overflow-y-auto pr-2 custom-scrollbar">
          <label
            v-for="perm in allPermissions"
            :key="perm.id"
            class="flex items-start gap-3 p-4 rounded-xl border cursor-pointer transition-all duration-200 group relative"
            :class="selectedPermissions.includes(perm.id)
              ? 'border-blue-500 bg-blue-50'
              : 'border-slate-200 hover:border-blue-200 hover:bg-blue-50/50'"
            tabindex="0"
            role="checkbox"
            :aria-checked="selectedPermissions.includes(perm.id)"
            :aria-label="'Permission: ' + perm.name"
          >
            <div class="pt-0.5">
              <input type="checkbox" :value="perm.id" v-model="selectedPermissions" class="w-4 h-4 rounded border-slate-300 text-blue-600 focus:ring-blue-500 focus:ring-offset-0 transition cursor-pointer accent-blue-600" />
            </div>
            <div>
              <p class="text-sm font-medium text-slate-900 group-hover:text-blue-700 transition-colors duration-200">{{ perm.name }}</p>
              <p class="text-xs text-slate-500 leading-relaxed mt-1 group-hover:text-slate-600 transition-colors duration-200">{{ perm.description || 'No description available for this capability.' }}</p>
            </div>
          </label>
        </div>
      </div>
      <div class="flex justify-end gap-3 mt-5 pt-6 border-t border-slate-200">
        <button @click="showPermissions = false" class="bg-slate-100 hover:bg-slate-200 text-slate-700 px-5 py-2.5 rounded-xl text-sm font-medium transition-colors duration-200">Cancel</button>
        <button @click="syncPermissions" class="bg-blue-600 hover:bg-blue-700 text-white px-5 py-2.5 rounded-xl text-sm font-medium transition-colors duration-200 shadow-sm">Apply Permission Set</button>
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
import ActionMenu from '@/components/ActionMenu.vue'

const notif = useNotificationStore()
const roles = ref([]); const loading = ref(true)
const allPermissions = ref([])
const showCreate = ref(false); const editingRole = ref(null); const showDelete = ref(false); const deletingRole = ref(null)
const showPermissions = ref(false); const syncingRole = ref(null); const selectedPermissions = ref([])
const form = reactive({ name: '', description: '' })

function getRoleActions(role) {
  return [
    { label: 'Edit Role', key: 'edit', handler: () => editRole(role) },
    { label: 'Manage Permissions', key: 'permissions', handler: () => openPermissions(role) },
    { label: 'View Details', key: 'view', handler: () => openPermissions(role) },
    { separator: true },
    { label: 'Delete Role', key: 'delete', handler: () => confirmDelete(role) }
  ]
}

async function fetchRoles() {
  loading.value = true
  try { const { data } = await api.get('/admin/roles'); roles.value = data }
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
    if (editingRole.value) { await api.put(`/admin/roles/${editingRole.value.id}`, form); notif.success('Updated!') }
    else { await api.post('/admin/roles', form); notif.success('Created!') }
    closeModal(); fetchRoles()
  } catch (err) { notif.error(err.response?.data?.message || 'Failed') }
}

async function syncPermissions() {
  try {
    await api.post(`/admin/roles/${syncingRole.value.id}/permissions`, { permissions: selectedPermissions.value })
    notif.success('Permissions synced!'); showPermissions.value = false; fetchRoles()
  } catch (err) { notif.error('Failed') }
}

async function deleteRole() {
  try { await api.delete(`/admin/roles/${deletingRole.value.id}`); notif.success('Deleted!'); showDelete.value = false; fetchRoles() }
  catch (err) { notif.error(err.response?.data?.message || 'Failed') }
}

onMounted(async () => {
  await fetchRoles()
  try { const { data } = await api.get('/admin/permissions'); allPermissions.value = data } catch (e) {}
})
</script>
