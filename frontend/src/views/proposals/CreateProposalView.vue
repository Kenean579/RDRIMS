<template>
  <div class="max-w-4xl mx-auto pb-12">
    <!-- Header -->
    <div class="mb-5 px-1">
      <router-link to="/app/proposals" class="inline-flex items-center gap-1.5 text-[10px] font-black text-slate-400 hover:text-brand transition-all mb-4  tracking-widest group">
        <svg class="w-4 h-4 group-hover:-translate-x-1 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
        Back to Protocols
      </router-link>
      <h1 class="text-2xl font-black text-slate-900 tracking-tighter leading-none ">Initiate Research Protocol</h1>
      <p class="text-[10px] font-bold text-slate-400 mt-2  tracking-widest flex items-center gap-2">
         <span class="w-1.5 h-1.5 rounded-full bg-brand animate-pulse"></span>
         Formal submission of scientific proposal for institutional review
      </p>
    </div>

    <!-- Step Indicator -->
    <div class="flex items-center gap-2 mb-8 bg-white rounded-[2rem] border border-slate-100 p-2 shadow-sm">
      <button
        v-for="(s, i) in steps"
        :key="i"
        @click="currentStep = i"
        class="flex-1 flex items-center justify-center gap-2 px-4 py-3 rounded-2xl text-[10px] font-black  tracking-widest transition-all"
        :class="[ currentStep === i ? 'bg-brand text-white shadow-lg shadow-brand/20' : currentStep > i ? 'bg-emerald-50 text-emerald-700' : 'text-slate-400 hover:text-slate-600' ]"
      >
        <span class="hidden sm:inline">{{ s }}</span>
        <span class="sm:hidden">{{ i + 1 }}</span>
      </button>
    </div>

    <form @submit.prevent="handleSubmit" class="animate-fade">
      <!-- Step 0: Basic Information -->
      <div v-show="currentStep === 0" class="card p-8 space-y-8">
        <div class="section-header">
           <h2 class="text-[13px] font-black text-slate-900  tracking-widest flex items-center gap-3">
             <span class="w-1.5 h-4 bg-brand rounded-full"></span>
             Primary Classification
           </h2>
        </div>
        
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
          <div>
            <label class="block text-[10px] font-black text-slate-400  tracking-widest mb-3 ml-1">Research Call</label>
            <select v-model="form.call_id" class="input h-14 font-bold bg-slate-50 border-slate-100">
              <option value="">Open Call (unsolicited)</option>
              <option v-for="call in openCalls" :key="call.id" :value="call.id">{{ call.title }}</option>
            </select>
          </div>
          <div>
            <label class="block text-[10px] font-black text-slate-400  tracking-widest mb-3 ml-1">Protocol Type</label>
            <LookupSelect v-model="form.type_id" lookup-key="proposal_types" placeholder="Select type" class="h-14" />
          </div>
          <div class="md:col-span-2">
            <label class="block text-[10px] font-black text-slate-400  tracking-widest mb-3 ml-1">Scientific Title <span class="text-rose-500">*</span></label>
            <input v-model="form.title" type="text" maxlength="255" required class="input h-14 font-black text-slate-800" placeholder="e.g. Longitudinal Analysis of Crop Resilience in Highland Regions" />
          </div>
          <div>
            <label class="block text-[10px] font-black text-slate-400  tracking-widest mb-3 ml-1">Academic Year</label>
            <select v-model="form.academic_year_id" class="input h-14 font-bold bg-slate-50">
              <option value="">Select year</option>
              <option v-for="y in academicYears" :key="y.id" :value="y.id">{{ y.name }} {{ y.is_current ? '(Active)' : '' }}</option>
            </select>
          </div>
          <div>
            <label class="block text-[10px] font-black text-slate-400  tracking-widest mb-3 ml-1">Initial Budget Request (ETB) <span class="text-rose-500">*</span></label>
            <input v-model.number="form.budget" type="number" min="0" step="0.01" required class="input h-14 font-black" placeholder="0.00" />
          </div>
        </div>

        <div class="pt-8 border-t border-slate-50">
          <label class="block text-[10px] font-black text-slate-400  tracking-widest mb-4 ml-1 flex items-center justify-between">
            Expertise Mapping
            <span class="text-[9px] font-bold text-brand bg-brand/5 px-2 py-0.5 rounded-lg border border-brand/10 ">Required</span>
          </label>
          <div class="flex flex-wrap gap-2 min-h-[100px] p-6 bg-slate-50 rounded-3xl border border-slate-100">
            <button
              v-for="exp in availableExpertises" :key="exp.id"
              type="button"
              @click="toggleKeyword(exp.name)"
              class="px-4 py-2 rounded-xl text-[10px] font-black  tracking-widest transition-all border-2"
              :class="isKeywordSelected(exp.name) ? 'bg-brand border-brand text-white shadow-lg shadow-brand/20' : 'bg-white border-slate-200 text-slate-400 hover:border-brand/40 hover:text-brand'"
            >
              {{ exp.name }}
            </button>
          </div>
        </div>

        <div class="space-y-6 pt-8 border-t border-slate-50">
           <h3 class="text-[13px] font-black text-slate-900  tracking-widest flex items-center gap-3">
             Scientific Narrative
           </h3>
           <div>
             <label class="block text-[10px] font-black text-slate-400  tracking-widest mb-3 ml-1">Executive Abstract <span class="text-rose-500">*</span></label>
             <textarea v-model="form.abstract" rows="6" required class="input pt-4 font-medium leading-relaxed resize-none" placeholder="Provide a concise scientific summary..."></textarea>
           </div>
           <div>
             <label class="block text-[10px] font-black text-slate-400  tracking-widest mb-3 ml-1">Specific Objectives <span class="text-rose-500">*</span></label>
             <textarea v-model="form.objectives" rows="5" required class="input pt-4 font-medium leading-relaxed resize-none" placeholder="1. Identify...&#10;2. Measure..."></textarea>
           </div>
        </div>
      </div>

      <!-- Step 1: Co-Investigators -->
      <div v-show="currentStep === 1" class="card p-8 space-y-8">
        <div class="flex items-center justify-between">
          <div>
            <h2 class="text-[13px] font-black text-slate-900  tracking-widest flex items-center gap-3">
              <span class="w-1.5 h-4 bg-brand rounded-full"></span>
              Research Consortium
            </h2>
            <p class="text-[10px] font-bold text-slate-400 mt-1  tracking-widest">You are the lead Principal Investigator</p>
          </div>
          <button type="button" @click="addInvestigator" class="btn btn-secondary h-11 px-6 text-[10px] font-black  tracking-widest border border-slate-100 flex items-center gap-2">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M12 4v16m8-8H4"/></svg>
            Add Associate
          </button>
        </div>

        <div v-if="form.investigators.length === 0" class="p-20 text-center bg-slate-50 rounded-[2.5rem] border-2 border-dashed border-slate-200">
           <div class="w-20 h-20 rounded-3xl bg-white border border-slate-100 shadow-sm flex items-center justify-center mx-auto mb-6 text-3xl">👥</div>
           <p class="text-[11px] font-black text-slate-400  tracking-widest">No associate investigators registered</p>
        </div>

        <div v-else class="grid grid-cols-1 gap-6">
          <div v-for="(inv, index) in form.investigators" :key="index" class="p-8 bg-slate-50 rounded-[2rem] border border-slate-200 relative group">
            <button type="button" @click="removeInvestigator(index)" class="absolute -top-3 -right-3 w-10 h-10 bg-white text-rose-500 rounded-full shadow-xl flex items-center justify-center border-2 border-rose-50 hover:bg-rose-50 transition-all opacity-0 group-hover:opacity-100">
               <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M6 18L18 6M6 6l12 12"/></svg>
            </button>
            
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
              <div class="md:col-span-2">
                <label class="block text-[10px] font-black text-slate-400  tracking-widest mb-3 ml-1">Institutional Lookup / External</label>
                <select v-model="inv.user_id" @change="onUserSelected(index)" class="input h-12 font-black">
                  <option value="">External Researcher (Manual Entry)</option>
                  <option v-for="u in availableUsers" :key="u.id" :value="u.id">{{ u.name }} ({{ u.email }})</option>
                </select>
              </div>
              <div>
                <label class="block text-[10px] font-black text-slate-400  tracking-widest mb-3 ml-1">Center Role <span class="text-rose-500">*</span></label>
                <LookupSelect v-model="inv.role_id" lookup-key="investigator_roles" placeholder="Select role" class="h-12" />
              </div>
              <template v-if="!inv.user_id">
                <div>
                  <label class="block text-[10px] font-black text-slate-400  tracking-widest mb-3 ml-1">Full Legal Name</label>
                  <input v-model="inv.name" type="text" class="input h-12 font-bold" />
                </div>
                <div>
                  <label class="block text-[10px] font-black text-slate-400  tracking-widest mb-3 ml-1">Contact Email</label>
                  <input v-model="inv.email" type="email" class="input h-12 font-bold" />
                </div>
                <div>
                  <label class="block text-[10px] font-black text-slate-400  tracking-widest mb-3 ml-1">Affiliated Institution</label>
                  <input v-model="inv.institution" type="text" class="input h-12 font-bold" />
                </div>
              </template>
            </div>
          </div>
        </div>
      </div>

      <!-- Step 2: Documents -->
      <div v-show="currentStep === 2" class="card p-8 space-y-8">
        <h2 class="text-[13px] font-black text-slate-900  tracking-widest flex items-center gap-3">
          <span class="w-1.5 h-4 bg-brand rounded-full"></span>
          Evidence & Supplementary Files
        </h2>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
          <div class="group">
            <FileUpload v-model="form.proposal_file" label="Main Proposal (PDF/DOCX)" :required="true" />
            <p class="text-[9px] font-black text-slate-300 mt-4 ml-1  tracking-tighter">Required: Final technical proposal, max 20MB</p>
          </div>
          <div class="group">
            <FileUpload v-model="form.ethics_file" label="Ethics Clearance (Self-Cert)" />
            <p class="text-[9px] font-black text-slate-300 mt-4 ml-1  tracking-tighter">Optional: Supporting ethical documentation</p>
          </div>
        </div>
      </div>

      <!-- Step 3: Review & Submit -->
      <div v-show="currentStep === 3" class="card p-8 space-y-8">
        <h2 class="text-[13px] font-black text-slate-900  tracking-widest flex items-center gap-3">
          <span class="w-1.5 h-4 bg-brand rounded-full"></span>
          Pre-Submission Verification
        </h2>
        
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
           <div class="md:col-span-2 space-y-6">
              <div class="p-8 bg-slate-50 rounded-[2rem] border border-slate-100 flex flex-col gap-4">
                 <div class="flex justify-between items-center text-[11px] font-bold">
                    <span class="text-slate-400  tracking-widest">Protocol Title</span>
                    <span class="text-slate-900 text-right max-w-xs">{{ form.title }}</span>
                 </div>
                 <div class="flex justify-between items-center text-[11px] font-bold">
                    <span class="text-slate-400  tracking-widest">Requested Allocation</span>
                    <span class="text-brand font-black">{{ formatCurrency(form.budget) }}</span>
                 </div>
                 <div class="flex justify-between items-center text-[11px] font-bold">
                    <span class="text-slate-400  tracking-widest">Team Size</span>
                    <span class="text-slate-900 font-black">{{ form.investigators.length + 1 }} Researchers</span>
                 </div>
              </div>
              
              <div class="p-8 bg-white rounded-[2rem] border-2 border-slate-100">
                <label class="flex items-start gap-4 cursor-pointer">
                  <input type="checkbox" v-model="form.confirmation" class="mt-1 w-5 h-5 accent-brand rounded-lg" required />
                  <span class="text-xs font-bold text-slate-600 leading-relaxed  tracking-tight">
                    I solemnly declare that this proposal is my original work and strictly adheres to the institutional research integrity framework and ethical standards.
                  </span>
                </label>
              </div>
           </div>
           
           <div class="space-y-4">
              <div class="p-6 bg-brand/5 rounded-3xl border border-brand/10">
                 <p class="text-[9px] font-black text-brand  tracking-widest mb-4">Integrity Checklist</p>
                 <div class="space-y-3">
                    <div v-for="chk in ['Title Finalized', 'Abstract Verified', 'Team Assigned', 'Files Attached']" :key="chk" class="flex items-center gap-2 text-[10px] font-bold text-slate-600">
                       <span class="w-4 h-4 rounded-full bg-emerald-500 text-white flex items-center justify-center text-[8px]">✓</span>
                       {{ chk }}
                    </div>
                 </div>
              </div>
           </div>
        </div>
      </div>

      <!-- Navigation Buttons -->
      <div class="flex items-center justify-between mt-8 px-1">
        <button v-show="currentStep > 0" type="button" @click="currentStep--" class="btn btn-secondary h-14 px-8 text-[11px] font-black  tracking-widest border border-slate-100 shadow-sm flex items-center gap-3">
          <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M15 19l-7-7 7-7"/></svg>
          Back
        </button>
        <div v-show="currentStep === 0"></div>

        <div class="flex items-center gap-4">
          <router-link to="/app/proposals" class="text-[10px] font-black text-slate-400 hover:text-slate-600  tracking-widest px-4 transition-colors">Abort</router-link>
          
          <button v-if="currentStep < steps.length - 1" type="button" @click="nextStep" class="btn btn-primary h-14 px-10 text-[11px] font-black  tracking-widest shadow-xl shadow-brand/20 flex items-center gap-3">
            Continue
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M9 5l7 7-7 7"/></svg>
          </button>
          
          <button v-else type="submit" :disabled="submitting" class="btn bg-slate-900 text-white h-14 px-10 text-[11px] font-black  tracking-widest shadow-xl shadow-slate-900/20 hover:bg-black transition-all flex items-center gap-3">
            <svg v-if="submitting" class="w-5 h-5 animate-spin" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"/></svg>
            {{ submitting ? 'Processing Digital Signature...' : 'Submit Final Protocol' }}
          </button>
        </div>
      </div>
    </form>
  </div>
</template>

<script setup>
import { ref, reactive, onMounted, computed } from 'vue'
import { useRouter, useRoute } from 'vue-router'
import { useAuthStore } from '@/stores/auth'
import { useNotificationStore } from '@/stores/notification'
import api from '@/services/api'
import LookupSelect from '@/components/LookupSelect.vue'
import FileUpload from '@/components/FileUpload.vue'
import { formatCurrency } from '@/utils/formatters'

const router = useRouter()
const route = useRoute()
const auth = useAuthStore()
const notif = useNotificationStore()

const steps = ['Classification', 'Team Consortium', 'Documentation', 'Verification']
const currentStep = ref(0)

const form = reactive({
  call_id: '',
  type_id: '',
  academic_year_id: '',
  title: '',
  keywords: '',
  abstract: '',
  objectives: '',
  methodology: 'Detailed methodology section...',
  budget: null,
  budget_allocation: { personnel: 0, equipment: 0, travel: 0, materials: 0, other: 0 },
  proposal_file: null,
  ethics_file: null,
  investigators: [],
  confirmation: false
})

const submitting = ref(false)
const openCalls = ref([])
const academicYears = ref([])
const availableUsers = ref([])
const availableExpertises = ref([])
const investigatorRoles = ref([])

function addInvestigator() {
  form.investigators.push({ user_id: '', name: '', email: '', role_id: '' })
}

function removeInvestigator(i) {
  form.investigators.splice(i, 1)
}

function isKeywordSelected(name) {
  if (!form.keywords) return false
  return form.keywords.split(',').map(k => k.trim()).includes(name)
}

function toggleKeyword(name) {
  let keys = form.keywords ? form.keywords.split(',').map(k => k.trim()).filter(k => k) : []
  if (keys.includes(name)) keys = keys.filter(k => k !== name)
  else keys.push(name)
  form.keywords = keys.join(', ')
}

function onUserSelected(i) {
  const inv = form.investigators[i]
  if (inv.user_id) { inv.name = ''; inv.email = ''; inv.institution = '' }
}

function nextStep() {
  if (currentStep.value === 0) {
    if (!form.title) return notif.error('Protocol title is required')
    if (!form.type_id) return notif.error('Classification type is required')
    if (!form.budget) return notif.error('Initial budget request is required')
    if (!form.keywords) return notif.error('Expertise mapping is required')
    if (!form.abstract) return notif.error('Executive abstract is required')
  }
  if (currentStep.value === 2) {
    if (!form.proposal_file) return notif.error('Main proposal document is missing')
  }
  currentStep.value++
}

async function handleSubmit() {
  if (!form.confirmation) return notif.error('Integrity declaration must be confirmed')
  
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
    
    const { data } = await api.post('/proposals', payload, {
      headers: { 'Content-Type': 'multipart/form-data' }
    })
    
    notif.success('Research protocol initiated successfully!')
    router.push(`/app/proposals/${data.id}`)
  } catch (err) {
    const errorMsg = err.response?.data?.message || 'Submission protocol failure'
    notif.error(errorMsg)
  } finally {
    submitting.value = false
  }
}

onMounted(async () => {
  try {
    const [cr, yr, ur, exp, roles] = await Promise.all([
      api.get('/calls', { params: { status: 'open' } }),
      api.get('/academic-years'),
      api.get('/users', { params: { per_page: 500 } }),
      api.get('/expertise'),
      api.get('/lookups/investigator_roles')
    ])
    openCalls.value = cr.data.data || cr.data
    academicYears.value = yr.data.data || yr.data
    availableUsers.value = ur.data.data || ur.data
    availableExpertises.value = exp.data
    investigatorRoles.value = roles.data
    if (route.query.call_id) form.call_id = route.query.call_id
  } catch (err) {
    notif.error('Subsystem data fetch timeout')
  }
})
</script>


