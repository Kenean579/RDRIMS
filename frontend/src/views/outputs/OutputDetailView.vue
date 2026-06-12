<template>
  <div class="flex flex-col gap-5 pb-6 animate-fade card">
    <!-- Header -->
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-6">
      <div>
        <router-link to="/app/outputs" class="flex items-center gap-2 text-indigo-600 font-bold text-xs mb-3 hover:translate-x-1 transition-transform">
          <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M10 19l-7-7m0 0l7-7m-7 7h18" /></svg>
          Back to repository
        </router-link>
        <h1 class="text-xl font-bold text-slate-900 tracking-tight leading-tight max-w-2xl">{{ output.title || 'Institutional Output' }}</h1>
        <p class="text-slate-500 font-medium mt-1 text-xs">Theses, internships, and standardized research outputs.</p>
      </div>
      <div v-if="!loading" class="flex items-center gap-3">
        <StatusBadge :status="output.status?.name" size="lg" />
      </div>
    </div>

    <div v-if="loading" class="grid grid-cols-1 lg:grid-cols-3 gap-5">
      <div class="lg:col-span-2 space-y-8">
        <div class="card h-48 animate-pulse bg-slate-50/50"></div>
        <div class="card h-96 animate-pulse bg-slate-50/50"></div>
      </div>
      <div class="card h-64 animate-pulse bg-slate-50/50"></div>
    </div>

    <template v-else>
      <div class="grid grid-cols-1 lg:grid-cols-3 gap-5 font-bold">
        <!-- Main Content -->
        <div class="lg:col-span-2 space-y-8">
          <!-- Output Specification -->
          <div class="card p-8 space-y-8">
            <h2 class="text-xs font-medium text-slate-400 mb-6 flex items-center gap-2">
              <span class="w-1 h-3 bg-indigo-500 rounded-full"></span>
              Output Specification
            </h2>
            <dl class="grid grid-cols-1 sm:grid-cols-2 gap-5 text-sm">
              <div>
                <dt class="text-xs font-medium text-slate-400 mb-1.5 ml-1">Title & Identification</dt>
                <dd class="text-sm font-bold text-slate-800 bg-slate-50 p-4 rounded-2xl border border-slate-100">{{ output.title }}</dd>
              </div>
              <div>
                <dt class="text-xs font-medium text-slate-400 mb-1.5 ml-1">Classification</dt>
                <dd class="p-4 rounded-2xl bg-white border border-slate-100 flex flex-wrap gap-2">
                  <span class="px-2.5 py-1 bg-indigo-50 text-indigo-600 rounded-2xl text-xs font-medium">{{ output.category?.name }}</span>
                  <span class="px-2.5 py-1 bg-slate-50 text-slate-500 rounded-2xl text-xs font-medium">{{ output.subtype?.name }}</span>
                </dd>
              </div>
              <div v-if="output.student_level">
                <dt class="text-xs font-medium text-slate-400 mb-1.5 ml-1">Academic Level</dt>
                <dd class="p-4 rounded-2xl bg-white border border-slate-100 text-slate-700 font-bold text-xs">{{ output.student_level?.name }}</dd>
              </div>
              <div v-if="output.project">
                <dt class="text-xs font-medium text-slate-400 mb-1.5 ml-1">Associated Project</dt>
                <dd class="p-4 rounded-2xl bg-white border border-slate-100 text-slate-700 font-bold truncate">{{ output.project?.title }}</dd>
              </div>
            </dl>
            <div class="pt-6 border-t border-slate-50">
              <p class="text-xs font-medium text-slate-400 mb-3 ml-1">Research Abstract</p>
              <p class="text-sm font-medium text-slate-600 leading-relaxed italic border-l-4 border-slate-100 pl-6">{{ output.abstract }}</p>
            </div>
            <div v-if="output.feedback" class="mt-4 bg-amber-50/50 border border-amber-100 rounded-2xl p-6">
              <p class="text-xs font-medium text-amber-600 mb-2">Reviewer Feedback</p>
              <p class="text-sm font-semibold text-amber-900 italic">{{ output.feedback }}</p>
            </div>
          </div>

          <!-- Status Timeline -->
          <div class="card p-8 bg-white border border-slate-100 shadow-sm relative overflow-hidden">
            <div class="absolute top-0 right-0 w-32 h-32 bg-brand/5 rounded-full -translate-y-1/2 translate-x-1/2"></div>
            <h2 class="text-xs font-medium text-slate-400 mb-6 flex items-center gap-2 relative z-10">
              <span class="w-1 h-3 bg-brand rounded-full"></span>
              Status Timeline
            </h2>
            
            <div class="space-y-4 relative z-10">
              <div v-for="(step, index) in statusTimeline" :key="index" class="flex items-center gap-4">
                <div class="flex flex-col items-center">
                  <div class="w-8 h-8 rounded-full flex items-center justify-center text-xs font-bold"
                    :class="step.completed ? 'bg-brand text-white' : step.current ? 'bg-emerald-500 text-white' : 'bg-slate-100 text-slate-400'">
                    {{ step.completed ? '✓' : index + 1 }}
                  </div>
                  <div v-if="index < statusTimeline.length - 1" class="w-0.5 h-8 bg-slate-200 mt-2"></div>
                </div>
                <div class="flex-1">
                  <p class="text-sm font-bold" :class="step.current ? 'text-brand' : step.completed ? 'text-slate-800' : 'text-slate-400'">{{ step.label }}</p>
                  <p v-if="step.description" class="text-xs font-medium text-slate-500">{{ step.description }}</p>
                </div>
              </div>
            </div>
          </div>

          <!-- Institutional Approval Workflow -->
          <div class="card p-8 bg-white border border-slate-100 shadow-sm relative overflow-hidden">
            <div class="absolute top-0 right-0 w-32 h-32 bg-brand/5 rounded-full -translate-y-1/2 translate-x-1/2"></div>
            <h2 class="text-xs font-medium text-slate-400 mb-6 flex items-center gap-2 relative z-10">
              <span class="w-1 h-3 bg-brand rounded-full"></span>
              Institutional Approval Workflow
            </h2>
            
            <div class="flex items-center gap-4 mb-5">
              <div class="p-3 bg-slate-50 rounded-2xl border border-slate-100 italic text-xs font-medium text-slate-400">Current State</div>
              <StatusBadge :status="output.status?.name || 'draft'" size="lg" />
            </div>
            
            <div class="flex flex-wrap gap-4 font-bold">
               <!-- Student Submission -->
               <button v-if="output.status?.name === 'draft'" @click="changeStatus(2)" class="btn bg-brand hover:bg-indigo-700 text-white h-11 px-5 text-xs">Submit Final Output</button>
               
               <!-- Supervisor Approval -->
               <template v-if="output.status?.name === 'submitted'">
                 <button @click="changeStatus(3)" class="btn bg-emerald-500 hover:bg-emerald-600 text-white h-11 px-5 text-xs">Supervisor Clearance</button>
                 <button @click="changeStatus(5)" class="btn bg-rose-500 hover:bg-rose-600 text-white h-11 px-5 text-xs">Reject Submission</button>
               </template>

               <!-- Department Head Final Sign-off -->
               <template v-if="output.status?.name === 'approved_by_supervisor'">
                 <button @click="changeStatus(4)" class="btn bg-indigo-600 hover:bg-indigo-700 text-white h-11 px-5 text-xs">Final Dept Approval</button>
                 <button @click="changeStatus(5)" class="btn bg-rose-500 hover:bg-rose-600 text-white h-11 px-5 text-xs">Reject Output</button>
               </template>

               <!-- Originality Check -->
               <button v-if="auth.hasRole('super_admin', 'research_admin', 'campus_admin', 'faculty_admin', 'director', 'department_head')" @click="openDetectionModal" class="btn bg-violet-600 hover:bg-violet-700 text-white h-11 px-5 text-xs flex items-center gap-2">
                 <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                 Check Originality
               </button>
            </div>
          </div>
        </div>

        <div class="flex flex-col gap-5">
          <!-- Participants Widget -->
          <div class="card p-8 bg-white border border-slate-100 shadow-sm">
            <div class="flex items-center justify-between mb-6">
              <h2 class="text-xs font-medium text-slate-400 flex items-center gap-2">
                <span class="w-1 h-3 bg-brand rounded-full"></span>
                Key Contributors
              </h2>
              <button @click="showAddParticipant = true" class="text-xs font-medium text-brand hover:underline">+ Assign</button>
            </div>
            
            <div v-if="output.participants?.length" class="space-y-4">
              <div v-for="p in output.participants" :key="p.id" class="flex items-center justify-between p-4 rounded-2xl bg-slate-50 border border-slate-100 group hover:border-brand/30 transition-all">
                <div class="flex items-center gap-3">
                  <div class="w-8 h-8 bg-brand-light text-brand rounded-2xl flex items-center justify-center text-xs font-medium ">{{ p.user?.name?.substring(0,1) }}</div>
                  <div class="min-w-0">
                    <p class="text-sm font-bold text-slate-800 leading-tight truncate">{{ p.user?.name }}</p>
                    <p class="text-xs font-medium text-slate-400 mt-0.5">{{ p.participant_type?.name }}</p>
                  </div>
                </div>
                <button @click="removeParticipant(p)" class="p-2 text-slate-300 hover:text-rose-500 opacity-0 group-hover:opacity-100 transition-all shrink-0">
                  <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                </button>
              </div>
            </div>
            <p v-else class="text-xs font-medium text-slate-400 italic text-center py-6">No contributors assigned.</p>
          </div>
        </div>
      </div>
    </template>

    <Modal :show="showAddParticipant" title="Assign Contributor" @close="showAddParticipant = false">
      <form @submit.prevent="addParticipant" class="space-y-5 px-1 py-1">
        <div>
          <label class="block text-xs text-slate-500 font-medium  tracking-wider mb-1.5 ml-1">System User *</label>
          <select v-model="participantForm.user_id" required class="input">
            <option value="">Select User</option>
            <option v-for="u in users" :key="u.id" :value="u.id">{{ u.name }}</option>
          </select>
        </div>
        <div>
          <label class="block text-xs text-slate-500 font-medium  tracking-wider mb-1.5 ml-1">Engagement Role *</label>
          <select v-model="participantForm.participant_type_id" required class="input">
            <option value="">Select Role</option>
            <option v-for="t in participantTypes" :key="t.id" :value="t.id">{{ t.name }}</option>
          </select>
        </div>
        <div class="flex justify-end gap-3 pt-4">
          <button type="button" @click="showAddParticipant = false" class="btn btn-secondary">Discard</button>
          <button type="submit" class="btn btn-primary px-5">Assign User</button>
        </div>
      </form>
    </Modal>

    <!-- Detection Services Modal -->
    <Modal :show="showDetectionModal" title="Select Detection Service" size="md" @close="showDetectionModal = false">
      <div class="space-y-5 px-1 py-1">
        <div v-if="detectionLoading" class="text-center py-8">
          <div class="animate-spin rounded-full h-8 w-8 border-b-2 border-brand mx-auto"></div>
        </div>
        <div v-else class="space-y-3">
          <label v-for="service in detectionServices" :key="service.id" 
            class="flex items-center p-4 rounded-xl border-2 cursor-pointer transition-all group"
            :class="selectedDetectionService === service.id ? 'border-brand bg-brand/5' : 'border-slate-100 hover:border-brand/30'">
            <div class="flex items-center gap-3">
              <input type="radio" :value="service.id" v-model="selectedDetectionService" class="w-4 h-4 text-brand focus:ring-brand border-slate-300" />
              <div class="min-w-0">
                <p class="text-sm font-bold text-slate-800 group-hover:text-brand capitalize">{{ service.name }}</p>
                <p class="text-xs font-medium text-slate-400 mt-0.5">
                  {{ getServiceDescription(service.name) }}
                </p>
              </div>
            </div>
          </label>
        </div>
        <div class="flex justify-end gap-3 pt-6 border-t border-slate-100">
          <button @click="showDetectionModal = false" class="btn btn-secondary px-6 font-bold tracking-widest text-xs">Cancel</button>
          <button @click="submitDetectionRequest" class="btn btn-primary px-5 font-bold tracking-widest text-xs" :disabled="!selectedDetectionService">Start Check</button>
        </div>
      </div>
    </Modal>
  </div>
</template>

<script setup>
import { ref, onMounted, computed } from 'vue'
import { useRoute } from 'vue-router'
import { useAuthStore } from '@/stores/auth'
import { useNotificationStore } from '@/stores/notification'
import api from '@/services/api'
import StatusBadge from '@/components/StatusBadge.vue'
import LoadingSkeleton from '@/components/LoadingSkeleton.vue'
import Modal from '@/components/Modal.vue'
import { formatCurrency } from '@/utils/formatters'
import { formatStatusName } from '@/utils/colors'

const route = useRoute()
const notif = useNotificationStore()
const auth = useAuthStore()
const output = ref({})
const loading = ref(true)
const outputStatuses = ref([])
const showAddParticipant = ref(false)
const participantForm = ref({ user_id: '', participant_type_id: '' })
const users = ref([])
const participantTypes = ref([])
const showDetectionModal = ref(false)
const detectionServices = ref([])
const selectedDetectionService = ref(null)
const detectionLoading = ref(false)

const statusTimeline = computed(() => {
  const status = output.value.status?.name || 'draft'
  const steps = [
    { label: 'Draft', completed: status !== 'draft', current: status === 'draft', description: 'Initial submission' },
    { label: 'Submitted', completed: ['approved_by_supervisor', 'approved', 'rejected'].includes(status), current: status === 'submitted', description: 'Submitted for review' },
    { label: 'Supervisor Approval', completed: ['approved', 'rejected'].includes(status), current: status === 'approved_by_supervisor', description: 'Supervisor reviewed' },
    { label: 'Final Approval', completed: status === 'approved', current: status === 'approved', description: 'Department head approved' },
    { label: 'Rejected', completed: status === 'rejected', current: status === 'rejected', description: 'Output rejected' }
  ]
  return steps
})

async function fetchOutput() {
  loading.value = true
  try { 
    const { data } = await api.get(`/outputs/${route.params.id}`)
    output.value = data 
  } catch (e) {
    notif.error('Failed to load output details')
  } finally { 
    loading.value = false 
  }
}

async function changeStatus(statusId) {
  try { 
    await api.post(`/outputs/${output.value.id}/status`, { status_id: statusId })
    notif.success('Institutional status updated!')
    fetchOutput() 
  } catch (err) { 
    notif.error(err.response?.data?.message || 'Workflow transition failed')
  }
}

async function addParticipant() {
  try { 
    await api.post(`/outputs/${output.value.id}/participants`, participantForm.value)
    notif.success('Contributor assigned successfully')
    showAddParticipant.value = false
    fetchOutput() 
  } catch (err) { 
    notif.error('Failed to assign contributor')
  }
}

async function removeParticipant(p) {
  try { 
    await api.delete(`/outputs/${output.value.id}/participants/${p.id}`)
    notif.success('Contributor removed')
    fetchOutput() 
  } catch (err) { 
    notif.error('Failed to remove contributor')
  }
}

async function fetchDetectionServices() {
  detectionLoading.value = true
  try {
    const { data } = await api.get('/detection/services')
    detectionServices.value = data || []
  } catch (err) {
    notif.error('Failed to load detection services')
  } finally {
    detectionLoading.value = false
  }
}

function getServiceDescription(serviceName) {
  const descriptions = {
    'plagiarismcheck': 'Comprehensive plagiarism and similarity detection',
    'originality': 'AI content detection and originality verification',
    'grammar': 'Grammar, spelling, and style checking'
  }
  return descriptions[serviceName] || 'Detection service'
}

async function submitDetectionRequest() {
  if (!selectedDetectionService.value) {
    notif.error('Please select a detection service')
    return
  }

  try {
    await api.post('/detection/requests', {
      detectable_type: 'App\\Models\\Output',
      detectable_id: output.value.id,
      service_id: selectedDetectionService.value
    })
    notif.success('Detection request submitted successfully')
    showDetectionModal.value = false
    selectedDetectionService.value = null
  } catch (err) {
    notif.error('Failed to submit detection request')
  }
}

async function openDetectionModal() {
  showDetectionModal.value = true
  if (detectionServices.value.length === 0) {
    await fetchDetectionServices()
  }
}

onMounted(async () => {
  await fetchOutput()
  try {
    const ss = await api.get('/lookups/output_statuses')
    const us = await api.get('/users', { params: { per_page: 200 } })
    const pts = await api.get('/lookups/participant_types')
    outputStatuses.value = ss.data
    users.value = us.data.data || us.data
    participantTypes.value = pts.data
  } catch (e) {}
})
</script>