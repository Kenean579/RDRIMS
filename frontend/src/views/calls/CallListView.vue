<template>
  <div class="flex flex-col gap-5 pb-6 animate-fade card">
    <!-- Header -->
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
      <div>
        <h1 class="text-xl font-bold text-slate-900 tracking-tight">Funding Calls</h1>
        <p class="text-slate-500 font-medium mt-1">Open applications for research grants.</p>
      </div>
      <div class="flex items-center gap-3">
        <button v-if="auth.hasRole('super_admin','research_admin')" @click="showCreate = true" class="btn btn-primary h-11 px-6 shadow-lg shadow-blue-500/20">
          <svg class="w-4 h-4 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 4v16m8-8H4" /></svg>
          Create Call
        </button>
        <button @click="fetchCalls" class="btn btn-secondary h-11 px-6 shadow-sm group">
          <svg class="w-4 h-4 mr-1.5 group-hover:rotate-180 transition-transform duration-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" /></svg>
          Refresh
        </button>
      </div>
    </div>

    <!-- Content -->
    <div v-if="loading" class="grid grid-cols-1 md:grid-cols-2 gap-6">
      <div v-for="i in 4" :key="i" class="card p-5 h-64 flex flex-col gap-4 bg-slate-50/50">
        <div class="h-6 w-24 bg-slate-200 rounded-lg animate-pulse"></div>
        <div class="h-8 w-3/4 bg-slate-100 rounded-lg animate-pulse"></div>
        <div class="h-24 w-full bg-slate-100/50 rounded-lg animate-pulse"></div>
      </div>
    </div>

    <div v-else-if="calls.length === 0" class="card">
      <EmptyState icon="📢" title="No calls found" description="There are currently no open calls. We will update you soon." />
    </div>

    <div v-else class="grid grid-cols-1 lg:grid-cols-2 gap-5">
      <div v-for="call in calls" :key="call.id" class="card group card-hover flex flex-col p-5 border-l-4 border-l-brand/20 hover:border-l-brand transition-all">
        <div class="flex flex-col sm:flex-row justify-between items-start gap-4 mb-6">
          <StatusBadge :status="call.status?.name || 'open'" />
          <div class="flex items-center gap-2 text-[10px] font-bold text-slate-400 capitalize tracking-widest bg-slate-100 px-3 py-1.5 rounded-xl">
            <svg class="w-4 h-4 text-slate-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" /></svg>
            Ends: {{ formatDate(call.deadline) }}
          </div>
        </div>

        <h3 class="text-xl font-bold text-slate-900 group-hover:text-brand transition-colors mb-4 leading-tight">{{ call.title }}</h3>
        <p class="text-sm text-slate-500 font-medium mb-5 flex-1 line-clamp-3 leading-relaxed">{{ call.description }}</p>

        <div class="flex items-center justify-between mt-auto pt-6 border-t border-slate-100">
          <div class="flex items-center gap-2">
            <router-link v-if="auth.hasPermission('submit_proposals')" :to="`/app/proposals/create?call_id=${call.id}`" class="btn btn-primary shadow-lg shadow-blue-500/20 px-6 text-[11px] font-bold capitalize tracking-widest h-10">
              Apply
            </router-link>
            <button @click="viewCall(call)" class="btn btn-ghost border border-slate-200 hover:border-brand hover:text-brand text-[11px] font-bold capitalize tracking-widest h-10 px-5">
              Info
            </button>
            <button v-if="auth.hasRole('super_admin','research_admin')" @click="editCall(call)" class="btn btn-ghost border border-slate-200 hover:border-amber-600 text-[11px] font-bold capitalize tracking-widest h-10 px-5 text-amber-600 hover:text-amber-700">
              Edit
            </button>
          </div>
          <div class="text-right">
             <p class="text-[9px] font-bold text-slate-400 capitalize tracking-widest mb-0.5">Award High</p>
             <p class="text-base font-bold text-brand">{{ formatCurrency(call.budget_limit) }}</p>
          </div>
        </div>
      </div>
    </div>

    <!-- Detail Modal -->
    <Modal :show="!!selectedCall && !editingCall" :title="selectedCall?.title" size="lg" @close="selectedCall = null">
      <div v-if="selectedCall" class="flex flex-col gap-5">
          <div class="p-6 rounded-2xl border border-slate-200 shadow-inner">
           <h4 class="text-[10px] font-bold text-slate-400 capitalize tracking-widest mb-3">About this call</h4>
           <p class="text-base text-slate-700 font-medium whitespace-pre-line leading-relaxed">{{ selectedCall.description }}</p>
        </div>
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6">
          <div class="p-5 bg-white rounded-2xl border border-slate-100 shadow-sm flex flex-col gap-1 text-center font-bold capitalize">
             <p class="text-[10px] text-slate-400 tracking-widest">Award High</p>
             <p class="text-lg text-brand">{{ formatCurrency(selectedCall.budget_limit) }}</p>
          </div>
          <div class="p-5 bg-white rounded-2xl border border-slate-100 shadow-sm flex flex-col gap-1 text-center font-bold capitalize">
             <p class="text-[10px] text-slate-400 tracking-widest">Ending Date</p>
             <p class="text-lg text-slate-900">{{ formatDate(selectedCall.deadline) }}</p>
          </div>
          <div class="p-5 bg-white rounded-2xl border border-slate-100 shadow-sm flex flex-col gap-1 text-center font-bold capitalize">
             <p class="text-[10px] text-slate-400 tracking-widest">Target Year</p>
             <p class="text-lg text-slate-900">{{ selectedCall.academic_year?.name || 'N/A' }}</p>
          </div>
           <div class="p-5 bg-white rounded-2xl border border-slate-100 shadow-sm flex flex-col gap-1 text-center font-bold capitalize">
             <p class="text-[10px] text-slate-400 tracking-widest">Thematic Area</p>
             <p class="text-lg text-slate-900">{{ selectedCall.thematic_areas || 'Multi-disciplinary' }}</p>
          </div>
        </div>
        <div class="pt-6 border-t border-slate-100 flex justify-end gap-3">
           <button @click="selectedCall = null" class="btn btn-secondary px-5 h-12 text-[11px] font-bold capitalize tracking-widest">Close</button>
           <router-link v-if="auth.hasPermission('submit_proposals')" :to="`/proposals/create?call_id=${selectedCall.id}`" @click="selectedCall = null" class="btn btn-primary px-5 h-12 shadow-lg shadow-blue-500/20 text-[11px] font-bold capitalize tracking-widest">
              Apply Now
           </router-link>
        </div>
      </div>
    </Modal>

    <!-- Create / Edit Call Modal (Admin Only) -->
    <Modal :show="showCreate || editingCall" :title="editingCall ? 'Edit Funding Call' : 'Create New Funding Call'" size="lg" @close="closeCallModal">
      <form @submit.prevent="saveCall" class="space-y-6">
        <div>
          <label class="block text-[11px] text-slate-500 font-bold capitalize tracking-widest mb-2 ml-1">Title *</label>
          <input v-model="callForm.title" type="text" required class="input h-12 font-bold" placeholder="e.g. National Research Innovation Grant 2025" />
        </div>
        <div>
          <label class="block text-[11px] text-slate-500 font-bold capitalize tracking-widest mb-2 ml-1">Description *</label>
          <textarea v-model="callForm.description" required rows="4" class="input resize-none pt-3" placeholder="Describe the research areas and what is expected..."></textarea>
        </div>
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-6">
          <div>
            <label class="block text-[11px] text-slate-500 font-bold capitalize tracking-widest mb-2 ml-1">Deadline *</label>
            <input v-model="callForm.deadline" type="date" required class="input h-12 font-bold" />
          </div>
          <div>
            <label class="block text-[11px] text-slate-500 font-bold capitalize tracking-widest mb-2 ml-1">Budget Limit (ETB)</label>
            <input v-model.number="callForm.budget_limit" type="number" min="0" step="1000" class="input h-12 font-bold" placeholder="500000" />
          </div>
        </div>
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-6">
          <div>
            <label class="block text-[11px] text-slate-500 font-bold capitalize tracking-widest mb-2 ml-1">Academic Year</label>
            <select v-model="callForm.academic_year_id" class="input h-12 font-bold">
              <option value="">Select Year</option>
              <option v-for="y in academicYears" :key="y.id" :value="y.id">{{ y.name }}</option>
            </select>
          </div>
          <div>
            <label class="block text-[11px] text-slate-500 font-bold capitalize tracking-widest mb-2 ml-1">Thematic Area</label>
            <select v-model="callForm.thematic_areas" class="input h-12 font-bold">
              <option value="">Select Area</option>
              <option v-for="t in thematicAreas" :key="t.id" :value="t.name">{{ t.name }}</option>
            </select>
          </div>
        </div>

        <!-- Hierarchical Scope Fields -->
        <div class="bg-slate-50 rounded-xl p-6 border border-slate-200 space-y-6">
          <h4 class="text-xs font-bold text-slate-400 capitalize tracking-widest border-b border-slate-200 pb-3">Scope & Targeting</h4>
          <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6">
            <div>
              <label class="block text-[11px] text-slate-500 font-bold capitalize tracking-widest mb-2 ml-1">University</label>
              <select v-model="callForm.university_id" :disabled="auth.hasRole('research_admin','director','department_head')" class="input h-12 font-bold" :class="{ 'bg-slate-100 cursor-not-allowed': auth.hasRole('research_admin','director','department_head') }">
                <option value="">All Universities</option>
                <option v-for="u in universities" :key="u.id" :value="u.id">{{ u.name }}</option>
              </select>
              <p v-if="auth.hasRole('research_admin','director','department_head')" class="text-[9px] text-slate-400 mt-1 ml-1">Auto-set by role</p>
            </div>
            <div>
              <label class="block text-[11px] text-slate-500 font-bold capitalize tracking-widest mb-2 ml-1">Campus</label>
              <select v-model="callForm.campus_id" :disabled="auth.hasRole('director','department_head')" class="input h-12 font-bold" :class="{ 'bg-slate-100 cursor-not-allowed': auth.hasRole('director','department_head') }">
                <option value="">All Campuses</option>
                <option v-for="c in campuses" :key="c.id" :value="c.id">{{ c.name }}</option>
              </select>
              <p v-if="auth.hasRole('director','department_head')" class="text-[9px] text-slate-400 mt-1 ml-1">Auto-set by role</p>
            </div>
            <div>
              <label class="block text-[11px] text-slate-500 font-bold capitalize tracking-widest mb-2 ml-1">Faculty</label>
              <select v-model="callForm.faculty_id" :disabled="auth.hasRole('director','department_head')" class="input h-12 font-bold" :class="{ 'bg-slate-100 cursor-not-allowed': auth.hasRole('director','department_head') }">
                <option value="">All Faculties</option>
                <option v-for="f in faculties" :key="f.id" :value="f.id">{{ f.name }}</option>
              </select>
              <p v-if="auth.hasRole('director','department_head')" class="text-[9px] text-slate-400 mt-1 ml-1">Auto-set by role</p>
            </div>
            <div>
              <label class="block text-[11px] text-slate-500 font-bold capitalize tracking-widest mb-2 ml-1">Department</label>
              <select v-model="callForm.department_id" :disabled="auth.hasRole('director','department_head')" class="input h-12 font-bold" :class="{ 'bg-slate-100 cursor-not-allowed': auth.hasRole('director','department_head') }">
                <option value="">All Departments</option>
                <option v-for="d in departments" :key="d.id" :value="d.id">{{ d.name }}</option>
              </select>
              <p v-if="auth.hasRole('director','department_head')" class="text-[9px] text-slate-400 mt-1 ml-1">Auto-set by role</p>
            </div>
            <div>
              <label class="block text-[11px] text-slate-500 font-bold capitalize tracking-widest mb-2 ml-1">Research Center</label>
              <select v-model="callForm.research_center_id" :disabled="auth.hasRole('director','department_head')" class="input h-12 font-bold" :class="{ 'bg-slate-100 cursor-not-allowed': auth.hasRole('director','department_head') }">
                <option value="">All Centers</option>
                <option v-for="rc in researchCenters" :key="rc.id" :value="rc.id">{{ rc.name }}</option>
              </select>
              <p v-if="auth.hasRole('director','department_head')" class="text-[9px] text-slate-400 mt-1 ml-1">Auto-set by role</p>
            </div>
          </div>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 gap-6">
          <div>
            <label class="block text-[11px] text-slate-500 font-bold capitalize tracking-widest mb-2 ml-1">Status</label>
            <select v-model="callForm.status_id" class="input h-12 font-bold">
              <option value="">Select Status</option>
              <option v-for="s in callStatuses" :key="s.id" :value="s.id">{{ s.name }}</option>
            </select>
          </div>
          <div>
            <label class="block text-[11px] text-slate-500 font-bold capitalize tracking-widest mb-2 ml-1">Guideline (File Reference)</label>
            <select v-model="callForm.guideline_file_id" class="input h-12 font-bold">
              <option value="">No Guideline Attached</option>
              <option v-for="f in files" :key="f.id" :value="f.id">{{ f.original_name }}</option>
            </select>
          </div>
        </div>
        <div class="flex justify-end gap-4 pt-6 border-t border-slate-100">
          <button type="button" @click="closeCallModal" class="btn btn-secondary px-5 h-11 text-[11px] font-bold capitalize tracking-widest">Cancel</button>
          <button type="submit" :disabled="saving" class="btn btn-primary px-5 h-11 shadow-lg shadow-blue-500/20 text-[11px] font-bold capitalize tracking-widest">
            {{ saving ? 'Saving...' : (editingCall ? 'Update Call' : 'Create Call') }}
          </button>
        </div>
      </form>
    </Modal>
  </div>
</template>

<script setup>
import { ref, reactive, onMounted, watch } from 'vue'
import { useAuthStore } from '@/stores/auth'
import { useNotificationStore } from '@/stores/notification'
import api from '@/services/api'
import StatusBadge from '@/components/StatusBadge.vue'
import Modal from '@/components/Modal.vue'
import EmptyState from '@/components/EmptyState.vue'
import { formatDate, formatCurrency } from '@/utils/formatters'

const auth = useAuthStore()
const notif = useNotificationStore()
const calls = ref([])
const loading = ref(true)
const saving = ref(false)
const selectedCall = ref(null)
const showCreate = ref(false)
const editingCall = ref(null)
const academicYears = ref([])
const callStatuses = ref([])
const thematicAreas = ref([])
const files = ref([])
const universities = ref([])
const campuses = ref([])
const faculties = ref([])
const departments = ref([])
const researchCenters = ref([])

const callForm = reactive({
  title: '', description: '', deadline: '', budget_limit: null,
  academic_year_id: '', status_id: '', thematic_areas: '', guideline_file_id: '',
  university_id: '', campus_id: '', faculty_id: '', department_id: '', research_center_id: ''
})

async function fetchCalls() {
  loading.value = true
  try {
    const { data } = await api.get('/calls')
    calls.value = data.data || data
  } catch (e) {}
  finally { loading.value = false }
}

function viewCall(call) { selectedCall.value = call }

function editCall(call) {
  editingCall.value = call
  Object.assign(callForm, {
    title: call.title,
    description: call.description,
    deadline: call.deadline?.substring(0, 10) || '',
    budget_limit: call.budget_limit,
    academic_year_id: call.academic_year_id || '',
    status_id: call.status_id || '',
    thematic_areas: call.thematic_areas || '',
    guideline_file_id: call.guideline_file_id || '',
    university_id: call.university_id || '',
    campus_id: call.campus_id || '',
    faculty_id: call.faculty_id || '',
    department_id: call.department_id || '',
    research_center_id: call.research_center_id || ''
  })
  // Auto-set scope based on role
  autoSetScopeByRole()
}

function closeCallModal() {
  showCreate.value = false
  editingCall.value = null
  Object.assign(callForm, { 
    title: '', description: '', deadline: '', budget_limit: null, 
    academic_year_id: '', status_id: '', thematic_areas: '', guideline_file_id: '',
    university_id: '', campus_id: '', faculty_id: '', department_id: '', research_center_id: ''
  })
}

function autoSetScopeByRole() {
  const user = auth.user
  if (!user) return
  
  // Research Admin: University fixed to their university
  if (auth.hasRole('research_admin')) {
    callForm.university_id = user.university_id
  }
  
  // Director: All fields auto-set to their research center
  if (auth.hasRole('director')) {
    callForm.university_id = user.university_id
    callForm.campus_id = user.campus_id
    callForm.faculty_id = user.faculty_id
    callForm.department_id = user.department_id
    callForm.research_center_id = user.research_center_id
  }
  
  // Department Head: All fields auto-set to their department
  if (auth.hasRole('department_head')) {
    callForm.university_id = user.university_id
    callForm.campus_id = user.campus_id
    callForm.faculty_id = user.faculty_id
    callForm.department_id = user.department_id
  }
}

async function saveCall() {
  saving.value = true
  try {
    const payload = { 
      ...callForm, 
      budget_limit: callForm.budget_limit || null, 
      academic_year_id: callForm.academic_year_id || null, 
      status_id: callForm.status_id || null,
      university_id: callForm.university_id || null,
      campus_id: callForm.campus_id || null,
      faculty_id: callForm.faculty_id || null,
      department_id: callForm.department_id || null,
      research_center_id: callForm.research_center_id || null
    }
    if (editingCall.value) {
      await api.put(`/calls/${editingCall.value.id}`, payload)
      notif.success('Call updated successfully!')
    } else {
      await api.post('/calls', payload)
      notif.success('Funding call created!')
    }
    closeCallModal()
    fetchCalls()
  } catch (err) {
    notif.error(err.response?.data?.message || 'Failed to save call')
  } finally { saving.value = false }
}

onMounted(async () => {
  fetchCalls()
  try {
    const [ys, ss, ts, fs, univ, camp, fac, dept, rc] = await Promise.all([
      api.get('/academic-years'),
      api.get('/lookups/call_statuses'),
      api.get('/lookups/thematic_areas'),
      api.get('/files'),
      api.get('/universities'),
      api.get('/campuses'),
      api.get('/faculties'),
      api.get('/departments'),
      api.get('/research-centers')
    ])
    academicYears.value = ys.data
    callStatuses.value = ss.data
    thematicAreas.value = ts.data
    files.value = fs.data.data || fs.data
    universities.value = univ.data.data || univ.data
    campuses.value = camp.data.data || camp.data
    faculties.value = fac.data.data || fac.data
    departments.value = dept.data.data || dept.data
    researchCenters.value = rc.data.data || rc.data
  } catch (e) {}
})

// Watch for create modal open to auto-set scope
watch(showCreate, (isOpen) => {
  if (isOpen) {
    autoSetScopeByRole()
  }
})
</script>
