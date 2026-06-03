<template>
  <div class="flex flex-col gap-5 pb-6 animate-fade card">
    <!-- Header -->
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
      <div>
        <h1 class="text-xl font-bold text-slate-900 tracking-tight">Research Centers</h1>
        <p class="text-slate-500 font-medium mt-1">Manage research institutes, labs, and hubs.</p>
      </div>
      <button @click="showCreate = true" class="btn btn-primary h-11 px-6 shadow-lg shadow-blue-500/20">
        <svg class="w-4 h-4 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 4v16m8-8H4" /></svg>
        Add New Center
      </button>
    </div>

    <!-- Content -->
    <div v-if="loading" class="card p-5 flex flex-col justify-center items-center gap-4">
      <div class="animate-spin rounded-full h-10 w-10 border-b-2 border-brand"></div>
      <p class="text-xs font-bold text-slate-400 capitalize tracking-widest">Loading centers...</p>
    </div>
    
    <div v-else-if="centers.length === 0" class="card">
       <EmptyState icon="🔬" title="No centers found" description="Add research centers to organize projects and budget." action-label="Add First Center" @action="showCreate = true" />
    </div>

    <div v-else class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-6">
      <div v-for="center in centers" :key="center.id" class="card p-6 flex flex-col group card-hover relative overflow-hidden border-l-4 border-l-brand hover:border-l-indigo-600 transition-all">
        <div class="flex items-start justify-between mb-4">
          <div class="flex-1 pr-4 min-w-0">
            <h3 class="text-base font-bold text-slate-900 leading-tight group-hover:text-brand transition-colors line-clamp-2 min-h-10">{{ center.name }}</h3>
            <div class="flex items-center gap-2 mt-2">
              <span class="px-2 py-0.5 bg-slate-100 text-slate-500 text-[10px] font-bold capitalize tracking-widest rounded-md border border-slate-200">
                CODE: {{ center.code }}
              </span>
            </div>
          </div>
          <div class="w-12 h-12 rounded-2xl bg-linear-to-br from-brand to-indigo-600 text-white flex items-center justify-center font-bold shadow-lg shadow-brand/30 shrink-0 capitalize tracking-tighter text-xs">
             {{ center.code?.substring(0,3) || 'RC' }}
          </div>
        </div>
        
        <p class="text-sm text-slate-500 font-medium flex-1 line-clamp-2 leading-relaxed mb-6">{{ center.description || 'Institutional research hub for advanced academic pursuit.' }}</p>
        
        <div class="flex flex-col gap-2 text-[9px] font-bold text-slate-400 capitalize tracking-widest mb-6 pt-5 border-t border-slate-100">
          <div class="flex items-center gap-1.5" v-if="center.university">
            <i class="fas fa-university text-brand/60"></i>
            <span class="text-slate-800">{{ center.university.name }}</span>
          </div>
          <div class="flex items-center gap-1.5" v-if="center.campus || center.faculty">
            <i class="fas fa-map-marker-alt text-slate-300"></i>
            <span class="truncate">{{ center.campus?.name || 'Main Campus' }} <template v-if="center.faculty">/ {{ center.faculty?.name }}</template></span>
          </div>
        </div>

        <div class="flex items-center justify-between bg-slate-50/50 rounded-xl p-1 gap-1">
          <button @click="editCenter(center)" class="btn btn-ghost bg-white hover:bg-indigo-50 hover:text-indigo-600 flex-1 justify-center text-[11px] font-bold capitalize tracking-wider py-2 shadow-xs">Edit</button>
          <button @click="confirmDelete(center)" class="btn btn-ghost text-rose-500 hover:bg-rose-50 flex-1 justify-center text-[11px] font-bold capitalize tracking-wider py-2">Delete</button>
        </div>
      </div>
    </div>iv>

    <!-- Modals -->
    <Modal :show="showCreate || !!editingCenter" :title="editingCenter ? 'Edit Center' : 'Add New Center'" size="lg" @close="closeModal">
      <form @submit.prevent="saveCenter" class="space-y-6">
        <div>
          <label class="block text-[11px] text-slate-500 font-bold capitalize tracking-widest mb-2 ml-1">Name *</label>
          <input v-model="form.name" type="text" required class="input h-12 font-bold" placeholder="e.g. Center for AI" />
        </div>
        
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-6">
          <div>
            <label class="block text-[11px] text-slate-500 font-bold capitalize tracking-widest mb-2 ml-1">Code *</label>
            <input v-model="form.code" type="text" required class="input h-12 font-bold" placeholder="e.g. CAIR-01" />
          </div>
        </div>
        <div class="grid grid-cols-1 sm:grid-cols-3 gap-6">
          <div>
            <label class="block text-[11px] text-slate-500 font-bold capitalize tracking-widest mb-2 ml-1">University</label>
            <select v-model="form.parent_university_id" class="input h-12 font-bold">
              <option value="">Select University</option>
              <option v-for="u in universities" :key="u.id" :value="u.id">{{ u.name }}</option>
            </select>
          </div>
          <div>
            <label class="block text-[11px] text-slate-500 font-bold capitalize tracking-widest mb-2 ml-1">Campus</label>
            <select v-model="form.campus_id" class="input h-12 font-bold">
              <option value="">Select Campus</option>
              <option v-for="c in campuses" :key="c.id" :value="c.id">{{ c.name }}</option>
            </select>
          </div>
          <div>
            <label class="block text-[11px] text-slate-500 font-bold capitalize tracking-widest mb-2 ml-1">Faculty</label>
            <select v-model="form.faculty_id" class="input h-12 font-bold">
              <option value="">Select Faculty</option>
              <option v-for="f in faculties" :key="f.id" :value="f.id">{{ f.name }}</option>
            </select>
          </div>
        </div>
        
        <div>
          <label class="block text-[11px] text-slate-500 font-bold capitalize tracking-widest mb-2 ml-1">Description</label>
          <textarea v-model="form.description" rows="3" class="input resize-none pt-3" placeholder="Tell us more about this center..."></textarea>
        </div>

        <div class="flex justify-end gap-3 pt-6 mt-6 border-t border-slate-100">
          <button type="button" @click="closeModal" class="btn btn-secondary px-6">Cancel</button>
          <button type="submit" class="btn btn-primary px-5 shadow-lg shadow-blue-500/20">{{ editingCenter ? 'Save Changes' : 'Save Center' }}</button>
        </div>
      </form>
    </Modal>

    <ConfirmDialog :show="showDelete" title="Delete Center" message="Are you sure you want to delete this research center? This cannot be undone." confirmText="Delete Now" variant="danger" @confirm="deleteCenter" @cancel="showDelete = false" />
  </div>
</template>

<script setup>
import { ref, reactive, computed, watch, onMounted } from 'vue'
import { useNotificationStore } from '@/stores/notification'
import api from '@/services/api'
import Modal from '@/components/Modal.vue'
import ConfirmDialog from '@/components/ConfirmDialog.vue'
import LoadingSkeleton from '@/components/LoadingSkeleton.vue'
import EmptyState from '@/components/EmptyState.vue'

const notif = useNotificationStore()
const centers = ref([]); const loading = ref(true)
const showCreate = ref(false); const editingCenter = ref(null); const showDelete = ref(false); const deletingCenter = ref(null)

// Hierarchy data
const universities = ref([])
const allCampuses = ref([])
const allFaculties = ref([])

// Form state
const form = reactive({ name: '', code: '', description: '', parent_university_id: '', campus_id: '', faculty_id: '' })

// Filtered lists based on parent selection
const campuses = computed(() => {
  if (!form.parent_university_id) return []
  return allCampuses.value.filter(c => c.university_id === form.parent_university_id)
})

const faculties = computed(() => {
  if (!form.campus_id) return []
  return allFaculties.value.filter(f => f.campus_id === form.campus_id)
})

// Reset children when parents change
watch(() => form.parent_university_id, () => {
  form.campus_id = ''
  form.faculty_id = ''
})

watch(() => form.campus_id, () => {
  form.faculty_id = ''
})

async function fetchCenters() {
  loading.value = true
  try {
    const [centersRes, uniRes, campRes, facRes] = await Promise.all([
      api.get('/research-centers'),
      api.get('/academic/universities'),
      api.get('/academic/campuses'),
      api.get('/academic/faculties')
    ])
    centers.value = centersRes.data.data || centersRes.data
    universities.value = uniRes.data.data || uniRes.data
    allCampuses.value = campRes.data.data || campRes.data
    allFaculties.value = facRes.data.data || facRes.data
  } catch (err) {
    notif.error('Failed to sync hierarchy data')
  } finally {
    loading.value = false
  }
}

function editCenter(c) { 
  editingCenter.value = c; 
  Object.assign(form, { 
    name: c.name, 
    code: c.code, 
    description: c.description || '', 
    parent_university_id: c.parent_university_id || c.university_id || '', 
    campus_id: c.campus_id || '',
    faculty_id: c.faculty_id || ''
  }) 
}
function closeModal() { 
  showCreate.value = false; 
  editingCenter.value = null; 
  Object.assign(form, { name: '', code: '', description: '', parent_university_id: '', campus_id: '', faculty_id: '' }) 
}
function confirmDelete(c) { deletingCenter.value = c; showDelete.value = true }

async function saveCenter() {
  try {
    const payload = { 
      ...form, 
      university_id: form.parent_university_id || null, // Backend might use university_id
      parent_university_id: form.parent_university_id || null,
      campus_id: form.campus_id || null,
      faculty_id: form.faculty_id || null
    }
    if (editingCenter.value) { await api.put(`/research-centers/${editingCenter.value.id}`, payload); notif.success('Updated!') }
    else { await api.post('/research-centers', payload); notif.success('Created!') }
    closeModal(); fetchCenters()
  } catch (err) { notif.error(err.response?.data?.message || 'Failed') }
}

async function deleteCenter() {
  try { await api.delete(`/research-centers/${deletingCenter.value.id}`); notif.success('Deleted!'); showDelete.value = false; fetchCenters() }
  catch (err) { notif.error('Failed') }
}

onMounted(() => fetchCenters())
</script>
