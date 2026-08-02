<template>
  <div class="flex flex-col gap-5 pb-6 animate-fade card">
    <!-- Header -->
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
      <div>
        <h1 class="text-xl font-bold text-slate-900 tracking-tight">Research Centers</h1>
        <p class="text-slate-500 font-medium mt-1">Manage research institutes, labs, and hubs.</p>
      </div>
      <button v-if="canManageCenters" @click="openCreate" class="btn btn-primary h-11 px-6">
        <svg class="w-4 h-4 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 4v16m8-8H4" /></svg>
        Add New Center
      </button>
    </div>

    <!-- Content -->
    <div v-if="loading" class="card p-8 flex flex-col justify-center items-center gap-4">
      <div class="animate-spin rounded-full h-10 w-10 border-b-2 border-brand"></div>
      <p class="text-xs font-medium text-slate-400">Loading centers...</p>
    </div>
    
    <div v-else-if="centers.length === 0" class="card">
       <EmptyState icon="🔬" title="No centers found" description="Add research centers to organize projects and budget." action-label="Add First Center" :show-action="canManageCenters" @action="openCreate" />
    </div>

    <div v-else class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-6">
      <div v-for="center in centers" :key="center.id" class="card p-8 flex flex-col group card-hover relative overflow-hidden transition-all">
        <div class="flex items-start justify-between mb-4">
          <div class="flex-1 pr-4 min-w-0">
            <h3 class="text-base font-bold text-slate-900 leading-tight group-hover:text-brand transition-colors line-clamp-2 min-h-10">{{ center.name }}</h3>
            <div class="flex items-center gap-2 mt-2">
              <span class="px-2 py-0.5 bg-slate-100 text-slate-500 text-xs font-medium rounded-md border border-slate-100">
                CODE: {{ center.code }}
              </span>
            </div>
          </div>
          <div class="w-12 h-12 bg-slate-50 rounded-2xl flex items-center justify-center overflow-hidden shrink-0 border border-slate-100 shadow-sm">
             <img v-if="imageUrl(center.logo_file)" :src="imageUrl(center.logo_file)" class="w-full h-full object-contain" />
             <span v-else class="text-brand font-bold text-sm">{{ center.code?.substring(0,3) || 'RC' }}</span>
          </div>
        </div>
        
        <p class="text-sm text-slate-500 font-medium flex-1 line-clamp-2 leading-relaxed mb-6">{{ center.description || 'Institutional research hub for advanced academic pursuit.' }}</p>
        
        <div class="flex flex-col gap-2 text-xs font-medium text-slate-400 mb-6 pt-5 border-t border-slate-100">
          <div class="flex items-center gap-1.5" v-if="center.university">
            <i class="fas fa-university text-brand/60"></i>
            <span class="text-slate-800">{{ center.university.name }}</span>
          </div>
          <div class="flex items-center gap-1.5" v-if="center.campus || center.faculty || center.department">
            <i class="fas fa-map-marker-alt text-slate-300"></i>
            <span class="truncate">
              {{ center.campus?.name || 'Main' }} 
              <template v-if="center.faculty">/ {{ center.faculty?.name }}</template>
              <template v-if="center.department">/ {{ center.department?.name }}</template>
            </span>
          </div>
        </div>

        <div v-if="canManageCenters" class="flex items-center justify-between bg-slate-50/50 p-1 gap-1" style="border-radius: 1rem">
          <button @click="openSetUpAdmin(center)" class="btn btn-ghost border border-brand/20 text-brand hover:bg-brand hover:text-white flex-1 justify-center text-xs font-bold py-2">
            Set Up Admin
          </button>
          <ActionMenu :actions="[
            { key: 'edit', label: 'Edit', handler: () => editCenter(center) },
            { separator: true },
            { key: 'delete', label: 'Delete', handler: () => confirmDelete(center) }
          ]" />
        </div>
      </div>
    </div>

    <!-- Modals -->
    <Modal :show="showCreate || !!editingCenter" :title="editingCenter ? 'Edit Center' : 'Add New Center'" size="lg" @close="closeModal">
      <form @submit.prevent="saveCenter" class="space-y-6">
        <div>
          <label class="block text-xs text-slate-500 font-medium mb-2 ml-1">Name *</label>
          <input v-model="form.name" type="text" required class="input h-12 font-bold" placeholder="e.g. Center for AI" />
        </div>
        
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-6">
          <div>
            <label class="block text-xs text-slate-500 font-medium mb-2 ml-1">Code *</label>
            <input v-model="form.code" type="text" required class="input h-12 font-bold" placeholder="e.g. CAIR-01" />
          </div>
        </div>
        <div class="rounded-2xl border border-slate-200 bg-slate-50/60 p-5">
          <div class="mb-4">
            <h3 class="text-sm font-bold text-slate-800">Institutional placement</h3>
            <p class="mt-1 text-xs text-slate-500">Choose the most specific level that owns this center.</p>
          </div>
          <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
          <div>
            <label class="block text-xs font-semibold text-slate-400 mb-2 ml-1">University *</label>
            <select v-model="form.parent_university_id" required class="input h-11 font-bold text-xs" :disabled="editingCenter || universities.length === 1" @change="onUniversityChange">
              <option value="" disabled>Select university</option>
              <option v-for="u in universities" :key="u.id" :value="u.id">{{ u.name }}</option>
            </select>
          </div>
          <div>
            <label class="block text-xs font-semibold text-slate-400 mb-2 ml-1">Campus</label>
            <select v-model="form.parent_campus_id" class="input h-11 font-bold text-xs" :disabled="editingCenter || !form.parent_university_id" @change="onCampusChange">
              <option value="">University Wide</option>
              <option v-for="c in campuses" :key="c.id" :value="c.id">{{ c.name }}</option>
            </select>
          </div>
          <div>
            <label class="block text-xs font-semibold text-slate-400 mb-2 ml-1">Faculty</label>
            <select v-model="form.parent_faculty_id" class="input h-11 font-bold text-xs" :disabled="editingCenter || !form.parent_campus_id" @change="onFacultyChange">
              <option value="">Campus Wide</option>
              <option v-for="f in faculties" :key="f.id" :value="f.id">{{ f.name }}</option>
            </select>
          </div>
          <div>
            <label class="block text-xs font-semibold text-slate-400 mb-2 ml-1">Department</label>
            <select v-model="form.parent_department_id" class="input h-11 font-bold text-xs" :disabled="editingCenter || !form.parent_faculty_id">
              <option value="">Faculty Wide</option>
              <option v-for="d in departments" :key="d.id" :value="d.id">{{ d.name }}</option>
            </select>
          </div>
        </div>
          <p v-if="editingCenter" class="mt-3 text-xs font-medium text-amber-700">Institutional placement cannot be changed after creation.</p>
        </div>
        
        <div>
          <label class="block text-xs text-slate-500 font-medium mb-2 ml-1">Center Description</label>
          <textarea v-model="form.description" rows="3" class="input resize-none pt-3" placeholder="Tell us more about this center..."></textarea>
        </div>

        <div>
          <label class="block text-xs font-bold text-slate-900  tracking-widest mb-3 ml-1">Branding</label>
          <div class="flex items-center gap-6 p-6 bg-slate-50/50 rounded-3xl border border-dashed border-slate-200">
            <div class="w-20 h-20 rounded-2xl bg-white border border-slate-100 flex items-center justify-center overflow-hidden shrink-0 shadow-sm transition-transform hover:scale-105">
               <img v-if="logoPreviewUrl || imageUrl(form.logo_file)" :src="logoPreviewUrl || imageUrl(form.logo_file)" class="w-full h-full object-contain" />
               <div v-else class="flex flex-col items-center gap-1 opacity-20">
                  <i class="fas fa-microscope text-xl"></i>
                  <span class="text-xs font-bold tracking-tighter">NO LOGO</span>
               </div>
            </div>
            <div class="flex-1">
              <input type="file" accept="image/png,image/jpeg,image/webp" class="hidden" id="center-logo-input" @change="selectLogo" />
              <label for="center-logo-input" class="bg-white hover:bg-slate-50 text-slate-700 px-5 py-2.5 rounded-xl border border-slate-200 shadow-sm text-xs font-bold  tracking-widest cursor-pointer transition-all active:scale-95 inline-block">
                 {{ logoFile ? 'Change Selected Logo' : 'Select Center Logo' }}
              </label>
              <p class="text-[10px] text-slate-400 mt-3 font-medium">PNG, JPG or WebP, up to 5 MB. Upload begins only when you save.</p>
            </div>
          </div>
        </div>

        <div class="flex justify-end gap-3 pt-6 mt-6 border-t border-slate-100">
          <button type="button" @click="closeModal" class="btn btn-secondary px-6">Cancel</button>
          <button type="submit" class="btn btn-primary px-5" :disabled="savingCenter">{{ savingCenter ? 'Saving...' : (editingCenter ? 'Save Changes' : 'Save Center') }}</button>
        </div>
      </form>
    </Modal>

    <ConfirmDialog :show="showDelete" title="Delete Center" :message="'Are you sure you want to delete \'' + (deletingCenter?.name) + '\'?'" confirmText="Delete" variant="danger" @confirm="deleteCenter" @cancel="showDelete = false" />

    <!-- Set Up Admin Modal -->
    <Modal :show="showSetUpAdmin" title="Assign Center Director" @close="showSetUpAdmin = false">
      <form @submit.prevent="saveAdmin" class="space-y-6 p-1">
        <div class="p-4 bg-brand/5 border border-brand/10 rounded-2xl mb-4">
           <p class="text-xs text-brand font-bold tracking-wide mb-1">Target Research Center</p>
           <h4 class="text-lg font-bold text-slate-800">{{ targetCenter?.name }}</h4>
        </div>

        <div class="space-y-4">
          <div class="space-y-1.5">
            <label class="block text-xs font-bold text-slate-500 ml-1">Director Full Name *</label>
            <input v-model="adminForm.name" type="text" required placeholder="e.g. Dr. Jane Smith" class="input h-12 font-medium" />
          </div>
          <div class="space-y-1.5">
            <label class="block text-xs font-bold text-slate-500 ml-1">Admin Email *</label>
            <input v-model="adminForm.email" type="email" required placeholder="admin@center.edu" class="input h-12 font-medium" />
          </div>
          <div class="p-3 bg-brand/5 border border-brand/10 rounded-xl mb-2 flex items-start gap-2.5">
            <svg class="w-4 h-4 text-brand mt-0.5 shrink-0" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
            <p class="text-xs text-brand leading-relaxed font-semibold">
              No password required. A secure activation email will be sent automatically to the new administrator to set their password.
            </p>
          </div>
        </div>

        <div class="p-4 bg-slate-50 rounded-2xl">
          <p class="text-xs text-slate-500 leading-relaxed">
            This user will be granted the <strong>director</strong> role. They will have full control over center-specific data, including proposals, projects, and thematic areas.
          </p>
        </div>

        <div class="flex justify-end gap-3 pt-6 border-t border-slate-100">
          <button type="button" @click="showSetUpAdmin = false" class="btn btn-secondary h-11 px-6">Discard</button>
          <button type="submit" class="btn btn-primary h-11 px-6" :disabled="submittingAdmin">
            {{ submittingAdmin ? 'Creating...' : 'Create & Assign Role' }}
          </button>
        </div>
      </form>
    </Modal>
  </div>
</template>

<script setup>
import { ref, reactive, computed, onMounted, onBeforeUnmount } from 'vue'
import { useAuthStore } from '@/stores/auth'
import { useNotificationStore } from '@/stores/notification'
import { imageUrl } from '@/utils/formatters'
import api from '@/services/api'
import Modal from '@/components/Modal.vue'
import ConfirmDialog from '@/components/ConfirmDialog.vue'
import LoadingSkeleton from '@/components/LoadingSkeleton.vue'
import EmptyState from '@/components/EmptyState.vue'
import ActionMenu from '@/components/ActionMenu.vue'

const auth = useAuthStore()
const notif = useNotificationStore()
const centers = ref([]); const loading = ref(true)
const showCreate = ref(false); const editingCenter = ref(null); const showDelete = ref(false); const deletingCenter = ref(null)
const savingCenter = ref(false)
const canManageCenters = computed(() => !auth.hasRole('super_admin') && auth.hasPermission('research_center.create'))

// Admin setup state
const showSetUpAdmin = ref(false); const targetCenter = ref(null); const submittingAdmin = ref(false)
const adminForm = reactive({ name: '', email: '' })

// Hierarchy data
const universities = ref([])
const allCampuses = ref([])
const allFaculties = ref([])
const allDepartments = ref([])

// Form state
const form = reactive({ 
  name: '', code: '', description: '', 
  parent_university_id: '', parent_campus_id: '', parent_faculty_id: '', parent_department_id: '',
  logo_file_id: null, logo_file: null 
})
const logoFile = ref(null)
const logoPreviewUrl = ref('')

// Filtered lists based on parent selection
const campuses = computed(() => {
  if (!form.parent_university_id) return []
  return allCampuses.value.filter(c => String(c.university_id) === String(form.parent_university_id))
})

const faculties = computed(() => {
  if (!form.parent_campus_id) return []
  return allFaculties.value.filter(f => String(f.campus_id) === String(form.parent_campus_id))
})

const departments = computed(() => {
  if (!form.parent_faculty_id) return []
  return allDepartments.value.filter(d => String(d.faculty_id) === String(form.parent_faculty_id))
})

function onUniversityChange() {
  form.parent_campus_id = ''
  form.parent_faculty_id = ''
  form.parent_department_id = ''
}

function onCampusChange() {
  form.parent_faculty_id = ''
  form.parent_department_id = ''
}

function onFacultyChange() {
  form.parent_department_id = ''
}

async function fetchCenters() {
  loading.value = true
  try {
    const [centersRes, optionsRes] = await Promise.all([
      api.get('/management/research-centers'),
      api.get('/management/research-centers/options')
    ])
    
    centers.value = (centersRes.data.data || centersRes.data).map(c => ({
      ...c,
      logo_file: c.logo_file || c.logoFile
    }))
    universities.value = optionsRes.data.universities || []
    allCampuses.value = optionsRes.data.campuses || []
    allFaculties.value = optionsRes.data.faculties || []
    allDepartments.value = optionsRes.data.departments || []
  } catch (err) {
    notif.error(err.response?.data?.message || 'Failed to load research centers and hierarchy data.')
    console.error('Failed to load research centers:', err)
  } finally {
    loading.value = false
  }
}

function openCreate() {
  closeModal()
  form.parent_university_id = universities.value.length === 1 ? universities.value[0].id : ''
  showCreate.value = true
}

function clearLogoSelection() {
  if (logoPreviewUrl.value) URL.revokeObjectURL(logoPreviewUrl.value)
  logoPreviewUrl.value = ''
  logoFile.value = null
}

function selectLogo(event) {
  const file = event.target.files?.[0]
  if (!file) return

  if (!['image/jpeg', 'image/png', 'image/webp'].includes(file.type)) {
    notif.error('Logo must be a PNG, JPG, or WebP image.')
    event.target.value = ''
    return
  }

  if (file.size > 5 * 1024 * 1024) {
    notif.error('Logo must not exceed 5 MB.')
    event.target.value = ''
    return
  }

  clearLogoSelection()
  logoFile.value = file
  logoPreviewUrl.value = URL.createObjectURL(file)
  event.target.value = ''
}

function editCenter(c) { 
  editingCenter.value = c; 
  Object.assign(form, { 
    name: c.name, 
    code: c.code, 
    description: c.description || '', 
    parent_university_id: c.parent_university_id || '', 
    parent_campus_id: c.parent_campus_id || '',
    parent_faculty_id: c.parent_faculty_id || '',
    parent_department_id: c.parent_department_id || '',
    logo_file_id: c.logo_file_id || null,
    logo_file: c.logo_file || null
  }) 
}

function closeModal() { 
  clearLogoSelection()
  showCreate.value = false; 
  editingCenter.value = null; 
  Object.assign(form, { 
    name: '', code: '', description: '', 
    parent_university_id: '', parent_campus_id: '', parent_faculty_id: '', parent_department_id: '',
    logo_file_id: null, logo_file: null
  }) 
}

function confirmDelete(c) { deletingCenter.value = c; showDelete.value = true }

async function saveCenter() {
  savingCenter.value = true
  try {
    const payload = new FormData()
    payload.append('name', form.name)
    payload.append('code', form.code)
    payload.append('description', form.description || '')
    if (logoFile.value) payload.append('logo', logoFile.value)

    if (editingCenter.value) {
      // PHP reliably parses multipart bodies sent as POST; Laravel handles the
      // method override and dispatches this to the update action.
      payload.append('_method', 'PUT')
      await api.post(`/research-centers/${editingCenter.value.id}`, payload)
      notif.success('Updated!')
    } else {
      payload.append('parent_university_id', form.parent_university_id)
      if (form.parent_campus_id) payload.append('parent_campus_id', form.parent_campus_id)
      if (form.parent_faculty_id) payload.append('parent_faculty_id', form.parent_faculty_id)
      if (form.parent_department_id) payload.append('parent_department_id', form.parent_department_id)
      await api.post('/research-centers', payload)
      notif.success('Created!')
    }

    closeModal(); fetchCenters()
  } catch (err) {
    const firstError = Object.values(err.response?.data?.errors || {}).flat()[0]
    notif.error(firstError || err.response?.data?.message || 'Failed to save research center.')
  } finally {
    savingCenter.value = false
  }
}

async function deleteCenter() {
  try { await api.delete(`/research-centers/${deletingCenter.value.id}`); notif.success('Deleted!'); showDelete.value = false; fetchCenters() }
  catch (err) { notif.error('Failed') }
}

function openSetUpAdmin(center) {
  targetCenter.value = center
  showSetUpAdmin.value = true
  Object.assign(adminForm, { name: '', email: '' })
}

async function saveAdmin() {
  submittingAdmin.value = true
  try {
    let userId = null
    // 1. Create User
    try {
      const res = await api.post('/users', {
        name: adminForm.name,
        email: adminForm.email,
        university_id: targetCenter.value.parent_university_id,
        is_active: true
      })
      userId = res.data.id
    } catch (createErr) {
      const errors = createErr.response?.data?.errors
      if (errors?.email) {
        const { data: usersData } = await api.get('/users', { params: { search: adminForm.email } })
        const existing = (usersData.data || usersData).find(u => u.email === adminForm.email)
        if (!existing) throw new Error('Existing user email conflict, but user not found.')
        userId = existing.id
        notif.info(`Using existing account for ${existing.name}`)
      } else throw createErr
    }

    // 2. Assign to Research Center (Update user model)
    await api.put(`/users/${userId}`, { 
      research_center_id: targetCenter.value.id,
      university_id: targetCenter.value.parent_university_id
    })

    // 3. Assign Role (director)
    const { data: roles } = await api.get('/roles')
    const directorRole = roles.find(r => r.name === 'director')
    if (directorRole) {
      await api.post(`/users/${userId}/roles`, { role_id: directorRole.id })
    }

    notif.success(`Director assigned to ${targetCenter.value.name}`)
    showSetUpAdmin.value = false
  } catch (err) {
    notif.error(err.response?.data?.message || err.message || 'Failed to setup admin')
  } finally {
    submittingAdmin.value = false
  }
}

onMounted(() => fetchCenters())
onBeforeUnmount(clearLogoSelection)
</script>

<style scoped>
.card { transition: all 150ms ease; background: #fff; border: 1px solid #e8ecf1; box-shadow: 0 1px 3px rgba(0,0,0,0.04); border-radius: 1rem; }
.btn { display: inline-flex; align-items: center; justify-content: center; transition: all 150ms ease; border-radius: 1rem; }
.btn:active { transform: scale(0.95); }
.btn:disabled { opacity: 0.5; }
</style>
