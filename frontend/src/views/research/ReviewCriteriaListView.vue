<template>
  <div class="flex flex-col gap-8 pb-12 animate-fade card">
    <!-- Header -->
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
      <div>
        <h1 class="text-3xl font-black text-slate-900 tracking-tight">Review Rules</h1>
        <p class="text-slate-500 font-medium mt-1">Set the rules used to score and evaluate research proposals.</p>
      </div>
      <button @click="showCreate = true" class="btn btn-primary h-11 px-6 shadow-lg shadow-blue-500/20">
        <svg class="w-4 h-4 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 4v16m8-8H4" /></svg>
        Add New Rule
      </button>
    </div>

    <!-- Content -->
    <div v-if="loading" class="card p-24 flex flex-col justify-center items-center gap-4">
      <div class="animate-spin rounded-full h-10 w-10 border-b-2 border-brand"></div>
      <p class="text-xs font-bold text-slate-400 uppercase tracking-widest">Loading rules...</p>
    </div>
    
    <div v-else-if="criteria.length === 0" class="card">
      <EmptyState icon="📋" title="No rules found" description="Add rules to help reviewers score research proposals." action-label="Add New Rule" @action="showCreate = true" />
    </div>

    <div v-else class="grid grid-cols-1 lg:grid-cols-2 gap-6">
      <div v-for="criterion in criteria" :key="criterion.id" class="card p-6 group card-hover relative border-l-4 border-l-transparent hover:border-l-brand transition-all flex flex-col">
        <div class="flex items-start justify-between gap-4 mb-4">
          <div class="flex-1 min-w-0">
            <div class="flex items-center gap-3 mb-2.5">
              <h3 class="text-lg font-black text-slate-900 group-hover:text-brand transition-colors leading-tight truncate">{{ criterion.name }}</h3>
              <span class="px-2 py-0.5 rounded-lg text-[9px] font-black uppercase tracking-widest border transition-colors" 
                    :class="criterion.is_active ? 'bg-emerald-50 text-emerald-600 border-emerald-100' : 'bg-slate-50 text-slate-400 border-slate-100'">
                {{ criterion.is_active ? 'Active' : 'Inactive' }}
              </span>
            </div>
            <p class="text-sm text-slate-500 font-medium leading-relaxed line-clamp-2">{{ criterion.description || 'No extra information for this rule.' }}</p>
          </div>
          <div class="w-16 h-16 rounded-2xl bg-slate-50 border border-slate-100 flex flex-col items-center justify-center shrink-0 shadow-sm">
            <span class="text-[9px] font-black text-slate-400 uppercase tracking-tighter mb-0.5">Max Score</span>
            <span class="text-xl font-black text-slate-900">{{ criterion.max_score }}</span>
          </div>
        </div>

        <div class="flex items-center justify-end gap-3 pt-5 border-t border-slate-50 mt-auto bg-slate-50/30 -mx-6 -mb-6 px-6 py-4 rounded-b-2xl">
          <button @click="editCriterion(criterion)" class="btn btn-ghost bg-white hover:bg-brand-light hover:text-brand text-[11px] font-black uppercase tracking-widest py-2 px-4 shadow-sm border border-slate-100">Edit</button>
          <button @click="confirmDelete(criterion)" class="btn btn-ghost text-red-400 hover:bg-red-50 hover:text-red-600 text-[11px] font-black uppercase tracking-widest py-2 px-4 transition-colors">Delete</button>
        </div>
      </div>
    </div>

    <!-- Create/Edit Modal -->
    <Modal :show="showCreate || !!editingCriterion" :title="editingCriterion ? 'Edit Rule' : 'Add Rule'" size="lg" @close="closeModal">
      <form @submit.prevent="saveCriterion" class="space-y-6">
        <div>
          <label class="block text-[11px] text-slate-500 font-black uppercase tracking-widest mb-2 ml-1">Rule Name *</label>
          <input v-model="form.name" type="text" required class="input h-12 font-bold" placeholder="e.g. Methodology, Impact" />
        </div>
        <div>
          <label class="block text-[11px] text-slate-500 font-black uppercase tracking-widest mb-2 ml-1">How to Score</label>
          <textarea v-model="form.description" rows="3" class="input resize-none pt-3" placeholder="Describe how reviewers should score this rule..."></textarea>
        </div>
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-6">
          <div>
            <label class="block text-[11px] text-slate-500 font-black uppercase tracking-widest mb-2 ml-1">Max Points *</label>
            <input v-model.number="form.max_score" type="number" required min="1" max="100" class="input h-12 font-black" />
          </div>
          <div class="flex flex-col">
            <label class="block text-[11px] text-slate-500 font-black uppercase tracking-widest mb-2 ml-1">Status</label>
            <label class="flex items-center h-12 gap-3 cursor-pointer p-4 bg-slate-50 rounded-xl border border-slate-100 hover:border-brand/20 transition-all">
              <input type="checkbox" v-model="form.is_active" class="w-5 h-5 rounded-lg border-slate-300 text-brand focus:ring-brand/10" />
              <span class="text-xs font-black text-slate-700 uppercase tracking-widest">Rule is Active</span>
            </label>
          </div>
        </div>
        <div class="flex justify-end gap-3 pt-6 mt-6 border-t border-slate-100">
          <button type="button" @click="closeModal" class="btn btn-secondary px-6">Cancel</button>
          <button type="submit" class="btn btn-primary px-10 shadow-lg shadow-blue-500/20">{{ editingCriterion ? 'Save Changes' : 'Save Rule' }}</button>
        </div>
      </form>
    </Modal>

    <ConfirmDialog :show="showDelete" title="Delete Rule" :message="'Are you sure you want to delete the \'' + (deletingCriterion?.name) + '\' rule? This may affect existing reviews.'" confirmText="Delete Now" variant="danger" @confirm="deleteCriterion" @cancel="showDelete = false" />
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
const criteria = ref([]); const loading = ref(true)
const showCreate = ref(false); const editingCriterion = ref(null); const showDelete = ref(false); const deletingCriterion = ref(null)
const form = reactive({ name: '', description: '', max_score: 10, is_active: true })

async function fetchCriteria() {
  loading.value = true
  try { const { data } = await api.get('/review-criteria'); criteria.value = data }
  catch (e) {} finally { loading.value = false }
}

function editCriterion(c) { editingCriterion.value = c; Object.assign(form, { name: c.name, description: c.description || '', max_score: c.max_score, is_active: c.is_active }) }
function closeModal() { showCreate.value = false; editingCriterion.value = null; Object.assign(form, { name: '', description: '', max_score: 10, is_active: true }) }
function confirmDelete(c) { deletingCriterion.value = c; showDelete.value = true }

async function saveCriterion() {
  try {
    if (editingCriterion.value) { await api.put(`/review-criteria/${editingCriterion.value.id}`, form); notif.success('Updated!') }
    else { await api.post('/review-criteria', form); notif.success('Added!') }
    closeModal(); fetchCriteria()
  } catch (err) { notif.error('Failed') }
}

async function deleteCriterion() {
  try { await api.delete(`/review-criteria/${deletingCriterion.value.id}`); notif.success('Deleted!'); showDelete.value = false; fetchCriteria() }
  catch (err) { notif.error('Failed') }
}

onMounted(fetchCriteria)
</script>
