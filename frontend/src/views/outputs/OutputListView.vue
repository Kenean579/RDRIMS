<template>
  <div class="flex flex-col gap-6 animate-fade card">
    <!-- Header -->
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
      <div>
        <h1 class="text-xl font-bold text-slate-900 tracking-tight">Research Outputs</h1>
        <p class="text-slate-500 font-medium mt-1">Track research results, student projects, and intellectual outcomes.</p>
      </div>
      <button @click="showCreate = true" class="btn btn-primary h-11 px-5 text-xs font-medium">
        Add Output
      </button>
    </div>

    <!-- Filters -->
    <div class="card p-8 bg-slate-50/50">
      <div class="flex flex-col sm:flex-row gap-4">
        <div class="flex-1 relative">
          <svg class="absolute left-3.5 top-1/2 -translate-y-1/2 w-4 h-4 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" /></svg>
          <input v-model="search" type="text" placeholder="Search by title..." class="input pl-10" @input="debounceSearch" />
        </div>
        <select v-model="statusFilter" @change="fetchOutputs(1)" class="input w-full sm:w-48 font-bold">
          <option value="">All Statuses</option>
          <option v-for="s in outputStatuses" :key="s.id" :value="s.name">{{ formatStatusName(s.name) }}</option>
        </select>
        <select v-model="categoryFilter" @change="fetchOutputs(1)" class="input w-full sm:w-48 font-bold">
          <option value="">All Categories</option>
          <option v-for="c in outputCategories" :key="c.id" :value="c.id">{{ formatStatusName(c.name) }}</option>
        </select>
      </div>
    </div>

    <!-- Content -->
    <div v-if="loading" class="card p-8 flex flex-col justify-center items-center gap-4">
       <div class="animate-spin rounded-full h-10 w-10 border-b-2 border-brand"></div>
       <p class="text-xs font-medium text-slate-400">Loading Repository...</p>
    </div>

    <div v-else-if="outputs.length === 0" class="card">
       <EmptyState icon="📤" title="No results found" description="Adjust your filters or add a new research output." action-label="Add New" @action="showCreate = true" />
    </div>

    <div v-else class="space-y-4">
      <div v-for="o in outputs" :key="o.id" class="card p-8 group card-hover/20 transition-all flex flex-col md:flex-row items-center justify-between gap-6">
          <div class="flex-1 min-w-0">
            <div class="flex items-center gap-3 mb-3">
              <h3 class="text-lg font-bold text-slate-800 group-hover:text-brand transition-colors line-clamp-1 leading-tight">{{ o.title }}</h3>
              <StatusBadge :status="o.status?.name || 'draft'" />
            </div>
            <p class="text-sm text-slate-500 font-medium line-clamp-2 leading-relaxed mb-4 italic">{{ o.abstract || 'No abstract provided.' }}</p>
            <div class="flex flex-wrap gap-2">
              <span class="px-2.5 py-1 bg-blue-50 text-blue-600 text-xs font-medium rounded-2xl border border-blue-100">{{ o.category?.name || 'Output' }}</span>
              <span v-if="o.subtype?.name" class="px-2.5 py-1 bg-indigo-50 text-indigo-600 text-xs font-medium rounded-2xl border border-indigo-100">{{ o.subtype.name }}</span>
              <span v-if="o.participants?.length" class="px-2.5 py-1 bg-emerald-50 text-emerald-600 text-xs font-medium rounded-2xl border border-emerald-100">{{ o.participants.length }} Contributors</span>
            </div>
          </div>
          <div class="flex md:flex-col gap-2 shrink-0">
            <button @click="editOutput(o)" class="btn btn-secondary text-xs font-medium tracking-widest  py-2.5 px-6">Edit</button>
            <button @click="confirmDelete(o)" class="btn btn-ghost text-red-500 hover:bg-red-50 text-xs font-medium tracking-widest  py-2.5 px-6">Delete</button>
          </div>
      </div>
      <div class="card p-4 bg-slate-50/50">
        <Pagination :current-page="pagination.current_page" :total-pages="pagination.last_page" :total="pagination.total" @page-change="fetchOutputs" />
      </div>
    </div>

    <!-- Create/Edit Modal -->
    <Modal :show="showCreate || !!editingOutput" :title="editingOutput ? 'Edit Research Output' : 'Register New Output'" size="lg" @close="closeModal">
      <form @submit.prevent="saveOutput" class="space-y-8">
        <!-- Step 1: Basic Info -->
          <div class="space-y-6">
            <div>
              <label class="block text-xs text-slate-500 font-medium mb-2 ml-1">Title <span class="text-rose-500">*</span></label>
              <input v-model="form.title" type="text" required class="input h-12 font-bold" />
            </div>
            <div>
              <label class="block text-xs text-slate-500 font-medium mb-3 ml-1">Output Type <span class="text-rose-500">*</span></label>
              <div class="flex gap-4">
                <label class="flex items-center gap-2 p-3 border rounded-2xl cursor-pointer transition-colors"
                  :class="form.participant_type === 'student' ? 'border-brand bg-brand/5' : 'border-slate-200 bg-white'">
                  <input type="radio" value="student" v-model="form.participant_type" class="w-4 h-4 text-brand focus:ring-brand border-slate-300" />
                  <span class="text-xs font-bold text-slate-700">Student Output</span>
                </label>
                <label class="flex items-center gap-2 p-3 border rounded-2xl cursor-pointer transition-colors"
                  :class="form.participant_type === 'research_center' ? 'border-brand bg-brand/5' : 'border-slate-200 bg-white'">
                  <input type="radio" value="research_center" v-model="form.participant_type" class="w-4 h-4 text-brand focus:ring-brand border-slate-300" />
                  <span class="text-xs font-bold text-slate-700">Research Center Output</span>
                </label>
              </div>
            </div>
            <div v-if="form.participant_type === 'student'" class="grid grid-cols-1 md:grid-cols-2 gap-6">
              <div>
                <label class="block text-xs text-slate-500 font-medium mb-2 ml-1">Level <span class="text-rose-500">*</span></label>
                <select v-model="form.level_id" required class="input h-12 font-bold">
                  <option value="">Select level</option>
                  <option v-for="l in studentLevels" :key="l.id" :value="l.id">{{ l.name }}</option>
                </select>
              </div>
            </div>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
              <div>
                <label class="block text-xs text-slate-500 font-medium mb-2 ml-1">Category <span class="text-rose-500">*</span></label>
                <select v-model="form.category_id" required class="input h-12 font-bold">
                  <option v-for="c in outputCategories" :key="c.id" :value="c.id">{{ c.name }}</option>
                </select>
              </div>
              <div>
                <label class="block text-xs text-slate-500 font-medium mb-2 ml-1">Subtype <span class="text-rose-500">*</span></label>
                <select v-model="form.subtype_id" required class="input h-12 font-bold">
                  <option v-for="s in outputSubtypes" :key="s.id" :value="s.id">{{ s.name }}</option>
                </select>
              </div>
            </div>
            <div>
              <label class="block text-xs text-slate-500 font-medium mb-2 ml-1">Abstract / Description</label>
              <textarea v-model="form.abstract" rows="4" class="input pt-3 font-medium resize-none"></textarea>
            </div>
          </div>

        <!-- Step 2: Contributors -->
        <div class="bg-slate-50/50 p-5 rounded-3xl border border-slate-100">
           <div class="flex items-center justify-between mb-6">
              <h3 class="text-xs font-medium text-slate-400 flex items-center gap-2">
                <span class="w-1 h-3 bg-brand rounded-full"></span>
                Contributors
              </h3>
              <button type="button" @click="addParticipant" class="btn btn-secondary h-9 px-4 text-xs  font-medium tracking-widest">
                Add Contributor
              </button>
           </div>
           
           <div v-if="form.participants.length === 0" class="text-center py-4">
             <p class="text-xs font-medium text-slate-300 italic">No other contributors added.</p>
           </div>

           <div v-else class="space-y-4">
             <div v-for="(p, index) in form.participants" :key="index" class="bg-white p-4 rounded-2xl border border-slate-100 relative group">
                <button type="button" @click="removeParticipant(index)" class="absolute -top-2 -right-2 w-7 h-7 bg-white text-rose-500 border border-slate-100 rounded-full shadow-sm flex items-center justify-center opacity-0 group-hover:opacity-100 transition-opacity">
                  <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M6 18L18 6M6 6l12 12"/></svg>
                </button>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                  <div>
                    <label class="block text-xs font-medium text-slate-400  mb-1">Contributor Role</label>
                    <select v-model="p.participant_type_id" required class="input h-9 text-xs font-bold">
                      <option v-for="t in participantTypes" :key="t.id" :value="t.id">{{ t.name }}</option>
                    </select>
                  </div>
                  <div>
                    <label class="block text-xs font-medium text-slate-400  mb-1">User</label>
                    <select v-model="p.user_id" required class="input h-9 text-xs font-bold bg-slate-50/50">
                      <option value="">Select university member</option>
                      <option v-for="u in users" :key="u.id" :value="u.id">{{ u.name }}</option>
                    </select>
                  </div>
                </div>
             </div>
           </div>
        </div>

        <div class="flex justify-end gap-3 pt-6 border-t border-slate-100">
           <button type="button" @click="closeModal" class="btn btn-secondary px-5 h-12">Cancel</button>
           <button type="submit" class="btn btn-primary px-6 h-12">
             {{ editingOutput ? 'Save Changes' : 'Register Output' }}
           </button>
        </div>
      </form>
    </Modal>

    <ConfirmDialog :show="showDelete" title="Permanently Delete" message="This output and all related documentation will be removed from the institutional repository." confirmText="Delete Now" variant="danger" @confirm="deleteOutput" @cancel="showDelete = false" />
  </div>
</template>

<script setup>
import { ref, reactive, onMounted } from 'vue'
import { useNotificationStore } from '@/stores/notification'
import api from '@/services/api'
import StatusBadge from '@/components/StatusBadge.vue'
import Modal from '@/components/Modal.vue'
import ConfirmDialog from '@/components/ConfirmDialog.vue'
import Pagination from '@/components/Pagination.vue'
import EmptyState from '@/components/EmptyState.vue'
import { formatStatusName } from '@/utils/colors'

const notif = useNotificationStore()
const outputs = ref([]); const loading = ref(true); const error = ref(null)
const pagination = reactive({ current_page: 1, last_page: 1, total: 0 })
const search = ref(''); const statusFilter = ref(''); const categoryFilter = ref('')
const outputStatuses = ref([]); const outputCategories = ref([]); const outputSubtypes = ref([]); const studentLevels = ref([]); const users = ref([]); const participantTypes = ref([])
const showCreate = ref(false); const editingOutput = ref(null); const showDelete = ref(false); const deletingOutput = ref(null)
const form = reactive({ title: '', abstract: '', category_id: '', subtype_id: '', participant_type: 'student', level_id: '', participants: [] })
let searchTimer = null

async function fetchOutputs(page = 1) {
  loading.value = true
  try {
    const params = { page }
    if (search.value) params.search = search.value
    if (statusFilter.value) params.status = statusFilter.value
    if (categoryFilter.value) params.category_id = categoryFilter.value
    const { data } = await api.get('/outputs', { params })
    outputs.value = data.data
    Object.assign(pagination, { current_page: data.current_page, last_page: data.last_page, total: data.total })
  } catch (err) { error.value = 'Failed to sync with repository' }
  finally { loading.value = false }
}

function debounceSearch() { clearTimeout(searchTimer); searchTimer = setTimeout(() => fetchOutputs(1), 400) }

function closeModal() {
  showCreate.value = false; editingOutput.value = null
  Object.assign(form, { title: '', abstract: '', category_id: '', subtype_id: '', participant_type: 'student', level_id: '', participants: [] })
}

function addParticipant() { 
  const pType = participantTypes.value.find(t => t.name === 'supervisor')
  form.participants.push({ user_id: '', participant_type_id: pType?.id || '' }) 
}
function removeParticipant(i) { form.participants.splice(i, 1) }

function editOutput(o) {
  editingOutput.value = o
  Object.assign(form, {
    title: o.title,
    abstract: o.abstract || '',
    category_id: o.category_id,
    subtype_id: o.subtype_id,
    participant_type: o.participant_type || 'student',
    level_id: o.level_id || '',
    participants: (o.participants || []).map(p => ({ user_id: p.user_id || '', name: p.name || '', email: p.email || '' }))
  })
}

async function saveOutput() {
  try {
    if (editingOutput.value) {
      await api.put(`/outputs/${editingOutput.value.id}`, form)
      notif.success('Output updated!')
    } else {
      await api.post('/outputs', form)
      notif.success('Output registered!')
    }
    closeModal(); fetchOutputs()
  } catch (err) { notif.error('Failed to index output') }
}

async function deleteOutput() {
  try {
    await api.delete(`/outputs/${deletingOutput.value.id}`)
    notif.success('Output archived')
    showDelete.value = false; fetchOutputs()
  } catch (err) { notif.error('Recall failed') }
}

onMounted(async () => {
  fetchOutputs()
  try {
    const ss = await api.get('/lookups/output_statuses')
    const cs = await api.get('/lookups/output_categories')
    const subs = await api.get('/lookups/output_subtypes')
    const lvls = await api.get('/lookups/student_levels')
    const pts = await api.get('/lookups/participant_types')
    const ur = await api.get('/users', { params: { per_page: 200 } })
    outputStatuses.value = ss.data; outputCategories.value = cs.data; outputSubtypes.value = subs.data
    studentLevels.value = lvls.data
    participantTypes.value = pts.data
    users.value = ur.data.data || ur.data
  } catch (e) {}
})
</script>
