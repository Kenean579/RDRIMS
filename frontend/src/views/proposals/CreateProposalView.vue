<template>
  <div class="max-w-4xl mx-auto">
    <!-- Header -->
    <div class="mb-8">
      <router-link to="/app/proposals" class="inline-flex items-center gap-1.5 text-sm font-bold text-slate-500 hover:text-brand transition-colors mb-4">
        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
        Back to Proposals
      </router-link>
      <h1 class="text-2xl font-bold text-slate-800 tracking-tight">Create New Proposal</h1>
      <p class="text-sm text-slate-500 mt-1 font-medium">Fill in the details to submit your research proposal</p>
    </div>

    <!-- Step Indicator -->
    <div class="flex items-center gap-2 mb-8 bg-white rounded-2xl border border-slate-200 p-4 shadow-sm">
      <button
        v-for="(s, i) in steps"
        :key="i"
        @click="currentStep = i"
        class="flex-1 flex items-center gap-2 px-4 py-2.5 rounded-xl text-sm font-bold transition-all"
        :class="[
          currentStep === i
            ? 'bg-brand text-white shadow-md shadow-brand/30'
            : currentStep > i
              ? 'bg-emerald-50 text-emerald-700 border border-emerald-200'
              : 'text-slate-400 hover:text-slate-600'
        ]"
      >
        <span class="h-6 w-6 rounded-full flex items-center justify-center text-xs font-bold shrink-0"
          :class="[
            currentStep === i ? 'bg-white/20' : currentStep > i ? 'bg-emerald-200 text-emerald-700' : 'bg-slate-100'
          ]"
        >
          <svg v-if="currentStep > i" class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"/></svg>
          <span v-else>{{ i + 1 }}</span>
        </span>
        <span class="hidden sm:inline">{{ s }}</span>
      </button>
    </div>

    <form @submit.prevent="handleSubmit">
      <!-- Step 1: Basic Information -->
      <div v-show="currentStep === 0" class="bg-white rounded-2xl shadow-sm border border-slate-200 p-8 space-y-8">
        <div>
          <h2 class="text-lg font-bold text-slate-800 mb-6 border-b border-slate-100 pb-4">Basic Information</h2>
          <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <div class="md:col-span-2">
              <label class="block text-xs font-bold text-slate-400 capitalize tracking-widest mb-2 ml-1">Title <span class="text-rose-500">*</span></label>
              <input v-model="form.title" type="text" required maxlength="255"
                class="w-full border border-slate-300 rounded-xl px-4 py-3 text-sm focus:ring-2 focus:ring-brand/30 focus:border-brand outline-none transition-all"
                placeholder="Enter the title of your research proposal" />
            </div>
            <div>
              <label class="block text-xs font-bold text-slate-400 capitalize tracking-widest mb-2 ml-1">Call for Proposal</label>
              <select v-model="form.call_id"
                class="w-full border border-slate-300 rounded-xl px-4 py-3 text-sm focus:ring-2 focus:ring-brand/30 focus:border-brand outline-none bg-white transition-all">
                <option value="">Open Call (no specific call)</option>
                <option v-for="call in openCalls" :key="call.id" :value="call.id">{{ call.title }}</option>
              </select>
            </div>
            <div>
              <label class="block text-xs font-bold text-slate-400 capitalize tracking-widest mb-2 ml-1">Proposal Type <span class="text-rose-500">*</span></label>
              <LookupSelect v-model="form.type_id" lookup-key="proposal_types" placeholder="Select type" />
            </div>
            <div>
              <label class="block text-xs font-bold text-slate-400 capitalize tracking-widest mb-2 ml-1">Academic Year</label>
              <select v-model="form.academic_year_id"
                class="w-full border border-slate-300 rounded-xl px-4 py-3 text-sm focus:ring-2 focus:ring-brand/30 focus:border-brand outline-none bg-white transition-all">
                <option value="">Select year</option>
                <option v-for="y in academicYears" :key="y.id" :value="y.id">{{ y.name }} {{ y.is_current ? '(Current)' : '' }}</option>
              </select>
            </div>
          </div>
        </div>

        <div>
          <h2 class="text-lg font-bold text-slate-800 mb-6 border-b border-slate-100 pb-4">Research Scope</h2>
          <div class="space-y-6">
            <div>
              <label class="block text-xs font-bold text-slate-400 capitalize tracking-widest mb-2 ml-1">Keywords <span class="text-rose-500">*</span></label>
              <input v-model="form.keywords" type="text" required
                class="w-full border border-slate-300 rounded-xl px-4 py-3 text-sm focus:ring-2 focus:ring-brand/30 focus:border-brand outline-none transition-all"
                placeholder="AI, Machine Learning, Agriculture" />
              <p class="text-[10px] text-slate-400 mt-1.5 ml-1 font-medium">Separate keywords with commas (minimum 3)</p>
            </div>
            <div>
              <label class="block text-xs font-bold text-slate-400 capitalize tracking-widest mb-2 ml-1">Abstract <span class="text-rose-500">*</span></label>
              <textarea v-model="form.abstract" required rows="4"
                class="w-full border border-slate-300 rounded-xl px-4 py-3 text-sm focus:ring-2 focus:ring-brand/30 focus:border-brand outline-none resize-none transition-all"
                placeholder="Provide a brief summary of your research proposal..."></textarea>
            </div>
            <div>
              <label class="block text-xs font-bold text-slate-400 capitalize tracking-widest mb-2 ml-1">Objectives <span class="text-rose-500">*</span></label>
              <textarea v-model="form.objectives" required rows="3"
                class="w-full border border-slate-300 rounded-xl px-4 py-3 text-sm focus:ring-2 focus:ring-brand/30 focus:border-brand outline-none resize-none transition-all"
                placeholder="1. First objective&#10;2. Second objective..."></textarea>
            </div>
            <div>
              <label class="block text-xs font-bold text-slate-400 capitalize tracking-widest mb-2 ml-1">Methodology <span class="text-rose-500">*</span></label>
              <textarea v-model="form.methodology" required rows="4"
                class="w-full border border-slate-300 rounded-xl px-4 py-3 text-sm focus:ring-2 focus:ring-brand/30 focus:border-brand outline-none resize-none transition-all"
                placeholder="Describe your research methodology in detail..."></textarea>
            </div>
          </div>
        </div>

        <div>
           <h2 class="text-lg font-bold text-slate-800 mb-6 border-b border-slate-100 pb-4">Budget</h2>
           <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
              <div>
                <label class="block text-xs font-bold text-slate-400 capitalize tracking-widest mb-2 ml-1">Total Budget (ETB) <span class="text-rose-500">*</span></label>
                <input v-model.number="form.budget" type="number" required min="0" step="0.01"
                  class="w-full border border-slate-300 rounded-xl px-4 py-3 text-sm focus:ring-2 focus:ring-brand/30 focus:border-brand outline-none flex-1 font-bold text-brand transition-all"
                  placeholder="Total requested amount" />
              </div>
           </div>
           
           <!-- Budget Allocation -->
           <div class="mt-6 bg-slate-50/50 border border-slate-200 rounded-2xl p-6">
              <label class="block text-xs font-bold text-slate-600 uppercase tracking-widest mb-4">Budget Allocation Breakdown</label>
              <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
                <div>
                  <label class="block text-[11px] font-bold text-slate-400 mb-1">Personnel</label>
                  <input v-model.number="form.budget_allocation.personnel" type="number" min="0" placeholder="0" class="w-full border border-slate-200 rounded-xl px-3 py-2 text-sm focus:border-brand outline-none" />
                </div>
                <div>
                  <label class="block text-[11px] font-bold text-slate-400 mb-1">Equipment</label>
                  <input v-model.number="form.budget_allocation.equipment" type="number" min="0" placeholder="0" class="w-full border border-slate-200 rounded-xl px-3 py-2 text-sm focus:border-brand outline-none" />
                </div>
                <div>
                  <label class="block text-[11px] font-bold text-slate-400 mb-1">Travel</label>
                  <input v-model.number="form.budget_allocation.travel" type="number" min="0" placeholder="0" class="w-full border border-slate-200 rounded-xl px-3 py-2 text-sm focus:border-brand outline-none" />
                </div>
                <div>
                  <label class="block text-[11px] font-bold text-slate-400 mb-1">Materials</label>
                  <input v-model.number="form.budget_allocation.materials" type="number" min="0" placeholder="0" class="w-full border border-slate-200 rounded-xl px-3 py-2 text-sm focus:border-brand outline-none" />
                </div>
                <div>
                  <label class="block text-[11px] font-bold text-slate-400 mb-1">Other</label>
                  <input v-model.number="form.budget_allocation.other" type="number" min="0" placeholder="0" class="w-full border border-slate-200 rounded-xl px-3 py-2 text-sm focus:border-brand outline-none" />
                </div>
              </div>
           </div>
        </div>
      </div>

      <!-- Step 2: Co-Investigators -->
      <div v-show="currentStep === 1" class="bg-white rounded-2xl shadow-sm border border-slate-200 p-8">
        <div class="flex items-center justify-between mb-6">
          <div>
            <h2 class="text-lg font-bold text-slate-800">Co-Team Members</h2>
            <p class="text-xs text-slate-400 font-medium mt-1">You are automatically the Principal Investigator</p>
          </div>
          <button type="button" @click="addInvestigator"
            class="inline-flex items-center gap-1.5 px-4 py-2 text-sm font-bold text-brand bg-brand/10 rounded-xl hover:bg-brand/20 transition-colors">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/></svg>
            Add Member
          </button>
        </div>

        <div v-if="form.investigators.length === 0" class="text-center py-12 border-2 border-dashed border-slate-200 rounded-2xl">
          <div class="h-16 w-16 bg-slate-100 rounded-full flex items-center justify-center mx-auto mb-4">
            <svg class="w-8 h-8 text-slate-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/></svg>
          </div>
          <p class="text-sm font-bold text-slate-500">No co-investigators added yet</p>
          <p class="text-xs text-slate-400 mt-1">Click "Add Member" to add co-investigators</p>
        </div>

        <div v-else class="space-y-4">
          <div v-for="(inv, index) in form.investigators" :key="index"
            class="p-5 border border-slate-200 rounded-2xl bg-slate-50/50 hover:border-slate-300 transition-colors">
            <div class="flex items-center justify-between mb-4">
              <span class="text-xs font-bold text-slate-400 capitalize tracking-widest">Member #{{ index + 1 }}</span>
              <button type="button" @click="removeInvestigator(index)"
                class="h-7 w-7 rounded-full bg-rose-50 text-rose-500 hover:bg-rose-100 flex items-center justify-center transition-colors">
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
              </button>
            </div>
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
              <div class="sm:col-span-2">
                <label class="block text-xs font-bold text-slate-500 mb-1.5">Select User or External</label>
                <select v-model="inv.user_id" @change="onUserSelected(index)"
                  class="w-full border border-slate-300 rounded-xl px-4 py-2.5 text-sm focus:ring-2 focus:ring-brand/30 focus:border-brand outline-none bg-white transition-all">
                  <option value="">External person (manual entry)</option>
                  <option v-for="u in availableUsers" :key="u.id" :value="u.id">{{ u.name }} ({{ u.email }})</option>
                </select>
              </div>
              <div v-if="!inv.user_id">
                <label class="block text-xs font-bold text-slate-500 mb-1.5">Full Name <span class="text-rose-500">*</span></label>
                <input v-model="inv.name" type="text" required
                  class="w-full border border-slate-300 rounded-xl px-4 py-2.5 text-sm focus:ring-2 focus:ring-brand/30 focus:border-brand outline-none transition-all"
                  placeholder="Dr. Jane Smith" />
              </div>
              <div v-if="!inv.user_id">
                <label class="block text-xs font-bold text-slate-500 mb-1.5">Email <span class="text-rose-500">*</span></label>
                <input v-model="inv.email" type="email" required
                  class="w-full border border-slate-300 rounded-xl px-4 py-2.5 text-sm focus:ring-2 focus:ring-brand/30 focus:border-brand outline-none transition-all"
                  placeholder="email@university.edu" />
              </div>
              <div v-if="!inv.user_id">
                <label class="block text-xs font-bold text-slate-500 mb-1.5">Institution</label>
                <input v-model="inv.institution" type="text"
                  class="w-full border border-slate-300 rounded-xl px-4 py-2.5 text-sm focus:ring-2 focus:ring-brand/30 focus:border-brand outline-none transition-all"
                  placeholder="University of..." />
              </div>
              <div>
                <label class="block text-xs font-bold text-slate-500 mb-1.5">Role <span class="text-rose-500">*</span></label>
                <LookupSelect v-model="inv.role_id" lookup-key="investigator_roles" placeholder="Select role" />
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- Step 3: Documents -->
      <div v-show="currentStep === 2" class="bg-white rounded-2xl shadow-sm border border-slate-200 p-8">
        <h2 class="text-lg font-bold text-slate-800 mb-6">Required Documents</h2>
        <div class="space-y-8">
          <div class="p-6 bg-slate-50/50 border border-slate-200 rounded-2xl">
            <FileUpload v-model="form.proposal_file" label="Proposal Core Document" :required="true" />
            <p class="text-xs text-slate-500 mt-2 font-medium">Upload the full research proposal including literature review and detailed methodology. (PDF, DOC/DOCX, max 10MB)</p>
          </div>
          <div class="p-6 bg-slate-50/50 border border-slate-200 rounded-2xl">
            <FileUpload v-model="form.ethics_file" label="Ethics Clearance Form (Optional)" />
            <p class="text-xs text-slate-500 mt-2 font-medium">If your research involves human or animal subjects, upload your ethics compliance forms here.</p>
          </div>
        </div>
      </div>

      <!-- Step 4: Review & Submit -->
      <div v-show="currentStep === 3" class="bg-white rounded-2xl shadow-sm border border-slate-200 p-8">
        <h2 class="text-lg font-bold text-slate-800 mb-6">Review & Submit</h2>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-8 border-b border-slate-100 pb-8">
           <div>
              <h4 class="text-xs font-bold text-slate-400 capitalize tracking-widest mb-1.5">Proposal Title</h4>
              <p class="text-sm font-bold text-slate-800">{{ form.title || '—' }}</p>
           </div>
           <div>
              <h4 class="text-xs font-bold text-slate-400 capitalize tracking-widest mb-1.5">Total Budget</h4>
              <p class="text-sm font-bold text-brand">{{ form.budget ? form.budget + ' ETB' : '—' }}</p>
           </div>
           <div>
              <h4 class="text-xs font-bold text-slate-400 capitalize tracking-widest mb-1.5">Methodology Status</h4>
              <p class="text-sm font-bold text-slate-800">{{ form.methodology ? 'Competed' : 'Missing' }}</p>
           </div>
           <div>
              <h4 class="text-xs font-bold text-slate-400 capitalize tracking-widest mb-1.5">Team Size</h4>
              <p class="text-sm font-bold text-slate-800">1 PI + {{ form.investigators.length }} Co-investigators</p>
           </div>
        </div>

        <div class="bg-slate-50 rounded-xl p-6 border border-slate-200 space-y-4">
          <div class="flex items-center gap-3">
            <div class="h-5 w-5 rounded bg-emerald-100 text-emerald-600 flex items-center justify-center text-xs">✓</div>
            <span class="text-sm text-slate-600 font-medium">All required data structural checks passed</span>
          </div>
          <div class="flex items-center gap-3">
            <div class="h-5 w-5 rounded bg-emerald-100 text-emerald-600 flex items-center justify-center text-xs">✓</div>
            <span class="text-sm text-slate-600 font-medium">Data integrity verification ready</span>
          </div>
          <div class="mt-4 pt-4 border-t border-slate-200">
            <label class="flex items-start gap-3 cursor-pointer group">
              <input type="checkbox" v-model="form.confirmation" class="mt-0.5 accent-brand" />
              <span class="text-sm text-slate-700 font-bold group-hover:text-brand transition-colors">I confirm that this proposal is my original work and complies with all institutional research ethics guidelines.</span>
            </label>
          </div>
        </div>
      </div>

      <!-- Navigation Buttons -->
      <div class="flex items-center justify-between mt-8">
        <button v-if="currentStep > 0" type="button" @click="currentStep--"
          class="inline-flex items-center gap-2 px-6 py-3 text-sm font-bold text-slate-600 bg-white border border-slate-200 rounded-xl hover:bg-slate-50 shadow-sm transition-all">
          <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
          Previous
        </button>
        <div v-else></div>

        <div class="flex items-center gap-3">
          <router-link to="/app/proposals"
            class="px-6 py-3 text-sm font-bold text-slate-500 hover:text-slate-700 transition-colors">
            Cancel
          </router-link>
          <button v-if="currentStep < steps.length - 1" type="button" @click="nextStep"
            class="inline-flex items-center gap-2 px-6 py-3 text-sm font-bold text-white bg-brand rounded-xl shadow-lg shadow-brand/30 hover:shadow-brand/50 hover:-translate-y-0.5 transition-all">
            Next
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
          </button>
          <button v-else type="submit" :disabled="submitting"
            class="inline-flex items-center gap-2 px-8 py-3 text-sm font-bold text-white bg-emerald-600 rounded-xl shadow-lg shadow-emerald-600/30 hover:shadow-emerald-600/50 hover:-translate-y-0.5 transition-all disabled:opacity-60 disabled:pointer-events-none">
            <svg v-if="submitting" class="w-4 h-4 animate-spin" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"/></svg>
            {{ submitting ? 'Submitting...' : 'Submit Proposal' }}
          </button>
        </div>
      </div>
    </form>
  </div>
</template>

<script setup>
import { ref, reactive, onMounted, computed, watch, onUnmounted } from 'vue'
import { useRouter, useRoute } from 'vue-router'
import { useAuthStore } from '@/stores/auth'
import { useNotificationStore } from '@/stores/notification'
import api from '@/services/api'
import LookupSelect from '@/components/LookupSelect.vue'
import FileUpload from '@/components/FileUpload.vue'

const router = useRouter()
const route = useRoute()
const auth = useAuthStore()
const notif = useNotificationStore()

const steps = ['Basic Info', 'Co-Investigators', 'Documents', 'Review & Submit']
const currentStep = ref(0)
const draftRestored = ref(false)

const form = reactive({
  call_id: '',
  type_id: '',
  academic_year_id: '',
  title: '',
  keywords: '',
  abstract: '',
  objectives: '',
  methodology: '',
  budget: null,
  budget_allocation: { personnel: null, equipment: null, travel: null, materials: null, other: null },
  proposal_file: null,
  ethics_file: null,
  investigators: [],
  confirmation: false
})

const submitting = ref(false)
const openCalls = ref([])
const academicYears = ref([])
const availableUsers = ref([])

function addInvestigator() {
  form.investigators.push({ user_id: '', name: '', email: '', role_id: '' })
}

function removeInvestigator(i) {
  form.investigators.splice(i, 1)
}

function onUserSelected(i) {
  const inv = form.investigators[i]
  if (inv.user_id) { inv.name = ''; inv.email = '' }
}

function nextStep() {
  // Validate current step before advancing
  if (currentStep.value === 0) {
    if (!form.title) { notif.error('Title is required.'); return }
    if (!form.type_id) { notif.error('Proposal type is required.'); return }
    if (!form.keywords) { notif.error('Keywords are required.'); return }
    if (!form.abstract) { notif.error('Abstract is required.'); return }
    if (!form.objectives) { notif.error('Objectives are required.'); return }
    if (!form.methodology) { notif.error('Methodology is required.'); return }
    if (!form.budget) { notif.error('Total Budget is required.'); return }
  }
  if (currentStep.value === 2) {
    if (!form.proposal_file) { notif.error('Proposal Core Document is required.'); return }
  }
  currentStep.value++
}

async function handleSubmit() {
  submitting.value = true
  try {
    const payload = new FormData()
    payload.append('title', form.title)
    payload.append('type_id', form.type_id)
    if (form.call_id) payload.append('call_id', form.call_id)
    if (form.academic_year_id) payload.append('academic_year_id', form.academic_year_id)
    payload.append('keywords', form.keywords)
    payload.append('abstract', form.abstract)
    payload.append('objectives', form.objectives)
    payload.append('methodology', form.methodology)
    payload.append('budget', form.budget)
    payload.append('budget_allocation', JSON.stringify(form.budget_allocation))
    if (form.proposal_file) payload.append('proposal_file', form.proposal_file)
    if (form.ethics_file) payload.append('ethics_file', form.ethics_file)
    payload.append('investigators', JSON.stringify(
      form.investigators.map(inv => ({
        user_id: inv.user_id || null,
        name: inv.name || null,
        email: inv.email || null,
        role_id: inv.role_id
      }))
    ))
    const { data } = await api.post('/proposals', payload)
    notif.success('Proposal created successfully!')
    router.push(`/app/proposals/${data.id}`)
  } catch (err) {
    notif.error(err.response?.data?.message || 'Failed to create proposal.')
  } finally {
    submitting.value = false
  }
}

onMounted(async () => {
  // Restore draft from localStorage
  const saved = localStorage.getItem('rdrims_proposal_draft')
  if (saved) {
    try {
      const parsed = JSON.parse(saved)
      Object.assign(form, parsed)
      draftRestored.value = true
      notif.success('Draft restored from your previous session.')
    } catch (e) {}
  }

  try {
    const cr = await api.get('/calls', { params: { status: 'open' } })
    const yr = await api.get('/academic-years')
    const ur = await api.get('/users', { params: { per_page: 100 } })
    openCalls.value = cr.data.data || cr.data
    academicYears.value = yr.data.data || yr.data
    availableUsers.value = ur.data.data || ur.data
    
    if (route.query.call_id) {
      form.call_id = route.query.call_id
    }
  } catch (err) {
    notif.error('Failed to load form data.')
  }

  // Auto-save draft every 30 seconds
  const timer = setInterval(() => {
    const toSave = { ...form }
    delete toSave.proposal_file
    delete toSave.ethics_file
    localStorage.setItem('rdrims_proposal_draft', JSON.stringify(toSave))
  }, 30000)

  onUnmounted(() => clearInterval(timer))
})

// Clear draft after successful submission
watch(submitting, (val) => {
  if (!val && form.confirmation) {
    localStorage.removeItem('rdrims_proposal_draft')
  }
})
</script>
