<template>
  <div class="flex flex-col gap-5 pb-6 animate-fade card">
    <!-- Header -->
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
      <div>
        <h1 class="text-xl font-bold text-slate-900 tracking-tight">Users</h1>
        <p class="text-slate-500 font-medium mt-1">People with access to the system.</p>
      </div>
      <button @click="showCreate = true" class="btn btn-primary h-11 px-6 shadow-lg shadow-blue-500/20">
        <svg class="w-4 h-4 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 4v16m8-8H4" /></svg>
        Add Person
      </button>
    </div>

    <!-- Filters -->
    <div class="card p-5 bg-slate-50/50">
      <div class="flex flex-col sm:flex-row gap-5 items-start">
        <div class="flex-1 w-full relative">
          <label class="block text-[11px] text-slate-500 font-bold capitalize tracking-widest mb-2 ml-1">Search</label>
          <div class="relative group">
            <span class="absolute left-3.5 top-1/2 -translate-y-1/2 text-slate-400 group-focus-within:text-brand transition-colors">
              <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><circle cx="11" cy="11" r="8"/><path d="m21 21-4.35-4.35"/></svg>
            </span>
            <input v-model="search" type="text" placeholder="Search by name or email..." class="input pl-10" @input="debounceSearch" />
          </div>
        </div>
        <div class="w-full sm:w-56">
          <label class="block text-[11px] text-slate-500 font-bold capitalize tracking-widest mb-2 ml-1">Role Filter</label>
          <select v-model="roleFilter" @change="fetchUsers(1)" class="input font-bold">
            <option value="">All Roles</option>
            <option v-for="r in roles" :key="r.id" :value="r.name">{{ r.name }}</option>
          </select>
        </div>
      </div>
    </div>

    <!-- Content -->
    <div v-if="loading" class="card p-5 flex justify-center items-center">
      <div class="animate-spin rounded-full h-10 w-10 border-b-2 border-brand"></div>
    </div>
    <div v-else-if="error" class="card p-6 text-center text-rose-500 font-bold capitalize tracking-widest text-xs">{{ error }}</div>
    <div v-else-if="!users || users.length === 0" class="card">
      <EmptyState icon="👥" title="No users found" description="Add researchers or admins to give them access." action-label="Add First Person" action-icon="add" @action="showCreate = true" />
    </div>

    <div v-else class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-6">
      <div v-for="u in users" :key="u.id" class="p-6 card flex flex-col items-center text-center group card-hover relative overflow-hidden border-t-4 border-t-brand hover:border-t-indigo-600 transition-all">
        <!-- Status Indicator -->
        <div class="absolute top-4 right-4">
           <span :class="u.is_active ? 'bg-emerald-500' : 'bg-slate-300'" class="w-2.5 h-2.5 rounded-full block shadow-sm ring-4 ring-white"></span>
        </div>

        <!-- Role Badge -->
        <div class="mb-4">
           <span class="px-2 py-0.5 bg-slate-100 text-slate-500 text-[9px] font-bold capitalize tracking-widest rounded-md border border-slate-200">
             {{ u.roles?.[0]?.name || 'Guest' }}
           </span>
        </div>

        <!-- Avatar -->
        <div class="w-20 h-20 rounded-3xl bg-linear-to-br from-slate-50 to-slate-100 flex items-center justify-center text-2xl font-bold text-slate-300 mb-4 border border-slate-100 group-hover:scale-110 transition-transform duration-500">
          {{ u.name.charAt(0) }}
        </div>

        <h3 class="text-base font-bold text-slate-800 leading-tight mb-1 group-hover:text-brand transition-colors">{{ u.name }}</h3>
        <p class="text-[10px] text-slate-400 font-bold capitalize tracking-widest mb-4 truncate w-full px-2">{{ u.email }}</p>

        <div class="w-full pt-4 border-t border-slate-50 mt-auto flex flex-col gap-2">
           <div class="flex items-center gap-1.5 justify-center mb-4">
             <i class="fas fa-building text-[10px] text-slate-300"></i>
             <span class="text-[9px] font-bold text-slate-400 capitalize tracking-widest">{{ u.department?.name || 'Central Unit' }}</span>
           </div>
           
           <div class="grid grid-cols-2 gap-2">
             <button @click="editUser(u)" class="btn btn-ghost border border-slate-200 hover:border-brand hover:text-brand text-[10px] font-bold capitalize tracking-widest py-2 rounded-xl">Profile</button>
             <button @click="confirmDelete(u)" class="btn btn-ghost border border-rose-300 hover:bg-rose-50 text-rose-500 text-[10px] font-bold capitalize tracking-widest py-2 rounded-xl">Manage</button>
           </div>
        </div>
      </div>
      
      <div class="md:col-span-2 lg:col-span-3 xl:col-span-4 px-5 py-4 bg-slate-50/50 rounded-2xl border border-slate-100 overflow-hidden mt-6">
        <Pagination :current-page="pagination.current_page" :total-pages="pagination.last_page" :total="pagination.total" @page-change="fetchUsers" />
      </div>
    </div>

    <!-- Modals -->
    <Modal :show="showCreate || !!editingUser" :title="editingUser ? 'Edit User' : 'Add New Person'" size="md" @close="closeModal">
      <form @submit.prevent="saveUser" class="space-y-6">
        <div class="grid grid-cols-1 gap-6">
           <div class="space-y-2">
              <label class="block text-[11px] text-slate-500 font-bold capitalize tracking-widest ml-1">Full Name *</label>
              <input v-model="form.name" type="text" required placeholder="e.g. John Doe" class="input h-12 font-bold" />
           </div>
           <div class="space-y-2">
              <label class="block text-[11px] text-slate-500 font-bold capitalize tracking-widest ml-1">Email Address *</label>
              <input v-model="form.email" type="email" required placeholder="e.g. john@university.edu" class="input h-12 font-bold" />
           </div>
        </div>
        
        <div class="space-y-2" v-if="!editingUser">
          <label class="block text-[11px] text-slate-500 font-bold capitalize tracking-widest ml-1">Password *</label>
          <input v-model="form.password" type="password" required minlength="8" placeholder="••••••••" class="input h-12 font-bold" />
          <p class="text-[10px] text-slate-400 font-bold capitalize tracking-widest mt-2 ml-1">Min. 8 characters</p>
        </div>

        <div class="space-y-4">
          <label class="block text-[11px] text-slate-500 font-bold capitalize tracking-widest ml-1 mb-2">Assign to Hierarchy</label>
          <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
            <div class="space-y-2">
              <label class="block text-[10px] text-slate-400 font-bold ml-1">University</label>
              <select v-model="form.university_id" class="input h-11 text-sm font-bold text-slate-700">
                <option value="">Select University</option>
                <option v-for="u in universities" :key="u.id" :value="u.id">{{ u.name }}</option>
              </select>
            </div>
            <div class="space-y-2">
              <label class="block text-[10px] text-slate-400 font-bold ml-1">Campus</label>
              <select v-model="form.campus_id" class="input h-11 text-sm font-bold text-slate-700" :disabled="!form.university_id">
                <option value="">Select Campus</option>
                <option v-for="c in filteredCampuses" :key="c.id" :value="c.id">{{ c.name }}</option>
              </select>
            </div>
            <div class="space-y-2">
              <label class="block text-[10px] text-slate-400 font-bold ml-1">Faculty / College</label>
              <select v-model="form.faculty_id" class="input h-11 text-sm font-bold text-slate-700" :disabled="!form.campus_id">
                <option value="">Select Faculty</option>
                <option v-for="f in filteredFaculties" :key="f.id" :value="f.id">{{ f.name }}</option>
              </select>
            </div>
            <div class="space-y-2">
              <label class="block text-[10px] text-slate-400 font-bold ml-1">Department</label>
              <select v-model="form.department_id" class="input h-11 text-sm font-bold text-slate-700" :disabled="!form.faculty_id">
                <option value="">Select Department</option>
                <option v-for="d in filteredDepartments" :key="d.id" :value="d.id">{{ d.name }}</option>
              </select>
            </div>
          </div>
        </div>

        <div class="space-y-3">
          <label class="block text-[11px] text-slate-500 font-bold capitalize tracking-widest ml-1">Roles</label>
          <div class="grid grid-cols-2 gap-3 bg-slate-50 p-4 rounded-2xl border border-slate-100 max-h-48 overflow-y-auto">
            <label v-for="r in roles" :key="r.id" class="flex items-center gap-2.5 text-xs font-bold text-slate-600 p-2.5 cursor-pointer hover:bg-white rounded-xl transition-all shadow-sm border border-transparent hover:border-slate-100">
              <input type="checkbox" :value="r.id" v-model="form.role_ids" class="w-4.5 h-4.5 rounded-lg border-slate-300 text-brand focus:ring-brand shadow-sm" />
              {{ r.name }}
            </label>
          </div>
        </div>

        <div class="flex justify-end gap-3 pt-6 mt-6 border-t border-slate-100">
          <button type="button" @click="closeModal" class="btn btn-secondary px-6 font-bold capitalize tracking-widest text-[11px]">Discard</button>
          <button type="submit" class="btn btn-primary px-5 shadow-lg shadow-blue-500/20 font-bold capitalize tracking-widest text-[11px]">
            {{ editingUser ? 'Save Changes' : 'Add Person' }}
          </button>
        </div>
      </form>
    </Modal>

    <ConfirmDialog :show="showDelete" title="Deactivate User" :message="'Are you sure you want to turn off access for ' + (deletingUser?.name) + '?'" confirmText="Deactivate" variant="danger" @confirm="deactivateUser" @cancel="showDelete = false" />
  </div>
</template>

<script setup>
import { ref, reactive, computed, watch, onMounted } from 'vue'
import { useNotificationStore } from '@/stores/notification'
import api from '@/services/api'
import Modal from '@/components/Modal.vue'
import ConfirmDialog from '@/components/ConfirmDialog.vue'
import Pagination from '@/components/Pagination.vue'
import EmptyState from '@/components/EmptyState.vue'

const notif = useNotificationStore()
const users = ref([]); const loading = ref(true); const error = ref(null)
const pagination = reactive({ current_page: 1, last_page: 1, total: 0 })
const search = ref(''); const roleFilter = ref('')
const roles = ref([]); const departments = ref([]); const universities = ref([]); const campuses = ref([]); const faculties = ref([])
const showCreate = ref(false); const editingUser = ref(null); const showDelete = ref(false); const deletingUser = ref(null)
const form = reactive({ name: '', email: '', password: '', university_id: '', campus_id: '', faculty_id: '', department_id: '', role_ids: [] })
let searchTimer = null

async function fetchUsers(page = 1) {
  loading.value = true; error.value = null
  try { const params = { page }; if (search.value) params.search = search.value; if (roleFilter.value) params.role = roleFilter.value; const { data } = await api.get('/users', { params }); users.value = data.data; Object.assign(pagination, { current_page: data.current_page, last_page: data.last_page, total: data.total }) }
  catch (err) { error.value = err.response?.data?.message || 'Failed' } finally { loading.value = false }
}
function debounceSearch() { clearTimeout(searchTimer); searchTimer = setTimeout(() => fetchUsers(1), 400) }

// Computed hierarchy
const filteredCampuses = computed(() => campuses.value.filter(c => c.university_id === form.university_id))
const filteredFaculties = computed(() => faculties.value.filter(f => f.campus_id === form.campus_id))
const filteredDepartments = computed(() => departments.value.filter(d => d.faculty_id === form.faculty_id))

// Watchers to clear children on change
watch(() => form.university_id, () => { form.campus_id = ''; form.faculty_id = ''; form.department_id = '' })
watch(() => form.campus_id, () => { form.faculty_id = ''; form.department_id = '' })
watch(() => form.faculty_id, () => { form.department_id = '' })

function editUser(u) {
  let uid = '', cid = '', fid = u.department?.faculty_id || ''
  if (u.department_id) {
    const dept = departments.value.find(d => d.id === u.department_id)
    if (dept) {
      fid = dept.faculty_id
      const fac = faculties.value.find(f => f.id === fid)
      if (fac) {
        cid = fac.campus_id
        const camp = campuses.value.find(c => c.id === cid)
        if (camp) uid = camp.university_id
      }
    }
  }
  editingUser.value = u
  Object.assign(form, { name: u.name, email: u.email, password: '', university_id: uid, campus_id: cid, faculty_id: fid, department_id: u.department_id || '', role_ids: u.roles?.map(r => r.id) || [] })
}

function closeModal() { showCreate.value = false; editingUser.value = null; Object.assign(form, { name: '', email: '', password: '', university_id: '', campus_id: '', faculty_id: '', department_id: '', role_ids: [] }) }
function confirmDelete(u) { deletingUser.value = u; showDelete.value = true }

async function saveUser() {
  try {
    const payload = { name: form.name, email: form.email, department_id: form.department_id || null }
    if (!editingUser.value) payload.password = form.password
    if (editingUser.value) { await api.put(`/users/${editingUser.value.id}`, payload); notif.success('Updated!') }
    else { const { data } = await api.post('/users', payload); if (form.role_ids.length) for (const rid of form.role_ids) await api.post(`/users/${data.id}/roles`, { role_id: rid }); notif.success('Created!') }
    closeModal(); fetchUsers()
  } catch (err) { notif.error(err.response?.data?.message || 'Failed') }
}

async function deactivateUser() {
  try { await api.delete(`/users/${deletingUser.value.id}`); notif.success('Deactivated!'); showDelete.value = false; fetchUsers() }
  catch (err) { notif.error(err.response?.data?.message || 'Failed') }
}

onMounted(async () => {
  await fetchUsers()
  try { 
    const [rr, dr, ur, cr, fr] = await Promise.all([
      api.get('/roles'), api.get('/departments'), api.get('/universities'), api.get('/campuses'), api.get('/faculties')
    ])
    roles.value = rr.data; departments.value = dr.data; universities.value = ur.data; campuses.value = cr.data; faculties.value = fr.data
  } catch (e) {}
})
</script>
