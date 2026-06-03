<template>
  <div class="flex flex-col gap-8 pb-12 animate-fade card">
    <!-- Header -->
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
      <div>
        <router-link to="/app/ethics-requests" class="flex items-center gap-2 text-brand font-black capitalize tracking-widest text-[10px] mb-3 hover:translate-x-1 transition-transform">
          <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M10 19l-7-7m0 0l7-7m-7 7h18" /></svg>
          Back to list
        </router-link>
        <h1 class="text-3xl font-black text-slate-900 tracking-tight">Ethics Approval</h1>
        <p class="text-slate-500 font-medium mt-1 capitalize tracking-widest text-[9px]">Reviewing submission details for safety and ethics.</p>
      </div>
      <div v-if="!loading" class="flex items-center gap-3">
        <StatusBadge :status="request.status?.name" size="lg" />
      </div>
    </div>

    <div v-if="loading" class="grid grid-cols-1 lg:grid-cols-3 gap-8">
      <div class="lg:col-span-2 card h-96 animate-pulse bg-slate-50/50 rounded-3xl"></div>
      <div class="card h-64 animate-pulse bg-slate-50/50 rounded-3xl"></div>
    </div>

    <div v-else class="grid grid-cols-1 lg:grid-cols-3 gap-8 font-bold">
      <!-- Main Content -->
      <div class="lg:col-span-2 space-y-8">
        <!-- Proposal Info -->
        <div class="card p-8 border-l-4 border-l-brand/20">
          <div class="flex items-center gap-4 mb-8">
            <div class="w-12 h-12 rounded-2xl bg-brand-light flex items-center justify-center text-brand shadow-inner">
               <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253" /></svg>
            </div>
            <div>
              <h2 class="text-lg font-black text-slate-900 tracking-tight">About the Work</h2>
              <p class="text-[9px] font-black text-slate-400 capitalize tracking-widest mt-0.5">Submission details and goals.</p>
            </div>
          </div>

          <h3 class="text-2xl font-black text-slate-900 mb-4 leading-tight tracking-tight">{{ request.proposal?.title }}</h3>
          <p class="text-slate-500 font-medium leading-relaxed mb-10 italic border-l-2 border-slate-100 pl-6">{{ request.proposal?.abstract }}</p>
          
          <div class="grid grid-cols-1 md:grid-cols-2 gap-6 p-6 bg-slate-50 rounded-2xl border border-slate-100 shadow-inner">
             <div>
               <p class="text-[9px] font-black text-slate-400 capitalize tracking-widest mb-1">Applicant</p>
               <p class="text-sm font-black text-slate-800">{{ request.proposal?.pi?.name }}</p>
             </div>
             <div>
               <p class="text-[9px] font-black text-slate-400 capitalize tracking-widest mb-1">Focus Area</p>
               <p class="text-sm font-black text-slate-800">{{ request.proposal?.thematic_area?.name || 'N/A' }}</p>
             </div>
          </div>
        </div>

        <!-- Documentation -->
        <div class="card p-8">
          <div class="flex items-center gap-4 mb-8">
            <div class="w-12 h-12 rounded-2xl bg-amber-50 flex items-center justify-center text-amber-500 shadow-inner">
               <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" /></svg>
            </div>
            <div>
              <h2 class="text-lg font-black text-slate-900 tracking-tight">Documents</h2>
              <p class="text-[9px] font-black text-slate-400 capitalize tracking-widest mt-0.5">Supported files for review.</p>
            </div>
          </div>

          <div v-if="request.files?.length" class="grid grid-cols-1 md:grid-cols-2 gap-4">
             <div v-for="f in request.files" :key="f.id" class="flex items-center justify-between p-4 bg-white border border-slate-100 rounded-2xl hover:border-brand hover:shadow-lg transition-all group overflow-hidden relative shadow-sm">
                <div class="flex items-center gap-4 relative z-10">
                   <div class="w-10 h-10 bg-slate-50 flex items-center justify-center rounded-xl text-slate-400 font-bold group-hover:text-brand transition-colors">📄</div>
                   <span class="text-xs font-black text-slate-700 truncate max-w-[140px] capitalize tracking-widest text-[9px]">{{ f.file_path.split('/').pop() }}</span>
                </div>
                <button @click="downloadFile(f)" class="btn btn-secondary h-9 px-4 text-[9px] font-black capitalize tracking-widest relative z-10 hover:bg-brand hover:text-white transition-all">Download</button>
             </div>
          </div>
          <div v-else class="p-12 text-center bg-slate-50/50 rounded-3xl border border-dashed border-slate-200">
             <p class="text-xs font-black text-slate-400 capitalize tracking-widest italic leading-relaxed">No specific ethics documents were uploaded with this submission.</p>
          </div>
        </div>
      </div>

      <!-- Actions Sidebar -->
      <div class="flex flex-col gap-8">
        <div class="card p-8 bg-slate-900 text-white shadow-2xl shadow-slate-900/40 relative overflow-hidden group">
          <div class="absolute inset-0 bg-brand/10 opacity-0 group-hover:opacity-100 transition-opacity duration-700"></div>
          <div class="relative z-10">
            <h2 class="text-lg font-black tracking-tight mb-6 flex items-center gap-3">
              <span class="w-2 h-8 bg-brand rounded-full"></span>
              Take Action
            </h2>
            
            <div class="space-y-6">
               <div>
                  <label class="block text-[9px] font-black text-slate-500 capitalize tracking-widest mb-3 ml-1">Your Comments</label>
                  <textarea v-model="decisionNote" rows="5" class="w-full bg-white/5 border border-white/10 rounded-2xl p-4 text-sm font-medium text-white focus:ring-2 focus:ring-brand outline-none resize-none shadow-inner" placeholder="Explain your decision..."></textarea>
               </div>
               
               <div class="flex flex-col gap-3 pt-4">
                  <button @click="submitDecision('approved')" class="btn bg-emerald-500 hover:bg-emerald-600 text-white h-12 rounded-2xl font-black capitalize tracking-widest text-[11px] shadow-lg shadow-emerald-500/20 border-0">
                    Approve Work
                  </button>
                  <button @click="submitDecision('needs_revision')" class="btn bg-amber-500 hover:bg-amber-600 text-white h-12 rounded-2xl font-black capitalize tracking-widest text-[11px] shadow-lg shadow-amber-500/20 border-0">
                    Ask for Changes
                  </button>
                  <button @click="submitDecision('rejected')" class="btn bg-rose-600 hover:bg-rose-700 text-white h-12 rounded-2xl font-black capitalize tracking-widest text-[11px] shadow-lg shadow-rose-600/20 border-0">
                    Reject It
                  </button>
               </div>
               
               <div class="pt-6 mt-6 border-t border-white/5">
                 <p class="text-[9px] font-black text-slate-500 capitalize tracking-widest italic leading-relaxed">Warning: Decisions are final and will notify the researcher immediately.</p>
               </div>
            </div>
          </div>
        </div>

        <div class="card p-6 bg-slate-50 border border-slate-100 shadow-inner rounded-3xl">
          <h4 class="text-[10px] font-black text-slate-400 capitalize tracking-widest mb-4">Quick Stats</h4>
          <div class="space-y-4">
            <div class="flex justify-between items-center group">
              <span class="text-[11px] font-black text-slate-500 capitalize tracking-widest group-hover:text-brand transition-colors">ID</span>
              <span class="text-xs font-black text-slate-800">#{{ request.id }}</span>
            </div>
            <div class="flex justify-between items-center group">
              <span class="text-[11px] font-black text-slate-500 capitalize tracking-widest group-hover:text-brand transition-colors">Revision Level</span>
              <span class="text-xs font-black text-slate-800">v1.2</span>
            </div>
          </div>
        </div>
      </div>
    </div>  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import { useRoute } from 'vue-router'
import { useNotificationStore } from '@/stores/notification'
import api from '@/services/api'
import StatusBadge from '@/components/StatusBadge.vue'

const route = useRoute(); const notif = useNotificationStore()
const request = ref({}); const loading = ref(true); const decisionNote = ref('')

async function fetchRequest() {
  loading.value = true
  try { const { data } = await api.get(`/ethics-requests/${route.params.id}`); request.value = data }
  catch (e) { notif.error('Failed to load request') }
  finally { loading.value = false }
}

async function submitDecision(status) {
  if (!decisionNote.value && status !== 'approved') return notif.warning('Please provide a note for this decision')
  try {
    await api.post(`/ethics-requests/${request.value.id}/decision`, { status, note: decisionNote.value })
    notif.success('Decision submitted successfully!')
    fetchRequest()
  } catch (err) { notif.error('Failed to submit decision') }
}

async function downloadFile(f) {
  window.open(`${import.meta.env.VITE_API_URL.replace('/api', '')}/storage/${f.file_path}`, '_blank')
}

onMounted(fetchRequest)
</script>
