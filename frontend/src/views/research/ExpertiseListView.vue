<template>
  <div class="flex flex-col gap-5 pb-6 animate-fade card">
    <!-- Header -->
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
      <div>
        <h1 class="text-xl font-bold text-slate-900 tracking-tight">Expertise Tags</h1>
        <p class="text-slate-500 font-medium mt-1">Manage keywords used to label research areas and researcher skills</p>
      </div>
      <button @click="showCreate = true" class="btn btn-primary h-11 px-6 shadow-lg shadow-blue-500/20">
        <svg class="w-4 h-4 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 4v16m8-8H4" /></svg>
        Add New Tag
      </button>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-4 gap-6">
      <div class="card p-6 bg-linear-to-br from-brand to-indigo-600 border-0 text-white shadow-xl shadow-brand/20">
        <p class="text-[10px] font-bold capitalize tracking-widest mb-1 opacity-80">Indexed Expertise</p>
        <p class="text-xl font-bold">{{ expertise.length }} Tags</p>
      </div>
      <div class="md:col-span-3 card p-2 bg-slate-50/50 flex items-center">
        <div class="w-full relative group">
          <span class="absolute left-5 top-1/2 -translate-y-1/2 text-slate-400 group-focus-within:text-brand transition-colors">
            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"><circle cx="11" cy="11" r="8"/><path d="m21 21-4.35-4.35"/></svg>
          </span>
          <input v-model="searchQuery" type="text" placeholder="Filter through academic skills & domain tags..." class="w-full bg-transparent h-14 pl-14 pr-6 text-sm font-bold text-slate-700 placeholder:text-slate-400 focus:outline-none" />
        </div>
      </div>
    </div>

    <!-- Content -->
    <div v-if="loading" class="card p-5 flex flex-col justify-center items-center gap-4">
      <div class="animate-spin rounded-full h-10 w-10 border-b-2 border-brand"></div>
      <p class="text-xs font-bold text-slate-400 capitalize tracking-widest">Synergizing tags...</p>
    </div>
    
    <div v-else-if="filteredExpertise.length === 0" class="card">
      <EmptyState icon="🧠" title="No matching expertise" :description="searchQuery ? 'No skills found matching \'' + searchQuery + '\'' : 'Expand the institutional knowledge base by adding tags.'" action-label="Add First Tag" action-icon="add" @action="showCreate = true" />
    </div>

    <div v-else class="card p-5 bg-white border-dashed border-2 border-slate-100">
      <div class="flex flex-wrap gap-4">
        <div v-for="exp in filteredExpertise" :key="exp.id" 
          class="inline-flex items-center gap-3 px-5 py-2.5 rounded-2xl bg-white border border-slate-200 shadow-sm hover:shadow-indigo-500/10 hover:border-indigo-400 hover:-translate-y-0.5 active:translate-y-0 transition-all duration-300 group cursor-default">
          <span class="text-xs font-bold text-slate-700 tracking-tight group-hover:text-indigo-600 transition-colors capitalize">{{ exp.name }}</span>
          <button @click="confirmDelete(exp)" class="w-6 h-6 rounded-lg flex items-center justify-center text-slate-300 hover:text-rose-500 hover:bg-rose-50 transition-all duration-300" title="Delete Tag">
            <i class="fas fa-times text-[10px]"></i>
          </button>
        </div>
      </div>
    </div>

    <!-- Modals -->
    <Modal :show="showCreate" title="Add New Tag" size="md" @close="showCreate = false">
      <form @submit.prevent="saveExpertise" class="space-y-6">
        <div>
          <label class="block text-[11px] text-slate-500 font-bold capitalize tracking-widest mb-2 ml-1">Tag Name *</label>
          <input v-model="form.name" type="text" required class="input h-12 font-bold focus:ring-4 focus:ring-brand/5" placeholder="e.g. Machine Learning, Biology" />
          <p class="text-[10px] text-slate-400 font-bold mt-2 ml-1 capitalize tracking-wide">Enter the name of the expertise area</p>
        </div>
        
        <div class="flex justify-end gap-3 pt-6 border-t border-slate-100">
          <button type="button" @click="showCreate = false" class="btn btn-secondary px-6">Cancel</button>
          <button type="submit" class="btn btn-primary px-5 shadow-lg shadow-blue-500/20">Save Tag</button>
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
