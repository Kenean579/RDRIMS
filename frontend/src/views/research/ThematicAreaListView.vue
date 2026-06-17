<template>
  <div class="flex flex-col gap-5 pb-6 animate-fade card">
    <!-- Header -->
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
      <div>
        <h1 class="text-xl font-bold text-slate-900 tracking-tight">Research Areas</h1>
        <p class="text-slate-500 font-medium mt-1">Focus areas for research and funding priority.</p>
      </div>
      <button @click="showCreate = true" class="btn btn-primary h-11 px-6">
        <svg class="w-4 h-4 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 4v16m8-8H4" /></svg>
        Add New Area
      </button>
    </div>

    <!-- Content -->
    <div v-if="loading" class="card p-8 flex flex-col justify-center items-center gap-4">
      <div class="animate-spin rounded-full h-10 w-10 border-b-2 border-brand"></div>
      <p class="text-xs font-medium text-slate-400">Loading areas...</p>
    </div>
    
    <div v-else-if="thematicAreas.length === 0" class="card">
      <EmptyState icon="🏷️" title="No areas found" description="Add research areas to help categorize proposals and funding." action-label="Add First Area" @action="showCreate = true" />
    </div>

    <div v-else class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
      <div v-for="t in thematicAreas" :key="t.id" class="card p-8 flex flex-col group card-hover relative overflow-hidden transition-all">
        <div class="flex items-start justify-between mb-4">
          <div class="flex-1 pr-4 min-w-0">
            <h3 class="text-base font-bold text-slate-800 leading-tight group-hover:text-brand transition-colors line-clamp-2 min-h-10">{{ t.name }}</h3>
            <p class="text-xs font-medium text-slate-400 mt-1">Research Area</p>
          </div>
          <div class="w-12 h-12 bg-slate-100 text-white flex items-center justify-center font-bold shrink-0  text-xs" style="border-radius: 1rem">
             {{ t.name.substring(0,2) }}
          </div>
        </div>
        
        <p class="text-sm text-slate-500 font-medium flex-1 line-clamp-2 leading-relaxed mb-6">{{ t.description || 'Global research focus area for institutional alignment.' }}</p>
        
        <div class="flex items-center justify-end bg-slate-50/50 p-1 gap-1 mt-auto" style="border-radius: 1rem">
          <ActionMenu :actions="[
            { key: 'edit', label: 'Edit', handler: () => editTheme(t) },
            { separator: true },
            { key: 'delete', label: 'Delete', handler: () => confirmDelete(t) }
          ]" />
        </div>
      </div>
    </div>

    <!-- Modals -->
    <Modal :show="showCreate || !!editingTheme" :title="editingTheme ? 'Edit Area' : 'Add Area'" size="lg" @close="closeModal">
      <form @submit.prevent="saveTheme" class="space-y-6">
        <div>
          <label class="block text-xs text-slate-500 font-medium mb-2 ml-1">Area Name *</label>
          <input v-model="form.name" type="text" required class="input h-12 font-bold" placeholder="e.g., Artificial Intelligence, Climate" />
        </div>
        <div>
          <label class="block text-xs text-slate-500 font-medium mb-2 ml-1">Description</label>
          <textarea v-model="form.description" rows="4" class="input resize-none pt-3" placeholder="Tell us more about this research area..."></textarea>
        </div>
        <div class="flex justify-end gap-3 pt-6 mt-6 border-t border-slate-100">
          <button type="button" @click="closeModal" class="btn btn-secondary px-6">Cancel</button>
          <button type="submit" class="btn btn-primary px-5">{{ editingTheme ? 'Save Changes' : 'Save Area' }}</button>
        </div>
      </form>
    </Modal>

    <ConfirmDialog :show="showDelete" title="Delete Area" message="Are you sure you want to delete this research area? This cannot be undone." confirmText="Delete Now" variant="danger" @confirm="deleteTheme" @cancel="showDelete = false" />
  </div>
</template>

<script setup>
import { ref, reactive, onMounted } from 'vue'
import { useNotificationStore } from '@/stores/notification'
import api from '@/services/api'
import Modal from '@/components/Modal.vue'
import ConfirmDialog from '@/components/ConfirmDialog.vue'
import EmptyState from '@/components/EmptyState.vue'
import ActionMenu from '@/components/ActionMenu.vue'

const notif = useNotificationStore()
const thematicAreas = ref([]); const loading = ref(true)
const showCreate = ref(false); const editingTheme = ref(null); const showDelete = ref(false); const deletingTheme = ref(null)
const form = reactive({ name: '', description: '' })

async function fetchThemes() {
  loading.value = true
  try { const { data } = await api.get('/lookups/thematic_areas'); thematicAreas.value = data }
  catch (e) {} finally { loading.value = false }
}

function editTheme(t) { editingTheme.value = t; Object.assign(form, { name: t.name, description: t.description || '' }) }
function closeModal() { showCreate.value = false; editingTheme.value = null; Object.assign(form, { name: '', description: '' }) }
function confirmDelete(t) { deletingTheme.value = t; showDelete.value = true }

async function saveTheme() {
  try {
    if (editingTheme.value) { await api.put(`/thematic-areas/${editingTheme.value.id}`, form); notif.success('Updated!') }
    else { await api.post('/thematic-areas', form); notif.success('Created!') }
    closeModal(); fetchThemes()
  } catch (err) { notif.error('Failed to save') }
}

async function deleteTheme() {
  try { await api.delete(`/thematic-areas/${deletingTheme.value.id}`); notif.success('Deleted!'); showDelete.value = false; fetchThemes() }
  catch (err) { notif.error('Failed to delete') }
}

onMounted(fetchThemes)
</script>
