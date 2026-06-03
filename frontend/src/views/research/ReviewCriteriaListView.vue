<template>
  <div class="flex flex-col gap-5 pb-6 animate-fade card">
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
      <div>
        <h1 class="text-xl font-bold text-slate-900 tracking-tight">Review Criteria</h1>
        <p class="text-slate-500 font-medium mt-1">Define evaluation metrics and scoring weights for research proposals.</p>
      </div>
      <button @click="showAdd = true" class="btn btn-primary h-11 px-5 text-[11px] font-bold capitalize tracking-widest shadow-lg shadow-blue-500/20">
        Add Criterion
      </button>
    </div>

    <div v-if="loading" class="card p-5 flex justify-center"><div class="animate-spin rounded-full h-10 w-10 border-b-2 border-brand"></div></div>
    
    <div v-else class="grid grid-cols-1 lg:grid-cols-2 gap-6">
      <div v-for="c in criteria" :key="c.id" class="card p-6 flex flex-col group card-hover relative overflow-hidden border-l-4 border-l-brand hover:border-l-indigo-600 transition-all">
        <div class="flex items-start justify-between mb-4">
          <div class="flex-1 pr-4 min-w-0">
            <h3 class="text-base font-bold text-slate-800 leading-tight group-hover:text-brand transition-colors line-clamp-2 min-h-10">{{ c.name }}</h3>
            <div class="flex items-center gap-2 mt-2">
              <span class="px-2.5 py-0.5 bg-emerald-50 text-emerald-600 border border-emerald-100 text-[10px] font-bold capitalize tracking-widest rounded-md">
                MAX SCORE: {{ c.max_score }}
              </span>
            </div>
          </div>
          <div class="w-12 h-12 rounded-2xl bg-linear-to-br from-brand to-indigo-600 text-white flex items-center justify-center font-bold shadow-lg shadow-brand/30 shrink-0 capitalize text-xs">
             <i class="fas fa-check-double scale-110"></i>
          </div>
        </div>
        
        <p class="text-sm text-slate-500 font-medium flex-1 line-clamp-2 leading-relaxed mb-6">{{ c.description || 'Grading metric for evaluate research quality and feasibility.' }}</p>
        
        <div class="flex items-center justify-between bg-slate-50/50 rounded-xl p-1 gap-1">
          <button @click="editItem(c)" class="btn btn-ghost bg-white hover:bg-brand-light hover:text-brand flex-1 justify-center text-[10px] font-bold capitalize tracking-widest py-2 shadow-xs">Edit</button>
          <button @click="confirmDelete(c)" class="btn btn-ghost text-rose-500 hover:bg-rose-50 flex-1 justify-center text-[10px] font-bold capitalize tracking-widest py-2">Delete</button>
        </div>
      </div>
    </div>

    <!-- Modals -->
    <Modal :show="showAdd || !!editingItem" :title="editingItem ? 'Edit Criterion' : 'Add Criterion'" @close="closeModal">
      <form @submit.prevent="saveItem" class="space-y-6">
        <div>
          <label class="block text-[11px] text-slate-500 font-bold capitalize tracking-widest mb-2 ml-1">Criterion Name *</label>
          <input v-model="form.name" type="text" required class="input h-12 font-bold" placeholder="e.g. Scientific Innovation" />
        </div>
        <div>
          <label class="block text-[11px] text-slate-500 font-bold capitalize tracking-widest mb-2 ml-1">Max Score (Weight) *</label>
          <input v-model.number="form.max_score" type="number" required class="input h-12 font-bold" placeholder="e.g. 20" />
        </div>
        <div>
          <label class="block text-[11px] text-slate-500 font-bold capitalize tracking-widest mb-2 ml-1">Description</label>
          <textarea v-model="form.description" rows="3" class="input pt-3 font-medium" placeholder="Evaluation guidance for reviewers..."></textarea>
        </div>
        
        <div class="flex justify-end gap-3 pt-6 border-t border-slate-100">
           <button type="button" @click="closeModal" class="btn btn-secondary px-6">Cancel</button>
           <button type="submit" class="btn btn-primary px-5 shadow-lg shadow-blue-500/20">Save Criterion</button>
        </div>
      </form>
    </Modal>

    <ConfirmDialog :show="showDelete" title="Permanently Delete" message="This will remove this criterion from all future review forms." confirmText="Delete" variant="danger" @confirm="deleteItem" @cancel="showDelete = false" />
  </div>
</template>

<script setup>
import { ref, reactive, onMounted } from 'vue'
import { useNotificationStore } from '@/stores/notification'
import api from '@/services/api'
import Modal from '@/components/Modal.vue'
import ConfirmDialog from '@/components/ConfirmDialog.vue'

const notif = useNotificationStore()
const criteria = ref([]); const loading = ref(true)
const showAdd = ref(false); const editingItem = ref(null); const showDelete = ref(false); const deletingItem = ref(null)
const form = reactive({ name: '', max_score: 5, description: '' })

async function fetchCriteria() {
  loading.value = true
  try {
    const { data } = await api.get('/review-criteria')
    criteria.value = data.data || data
  } catch (e) {} finally { loading.value = false }
}

function closeModal() {
  showAdd.value = false; editingItem.value = null
  Object.assign(form, { name: '', max_score: 5, description: '' })
}

function editItem(item) {
  editingItem.value = item
  Object.assign(form, { name: item.name, max_score: item.max_score, description: item.description })
}

function confirmDelete(item) {
  deletingItem.value = item
  showDelete.value = true
}

async function saveItem() {
  try {
    if (editingItem.value) {
      await api.put(`/review-criteria/${editingItem.value.id}`, form)
      notif.success('Criterion updated!')
    } else {
      await api.post('/review-criteria', form)
      notif.success('Criterion added!')
    }
    closeModal(); fetchCriteria()
  } catch (err) {
    notif.error('Failed to save criterion')
  }
}

async function deleteItem() {
  try {
    await api.delete(`/review-criteria/${deletingItem.value.id}`)
    notif.success('Criterion removed!')
    showDelete.value = false; fetchCriteria()
  } catch (err) {
    notif.error('Failed to delete')
  }
}

onMounted(fetchCriteria)
</script>
