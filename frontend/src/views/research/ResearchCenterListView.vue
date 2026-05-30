<template>
  <div class="flex flex-col gap-8 pb-12 animate-fade card">
    <!-- Header -->
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
      <div>
        <h1 class="text-3xl font-black text-slate-900 tracking-tight">Research Centers</h1>
        <p class="text-slate-500 font-medium mt-1">Manage research institutes, labs, and hubs.</p>
      </div>
      <button @click="showCreate = true" class="btn btn-primary h-11 px-6 shadow-lg shadow-blue-500/20">
        <svg class="w-4 h-4 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 4v16m8-8H4" /></svg>
        Add New Center
      </button>
    </div>

    <!-- Content -->
    <div v-if="loading" class="card p-24 flex flex-col justify-center items-center gap-4">
      <div class="animate-spin rounded-full h-10 w-10 border-b-2 border-brand"></div>
      <p class="text-xs font-bold text-slate-400 uppercase tracking-widest">Loading centers...</p>
    </div>
    
    <div v-else-if="centers.length === 0" class="card">
      <EmptyState icon="🔬" title="No centers found" description="Add research centers to organize projects and budget." action-label="Add First Center" @action="showCreate = true" />
    </div>

    <div v-else class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-6">
      <div v-for="center in centers" :key="center.id" class="card p-6 flex flex-col group card-hover relative border-l-4 border-l-transparent hover:border-l-brand transition-all">
        <div class="flex items-start gap-4 mb-5">
           <div class="w-12 h-12 rounded-2xl bg-brand-light text-brand flex items-center justify-center font-black text-xs tracking-tighter shadow-sm shrink-0">{{ center.code || 'RC' }}</div>
           <div class="min-w-0">
             <h3 class="text-base font-black text-slate-900 leading-tight group-hover:text-brand transition-colors line-clamp-2">{{ center.name }}</h3>
             <p class="text-[10px] font-black text-slate-400 uppercase tracking-widest mt-1.5">Code: {{ center.code }}</p>
           </div>
        </div>
        
        <p class="text-sm text-slate-500 font-medium flex-1 line-clamp-3 leading-relaxed mb-6">{{ center.description || 'No description found for this center.' }}</p>
        
        <div class="flex flex-wrap items-center gap-4 text-[10px] font-black text-slate-400 uppercase tracking-widest mb-6 pt-5 border-t border-slate-50">
          <span class="flex items-center gap-1.5" v-if="center.director">
            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" /></svg>
            Director: {{ center.director.name }}
          </span>
          <span class="flex items-center gap-1.5">
            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" /></svg>
            University Affiliation
          </span>
        </div>

        <div class="flex items-center justify-between bg-slate-50/50 rounded-xl p-2">
          <button @click="editCenter(center)" class="btn btn-ghost hover:bg-white flex-1 justify-center text-[11px] font-black uppercase tracking-wider py-2">Edit</button>
          <div class="w-px h-4 bg-slate-200"></div>
          <button @click="confirmDelete(center)" class="btn btn-ghost text-red-500 hover:bg-red-50 flex-1 justify-center text-[11px] font-black uppercase tracking-wider py-2">Delete</button>
        </div>
      </div>
    </div>

    <!-- Modals -->
    <Modal :show="showCreate || !!editingCenter" :title="editingCenter ? 'Edit Center' : 'Add New Center'" size="lg" @close="closeModal">
      <form @submit.prevent="saveCenter" class="space-y-6">
        <div>
          <label class="block text-[11px] text-slate-500 font-black uppercase tracking-widest mb-2 ml-1">Name *</label>
          <input v-model="form.name" type="text" required class="input h-12 font-bold" placeholder="e.g. Center for AI" />
        </div>
        
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-6">
          <div>
            <label class="block text-[11px] text-slate-500 font-black uppercase tracking-widest mb-2 ml-1">Code *</label>
            <input v-model="form.code" type="text" required class="input h-12 font-black" placeholder="e.g. CAIR-01" />
          </div>
          <div>
            <label class="block text-[11px] text-slate-500 font-black uppercase tracking-widest mb-2 ml-1">University</label>
            <select v-model="form.parent_university_id" class="input h-12 font-bold">
              <option value="">Select University</option>
              <option v-for="u in universities" :key="u.id" :value="u.id">{{ u.name }}</option>
            </select>
          </div>
        </div>
        
        <div>
          <label class="block text-[11px] text-slate-500 font-black uppercase tracking-widest mb-2 ml-1">Description</label>
          <textarea v-model="form.description" rows="4" class="input resize-none pt-3" placeholder="Tell us more about this center..."></textarea>
        </div>

        <div class="flex justify-end gap-3 pt-6 mt-6 border-t border-slate-100">
          <button type="button" @click="closeModal" class="btn btn-secondary px-6">Cancel</button>
          <button type="submit" class="btn btn-primary px-10 shadow-lg shadow-blue-500/20">{{ editingCenter ? 'Save Changes' : 'Save Center' }}</button>
        </div>
      </form>
    </Modal>

    <ConfirmDialog :show="showDelete" title="Delete Center" message="Are you sure you want to delete this research center? This cannot be undone." confirmText="Delete Now" variant="danger" @confirm="deleteCenter" @cancel="showDelete = false" />
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
const centers = ref([]); const loading = ref(true); const universities = ref([])
const showCreate = ref(false); const editingCenter = ref(null); const showDelete = ref(false); const deletingCenter = ref(null)
const form = reactive({ name: '', code: '', description: '', parent_university_id: '' })

async function fetchCenters() {
  loading.value = true
  try { const { data } = await api.get('/research-centers'); centers.value = data }
  catch (e) {} finally { loading.value = false }
}

function editCenter(c) { editingCenter.value = c; Object.assign(form, { name: c.name, code: c.code, description: c.description || '', parent_university_id: c.parent_university_id || '' }) }
function closeModal() { showCreate.value = false; editingCenter.value = null; Object.assign(form, { name: '', code: '', description: '', parent_university_id: '' }) }
function confirmDelete(c) { deletingCenter.value = c; showDelete.value = true }

async function saveCenter() {
  try {
    const payload = { ...form, parent_university_id: form.parent_university_id || null }
    if (editingCenter.value) { await api.put(`/research-centers/${editingCenter.value.id}`, payload); notif.success('Updated!') }
    else { await api.post('/research-centers', payload); notif.success('Created!') }
    closeModal(); fetchCenters()
  } catch (err) { notif.error(err.response?.data?.message || 'Failed') }
}

async function deleteCenter() {
  try { await api.delete(`/research-centers/${deletingCenter.value.id}`); notif.success('Deleted!'); showDelete.value = false; fetchCenters() }
  catch (err) { notif.error('Failed') }
}

onMounted(async () => {
  await fetchCenters()
  try { const { data } = await api.get('/universities'); universities.value = data } catch (e) {}
})
</script>
