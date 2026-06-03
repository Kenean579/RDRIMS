<template>
  <div class="flex flex-col gap-8 pb-12 animate-fade card">
    <!-- Header -->
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-6">
      <div>
        <router-link to="/app/outputs" class="flex items-center gap-2 text-indigo-600 font-black capitalize tracking-widest text-[10px] mb-3 hover:translate-x-1 transition-transform">
          <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M10 19l-7-7m0 0l7-7m-7 7h18" /></svg>
          Back to repository
        </router-link>
        <h1 class="text-3xl font-black text-slate-900 tracking-tight leading-tight max-w-2xl">{{ output.title || 'Institutional Output' }}</h1>
        <p class="text-slate-500 font-medium mt-1 capitalize tracking-widest text-[9px]">Theses, internships, and standardized research outputs.</p>
      </div>
      <div v-if="!loading" class="flex items-center gap-3">
        <StatusBadge :status="output.status?.name" size="lg" />
      </div>
    </div>

    <div v-if="loading" class="grid grid-cols-1 lg:grid-cols-3 gap-8">
      <div class="lg:col-span-2 space-y-8">
        <div class="card h-48 animate-pulse bg-slate-50/50"></div>
        <div class="card h-96 animate-pulse bg-slate-50/50"></div>
      </div>
      <div class="card h-64 animate-pulse bg-slate-50/50"></div>
    </div>

    <template v-else>
      <div class="grid grid-cols-1 lg:grid-cols-3 gap-8 font-bold">
        <!-- Main Content -->
        <div class="lg:col-span-2 space-y-8">
          <!-- Output Specification -->
          <div class="card p-8 space-y-8">
            <h2 class="text-xs font-black text-slate-400 capitalize tracking-widest mb-6 flex items-center gap-2">
              <span class="w-1 h-3 bg-indigo-500 rounded-full"></span>
              Output Specification
            </h2>
            <dl class="grid grid-cols-1 sm:grid-cols-2 gap-8 text-sm">
              <div>
                <dt class="text-[10px] font-black text-slate-400 capitalize tracking-widest mb-1.5 ml-1">Title & Identification</dt>
                <dd class="text-sm font-black text-slate-800 bg-slate-50 p-4 rounded-xl border border-slate-100">{{ output.title }}</dd>
              </div>
              <div>
                <dt class="text-[10px] font-black text-slate-400 capitalize tracking-widest mb-1.5 ml-1">Classification</dt>
                <dd class="p-4 rounded-xl bg-white border border-slate-100 flex flex-wrap gap-2">
                  <span class="px-2.5 py-1 bg-indigo-50 text-indigo-600 rounded-lg text-[10px] font-black capitalize tracking-widest">{{ output.category?.name }}</span>
                  <span class="px-2.5 py-1 bg-slate-50 text-slate-500 rounded-lg text-[10px] font-black capitalize tracking-widest">{{ output.subtype?.name }}</span>
                </dd>
              </div>
              <div v-if="output.student_level">
                <dt class="text-[10px] font-black text-slate-400 capitalize tracking-widest mb-1.5 ml-1">Academic Level</dt>
                <dd class="p-4 rounded-xl bg-white border border-slate-100 text-slate-700 font-bold capitalize tracking-widest text-[11px]">{{ output.student_level?.name }}</dd>
              </div>
              <div v-if="output.project">
                <dt class="text-[10px] font-black text-slate-400 capitalize tracking-widest mb-1.5 ml-1">Associated Project</dt>
                <dd class="p-4 rounded-xl bg-white border border-slate-100 text-slate-700 font-bold truncate">{{ output.project?.title }}</dd>
              </div>
            </dl>
            <div class="pt-6 border-t border-slate-50">
              <p class="text-[10px] font-black text-slate-400 capitalize tracking-widest mb-3 ml-1">Research Abstract</p>
              <p class="text-sm font-medium text-slate-600 leading-relaxed italic border-l-4 border-slate-100 pl-6">{{ output.abstract }}</p>
            </div>
            <div v-if="output.feedback" class="mt-4 bg-amber-50/50 border border-amber-100 rounded-2xl p-6">
              <p class="text-[10px] font-black text-amber-600 capitalize tracking-widest mb-2">Reviewer Feedback</p>
              <p class="text-sm font-semibold text-amber-900 italic">{{ output.feedback }}</p>
            </div>
          </div>

          <!-- Institutional Approval Workflow -->
          <div class="card p-8 bg-white border border-slate-100 shadow-sm relative overflow-hidden">
            <div class="absolute top-0 right-0 w-32 h-32 bg-brand/5 rounded-full -translate-y-1/2 translate-x-1/2"></div>
            <h2 class="text-xs font-black text-slate-400 capitalize tracking-widest mb-6 flex items-center gap-2 relative z-10">
              <span class="w-1 h-3 bg-brand rounded-full"></span>
              Institutional Approval Workflow
            </h2>
            
            <div class="flex items-center gap-4 mb-8">
              <div class="p-3 bg-slate-50 rounded-2xl border border-slate-100 italic text-[10px] font-black text-slate-400 capitalize tracking-widest">Current State</div>
              <StatusBadge :status="output.status?.name || 'draft'" size="lg" />
            </div>
            
            <div class="flex flex-wrap gap-4 font-black capitalize tracking-widest">
               <!-- Student Submission -->
               <button v-if="output.status?.name === 'draft'" @click="changeStatus(2)" class="btn bg-brand hover:bg-indigo-700 text-white h-11 px-8 text-[11px] shadow-lg shadow-brand/20">Submit Final Output</button>
               
               <!-- Supervisor Approval -->
               <template v-if="output.status?.name === 'submitted'">
                 <button @click="changeStatus(3)" class="btn bg-emerald-500 hover:bg-emerald-600 text-white h-11 px-8 text-[11px] shadow-lg shadow-emerald-500/20">Supervisor Clearance</button>
                 <button @click="changeStatus(5)" class="btn bg-rose-500 hover:bg-rose-600 text-white h-11 px-8 text-[11px] shadow-lg shadow-rose-500/20">Reject Submission</button>
               </template>

               <!-- Department Head Final Sign-off -->
               <template v-if="output.status?.name === 'approved_by_supervisor'">
                 <button @click="changeStatus(4)" class="btn bg-indigo-600 hover:bg-indigo-700 text-white h-11 px-8 text-[11px] shadow-lg shadow-indigo-600/20">Final Dept Approval</button>
                 <button @click="changeStatus(5)" class="btn bg-rose-500 hover:bg-rose-600 text-white h-11 px-8 text-[11px] shadow-lg shadow-rose-500/20">Reject Output</button>
               </template>
            </div>
          </div>
        </div>

        <div class="flex flex-col gap-8">
          <!-- Participants Widget -->
          <div class="card p-8 bg-white border border-slate-100 shadow-sm">
            <div class="flex items-center justify-between mb-6">
              <h2 class="text-xs font-black text-slate-400 capitalize tracking-widest flex items-center gap-2">
                <span class="w-1 h-3 bg-brand rounded-full"></span>
                Key Contributors
              </h2>
              <button @click="showAddParticipant = true" class="text-[10px] font-black text-brand capitalize tracking-widest hover:underline">+ Assign</button>
            </div>
            
            <div v-if="output.participants?.length" class="space-y-4">
              <div v-for="p in output.participants" :key="p.id" class="flex items-center justify-between p-4 rounded-2xl bg-slate-50 border border-slate-100 group hover:border-brand/30 transition-all">
                <div class="flex items-center gap-3">
                  <div class="w-8 h-8 bg-brand-light text-brand rounded-lg flex items-center justify-center text-[10px] font-black capitalize shadow-inner">{{ p.user?.name?.substring(0,1) }}</div>
                  <div class="min-w-0">
                    <p class="text-sm font-black text-slate-800 leading-tight truncate">{{ p.user?.name }}</p>
                    <p class="text-[9px] font-black text-slate-400 capitalize tracking-widest mt-0.5">{{ p.participant_type?.name }}</p>
                  </div>
                </div>
                <button @click="removeParticipant(p)" class="p-2 text-slate-300 hover:text-rose-500 opacity-0 group-hover:opacity-100 transition-all shrink-0">
                  <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                </button>
              </div>
            </div>
            <p v-else class="text-[10px] font-black text-slate-400 capitalize tracking-widest italic text-center py-6">No contributors assigned.</p>
          </div>
        </div>
      </div>
    </template>

    <Modal :show="showAddParticipant" title="Assign Contributor" @close="showAddParticipant = false">
      <form @submit.prevent="addParticipant" class="space-y-5 px-1 py-1">
        <div>
          <label class="block text-[11px] text-slate-500 font-bold capitalize tracking-wider mb-1.5 ml-1">System User *</label>
          <select v-model="participantForm.user_id" required class="input">
            <option value="">Select User</option>
            <option v-for="u in users" :key="u.id" :value="u.id">{{ u.name }}</option>
          </select>
        </div>
        <div>
          <label class="block text-[11px] text-slate-500 font-bold capitalize tracking-wider mb-1.5 ml-1">Engagement Role *</label>
          <select v-model="participantForm.participant_type_id" required class="input">
            <option value="">Select Role</option>
            <option v-for="t in participantTypes" :key="t.id" :value="t.id">{{ t.name }}</option>
          </select>
        </div>
        <div class="flex justify-end gap-3 pt-4">
          <button type="button" @click="showAddParticipant = false" class="btn btn-secondary">Discard</button>
          <button type="submit" class="btn btn-primary px-10">Assign User</button>
        </div>
      </form>
    </Modal>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import { useRoute } from 'vue-router'
import { useNotificationStore } from '@/stores/notification'
import api from '@/services/api'
import StatusBadge from '@/components/StatusBadge.vue'
import LoadingSkeleton from '@/components/LoadingSkeleton.vue'
import Modal from '@/components/Modal.vue'
import { formatCurrency } from '@/utils/formatters'
import { formatStatusName } from '@/utils/colors'

const route = useRoute()
const notif = useNotificationStore()
const output = ref({})
const loading = ref(true)
const outputStatuses = ref([])
const showAddParticipant = ref(false)
const participantForm = ref({ user_id: '', participant_type_id: '' })
const users = ref([])
const participantTypes = ref([])

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

onMounted(async () => {
  await fetchOutput()
  try {
    const [ss, us, pts] = await Promise.all([
      api.get('/lookups/output_statuses'), 
      api.get('/users', { params: { per_page: 200 } }), 
      api.get('/lookups/participant_types')
    ])
    outputStatuses.value = ss.data
    users.value = us.data.data || us.data
    participantTypes.value = pts.data
  } catch (e) {}
})
</script>
