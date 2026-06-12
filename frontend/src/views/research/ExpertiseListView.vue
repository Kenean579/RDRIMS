<template>
  <div class="space-y-6 animate-fade pb-10">
    <!-- Header -->
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 px-1">
      <div>
        <h1 class="text-2xl font-bold text-slate-900 tracking-tighter  leading-none">Expertise Taxonomy</h1>
        <p class="text-xs font-bold text-slate-400 mt-2  tracking-widest flex items-center gap-2">
           <span class="w-1.5 h-1.5 rounded-full bg-brand animate-pulse"></span>
           Institutional keyword registry for scientific classification
        </p>
      </div>
      <button v-if="auth.hasRole('super_admin')" @click="showCreate = true" class="btn btn-primary h-14 px-8 text-xs font-bold  tracking-widest shadow-xl shadow-brand/20 flex items-center gap-3">
        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M12 4v16m8-8H4" /></svg>
        Register New Domain
      </button>
    </div>

    <!-- Stats & Search -->
    <div class="grid grid-cols-1 md:grid-cols-4 gap-6">
      <div class="card p-8 bg-slate-900 border-0 text-white shadow-2xl shadow-slate-900/10 flex flex-col justify-between overflow-hidden relative group">
        <div class="absolute -right-6 -bottom-6 w-32 h-32 bg-white/5 rounded-full group-hover:scale-110 transition-transform duration-700" style="background: rgba(255,255,255,0.05)"></div>
        <p class="text-xs font-bold mb-1 opacity-60  tracking-widest relative z-10">Active Index</p>
        <p class="text-4xl font-bold tracking-tighter relative z-10">{{ expertise.length }}<span class="text-brand">.</span></p>
      </div>
      
      <div class="md:col-span-3 card p-2 flex items-center shadow-lg shadow-slate-200/50" style="background: #fff">
        <div class="w-full relative group">
          <span class="absolute left-6 top-1/2 -translate-y-1/2 text-slate-300 group-focus-within:text-brand transition-colors">
            <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="4" stroke-linecap="round" stroke-linejoin="round"><circle cx="11" cy="11" r="8"/><path d="m21 21-4.35-4.35"/></svg>
          </span>
          <input v-model="searchQuery" type="text" placeholder="Filter institutional knowledge domains..." class="w-full bg-transparent h-14 pl-16 pr-8 text-xs font-bold  tracking-widest text-slate-700 placeholder:text-slate-300 focus:outline-none" />
        </div>
      </div>
    </div>

    <!-- Content Area -->
    <div v-if="loading" class="card p-20 flex flex-col justify-center items-center gap-6">
      <div class="w-16 h-16 border-4 border-t-brand rounded-full animate-spin" style="border-color: #f1f5f9 #f1f5f9 #f1f5f9 #0250A3"></div>
      <p class="text-xs font-bold text-slate-400  tracking-widest">Synchronizing Registry...</p>
    </div>
    
    <div v-else-if="filteredExpertise.length === 0" class="card p-20 text-center border-dashed border-2">
       <div class="w-24 h-24 flex items-center justify-center text-4xl mx-auto mb-8 shadow-inner" style="background: #f8fafc; border: 1px solid #f1f5f9; border-radius: 2rem">🧠</div>
       <h2 class="text-[13px] font-bold text-slate-900  tracking-widest mb-2">No Matching Domains</h2>
       <p class="text-xs font-bold text-slate-400  tracking-widest max-w-sm mx-auto leading-relaxed">
         The current search query does not match any registered institutional knowledge areas.
       </p>
       <button v-if="auth.hasRole('super_admin')" @click="showCreate = true" class="mt-8 btn btn-secondary h-12 px-8 text-xs font-bold  tracking-widest border border-slate-200">Reset & Add New</button>
    </div>

    <div v-else class="card p-8 backdrop-blur-xl border-dashed border-2" style="background: rgba(255,255,255,0.5); border-color: #f1f5f9">
      <div class="flex flex-wrap gap-4">
        <div v-for="exp in filteredExpertise" :key="exp.id" 
          class="inline-flex items-center gap-4 px-6 py-3.5 rounded-[1.25rem] border border-slate-200 shadow-sm hover:shadow-xl hover:shadow-brand/5 hover:border-brand/40 hover:-translate-y-1 transition-all duration-500 group cursor-default" style="background: #fff">
          <div class="w-8 h-8 flex items-center justify-center text-xs text-slate-400 group-hover:bg-brand/10 group-hover:text-brand transition-colors font-bold" style="background: #f8fafc; border-radius: 0.75rem">
            {{ exp.name.charAt(0).toUpperCase() }}
          </div>
          <span class="text-xs font-bold text-slate-900  tracking-widest group-hover:text-brand transition-colors">{{ exp.name }}</span>
          
          <div v-if="auth.hasRole('super_admin')" class="flex items-center gap-1 pl-4 ml-2 opacity-0 group-hover:opacity-100 transition-opacity translate-x-2 group-hover:translate-x-0 transition-all duration-300" style="border-left: 1px solid #f1f5f9">
            <button @click="editItem(exp)" class="w-8 h-8 rounded-xl flex items-center justify-center text-slate-400 hover:text-brand hover:bg-brand/5 transition-all" title="Edit Domain">
              <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"/></svg>
            </button>
            <button @click="confirmDelete(exp)" class="w-8 h-8 rounded-xl flex items-center justify-center text-slate-400 hover:text-rose-500 hover:bg-rose-50 transition-all" title="Retire Domain">
              <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="4" d="M6 18L18 6M6 6l12 12"/></svg>
            </button>
          </div>
        </div>
      </div>
    </div>

    <!-- Modals -->
    <Modal :show="showCreate || !!editingItem" :title="editingItem ? 'Update Knowledge Area' : 'Register New Expertise'" size="md" @close="closeModal">
      <form @submit.prevent="saveExpertise" class="space-y-8 p-4">
        <div>
          <label class="block text-xs font-bold text-slate-400  tracking-widest mb-4 ml-1">Domain Title <span class="text-rose-500">*</span></label>
          <input v-model="form.name" type="text" required class="input h-14 font-bold  tracking-widest text-slate-800" placeholder="e.g. MOLECULAR GENETICS" />
          <p class="text-xs font-bold text-slate-400 mt-4 ml-1  tracking-tighter">This keyword will be available for researcher categorization and proposal tagging.</p>
        </div>
        
        <div class="flex justify-end gap-4 pt-8 border-t border-slate-50">
          <button type="button" @click="closeModal" class="btn btn-secondary h-12 px-8 text-xs font-bold  tracking-widest" style="border: 1px solid #f1f5f9">Abort Changes</button>
          <button type="submit" :disabled="submitting" class="btn btn-primary h-12 px-10 text-xs font-bold  tracking-widest shadow-lg shadow-brand/20">
             {{ editingItem ? 'Commit Update' : 'Register Domain' }}
          </button>
        </div>
      </form>
    </Modal>

    <ConfirmDialog :show="showDelete" title="Retire Domain" :message="'Confirming the permanent retirement of \'' + (deletingExp?.name) + '\' from the institutional registry. This cannot be undone.'" confirmText="Retire Keyword" variant="danger" @confirm="deleteExpertise" @cancel="showDelete = false" />
  </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue'
import { useAuthStore } from '@/stores/auth'
import { useNotificationStore } from '@/stores/notification'
import api from '@/services/api'
import Modal from '@/components/Modal.vue'
import ConfirmDialog from '@/components/ConfirmDialog.vue'
import EmptyState from '@/components/EmptyState.vue'

const auth = useAuthStore()
const notif = useNotificationStore()
const expertise = ref([])
const loading = ref(true)
const submitting = ref(false)
const searchQuery = ref('')
const showCreate = ref(false)
const editingItem = ref(null)
const showDelete = ref(false)
const deletingExp = ref(null)
const form = ref({ name: '' })

const filteredExpertise = computed(() => {
  if (!expertise.value) return []
  if (!searchQuery.value) return expertise.value
  const q = searchQuery.value.toLowerCase()
  return expertise.value.filter(e => e.name.toLowerCase().includes(q))
})

async function fetchExpertise() {
  loading.value = true
  try {
    const { data } = await api.get('/expertise')
    expertise.value = Array.isArray(data) ? data : (data.data || [])
  } catch (e) {
    console.error(e)
  } finally {
    loading.value = false
  }
}

function editItem(item) {
  editingItem.value = item
  form.value.name = item.name
}

function closeModal() {
  showCreate.value = false
  editingItem.value = null
  form.value.name = ''
}

function confirmDelete(e) {
  deletingExp.value = e
  showDelete.value = true
}

async function saveExpertise() {
  submitting.value = true
  try {
    if (editingItem.value) {
      await api.put(`/expertise/${editingItem.value.id}`, form.value)
      notif.success('Domain updated successfully')
    } else {
      await api.post('/expertise', form.value)
      notif.success('New domain registered')
    }
    closeModal()
    fetchExpertise()
  } catch (err) {
    notif.error('Protocol synchronization failure')
  } finally {
    submitting.value = false
  }
}

async function deleteExpertise() {
  try {
    await api.delete(`/expertise/${deletingExp.value.id}`)
    notif.success('Domain retired from registry')
    showDelete.value = false
    fetchExpertise()
  } catch (err) {
    notif.error('Retirement protocol failed')
  }
}

onMounted(fetchExpertise)
</script>

<style scoped>
.card { @apply rounded-[2.5rem]; background: #fff; border: 1px solid #e8ecf1; box-shadow: 0 1px 3px rgba(0,0,0,0.04); }
.btn { @apply inline-flex items-center justify-center transition-all active:scale-95 disabled:opacity-50; border-radius: 1rem; }
.input { @apply w-full outline-none focus:ring-4 transition-all; background: #f8fafc; border: 1px solid #f1f5f9; border-radius: 1rem; padding-left: 1.25rem; padding-right: 1.25rem; box-shadow: 0 0 0 4px var(--color-brand-light); font-size: 0.875rem; }
</style>
