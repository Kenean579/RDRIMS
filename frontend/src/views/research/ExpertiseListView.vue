<template>
  <div class="flex flex-col gap-5 pb-6 animate-fade">
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
      <div>
        <h1 class="text-xl font-bold text-slate-900">Expertise Tags</h1>
        <p class="text-sm text-slate-500 mt-1">Manage expertise areas used in researcher profiles and classification.</p>
      </div>
      <button v-if="canManage" type="button" class="btn btn-primary h-11 px-5" @click="openCreate">
        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 4v16m8-8H4" /></svg>
        Add Expertise Tag
      </button>
    </div>

    <div class="grid grid-cols-1 sm:grid-cols-[180px_1fr] gap-4">
      <div class="card p-5">
        <p class="text-xs font-semibold text-slate-500">Total tags</p>
        <p class="text-3xl font-bold text-slate-900 mt-1">{{ expertise.length }}</p>
      </div>
      <div class="card p-5 flex items-end">
        <div class="w-full">
          <label for="expertise-search" class="block text-xs font-semibold text-slate-500 mb-2">Search expertise</label>
          <div class="relative">
            <svg class="absolute left-3.5 top-1/2 -translate-y-1/2 w-4 h-4 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><circle cx="11" cy="11" r="8" stroke-width="2"/><path d="m21 21-4.35-4.35" stroke-width="2"/></svg>
            <input id="expertise-search" v-model.trim="searchQuery" class="input pl-10" placeholder="Search by tag name..." />
          </div>
        </div>
      </div>
    </div>

    <div v-if="loading" class="card p-12 flex justify-center">
      <div class="w-9 h-9 rounded-full border-4 border-slate-200 border-t-brand animate-spin"></div>
    </div>

    <div v-else-if="error" class="card p-10 text-center">
      <p class="text-sm font-semibold text-rose-600">{{ error }}</p>
      <button type="button" class="btn btn-secondary mt-4" @click="fetchExpertise">Retry</button>
    </div>

    <EmptyState
      v-else-if="filteredExpertise.length === 0"
      icon="tags"
      :title="searchQuery ? 'No matching tags' : 'No expertise tags found'"
      :description="searchQuery ? 'Try a different search term.' : 'Add an expertise tag to start building the taxonomy.'"
      :action-label="canManage && !searchQuery ? 'Add First Tag' : ''"
      @action="openCreate"
    />

    <div v-else class="card overflow-hidden">
      <div class="px-5 py-4 border-b border-slate-100 flex items-center justify-between">
        <p class="text-sm font-semibold text-slate-700">
          {{ filteredExpertise.length }} {{ filteredExpertise.length === 1 ? 'tag' : 'tags' }}
        </p>
        <button v-if="searchQuery" type="button" class="text-xs font-semibold text-brand hover:underline" @click="searchQuery = ''">Clear search</button>
      </div>

      <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 divide-y sm:divide-y-0 border-slate-100">
        <div v-for="exp in filteredExpertise" :key="exp.id" class="flex items-center gap-3 p-4 border-slate-100 sm:border-b sm:border-r hover:bg-slate-50 transition-colors">
          <div class="w-9 h-9 rounded-xl bg-brand/10 text-brand flex items-center justify-center text-sm font-bold shrink-0">
            {{ exp.name.charAt(0).toUpperCase() }}
          </div>
          <span class="flex-1 min-w-0 text-sm font-semibold text-slate-800 truncate" :title="exp.name">{{ exp.name }}</span>
          <ActionMenu v-if="canManage" :actions="[
            { key: 'edit', label: 'Edit', handler: () => editItem(exp) },
            { separator: true },
            { key: 'delete', label: 'Delete', handler: () => confirmDelete(exp) }
          ]" size="sm" />
        </div>
      </div>
    </div>

    <Modal :show="showCreate || !!editingItem" :title="editingItem ? 'Edit Expertise Tag' : 'Add Expertise Tag'" size="md" @close="closeModal">
      <form class="space-y-6" @submit.prevent="saveExpertise">
        <div>
          <label for="expertise-name" class="block text-xs font-semibold text-slate-600 mb-2">Tag name *</label>
          <input id="expertise-name" v-model.trim="form.name" required maxlength="255" class="input h-11" placeholder="e.g. Molecular Genetics" />
          <p class="text-xs text-slate-400 mt-2">Use a concise name that researchers will recognize.</p>
        </div>
        <div class="flex justify-end gap-3 pt-5 border-t border-slate-100">
          <button type="button" class="btn btn-secondary" @click="closeModal">Cancel</button>
          <button type="submit" class="btn btn-primary" :disabled="submitting || !form.name">
            {{ submitting ? 'Saving...' : editingItem ? 'Save Changes' : 'Add Tag' }}
          </button>
        </div>
      </form>
    </Modal>

    <ConfirmDialog
      :show="showDelete"
      title="Delete Expertise Tag"
      :message="`Delete '${deletingExp?.name || ''}'? This may remove it from associated researcher profiles.`"
      confirmText="Delete Tag"
      variant="danger"
      @confirm="deleteExpertise"
      @cancel="closeDelete"
    />
  </div>
</template>

<script setup>
import { computed, onMounted, reactive, ref } from 'vue'
import { useAuthStore } from '@/stores/auth'
import { useNotificationStore } from '@/stores/notification'
import api from '@/services/api'
import ActionMenu from '@/components/ActionMenu.vue'
import ConfirmDialog from '@/components/ConfirmDialog.vue'
import EmptyState from '@/components/EmptyState.vue'
import Modal from '@/components/Modal.vue'

const auth = useAuthStore()
const notif = useNotificationStore()
const canManage = computed(() => auth.hasRole('super_admin', 'research_admin'))
const expertise = ref([])
const loading = ref(true)
const error = ref('')
const submitting = ref(false)
const searchQuery = ref('')
const showCreate = ref(false)
const editingItem = ref(null)
const showDelete = ref(false)
const deletingExp = ref(null)
const form = reactive({ name: '' })

const filteredExpertise = computed(() => {
  const query = searchQuery.value.toLocaleLowerCase()
  return expertise.value.filter(item => !query || item.name.toLocaleLowerCase().includes(query))
})

async function fetchExpertise() {
  loading.value = true
  error.value = ''
  try {
    const { data } = await api.get('/expertise', { timeout: 15000 })
    expertise.value = (Array.isArray(data) ? data : (data.data || []))
      .sort((a, b) => a.name.localeCompare(b.name))
  } catch (err) {
    error.value = err.response?.data?.message || 'Failed to load expertise tags.'
  } finally {
    loading.value = false
  }
}

function openCreate() {
  editingItem.value = null
  form.name = ''
  showCreate.value = true
}

function editItem(item) {
  editingItem.value = item
  form.name = item.name
}

function closeModal() {
  showCreate.value = false
  editingItem.value = null
  form.name = ''
}

function confirmDelete(item) {
  deletingExp.value = item
  showDelete.value = true
}

function closeDelete() {
  showDelete.value = false
  deletingExp.value = null
}

async function saveExpertise() {
  submitting.value = true
  try {
    const payload = { name: form.name }
    if (editingItem.value) {
      await api.put(`/expertise/${editingItem.value.id}`, payload)
      notif.success('Expertise tag updated.')
    } else {
      await api.post('/expertise', payload)
      notif.success('Expertise tag added.')
    }
    closeModal()
    await fetchExpertise()
  } catch (err) {
    const errors = err.response?.data?.errors
    notif.error(errors ? Object.values(errors).flat()[0] : (err.response?.data?.message || 'Failed to save expertise tag.'))
  } finally {
    submitting.value = false
  }
}

async function deleteExpertise() {
  try {
    await api.delete(`/expertise/${deletingExp.value.id}`)
    notif.success('Expertise tag deleted.')
    closeDelete()
    await fetchExpertise()
  } catch (err) {
    notif.error(err.response?.data?.message || 'Failed to delete expertise tag.')
  }
}

onMounted(fetchExpertise)
</script>
