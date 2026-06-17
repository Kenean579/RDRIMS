<template>
  <div class="flex flex-col gap-6 animate-fade">
    <!-- Header -->
    <div class="section-header">
      <div>
        <h1 class="section-title">System Permissions</h1>
        <p class="section-subtitle">Define granular access controls for platform features</p>
      </div>
      <button @click="showCreate = true" class="btn btn-primary" aria-label="Create a new system permission">
        <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 4v16m8-8H4" /></svg>
        Create Permission
      </button>
    </div>

    <!-- Loading State -->
    <div v-if="loading" class="grid grid-cols-1 md:grid-cols-2 gap-5">
      <div v-for="i in 4" :key="i" class="bg-white border border-slate-200 rounded-2xl p-6 animate-pulse">
        <div class="flex items-start justify-between mb-4">
          <div class="flex-1">
            <div class="h-4 w-40 bg-slate-100 rounded-lg mb-2"></div>
            <div class="h-3 w-24 bg-slate-50 rounded-lg"></div>
          </div>
          <div class="w-11 h-11 bg-slate-100 rounded-xl"></div>
        </div>
        <div class="h-3 w-full bg-slate-50 rounded-lg mb-2"></div>
        <div class="h-3 w-2/3 bg-slate-50 rounded-lg"></div>
      </div>
    </div>

    <!-- Empty State -->
    <div v-else-if="permissions.length === 0" class="bg-white border border-slate-200 rounded-2xl">
      <EmptyState icon="🔐" title="No permissions defined" description="Create permissions to control access to system features." action-label="Create Permission" @action="showCreate = true" />
    </div>

    <!-- Permission Cards Grid -->
    <div v-else class="grid grid-cols-1 md:grid-cols-2 gap-5">
      <div
        v-for="perm in permissions"
        :key="perm.id"
        class="bg-white border border-slate-200 rounded-2xl p-6 group hover:border-blue-200 hover:shadow-lg transition-all duration-300 relative"
        tabindex="0"
        :aria-label="'Permission: ' + perm.name"
      >
        <!-- Card Header -->
        <div class="flex items-start justify-between mb-4">
          <div class="flex-1 pr-4 min-w-0">
            <h3 class="text-base font-semibold font-mono text-slate-900 group-hover:text-blue-700 transition-colors duration-200 leading-tight break-all">{{ perm.name }}</h3>
            <div class="flex flex-wrap gap-1.5 mt-2">
              <span v-for="role in perm.roles" :key="role.id" class="inline-block px-2.5 py-0.5 bg-blue-50 text-blue-700 text-xs font-medium rounded-full border border-blue-100">{{ role.name }}</span>
              <span v-if="!perm.roles?.length" class="inline-block px-2.5 py-0.5 bg-slate-100 text-slate-500 text-xs font-medium rounded-full border border-slate-200">Unassigned</span>
            </div>
          </div>
          <div class="w-11 h-11 rounded-xl bg-blue-50 text-blue-600 flex items-center justify-center shrink-0">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 7a2 2 0 012 2m4 0a6 6 0 01-7.743 5.743L11 17H9v2H7v2H4a1 1 0 01-1-1v-2.586a1 1 0 01.293-.707l5.964-5.964A6 6 0 1121 9z" /></svg>
          </div>
        </div>
        
        <!-- Description -->
        <p class="text-sm text-slate-500 line-clamp-2 leading-relaxed mb-5">{{ perm.description || 'No description available.' }}</p>

        <!-- Footer with menu -->
        <div class="flex items-center justify-between pt-4 border-t border-slate-200">
          <span class="bg-blue-50 text-blue-700 rounded-full px-3 py-1 text-xs font-medium">
            {{ perm.roles?.length || 0 }} roles
          </span>
          <ActionMenu
            :actions="getPermActions(perm)"
            align="right"
            size="sm"
          />
        </div>
      </div>
    </div>

    <!-- Create/Edit Modal -->
    <Modal :show="showCreate || !!editingPerm" :title="editingPerm ? 'Modify Permission' : 'Create New Permission'" @close="closeModal">
      <form @submit.prevent="savePermission" class="space-y-5 px-1 py-1">
        <div>
          <label class="block text-xs font-medium text-slate-500 tracking-wider mb-1.5 ml-1" for="perm-name-input">Permission Key *</label>
          <input id="perm-name-input" v-model="form.name" type="text" required class="input" placeholder="e.g., submit_proposals" aria-required="true" />
          <p class="text-xs text-slate-400 mt-1.5 ml-1">Use lowercase with underscores. Must be unique.</p>
        </div>
        <div>
          <label class="block text-xs font-medium text-slate-500 tracking-wider mb-1.5 ml-1" for="perm-desc-input">Description</label>
          <textarea id="perm-desc-input" v-model="form.description" rows="2" class="input resize-none" placeholder="What does this permission allow?"></textarea>
        </div>
        <div class="flex justify-end gap-3 pt-4 border-t border-slate-200">
          <button type="button" @click="closeModal" class="bg-slate-100 hover:bg-slate-200 text-slate-700 px-5 py-2.5 rounded-xl text-sm font-medium transition-colors duration-200">Cancel</button>
          <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white px-5 py-2.5 rounded-xl text-sm font-medium transition-colors duration-200 shadow-sm">{{ editingPerm ? 'Update' : 'Create' }}</button>
        </div>
      </form>
    </Modal>

    <ConfirmDialog :show="showDelete" title="Delete Permission" :message="'Are you sure you want to delete the permission \'' + (deletingPerm?.name) + '\'? This will remove it from all roles.'" confirmText="Delete" variant="danger" @confirm="deletePermission" @cancel="showDelete = false" />
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
const permissions = ref([]); const loading = ref(true)
const showCreate = ref(false); const editingPerm = ref(null)
const showDelete = ref(false); const deletingPerm = ref(null)
const form = reactive({ name: '', description: '' })

function getPermActions(perm) {
  return [
    { label: 'Edit Permission', key: 'edit', handler: () => editPermission(perm) },
    { separator: true },
    { label: 'Delete Permission', key: 'delete', handler: () => confirmDelete(perm) }
  ]
}

async function fetchPermissions() {
  loading.value = true
  try { const { data } = await api.get('/admin/permissions'); permissions.value = data }
  catch (e) { notif.error('Failed to load permissions') }
  finally { loading.value = false }
}

function editPermission(perm) { editingPerm.value = perm; Object.assign(form, { name: perm.name, description: perm.description || '' }) }
function closeModal() { showCreate.value = false; editingPerm.value = null; Object.assign(form, { name: '', description: '' }) }
function confirmDelete(perm) { deletingPerm.value = perm; showDelete.value = true }

async function savePermission() {
  try {
    if (editingPerm.value) { await api.put(`/admin/permissions/${editingPerm.value.id}`, form); notif.success('Updated!') }
    else { await api.post('/admin/permissions', form); notif.success('Created!') }
    closeModal(); fetchPermissions()
  } catch (err) { notif.error(err.response?.data?.message || 'Failed') }
}

async function deletePermission() {
  try { await api.delete(`/admin/permissions/${deletingPerm.value.id}`); notif.success('Deleted!'); showDelete.value = false; fetchPermissions() }
  catch (err) { notif.error(err.response?.data?.message || 'Failed') }
}

onMounted(fetchPermissions)
</script>
