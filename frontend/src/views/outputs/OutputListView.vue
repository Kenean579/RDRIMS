<template>
  <div class="flex flex-col gap-6 animate-fade card">
    <!-- Header -->
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
      <div>
        <h1 class="text-3xl font-black text-slate-900 tracking-tight">Innovation</h1>
        <p class="text-slate-500 font-medium mt-1">Track research results and student work.</p>
      </div>
      <button @click="showCreate = true" class="btn btn-primary h-11 px-6 shadow-lg shadow-blue-500/20">
        <svg class="w-4 h-4 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 4v16m8-8H4" /></svg>
        Add New Output
      </button>
    </div>

    <!-- Filters -->
    <div class="card p-5 bg-slate-50/50">
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
          <option v-for="c in outputCategories" :key="c.id" :value="c.name">{{ formatStatusName(c.name) }}</option>
        </select>
      </div>
    </div>

    <!-- Content -->
    <div v-if="loading" class="card p-24 flex flex-col justify-center items-center gap-4">
      <div class="animate-spin rounded-full h-10 w-10 border-b-2 border-brand"></div>
      <p class="text-xs font-bold text-slate-400 uppercase tracking-widest">Loading outputs...</p>
    </div>
    
    <div v-else-if="error" class="card border-red-100 bg-red-50/30 p-16 text-center">
      <p class="text-sm text-red-600 font-bold uppercase tracking-widest">{{ error }}</p>
      <button @click="fetchOutputs(1)" class="btn btn-ghost mt-4 text-xs font-bold uppercase">Retry</button>
    </div>
    
    <div v-else-if="outputs.length === 0" class="card">
      <EmptyState icon="📤" title="No outputs found" description="Register your research or student work to showcase achievements." action-label="Add First Output" @action="showCreate = true" />
    </div>

    <div v-else class="space-y-4">
      <div v-for="o in outputs" :key="o.id" class="card p-6 group card-hover border-l-4 border-l-brand/20 hover:border-l-brand transition-all">
        <div class="flex flex-col md:flex-row items-center justify-between gap-6">
          <div class="flex-1 min-w-0">
            <div class="flex items-center gap-3 mb-3">
              <h3 class="text-lg font-black text-slate-900 group-hover:text-brand transition-colors line-clamp-1">{{ o.title }}</h3>
              <StatusBadge :status="o.status?.name || 'draft'" />
            </div>
            <p class="text-sm text-slate-500 font-medium line-clamp-2 leading-relaxed mb-4">{{ o.abstract }}</p>
            <div class="flex flex-wrap gap-2">
              <span class="px-2.5 py-1 bg-blue-50 text-blue-600 text-[10px] font-black uppercase tracking-widest rounded-lg border border-blue-100">{{ o.category?.name }}</span>
              <span v-if="o.subtype?.name" class="px-2.5 py-1 bg-indigo-50 text-indigo-600 text-[10px] font-black uppercase tracking-widest rounded-lg border border-indigo-100">{{ o.subtype.name }}</span>
              <span v-if="o.student_level?.name" class="px-2.5 py-1 bg-purple-50 text-purple-600 text-[10px] font-black uppercase tracking-widest rounded-lg border border-purple-100">{{ o.student_level.name }}</span>
            </div>
          </div>
          <div class="flex md:flex-col gap-2 shrink-0">
            <button @click="editOutput(o)" class="btn btn-secondary text-[11px] font-black tracking-widest uppercase py-2 px-6">Edit</button>
            <button @click="confirmDelete(o)" class="btn btn-ghost text-red-500 hover:bg-red-50 text-[11px] font-black tracking-widest uppercase py-2 px-6">Delete</button>
          </div>
        </div>
      </div>
      <div class="card p-4 bg-slate-50/50 border border-slate-100 shadow-sm">
        <Pagination :current-page="pagination.current_page" :total-pages="pagination.last_page" :total="pagination.total" @page-change="fetchOutputs" />
      </div>
    </div>

    <!-- Create/Edit Modal -->
    <Modal :show="showCreate || !!editingOutput" :title="editingOutput ? 'Edit Output' : 'Add New Output'" @close="closeModal">
      <form @submit.prevent="saveOutput" class="space-y-6">
        <div>
          <label class="block text-[11px] text-slate-500 font-black uppercase tracking-widest mb-2 ml-1">Title *</label>
          <input v-model="form.title" type="text" required class="input h-12 font-bold" placeholder="e.g. New Research Model" />
        </div>
        <div>
          <label class="block text-[11px] text-slate-500 font-black uppercase tracking-widest mb-2 ml-1">Description *</label>
          <textarea v-model="form.abstract" required rows="4" class="input resize-none pt-3" placeholder="Tell us more about this output..."></textarea>
        </div>
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-6">
          <div>
            <label class="block text-[11px] text-slate-500 font-black uppercase tracking-widest mb-2 ml-1">Category *</label>
            <select v-model="form.category_id" required class="input h-12 font-bold">
              <option value="">Select Category</option>
              <option v-for="c in outputCategories" :key="c.id" :value="c.id">{{ formatStatusName(c.name) }}</option>
            </select>
          </div>
          <div>
            <label class="block text-[11px] text-slate-500 font-black uppercase tracking-widest mb-2 ml-1">Type *</label>
            <select v-model="form.subtype_id" required class="input h-12 font-bold">
              <option value="">Select Type</option>
              <option v-for="s in outputSubtypes" :key="s.id" :value="s.id">{{ s.name }}</option>
            </select>
          </div>
        </div>
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-6">
          <div>
            <label class="block text-[11px] text-slate-500 font-black uppercase tracking-widest mb-2 ml-1">Project</label>
            <select v-model="form.project_id" class="input h-12 font-bold">
              <option value="">None</option>
              <option v-for="p in projects" :key="p.id" :value="p.id">{{ p.title }}</option>
            </select>
          </div>
          <div>
            <label class="block text-[11px] text-slate-500 font-black uppercase tracking-widest mb-2 ml-1">Year</label>
            <select v-model="form.academic_year_id" class="input h-12 font-bold">
              <option value="">Select Year</option>
              <option v-for="y in academicYears" :key="y.id" :value="y.id">{{ y.name }}</option>
            </select>
          </div>
        </div>
        <div class="flex justify-end gap-3 pt-6 mt-6 border-t border-slate-100">
          <button type="button" @click="closeModal" class="btn btn-secondary px-6">Cancel</button>
          <button type="submit" class="btn btn-primary px-10 shadow-lg shadow-blue-500/20">{{ editingOutput ? 'Save Changes' : 'Save Output' }}</button>
        </div>
      </form>
    </Modal>

    <ConfirmDialog :show="showDelete" title="Delete Output" message="Are you sure you want to delete this research output? This cannot be undone." confirmText="Delete Now" variant="danger" @confirm="deleteOutput" @cancel="showDelete = false" />
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
import LoadingSkeleton from '@/components/LoadingSkeleton.vue'
import EmptyState from '@/components/EmptyState.vue'
import { formatStatusName } from '@/utils/colors'

const notif = useNotificationStore()
const outputs = ref([]); const loading = ref(true); const error = ref(null)
const pagination = reactive({ current_page: 1, last_page: 1, total: 0 })
const search = ref(''); const statusFilter = ref(''); const categoryFilter = ref('')
const outputStatuses = ref([]); const outputCategories = ref([]); const outputSubtypes = ref([])
const projects = ref([]); const academicYears = ref([])
const showCreate = ref(false); const editingOutput = ref(null); const showDelete = ref(false); const deletingOutput = ref(null)
const form = reactive({ title: '', abstract: '', category_id: '', subtype_id: '', project_id: '', academic_year_id: '' })
let searchTimer = null

async function fetchOutputs(page = 1) {
  loading.value = true; error.value = null
  try { const params = { page }; if (search.value) params.search = search.value; if (statusFilter.value) params.status = statusFilter.value; if (categoryFilter.value) params.category = categoryFilter.value; const { data } = await api.get('/outputs', { params }); outputs.value = data.data; Object.assign(pagination, { current_page: data.current_page, last_page: data.last_page, total: data.total }) }
  catch (err) { error.value = err.response?.data?.message || 'Failed' } finally { loading.value = false }
}
function debounceSearch() { clearTimeout(searchTimer); searchTimer = setTimeout(() => fetchOutputs(1), 400) }
function editOutput(o) { editingOutput.value = o; Object.assign(form, { title: o.title, abstract: o.abstract || '', category_id: o.category_id, subtype_id: o.subtype_id, project_id: o.project_id || '', academic_year_id: o.academic_year_id || '' }) }
function closeModal() { showCreate.value = false; editingOutput.value = null; Object.assign(form, { title: '', abstract: '', category_id: '', subtype_id: '', project_id: '', academic_year_id: '' }) }
function confirmDelete(o) { deletingOutput.value = o; showDelete.value = true }

async function saveOutput() {
  try {
    const payload = { ...form, project_id: form.project_id || null, academic_year_id: form.academic_year_id || null }
    if (editingOutput.value) { await api.put(`/outputs/${editingOutput.value.id}`, payload); notif.success('Updated!') }
    else { await api.post('/outputs', payload); notif.success('Added!') }
    closeModal(); fetchOutputs()
  } catch (err) { notif.error(err.response?.data?.message || 'Failed') }
}

async function deleteOutput() {
  try { await api.delete(`/outputs/${deletingOutput.value.id}`); notif.success('Deleted!'); showDelete.value = false; fetchOutputs() }
  catch (err) { notif.error(err.response?.data?.message || 'Failed') }
}

onMounted(async () => {
  await fetchOutputs()
  try { const [ss, cs, subs, ps, ys] = await Promise.all([api.get('/lookups/output_statuses'), api.get('/lookups/output_categories'), api.get('/lookups/output_subtypes'), api.get('/projects',{params:{per_page:100}}), api.get('/academic-years')]); outputStatuses.value = ss.data; outputCategories.value = cs.data; outputSubtypes.value = subs.data; projects.value = ps.data.data || ps.data; academicYears.value = ys.data } catch (e) {}
})
</script>
