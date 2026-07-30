<template>
  <div class="flex flex-col gap-5 pb-6 animate-fade card">
    <!-- Header -->
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
      <div>
        <h1 class="text-xl font-bold text-slate-900 tracking-tight">Call for Proposals</h1>
        <p class="text-slate-500 font-medium mt-1">Open applications for research grants.</p>
      </div>
      <div class="flex items-center gap-3">
        <button v-if="auth.hasRole('super_admin','research_admin','campus_admin','faculty_admin','director','department_head')" @click="showCreate = true" class="btn btn-primary h-11 px-6">
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
      <div v-for="i in 4" :key="i" class="card p-8 h-64 flex flex-col gap-4 bg-slate-50/50">
        <div class="h-6 w-24 bg-slate-200 rounded-2xl animate-pulse"></div>
        <div class="h-8 w-3/4 bg-slate-100 rounded-2xl animate-pulse"></div>
        <div class="h-24 w-full bg-slate-100/50 rounded-2xl animate-pulse"></div>
      </div>
    </div>

    <div v-else-if="calls.length === 0" class="card">
      <EmptyState icon="📢" title="No calls found" description="There are currently no open calls. We will update you soon." />
    </div>

    <div v-else class="grid grid-cols-1 lg:grid-cols-2 gap-5">
      <div v-for="call in calls" :key="call.id" class="card group card-hover flex flex-col p-8 transition-all">
        <div class="flex flex-col sm:flex-row justify-between items-start gap-4 mb-6">
          <StatusBadge :status="call.status?.name || 'open'" />
          <div class="flex items-center gap-2 text-xs font-medium text-slate-400 bg-slate-100 px-3 py-1.5 rounded-2xl">
            <svg class="w-4 h-4 text-slate-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" /></svg>
            Ends: {{ formatDate(call.deadline) }}
          </div>
        </div>

        <h3 class="text-xl font-bold text-slate-900 group-hover:text-brand transition-colors mb-4 leading-tight">{{ call.title }}</h3>
        <div class="flex-1 mb-5 min-h-0">
          <p class="text-sm text-slate-500 font-medium line-clamp-3 leading-relaxed">{{ call.description }}</p>
        </div>

        <div class="flex items-center justify-between mt-auto pt-6 border-t border-slate-100">
          <div class="flex items-center gap-2">
            <router-link v-if="auth.hasPermission('submit_proposals')" :to="`/app/proposals/create?call_id=${call.id}`" class="btn btn-primary px-6 text-xs font-medium h-10">
              Apply
            </router-link>
            <button v-else @click="handleGuestApply" class="btn btn-primary px-6 text-xs font-medium h-10">
              Apply
            </button>
          </div>
          <div class="flex items-center gap-2">
            <div class="text-right mr-2">
               <p class="text-xs font-medium text-slate-400 mb-0.5">Award High</p>
               <p class="text-base font-bold text-brand">{{ formatCurrency(call.budget_limit) }}</p>
            </div>
            <ActionMenu :actions="[
              { key: 'view', label: 'View Info', handler: () => viewCall(call) },
              { key: 'edit', label: 'Edit', show: auth.hasRole('super_admin','research_admin','campus_admin','faculty_admin','director','department_head'), handler: () => editCall(call) },
              { separator: true },
              { key: 'delete', label: 'Delete', show: auth.hasRole('super_admin','research_admin','campus_admin','faculty_admin','director','department_head'), handler: () => deleteCall(call) }
            ]" />
          </div>
        </div>
      </div>
    </div>

    <!-- Detail Modal -->
    <Modal :show="!!selectedCall && !editingCall" :title="selectedCall?.title" size="lg" @close="selectedCall = null">
      <div v-if="selectedCall" class="flex flex-col gap-5">
          <div class="p-6 rounded-2xl border border-slate-100">
           <h4 class="text-xs font-medium text-slate-400 mb-3">About this call</h4>
           <p class="text-base text-slate-700 font-medium whitespace-pre-line leading-relaxed">{{ selectedCall.description }}</p>
        </div>
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6">
          <div class="p-5 bg-white rounded-2xl border border-slate-100 shadow-sm flex flex-col gap-1 text-center font-bold ">
             <p class="text-xs text-slate-400 tracking-widest">Award High</p>
             <p class="text-lg text-brand">{{ formatCurrency(selectedCall.budget_limit) }}</p>
          </div>
          <div class="p-5 bg-white rounded-2xl border border-slate-100 shadow-sm flex flex-col gap-1 text-center font-bold ">
             <p class="text-xs text-slate-400 tracking-widest">Ending Date</p>
             <p class="text-lg text-slate-900">{{ formatDate(selectedCall.deadline) }}</p>
          </div>
          <div class="p-5 bg-white rounded-2xl border border-slate-100 shadow-sm flex flex-col gap-1 text-center font-bold ">
             <p class="text-xs text-slate-400 tracking-widest">Target Year</p>
             <p class="text-lg text-slate-900">{{ selectedCall.academic_year?.name || 'N/A' }}</p>
          </div>
           <div class="p-5 bg-white rounded-2xl border border-slate-100 shadow-sm flex flex-col gap-1 text-center font-bold ">
             <p class="text-xs text-slate-400 tracking-widest">Thematic Area</p>
             <p class="text-lg text-slate-900">{{ selectedCall.thematic_areas || 'Multi-disciplinary' }}</p>
          </div>
        </div>
        <div class="pt-6 border-t border-slate-100 flex justify-end gap-3">
           <button @click="selectedCall = null" class="btn btn-secondary px-5 h-12 text-xs font-medium">Close</button>
           <router-link v-if="auth.hasPermission('submit_proposals')" :to="`/app/proposals/create?call_id=${selectedCall.id}`" @click="selectedCall = null" class="btn btn-primary px-5 h-12 text-xs font-medium">
              Apply Now
           </router-link>
           <button v-else @click="handleGuestApply" class="btn btn-primary px-5 h-12 text-xs font-medium">
              Apply Now
           </button>
        </div>
      </div>
    </Modal>

    <!-- Create / Edit Call Modal (Admin Only) -->
    <Modal :show="showCreate || editingCall" :title="editingCall ? 'Edit Call for Proposal' : 'Create New Call for Proposal'" size="lg" @close="closeCallModal">
      <form @submit.prevent="saveCall" class="space-y-6">
        <div>
          <label class="block text-xs text-slate-500 font-medium mb-2 ml-1">Title *</label>
          <input v-model="callForm.title" type="text" required class="input h-12 font-bold" placeholder="e.g. National Research Innovation Grant 2025" />
        </div>
        <div>
          <label class="block text-xs text-slate-500 font-medium mb-2 ml-1">Description *</label>
          <textarea v-model="callForm.description" required rows="4" class="input resize-none pt-3" placeholder="Describe the research areas and what is expected..."></textarea>
        </div>
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-6">
          <div>
            <label class="block text-xs text-slate-500 font-medium mb-2 ml-1">Deadline *</label>
            <input v-model="callForm.deadline" type="date" required class="input h-12 font-bold" />
          </div>
          <div>
            <label class="block text-xs text-slate-500 font-medium mb-2 ml-1">Budget Limit (ETB)</label>
            <input v-model.number="callForm.budget_limit" type="number" min="0" step="1000" class="input h-12 font-bold" placeholder="500000" />
          </div>
        </div>
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-6">
          <div>
            <label class="block text-xs text-slate-500 font-medium mb-2 ml-1">Academic Year</label>
            <select v-model="callForm.academic_year_id" class="input h-12 font-bold">
              <option value="">Select Year</option>
              <option v-for="y in academicYears" :key="y.id" :value="y.id">{{ y.name }}</option>
            </select>
          </div>
          <div>
            <label class="block text-xs text-slate-500 font-medium mb-2 ml-1">Thematic Area(s)</label>
            <input v-model="callForm.thematic_areas" type="text" class="input h-12 font-bold" placeholder="e.g. Health, Ecosystems, AI" />
          </div>
        </div>

        <div>
          <label class="block text-xs text-slate-500 font-medium mb-2 ml-1">Link to Community Problem (Optional)</label>
          <select v-model="callForm.community_problem_id" class="input h-12 font-bold bg-brand/5 border-brand/10">
            <option value="">No specific community problem</option>
            <option v-for="cp in communityProblems" :key="cp.id" :value="cp.id">
              {{ cp.title }} ({{ cp.location }})
            </option>
          </select>
          <p class="text-[10px] text-slate-400 mt-1.5 ml-1 font-medium italic">If this call originates from a community-reported issue, select it here.</p>
        </div>

        <!-- Hierarchical Scope Fields -->
        <div class="bg-slate-50 rounded-2xl p-6 border border-slate-100 space-y-6">
          <h4 class="text-xs font-medium text-slate-400 border-b border-slate-100 pb-3">Scope & Targeting</h4>

          <!-- Read-only hierarchy breadcrumb derived from auth context -->
          <div v-if="userHierarchyBreadcrumb.length" class="flex items-center gap-2 flex-wrap bg-white rounded-xl px-4 py-3 border border-slate-200">
            <svg class="w-4 h-4 text-slate-300 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/></svg>
            <template v-for="(crumb, ci) in userHierarchyBreadcrumb" :key="ci">
              <span class="text-xs font-bold text-slate-600">{{ crumb }}</span>
              <svg v-if="ci < userHierarchyBreadcrumb.length - 1" class="w-3 h-3 text-slate-300 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
            </template>
            <span class="ml-auto text-[10px] text-slate-400 font-medium italic">Auto-detected from your role</span>
          </div>

          <!-- Target scope selector -->
          <div>
            <label class="block text-xs text-slate-500 font-medium mb-2 ml-1">Target Scope *</label>
            <select v-model="callForm.target_scope" class="input h-12 font-bold">
              <option v-for="opt in availableTargetScopes" :key="opt.value" :value="opt.value">{{ opt.label }}</option>
            </select>
            <p class="text-[10px] text-slate-400 mt-1.5 ml-1 font-medium italic">Choose how broadly this call should be visible.</p>
          </div>

          <!-- Conditional child dropdowns based on selected scope -->
          <div v-if="callForm.target_scope === 'campus'" class="transition-all">
            <label class="block text-xs text-slate-500 font-medium mb-2 ml-1">Select Campus</label>
            <select v-model="callForm.campus_id" class="input h-12 font-bold">
              <option value="">Choose a campus...</option>
              <option v-for="c in filteredCampuses" :key="c.id" :value="c.id">{{ c.name }}</option>
            </select>
          </div>
          <div v-if="callForm.target_scope === 'faculty'" class="space-y-4 transition-all">
            <div>
              <label class="block text-xs text-slate-500 font-medium mb-2 ml-1">Campus</label>
              <select v-model="callForm.campus_id" class="input h-12 font-bold">
                <option value="">All Campuses</option>
                <option v-for="c in filteredCampuses" :key="c.id" :value="c.id">{{ c.name }}</option>
              </select>
            </div>
            <div>
              <label class="block text-xs text-slate-500 font-medium mb-2 ml-1">Select Faculty</label>
              <select v-model="callForm.faculty_id" class="input h-12 font-bold">
                <option value="">Choose a faculty...</option>
                <option v-for="f in filteredFaculties" :key="f.id" :value="f.id">{{ f.name }}</option>
              </select>
            </div>
          </div>
          <div v-if="callForm.target_scope === 'department'" class="space-y-4 transition-all">
            <div>
              <label class="block text-xs text-slate-500 font-medium mb-2 ml-1">Campus</label>
              <select v-model="callForm.campus_id" class="input h-12 font-bold">
                <option value="">All Campuses</option>
                <option v-for="c in filteredCampuses" :key="c.id" :value="c.id">{{ c.name }}</option>
              </select>
            </div>
            <div>
              <label class="block text-xs text-slate-500 font-medium mb-2 ml-1">Faculty</label>
              <select v-model="callForm.faculty_id" class="input h-12 font-bold">
                <option value="">All Faculties</option>
                <option v-for="f in filteredFaculties" :key="f.id" :value="f.id">{{ f.name }}</option>
              </select>
            </div>
            <div>
              <label class="block text-xs text-slate-500 font-medium mb-2 ml-1">Select Department</label>
              <select v-model="callForm.department_id" class="input h-12 font-bold">
                <option value="">Choose a department...</option>
                <option v-for="d in filteredDepartments" :key="d.id" :value="d.id">{{ d.name }}</option>
              </select>
            </div>
          </div>
          <div v-if="callForm.target_scope === 'research_center'" class="space-y-4 transition-all">
            <div>
              <label class="block text-xs text-slate-500 font-medium mb-2 ml-1">Select Research Center</label>
              <select v-model="callForm.research_center_id" class="input h-12 font-bold">
                <option value="">Choose a research center...</option>
                <option v-for="rc in filteredResearchCenters" :key="rc.id" :value="rc.id">{{ rc.name }}</option>
              </select>
            </div>
          </div>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 gap-6">
          <div>
            <label class="block text-xs text-slate-500 font-medium mb-2 ml-1">Status</label>
            <select v-model="callForm.status_id" class="input h-12 font-bold">
              <option value="">Select Status</option>
              <option v-for="s in callStatuses" :key="s.id" :value="s.id">{{ s.name }}</option>
            </select>
          </div>
          <div>
            <label class="block text-xs text-slate-500 font-medium mb-2 ml-1">Guideline (Select Existing File)</label>
            <select v-model="callForm.guideline_file_id" class="input h-12 font-bold">
              <option value="">No Guideline Attached</option>
              <option v-for="f in files" :key="f.id" :value="f.id">{{ f.original_filename }}</option>
            </select>
          </div>
        </div>

        <div>
          <label class="block text-xs text-slate-500 font-medium mb-2 ml-1">Or Upload New Guideline Document</label>
          <FileUpload v-model="guidelineUploadFile" @update:modelValue="onGuidelineFileChange" />
        </div>
        <div class="flex justify-end gap-4 pt-6 border-t border-slate-100">
          <button type="button" @click="closeCallModal" class="btn btn-secondary px-5 h-11 text-xs font-medium">Cancel</button>
          <button type="submit" :disabled="saving" class="btn btn-primary px-5 h-11 text-xs font-medium">
            {{ saving ? 'Saving...' : (editingCall ? 'Update Call' : 'Create Call') }}
          </button>
        </div>
      </form>
    </Modal>
  </div>
</template>

<script setup>
import { ref, reactive, computed, onMounted, watch } from 'vue'
import { useRoute } from 'vue-router'
import { useAuthStore } from '@/stores/auth'
import { useNotificationStore } from '@/stores/notification'
import api from '@/services/api'
import StatusBadge from '@/components/StatusBadge.vue'
import Modal from '@/components/Modal.vue'
import EmptyState from '@/components/EmptyState.vue'
import FileUpload from '@/components/FileUpload.vue'
import ActionMenu from '@/components/ActionMenu.vue'
import { formatDate, formatCurrency } from '@/utils/formatters'

const route = useRoute()
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
const files = ref([])
const universities = ref([])
const campuses = ref([])
const faculties = ref([])
const departments = ref([])
const researchCenters = ref([])
const communityProblems = ref([])

const guidelineUploadFile = ref(null)

const callForm = reactive({
  title: '', description: '', deadline: '', budget_limit: null,
  academic_year_id: '', status_id: '', thematic_areas: '', guideline_file_id: '',
  target_scope: 'organization',
  university_id: '', campus_id: '', faculty_id: '', department_id: '', research_center_id: '',
  community_problem_id: ''
})

// ─── Computed: User hierarchy breadcrumb ───
const userHierarchyBreadcrumb = computed(() => {
  const user = auth.user
  if (!user) return []
  const crumbs = []
  if (user.university?.name) crumbs.push(user.university.name)
  if (user.department?.faculty?.campus?.name) crumbs.push(user.department.faculty.campus.name)
  if (user.department?.faculty?.name) crumbs.push(user.department.faculty.name)
  if (user.department?.name) crumbs.push(user.department.name)
  if (user.research_centers?.length) crumbs.push(user.research_centers[0].name)
  if (auth.hasRole('super_admin') && crumbs.length === 0) crumbs.push('Platform (All Organizations)')
  return crumbs
})

// ─── Available target scopes ───
const availableTargetScopes = computed(() => {
  const scopes = [{ value: 'organization', label: 'Entire Organization' }]
  const scope = auth.userScope
  if (['system', 'university', 'campus'].includes(scope) || auth.hasRole('super_admin')) {
    scopes.push({ value: 'campus', label: 'Specific Campus' })
  }
  if (['system', 'university', 'campus', 'faculty'].includes(scope) || auth.hasRole('super_admin')) {
    scopes.push({ value: 'faculty', label: 'Specific Faculty' })
  }
  if (['system', 'university', 'campus', 'faculty', 'department'].includes(scope) || auth.hasRole('super_admin')) {
    scopes.push({ value: 'department', label: 'Specific Department' })
  }
  scopes.push({ value: 'research_center', label: 'Specific Research Center' })
  return scopes
})

const filteredCampuses = computed(() => {
  if (!callForm.university_id) return campuses.value
  return campuses.value.filter(c => c.university_id == callForm.university_id)
})

const filteredFaculties = computed(() => {
  if (!callForm.campus_id) return faculties.value
  return faculties.value.filter(f => f.campus_id == callForm.campus_id)
})

const filteredDepartments = computed(() => {
  if (!callForm.faculty_id) return departments.value
  return departments.value.filter(d => d.faculty_id == callForm.faculty_id)
})

const filteredResearchCenters = computed(() => {
  let rc = researchCenters.value
  if (callForm.department_id) {
    return rc.filter(r => r.parent_department_id == callForm.department_id)
  }
  if (callForm.faculty_id) {
    const deptIds = departments.value.filter(d => d.faculty_id == callForm.faculty_id).map(d => d.id)
    return rc.filter(r => r.parent_faculty_id == callForm.faculty_id || (r.parent_department_id && deptIds.includes(r.parent_department_id)))
  }
  if (callForm.campus_id) {
    const facIds = faculties.value.filter(f => f.campus_id == callForm.campus_id).map(f => f.id)
    const deptIds = departments.value.filter(d => facIds.includes(d.faculty_id)).map(d => d.id)
    return rc.filter(r => r.parent_campus_id == callForm.campus_id || (r.parent_faculty_id && facIds.includes(r.parent_faculty_id)) || (r.parent_department_id && deptIds.includes(r.parent_department_id)))
  }
  if (callForm.university_id) {
    const campIds = campuses.value.filter(c => c.university_id == callForm.university_id).map(c => c.id)
    const facIds = faculties.value.filter(f => campIds.includes(f.campus_id)).map(f => f.id)
    const deptIds = departments.value.filter(d => facIds.includes(d.faculty_id)).map(d => d.id)
    return rc.filter(r => r.parent_university_id == callForm.university_id || (r.parent_campus_id && campIds.includes(r.parent_campus_id)) || (r.parent_faculty_id && facIds.includes(r.parent_faculty_id)) || (r.parent_department_id && deptIds.includes(r.parent_department_id)))
  }
  return rc
})

// ─── Fetch functions ───
async function fetchCalls() {
  loading.value = true
  try {
    const { data } = await api.get('/calls')
    calls.value = data.data || data
  } catch (e) {
    console.error('Failed to fetch calls:', e)
    notif.error('Failed to load calls.')
  } finally {
    loading.value = false
  }
}

function viewCall(call) { selectedCall.value = call }

function handleGuestApply() {
  notif.warning('You do not have permission to submit proposals.')
}

function editCall(call) {
  editingCall.value = call
  const derivedScope = call.research_center_id ? 'research_center' :
                        call.department_id ? 'department' :
                        call.faculty_id ? 'faculty' :
                        call.campus_id ? 'campus' : 'organization'
  Object.assign(callForm, {
    title: call.title,
    description: call.description,
    deadline: call.deadline?.substring(0, 10) || '',
    budget_limit: call.metadata?.budget_limit || null,
    academic_year_id: call.academic_year_id || '',
    status_id: call.status_id || '',
    thematic_areas: call.thematic_areas || '',
    guideline_file_id: call.guideline_file_id || '',
    target_scope: derivedScope,
    university_id: call.university_id || '',
    campus_id: call.campus_id || '',
    faculty_id: call.faculty_id || '',
    department_id: call.department_id || '',
    research_center_id: call.research_center_id || '',
    community_problem_id: call.community_problem_id || ''
  })
  showCreate.value = true
  autoSetScopeByRole()
}

function closeCallModal() {
  showCreate.value = false
  editingCall.value = null
  guidelineUploadFile.value = null
  Object.assign(callForm, { 
    title: '', description: '', deadline: '', budget_limit: null, 
    academic_year_id: '', status_id: '', thematic_areas: '', guideline_file_id: '',
    target_scope: 'organization',
    university_id: '', campus_id: '', faculty_id: '', department_id: '', research_center_id: '',
    community_problem_id: ''
  })
}

function autoSetScopeByRole() {
  const user = auth.user
  if (!user) return
  
  if (auth.hasRole('super_admin')) return
  
  if (auth.hasRole('research_admin') && !auth.hasRole('super_admin')) {
    if (user.university_id) callForm.university_id = user.university_id
  }
  
  if (auth.hasRole('campus_admin') && !auth.hasRole('research_admin', 'super_admin')) {
    if (user.department_id) {
      callForm.university_id = user.department?.faculty?.campus?.university_id || ''
      callForm.campus_id = user.department?.faculty?.campus_id || ''
    }
  }
  
  if (auth.hasRole('faculty_admin') && !auth.hasRole('campus_admin', 'research_admin', 'super_admin')) {
    if (user.department_id) {
      callForm.university_id = user.department?.faculty?.campus?.university_id || ''
      callForm.campus_id = user.department?.faculty?.campus_id || ''
      callForm.faculty_id = user.department?.faculty_id || ''
    }
  }
  
  if (auth.hasRole('department_head') && !auth.hasRole('faculty_admin', 'campus_admin', 'research_admin', 'super_admin')) {
    if (user.department_id) {
      callForm.university_id = user.department?.faculty?.campus?.university_id || ''
      callForm.campus_id = user.department?.faculty?.campus_id || ''
      callForm.faculty_id = user.department?.faculty_id || ''
      callForm.department_id = user.department_id || ''
    }
    callForm.target_scope = 'department'
  }
  
  if (auth.hasRole('director') && !auth.hasRole('department_head', 'faculty_admin', 'campus_admin', 'research_admin', 'super_admin')) {
    if (user.research_centers && user.research_centers.length > 0) {
      callForm.research_center_id = user.research_centers[0].id
    }
    callForm.target_scope = 'research_center'
  }
}

async function onGuidelineFileChange(file) {
  if (!file) {
    callForm.guideline_file_id = ''
    return
  }
  const fd = new FormData()
  fd.append('file', file)
  try {
    notif.info('Uploading guideline file...')
    const { data } = await api.post('/files', fd)
    const uploadedFile = data.data || data
    files.value.push(uploadedFile)
    callForm.guideline_file_id = uploadedFile.id
    notif.success('Guideline file uploaded successfully!')
  } catch (err) {
    notif.error('Failed to upload guideline file')
    guidelineUploadFile.value = null
  }
}

async function saveCall() {
  saving.value = true
  try {
    const scope = callForm.target_scope
    
    // Ensure university_id is always present (required by backend)
    const universityId = callForm.university_id || auth.user?.university_id
    if (!universityId) {
      notif.error('University is required to create a call')
      saving.value = false
      return
    }
    
    const payload = { 
      ...callForm, 
      budget_limit: callForm.budget_limit || null, 
      academic_year_id: callForm.academic_year_id || null, 
      status_id: callForm.status_id || null,
      university_id: universityId,
      campus_id: ['campus','faculty','department'].includes(scope) ? (callForm.campus_id || null) : null,
      faculty_id: ['faculty','department'].includes(scope) ? (callForm.faculty_id || null) : null,
      department_id: scope === 'department' ? (callForm.department_id || null) : null,
      research_center_id: scope === 'research_center' ? (callForm.research_center_id || null) : null,
      community_problem_id: callForm.community_problem_id || null
    }
    delete payload.target_scope
    
    // If no status_id is set, backend defaults to 'open'
    if (editingCall.value) {
      await api.put(`/calls/${editingCall.value.id}`, payload)
      notif.success('Call updated successfully!')
    } else {
      await api.post('/calls', payload)
      notif.success('Call for proposal created!')
    }
    closeCallModal()
    await fetchCalls()
  } catch (err) {
    console.error('Save error:', err.response?.data)
    notif.error(err.response?.data?.message || 'Failed to save call')
  } finally { 
    saving.value = false 
  }
}

async function deleteCall(call) {
  if (!confirm(`Delete "${call.title}"? This cannot be undone.`)) return
  try {
    await api.delete(`/calls/${call.id}`)
    notif.success('Call deleted')
    await fetchCalls()
  } catch (err) {
    notif.error(err.response?.data?.message || 'Failed to delete call')
  }
}

// ─── Load dropdown data ───
async function loadDropdownData() {
  try {
    const [ys, ss, fs, univ, camp, fac, dept, rc, cp] = await Promise.all([
      api.get('/academic-years').catch(() => ({ data: [] })),
      api.get('/lookups/call_statuses').catch(() => ({ data: [] })),
      api.get('/files').catch(() => ({ data: [] })),
      api.get('/universities').catch(() => ({ data: [] })),
      api.get('/campuses').catch(() => ({ data: [] })),
      api.get('/faculties').catch(() => ({ data: [] })),
      api.get('/departments').catch(() => ({ data: [] })),
      api.get('/research-centers').catch(() => ({ data: [] })),
      api.get('/community-problems', { params: { status: 'open' } }).catch(() => ({ data: [] })),
    ])
    academicYears.value = ys.data
    callStatuses.value = ss.data
    files.value = fs.data.data || fs.data
    universities.value = univ.data.data || univ.data
    campuses.value = camp.data.data || camp.data
    faculties.value = fac.data.data || fac.data
    departments.value = dept.data.data || dept.data
    researchCenters.value = rc.data.data || rc.data
    communityProblems.value = cp.data.data || cp.data
  } catch (e) {
    console.error('Failed to load dropdown data:', e)
  }
}

// ─── Watchers ───
watch(showCreate, (isOpen) => {
  if (isOpen) autoSetScopeByRole()
})

watch(() => callForm.target_scope, (newScope) => {
  if (newScope === 'organization') {
    callForm.campus_id = ''; callForm.faculty_id = ''; callForm.department_id = ''; callForm.research_center_id = ''
  } else if (newScope === 'campus') {
    callForm.faculty_id = ''; callForm.department_id = ''; callForm.research_center_id = ''
  } else if (newScope === 'faculty') {
    callForm.department_id = ''; callForm.research_center_id = ''
  } else if (newScope === 'department') {
    callForm.research_center_id = ''
  } else if (newScope === 'research_center') {
    callForm.campus_id = ''; callForm.faculty_id = ''; callForm.department_id = ''
  }
})

watch(() => callForm.campus_id, () => { callForm.faculty_id = ''; callForm.department_id = '' })
watch(() => callForm.faculty_id, () => { callForm.department_id = '' })

// ─── Lifecycle ───
onMounted(async () => {
  await loadDropdownData()
  await fetchCalls()
  
  if (route.query.community_problem_id) {
    callForm.community_problem_id = route.query.community_problem_id
    showCreate.value = true
    const problem = communityProblems.value.find(p => p.id == route.query.community_problem_id)
    if (problem) {
      callForm.title = `Research Call: ${problem.title}`
      callForm.description = `Targeting community issue: ${problem.description}\n\nLocation: ${problem.location}`
    }
  }
});

</script>
