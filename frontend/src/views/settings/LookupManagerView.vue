<template>
  <div class="flex flex-col gap-5 pb-6 animate-fade card">
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
      <div>
        <h1 class="text-xl font-bold text-slate-900 tracking-tight">System Lookups</h1>
        <p class="text-slate-500 font-medium mt-1">Manage state machines and classification lists.</p>
      </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-4 gap-5">
      <!-- Sidebar -->
      <div class="lg:col-span-1 space-y-2">
        <label class="block text-xs font-medium text-slate-400 mb-3 ml-1">Select Table</label>
        <button 
          v-for="table in tables" :key="table.id"
          @click="selectTable(table)"
          class="w-full text-left px-4 py-3 rounded-2xl text-xs font-bold transition-all relative group overflow-hidden"
          :class="activeTable?.id === table.id ? 'bg-brand text-white' : 'bg-slate-50 text-slate-600 hover:bg-white hover:shadow-md border border-transparent hover:border-slate-100'"
        >
          {{ table.label }}
          <span v-if="activeTable?.id === table.id" class="absolute right-0 top-0 w-12 h-12 bg-white/10 rounded-full translate-x-4 -translate-y-4"></span>
        </button>
      </div>

      <!-- Main Content -->
      <div class="lg:col-span-3 space-y-6">
        <div v-if="!activeTable" class="card p-8 text-center">
          <div class="w-16 h-16 bg-slate-50 rounded-full flex items-center justify-center mx-auto mb-4 text-slate-300">
            <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M4 6h16M4 10h16M4 14h16M4 18h16" /></svg>
          </div>
          <p class="text-sm font-bold text-slate-600">Select a lookup table to begin management.</p>
        </div>

        <div v-else class="space-y-6 animate-fade-in">
          <div class="flex items-center justify-between bg-slate-50/50 p-4 rounded-2xl border border-slate-100">
             <div class="flex items-center gap-3">
               <div class="w-10 h-10 bg-white rounded-2xl shadow-sm flex items-center justify-center text-brand">
                 <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10" /></svg>
               </div>
               <div>
                 <h2 class="text-lg font-bold text-slate-800 tracking-tight">{{ activeTable.label }}</h2>
                 <p class="text-xs font-medium text-slate-400">Table: {{ activeTable.id }}</p>
               </div>
             </div>
             <button @click="showAdd = true" class="btn btn-primary h-10 px-6 text-xs font-medium">Add Option</button>
          </div>

          <div v-if="loading" class="card p-8 flex justify-center"><div class="animate-spin rounded-full h-10 w-10 border-b-2 border-brand"></div></div>
          
          <div v-else class="card overflow-hidden">
            <div class="overflow-x-auto">
              <table class="table-auto">
                <thead>
                  <tr>
                    <th class="w-20 pl-8 py-4 text-xs font-medium text-slate-400">ID</th>
                    <th class="py-4 text-xs font-medium text-slate-400">Name / Label</th>
                    <th class="pr-8 py-4 text-xs font-medium text-slate-400 text-right">Actions</th>
                  </tr>
                </thead>
                <tbody class="divide-y divide-slate-50">
                  <tr v-for="item in items" :key="item.id" class="hover:bg-slate-50/30 transition-colors group">
                    <td class="pl-8 py-4 text-xs font-medium text-slate-400">{{ item.id }}</td>
                    <td class="py-4">
                      <span class="px-3 py-1 bg-slate-100 text-slate-700 text-xs font-bold  tracking-tight rounded-2xl border border-slate-100">{{ item.name }}</span>
                    </td>
                    <td class="pr-8 py-4 text-right">
                      <div class="flex justify-end">
                        <ActionMenu :actions="[
                          { key: 'edit', label: 'Rename', handler: () => editItem(item) },
                          { separator: true },
                          { key: 'delete', label: 'Delete', handler: () => confirmDelete(item) }
                        ]" />
                      </div>
                    </td>
                  </tr>
                </tbody>
              </table>
            </div>
            <div v-if="items.length === 0" class="p-6 text-center text-xs font-medium text-slate-400 italic">No records in this lookup.</div>
          </div>
        </div>
      </div>
    </div>

    <!-- Modals -->
    <Modal :show="showAdd || !!editingItem" :title="editingItem ? 'Edit Lookup' : 'Add New Option'" @close="closeModal">
      <form @submit.prevent="saveItem" class="space-y-4">
        <div>
          <label class="block text-xs text-slate-500 font-medium mb-2 ml-1">Option Name *</label>
          <input v-model="form.name" type="text" required placeholder="e.g. In Progress" class="input h-12 font-bold" />
        </div>
        <div class="flex justify-end gap-3 pt-6 border-t border-slate-100">
           <button type="button" @click="closeModal" class="btn btn-secondary px-6">Discard</button>
           <button type="submit" class="btn btn-primary px-5">Save</button>
        </div>
      </form>
    </Modal>

    <ConfirmDialog :show="showDelete" title="Permanently Delete" message="This will remove the lookup option. Associated records may break if still referencing this ID." confirmText="Delete" variant="danger" @confirm="deleteItem" @cancel="showDelete = false" />
  </div>
</template>

<script setup>
import { ref, reactive, onMounted } from 'vue'
import { useNotificationStore } from '@/stores/notification'
import api from '@/services/api'
import Modal from '@/components/Modal.vue'
import ConfirmDialog from '@/components/ConfirmDialog.vue'
import ActionMenu from '@/components/ActionMenu.vue'

const notif = useNotificationStore()
const tables = [
  { id: 'call_statuses', label: 'Call Statuses' },
  { id: 'proposal_types', label: 'Proposal Types' },
  { id: 'proposal_statuses', label: 'Proposal Statuses' },
  { id: 'review_decisions', label: 'Review Decisions' },
  { id: 'finance_check_statuses', label: 'Finance Statuses' },
  { id: 'ethics_approval_statuses', label: 'Ethics Statuses' },
  { id: 'patent_statuses', label: 'Patent Statuses' },
  { id: 'community_problem_statuses', label: 'Community Statuses' },
  { id: 'project_statuses', label: 'Project Statuses' },
  { id: 'milestone_statuses', label: 'Milestone Statuses' },
  { id: 'task_statuses', label: 'Task Statuses' },
  { id: 'investigator_roles', label: 'Investigator Roles' },
  { id: 'invitation_statuses', label: 'Invitation Statuses' },
  { id: 'agreement_types', label: 'Agreement Types' },
  { id: 'output_categories', label: 'Output Categories' },
  { id: 'student_levels', label: 'Student Levels' },
  { id: 'output_subtypes', label: 'Output Subtypes' },
  { id: 'detection_services', label: 'Detection Services' },
  { id: 'detection_statuses', label: 'Detection Statuses' },
  { id: 'participant_types', label: 'Participant Types' },
  { id: 'output_statuses', label: 'Output Statuses' }
]

const activeTable = ref(null)
const items = ref([]); const loading = ref(false)
const showAdd = ref(false); const editingItem = ref(null); const showDelete = ref(false); const deletingItem = ref(null)
const form = reactive({ name: '' })

async function selectTable(table) {
  activeTable.value = table
  loading.value = true
  try {
    const { data } = await api.get(`/lookups/${table.id}`)
    items.value = Array.isArray(data) ? data : (data.data || [])
  } catch (err) {
    notif.error('Failed to load lookup')
    items.value = []
  } finally {
    loading.value = false
  }
}

function closeModal() {
  showAdd.value = false; editingItem.value = null
  form.name = ''
}

function editItem(item) {
  editingItem.value = item
  form.name = item.name
}

function confirmDelete(item) {
  deletingItem.value = item
  showDelete.value = true
}

async function saveItem() {
  try {
    if (editingItem.value) {
      await api.put(`/lookups/${activeTable.value.id}/${editingItem.value.id}`, form)
      notif.success('Renamed!')
    } else {
      await api.post(`/lookups/${activeTable.value.id}`, form)
      notif.success('Added!')
    }
    closeModal(); selectTable(activeTable.value)
  } catch (err) {
    notif.error('Operation failed')
  }
}

async function deleteItem() {
  try {
    await api.delete(`/lookups/${activeTable.value.id}/${deletingItem.value.id}`)
    notif.success('Deleted!')
    showDelete.value = false; selectTable(activeTable.value)
  } catch (err) {
    notif.error('Failed to delete')
  }
}

onMounted(() => {
  // Select first table by default
  selectTable(tables[0])
})
</script>
