<template>
  <div class="flex flex-col gap-8 pb-12 animate-fade card">
    <!-- Header -->
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
      <div>
        <h1 class="text-3xl font-black text-slate-900 tracking-tight">Expertise Tags</h1>
        <p class="text-slate-500 font-medium mt-1">Manage keywords used to label research areas and researcher skills</p>
      </div>
      <button @click="showCreate = true" class="btn btn-primary h-11 px-6 shadow-lg shadow-blue-500/20">
        <svg class="w-4 h-4 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 4v16m8-8H4" /></svg>
        Add New Tag
      </button>
    </div>

    <!-- Stats & Filters -->
    <div class="grid grid-cols-1 md:grid-cols-4 gap-6">
      <div class="card p-5 bg-brand-light/20 border-brand/10">
        <p class="text-[10px] font-black text-brand uppercase tracking-widest mb-1">Total Tags</p>
        <p class="text-2xl font-black text-slate-900">{{ expertise.length }} Items</p>
      </div>
      <div class="md:col-span-3 card p-5 flex items-center">
        <div class="w-full relative group">
          <span class="absolute left-3.5 top-1/2 -translate-y-1/2 text-slate-400 group-focus-within:text-brand transition-colors">
            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><circle cx="11" cy="11" r="8"/><path d="m21 21-4.35-4.35"/></svg>
          </span>
          <input v-model="searchQuery" type="text" placeholder="Search tags..." class="input pl-11 h-12 bg-transparent border-transparent focus:bg-white" />
        </div>
      </div>
    </div>

    <!-- Content -->
    <div v-if="loading" class="card p-24 flex flex-col justify-center items-center gap-4">
      <div class="animate-spin rounded-full h-10 w-10 border-b-2 border-brand"></div>
      <p class="text-xs font-bold text-slate-400 uppercase tracking-widest">Loading...</p>
    </div>
    
    <div v-else-if="filteredExpertise.length === 0" class="card">
      <EmptyState icon="🧠" title="No tags found" :description="searchQuery ? 'No tags found matching \'' + searchQuery + '\'' : 'Add tags to help categorize research.'" action-label="Add New Tag" action-icon="add" @action="showCreate = true" />
    </div>

    <div v-else class="card p-8 bg-slate-50/30">
      <div class="flex flex-wrap gap-3">
        <div v-for="exp in filteredExpertise" :key="exp.id" 
          class="inline-flex items-center gap-2.5 px-4 py-2 rounded-2xl bg-white border border-slate-100 shadow-sm hover:shadow-md hover:border-brand/20 hover:scale-[1.02] active:scale-[0.98] transition-all duration-200 group cursor-default">
          <span class="text-sm font-black text-slate-700 tracking-tight group-hover:text-brand transition-colors">{{ exp.name }}</span>
          <button @click="confirmDelete(exp)" class="w-5 h-5 rounded-lg flex items-center justify-center text-slate-300 hover:text-red-500 hover:bg-red-50 transition-all duration-200" title="Delete Tag">
            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M6 18L18 6M6 6l12 12" /></svg>
          </button>
        </div>
      </div>
    </div>

    <!-- Modals -->
    <Modal :show="showCreate" title="Add New Tag" size="md" @close="showCreate = false">
      <form @submit.prevent="saveExpertise" class="space-y-6">
        <div>
          <label class="block text-[11px] text-slate-500 font-black uppercase tracking-widest mb-2 ml-1">Tag Name *</label>
          <input v-model="form.name" type="text" required class="input h-12 font-bold focus:ring-4 focus:ring-brand/5" placeholder="e.g. Machine Learning, Biology" />
          <p class="text-[10px] text-slate-400 font-bold mt-2 ml-1 uppercase tracking-wide">Enter the name of the expertise area</p>
        </div>
        
        <div class="flex justify-end gap-3 pt-6 border-t border-slate-100">
          <button type="button" @click="showCreate = false" class="btn btn-secondary px-6">Cancel</button>
          <button type="submit" class="btn btn-primary px-10 shadow-lg shadow-blue-500/20">Save Tag</button>
        </div>
      </form>
    </Modal>

    <ConfirmDialog :show="showDelete" title="Delete Tag" :message="'Are you sure you want to delete the \'' + (deletingExp?.name) + '\' tag? This will remove it from the system.'" confirmText="Delete Now" variant="danger" @confirm="deleteExpertise" @cancel="showDelete = false" />
  </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue'
import { useNotificationStore } from '@/stores/notification'
import api from '@/services/api'
import Modal from '@/components/Modal.vue'
import ConfirmDialog from '@/components/ConfirmDialog.vue'
import LoadingSkeleton from '@/components/LoadingSkeleton.vue'
import EmptyState from '@/components/EmptyState.vue'

const notif = useNotificationStore()
const expertise = ref([])
const loading = ref(true)
const searchQuery = ref('')
const showCreate = ref(false)
const showDelete = ref(false)
const deletingExp = ref(null)
const form = ref({ name: '' })

const filteredExpertise = computed(() => {
  if (!searchQuery.value) return expertise.value
  const q = searchQuery.value.toLowerCase()
  return expertise.value.filter(e => e.name.toLowerCase().includes(q))
})

async function fetchExpertise() {
  loading.value = true
  try {
    const { data } = await api.get('/expertise')
    expertise.value = data
  } catch (e) {
    console.error(e)
  } finally {
    loading.value = false
  }
}

function confirmDelete(e) {
  deletingExp.value = e
  showDelete.value = true
}

async function saveExpertise() {
  try {
    await api.post('/expertise', form.value)
    notif.success('Expertise tag added!')
    showCreate.value = false
    form.value.name = ''
    fetchExpertise()
  } catch (err) {
    notif.error('Failed to add tag')
  }
}

async function deleteExpertise() {
  try {
    await api.delete(`/expertise/${deletingExp.value.id}`)
    notif.success('Tag deleted')
    showDelete.value = false
    fetchExpertise()
  } catch (err) {
    notif.error('Failed to delete tag')
  }
}

onMounted(fetchExpertise)
</script>
