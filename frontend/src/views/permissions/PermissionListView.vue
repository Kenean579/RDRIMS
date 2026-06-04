<template>
  <div class="flex flex-col gap-6 card">
    <!-- Header -->
    <div class="section-header">
      <div>
        <h1 class="section-title">System Permissions</h1>
        <p class="section-subtitle">Define granular access controls for platform features</p>
      </div>
      <button @click="showCreate = true" class="btn btn-primary">
        <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 4v16m8-8H4" /></svg>
        Create Permission
      </button>
    </div>

    <!-- Content Wrapper -->
    <div v-if="loading" class="grid grid-cols-1 gap-4">
      <div v-for="i in 3" :key="i" class="card h-24 animate-pulse bg-slate-50/50"></div>
    </div>
    <div v-else-if="permissions.length === 0" class="card">
      <EmptyState icon="🔐" title="No permissions defined" description="Create permissions to control access to system features." action-label="Create Permission" @action="showCreate = true" />
    </div>

    <div v-else class="grid grid-cols-1 lg:grid-cols-2 gap-5">
      <div v-for="perm in permissions" :key="perm.id" class="card p-8 flex flex-col group card-hover relative overflow-hidden transition-all">
        <div class="flex items-start justify-between mb-4">
          <div class="flex-1 pr-4">
            <h3 class="text-base font-bold font-mono text-slate-800 group-hover:text-cyan-700 transition leading-tight break-all">{{ perm.name }}</h3>
            <div class="flex flex-wrap gap-1.5 mt-2">
              <span v-for="role in perm.roles" :key="role.id" class="inline-block px-2 py-0.5 bg-blue-50 text-blue-600 text-[10px] font-medium rounded-md border border-blue-100">{{ role.name }}</span>
              <span v-if="!perm.roles?.length" class="inline-block px-2 py-0.5 bg-slate-100 text-slate-500 text-[10px] font-medium rounded-md border border-slate-100">Unassigned</span>
            </div>
          </div>
          <div class="w-12 h-12 rounded-2xl bg-slate-100 text-white flex items-center justify-center font-bold shrink-0">
             <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M15 7a2 2 0 012 2m4 0a6 6 0 01-7.743 5.743L11 17H9v2H7v2H4a1 1 0 01-1-1v-2.586a1 1 0 01.293-.707l5.964-5.964A6 6 0 1121 9z" /></svg>
          </div>
        </div>
        
        <p class="text-sm text-slate-500 line-clamp-2 leading-relaxed mb-6 flex-1 italic">{{ perm.description || 'No description available.' }}</p>

        <div class="mt-auto pt-4 border-t border-slate-100 flex items-center justify-end">
           <div class="flex gap-2">
             <button @click="editPermission(perm)" class="btn btn-ghost text-slate-500 hover:text-cyan-600" style="padding: 6px 10px; font-size: 11px; font-weight: bold;">Edit</button>
             <button @click="confirmDelete(perm)" class="btn btn-ghost text-red-500 hover:bg-red-50" style="padding: 6px 10px; font-size: 11px; font-weight: bold;">Delete</button>
           </div>
        </div>
      </div>
    </div>

    <!-- Create/Edit Modal -->
    <Modal :show="showCreate || !!editingPerm" :title="editingPerm ? 'Modify Permission' : 'Create New Permission'" @close="closeModal">
      <form @submit.prevent="savePermission" class="space-y-5 px-1 py-1">
        <div>
          <label class="block text-[11px] text-slate-500 font-medium  tracking-wider mb-1.5 ml-1">Permission Key *</label>
          <input v-model="form.name" type="text" required class="input" placeholder="e.g., submit_proposals" />
          <p class="text-[10px] text-slate-400 mt-1.5 ml-1">Use lowercase with underscores. Must be unique.</p>
        </div>
        <div>
          <label class="block text-[11px] text-slate-500 font-medium  tracking-wider mb-1.5 ml-1">Description</label>
          <textarea v-model="form.description" rows="2" class="input resize-none" placeholder="What does this permission allow?"></textarea>
        </div>
        <div class="flex justify-end gap-3 pt-4">
          <button type="button" @click="closeModal" class="btn btn-secondary">Cancel</button>
          <button type="submit" class="btn btn-primary px-5">{{ editingPerm ? 'Update' : 'Create' }}</button>
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

const notif = useNotificationStore()
const permissions = ref([]); const loading = ref(true)
const showCreate = ref(false); const editingPerm = ref(null)
const showDelete = ref(false); const deletingPerm = ref(null)
const form = reactive({ name: '', description: '' })

async function fetchPermissions() {
  loading.value = true
  try { const { data } = await api.get('/permissions'); permissions.value = data }
  catch (e) { notif.error('Failed to load permissions') }
  finally { loading.value = false }
}

function editPermission(perm) { editingPerm.value = perm; Object.assign(form, { name: perm.name, description: perm.description || '' }) }
function closeModal() { showCreate.value = false; editingPerm.value = null; Object.assign(form, { name: '', description: '' }) }
function confirmDelete(perm) { deletingPerm.value = perm; showDelete.value = true }

async function savePermission() {
  try {
    if (editingPerm.value) { await api.put(`/permissions/${editingPerm.value.id}`, form); notif.success('Updated!') }
    else { await api.post('/permissions', form); notif.success('Created!') }
    closeModal(); fetchPermissions()
  } catch (err) { notif.error(err.response?.data?.message || 'Failed') }
}

async function deletePermission() {
  try { await api.delete(`/permissions/${deletingPerm.value.id}`); notif.success('Deleted!'); showDelete.value = false; fetchPermissions() }
  catch (err) { notif.error(err.response?.data?.message || 'Failed') }
}

onMounted(fetchPermissions)
</script>
