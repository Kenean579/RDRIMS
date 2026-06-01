<template>
  <div class="flex flex-col gap-8 pb-16 animate-fade">
    <!-- Header -->
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-6">
      <div>
        <router-link to="/projects" class="flex items-center gap-2 text-brand font-black uppercase tracking-widest text-[10px] mb-3 hover:translate-x-1 transition-transform">
          <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M10 19l-7-7m0 0l7-7m-7 7h18" /></svg>
          Back to Projects
        </router-link>
        <h1 class="text-3xl font-black text-slate-900 tracking-tight leading-tight max-w-2xl">{{ project.title || 'Project Loading...' }}</h1>
        <p class="text-slate-500 font-medium mt-1 uppercase tracking-widest text-[9px] flex items-center gap-2">
          Project ID: {{ project.id ? String(project.id).padStart(4, '0') : '...' }}
          <span class="w-1 h-1 rounded-full bg-slate-300"></span>
          Original Proposal ID: {{ project.proposal_id || '...' }}
        </p>
      </div>
      <div v-if="!loading" class="flex items-center gap-3">
        <StatusBadge :status="project.status?.name" size="lg" />
        <button v-if="(isPI || auth.hasRole('super_admin', 'research_admin')) && project.status?.name !== 'completed' && allMilestonesDone && project.milestones?.length > 0" 
          @click="completeProject" 
          class="btn bg-brand hover:bg-brand-dark text-white text-[11px] font-black uppercase tracking-widest h-11 px-6 shadow-lg shadow-brand/30">
          Mark Completed
        </button>
      </div>
    </div>

    <!-- Loading State -->
    <div v-if="loading" class="grid grid-cols-1 lg:grid-cols-3 gap-8">
      <div class="lg:col-span-2 space-y-8">
        <div class="bg-white rounded-3xl border border-slate-100 p-8 h-48 animate-pulse shadow-sm"></div>
        <div class="bg-white rounded-3xl border border-slate-100 p-8 h-96 animate-pulse shadow-sm"></div>
      </div>
      <div class="bg-white rounded-3xl border border-slate-100 p-8 h-64 animate-pulse shadow-sm"></div>
    </div>

    <!-- Main Content -->
    <div v-else class="grid grid-cols-1 lg:grid-cols-3 gap-8 font-bold">
      
      <!-- Left Column: Primary Details & Tasks -->
      <div class="lg:col-span-2 space-y-8">
        <!-- Overview Grid -->
        <div class="card p-8 border-l-4 border-l-brand/20">
          <h2 class="text-xs font-black text-slate-400 uppercase tracking-widest mb-8 flex items-center gap-2">
            <span class="w-1 h-3 bg-brand rounded-full"></span>
            Project Overview
          </h2>
          <div class="grid grid-cols-2 sm:grid-cols-4 gap-6 text-sm">
            <div>
              <p class="text-[10px] font-black text-slate-400 uppercase tracking-widest mb-1.5 ml-1">Start Date</p>
              <div class="font-bold text-slate-700 bg-slate-50 p-3 rounded-xl border border-slate-100">{{ formatDate(project.start_date) }}</div>
            </div>
            <div>
              <p class="text-[10px] font-black text-slate-400 uppercase tracking-widest mb-1.5 ml-1">Target End</p>
              <div class="font-bold text-slate-700 bg-slate-50 p-3 rounded-xl border border-slate-100">{{ formatDate(project.end_date) }}</div>
            </div>
            <div>
              <p class="text-[10px] font-black text-slate-400 uppercase tracking-widest mb-1.5 ml-1">Total Budget</p>
              <div class="font-black text-slate-700 bg-slate-50 p-3 rounded-xl border border-slate-100">{{ formatCurrency(project.total_budget) }}</div>
            </div>
            <div>
              <p class="text-[10px] font-black text-slate-400 uppercase tracking-widest mb-1.5 ml-1">Remaining</p>
              <div class="font-black text-emerald-600 bg-emerald-50 p-3 rounded-xl border border-emerald-100">{{ formatCurrency(project.remaining_budget) }}</div>
            </div>
          </div>
          <div class="mt-6 pt-6 border-t border-slate-100">
            <p class="text-[10px] font-black text-slate-400 uppercase tracking-widest mb-2 ml-1">Abstract Reference</p>
            <p class="text-xs font-medium text-slate-600 leading-relaxed bg-slate-50 p-4 rounded-xl border border-slate-100 line-clamp-3 hover:line-clamp-none transition-all">
              {{ project.proposal?.abstract || 'No abstract provided in original proposal.' }}
            </p>
          </div>
        </div>

        <!-- Milestones & Tasks -->
        <div class="card p-8">
          <div class="flex items-center justify-between mb-8">
            <h2 class="text-xs font-black text-slate-400 uppercase tracking-widest flex items-center gap-2">
              <span class="w-1 h-3 bg-brand rounded-full"></span>
              Execution Milestones
            </h2>
            <button v-if="isPI || auth.hasRole('super_admin', 'research_admin')" @click="showAddMilestone = true" 
              class="inline-flex items-center gap-1.5 px-4 py-2 text-[10px] font-black uppercase tracking-widest text-brand bg-brand/10 rounded-xl hover:bg-brand/20 transition-colors">
              <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/></svg>
              Add Milestone
            </button>
          </div>

          <div v-if="project.milestones?.length" class="space-y-6">
            <div v-for="m in project.milestones" :key="m.id" 
              class="border border-slate-200 rounded-2xl group transition-all"
              :class="m.status?.name === 'completed' ? 'bg-emerald-50/30 border-emerald-100' : 'bg-white shadow-sm hover:shadow-md'">
              
              <!-- Milestone Header -->
              <div class="p-5 flex items-start gap-4">
                <div class="w-10 h-10 rounded-xl flex items-center justify-center shrink-0 border"
                  :class="m.status?.name === 'completed' ? 'bg-emerald-100 text-emerald-600 border-emerald-200' : 'bg-slate-50 text-slate-400 border-slate-200'">
                  <svg v-if="m.status?.name === 'completed'" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"/></svg>
                  <span v-else class="text-xs font-black">{{ m.percentage }}%</span>
                </div>
                
                <div class="flex-1 min-w-0 pt-0.5">
                  <h3 class="text-base font-black text-slate-800 leading-tight mb-1" :class="{ 'line-through opacity-80': m.status?.name === 'completed' }">
                    {{ m.title }}
                  </h3>
                  <div class="flex items-center gap-3 text-[10px] font-black uppercase tracking-widest text-slate-400">
                    <span class="flex items-center gap-1">
                      <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                      Due: {{ formatDate(m.due_date) }}
                    </span>
                    <span class="w-1 h-1 rounded-full bg-slate-300"></span>
                    <span>Weight: {{ m.percentage }}%</span>
                  </div>
                </div>

                <div class="flex flex-col items-end gap-2 shrink-0">
                  <StatusBadge :status="m.status?.name" />
                  <button @click="m.showTasks = !m.showTasks" class="text-[9px] font-black uppercase tracking-widest text-brand hover:underline px-2 py-1 bg-brand/5 rounded-lg border border-brand/10">
                    Tasks ({{ m.tasks?.length || 0 }})
                  </button>
                </div>
              </div>

              <!-- Tasks List -->
              <div v-show="m.showTasks" class="border-t border-slate-100 bg-slate-50/50 p-5 rounded-b-2xl">
                <div class="space-y-2.5 mb-4 max-h-64 overflow-y-auto pr-2 custom-scrollbar">
                  <div v-for="t in (m.tasks || [])" :key="t.id" 
                    class="flex items-center gap-3 bg-white p-3 rounded-xl border transition-colors shadow-sm"
                    :class="t.status?.name === 'done' ? 'border-emerald-100 bg-emerald-50/30 opacity-70' : 'border-slate-200 hover:border-brand/30'">
                    
                    <input type="checkbox" :checked="t.status?.name === 'done'" @change="toggleTask(t)" 
                      class="w-5 h-5 rounded border-slate-300 text-brand focus:ring-brand cursor-pointer focus:ring-offset-0" />
                    
                    <div class="flex-1 min-w-0">
                      <p class="text-sm font-bold text-slate-700 leading-tight" :class="{'line-through text-slate-400': t.status?.name === 'done'}">
                        {{ t.title }}
                      </p>
                    </div>
                    
                    <span class="px-2 py-1 bg-slate-100 rounded-lg text-[9px] font-black uppercase tracking-widest text-slate-500 shrink-0">
                      {{ t.assigned_to?.name ? getInitials(t.assigned_to.name) : 'Unassigned' }}
                    </span>
                  </div>
                  
                  <p v-if="!m.tasks?.length" class="text-[10px] font-black uppercase tracking-widest text-slate-400 text-center py-4 italic">
                    No tasks added to this milestone.
                  </p>
                </div>

                <!-- Add Task Input -->
                <div v-if="isPI || auth.hasRole('super_admin', 'research_admin')" class="flex items-center gap-3">
                  <input v-model="newTaskTitles[m.id]" type="text" placeholder="Type a new task and press enter..." 
                    class="flex-1 border border-slate-300 rounded-xl px-4 py-2.5 text-xs font-bold text-slate-700 outline-none focus:ring-2 focus:ring-brand/30 focus:border-brand transition-all bg-white" 
                    @keyup.enter="addTask(m)" />
                  <button @click="addTask(m)" :disabled="!newTaskTitles[m.id]" 
                    class="btn bg-slate-800 hover:bg-slate-900 text-white px-5 h-[38px] text-[10px] font-black uppercase tracking-widest disabled:opacity-50">
                    Add
                  </button>
                </div>
              </div>
            </div>
          </div>
          
          <div v-else class="text-center py-12 border-2 border-dashed border-slate-200 rounded-2xl">
            <div class="h-16 w-16 bg-slate-50 rounded-full flex items-center justify-center mx-auto mb-4 text-slate-300">
              <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/></svg>
            </div>
            <p class="text-sm font-black text-slate-600 mb-1">No milestones defined</p>
            <p class="text-[10px] font-black uppercase tracking-widest text-slate-400">Break down the project into manageable phases.</p>
          </div>
        </div>
      </div>

      <!-- Right Column: Finance & Team -->
      <div class="flex flex-col gap-8">
        
        <!-- Finance Tracking -->
        <div class="card p-8">
          <h2 class="text-xs font-black text-slate-400 uppercase tracking-widest mb-6 flex items-center gap-2">
            <span class="w-1 h-3 bg-brand rounded-full"></span>
            Financial Tracking
          </h2>
          
          <!-- Radial Progress / Spend Ratio -->
          <div class="flex items-center justify-center mb-8">
             <div class="relative w-32 h-32 flex items-center justify-center rounded-full bg-slate-50 border-[6px] border-slate-100 shadow-inner">
                <!-- A faux circular indicator -->
                <svg class="absolute inset-0 w-full h-full transform -rotate-90" viewBox="0 0 100 100">
                   <circle cx="50" cy="50" r="46" fill="none" stroke="currentColor" stroke-width="8" class="text-slate-100" />
                   <circle cx="50" cy="50" r="46" fill="none" stroke="currentColor" stroke-width="8" class="text-brand transition-all duration-1000 ease-out" 
                    :stroke-dasharray="289" :stroke-dashoffset="289 - (289 * spendRatio / 100)" stroke-linecap="round" />
                </svg>
                <div class="text-center absolute z-10 flex flex-col items-center">
                  <span class="text-3xl font-black text-slate-800 leading-none">{{ spendRatio }}<span class="text-lg">%</span></span>
                  <span class="text-[8px] font-black uppercase tracking-widest text-slate-400 mt-1">Utilized</span>
                </div>
             </div>
          </div>

          <div class="space-y-4 text-sm mt-4">
            <div class="flex justify-between items-center p-3 rounded-xl bg-rose-50 border border-rose-100">
              <span class="text-[10px] font-black uppercase tracking-widest text-rose-500">Amount Spent</span>
              <span class="font-black text-rose-600">{{ formatCurrency(project.spent_amount) }}</span>
            </div>
            <div class="flex justify-between items-center p-3 rounded-xl bg-slate-50 border border-slate-100">
              <span class="text-[10px] font-black uppercase tracking-widest text-slate-500">Total Disbursed</span>
              <span class="font-black text-slate-700">{{ formatCurrency(project.disbursed_amount) }}</span>
            </div>
            
            <div class="pt-4 mt-2 border-t border-slate-100">
              <router-link :to="`/projects/${project.id}/finance`" class="btn bg-white border border-slate-200 hover:border-brand w-full h-10 text-[10px] font-black uppercase tracking-widest text-slate-600 hover:text-brand transition-all shadow-sm">
                View Full Ledgers
              </router-link>
            </div>
          </div>
        </div>

        <!-- Project Team -->
        <div class="card p-8">
          <h2 class="text-xs font-black text-slate-400 uppercase tracking-widest mb-6 flex items-center gap-2">
            <span class="w-1 h-3 bg-brand rounded-full"></span>
            Project Team
          </h2>
          <div class="flex flex-col gap-4">
            <!-- PI First -->
            <div class="flex items-center gap-4 p-4 rounded-2xl bg-brand/5 border border-brand/10 shadow-sm relative overflow-hidden">
              <div class="absolute right-0 top-0 w-16 h-16 bg-brand/10 rounded-bl-full z-0"></div>
              <div class="w-10 h-10 bg-brand text-white rounded-xl flex items-center justify-center text-xs font-black uppercase shadow-lg shadow-brand/30 shrink-0 z-10">
                {{ getInitials(project.pi?.name) }}
              </div>
              <div class="z-10 min-w-0">
                <p class="text-sm font-black text-slate-800 truncate">{{ project.pi?.name || 'Unknown' }}</p>
                <p class="text-[9px] font-black text-brand uppercase tracking-widest mt-0.5">Principal Investigator</p>
              </div>
            </div>
            
            <!-- Other Investigators -->
            <div v-for="inv in project.investigators" :key="inv.id" class="flex items-center gap-4 p-3 rounded-2xl bg-slate-50/80 border border-slate-100 hover:bg-white transition-colors">
              <div class="w-9 h-9 bg-slate-200 text-slate-500 rounded-xl flex items-center justify-center text-[10px] font-black uppercase shrink-0">
                {{ getInitials(inv.user?.name) }}
              </div>
              <div class="min-w-0">
                <p class="text-xs font-black text-slate-700 truncate">{{ inv.user?.name }}</p>
                <p class="text-[9px] font-black text-slate-400 uppercase tracking-widest mt-0.5">{{ inv.role?.name || 'Co-Investigator' }}</p>
              </div>
            </div>
          </div>
        </div>

      </div>
    </div>

    <!-- Add Milestone Modal -->
    <Modal :show="showAddMilestone" title="Define New Milestone" @close="showAddMilestone = false">
      <form @submit.prevent="addMilestone" class="space-y-6">
         <div>
            <label class="block text-[10px] font-black text-slate-500 uppercase tracking-widest mb-2 ml-1">Milestone Title *</label>
            <input v-model="milestoneForm.title" type="text" required placeholder="e.g. Phase 1 Data Collection"
              class="w-full border border-slate-300 rounded-xl px-4 py-3 text-sm font-bold focus:ring-2 focus:ring-brand/30 focus:border-brand outline-none transition-all" />
         </div>
         <div class="grid grid-cols-2 gap-6">
            <div>
              <label class="block text-[10px] font-black text-slate-500 uppercase tracking-widest mb-2 ml-1">Due Date *</label>
              <input v-model="milestoneForm.due_date" type="date" required 
                class="w-full border border-slate-300 rounded-xl px-4 py-3 text-sm font-bold focus:ring-2 focus:ring-brand/30 focus:border-brand outline-none transition-all" />
            </div>
            <div>
              <label class="block text-[10px] font-black text-slate-500 uppercase tracking-widest mb-2 ml-1">Weight (%) *</label>
              <input v-model.number="milestoneForm.percentage" type="number" required min="1" max="100" 
                class="w-full border border-slate-300 rounded-xl px-4 py-3 text-sm font-bold focus:ring-2 focus:ring-brand/30 focus:border-brand outline-none transition-all" />
            </div>
         </div>
         <div class="flex justify-end gap-3 pt-6 border-t border-slate-100">
           <button type="button" @click="showAddMilestone = false" class="btn btn-secondary px-8 h-11 text-[11px] font-black uppercase tracking-widest">Cancel</button>
           <button type="submit" class="btn btn-primary px-10 h-11 shadow-lg shadow-blue-500/20 text-[11px] font-black uppercase tracking-widest">Create</button>
         </div>
      </form>
    </Modal>
  </div>
</template>

<script setup>
import { ref, reactive, computed, onMounted } from 'vue'
import { useRoute } from 'vue-router'
import { useAuthStore } from '@/stores/auth'
import { useNotificationStore } from '@/stores/notification'
import api from '@/services/api'
import StatusBadge from '@/components/StatusBadge.vue'
import Modal from '@/components/Modal.vue'
import { formatDate, formatCurrency, getInitials } from '@/utils/formatters'

const route = useRoute(); const auth = useAuthStore(); const notif = useNotificationStore()
const project = ref({}); const loading = ref(true)
const showAddMilestone = ref(false)
const milestoneForm = reactive({ title: '', due_date: '', percentage: 10 })
const newTaskTitles = reactive({})

const isPI = computed(() => auth.user?.id === project.value.pi_id)

const allMilestonesDone = computed(() => {
  if (!project.value.milestones || project.value.milestones.length === 0) return false;
  return project.value.milestones.every(m => m.status?.name === 'completed' || m.status?.name === 'done');
})

const spendRatio = computed(() => {
  if (!project.value.total_budget) return 0
  return Math.round((project.value.spent_amount / project.value.total_budget) * 100)
})

async function completeProject() {
  try {
    await api.put(`/projects/${project.value.id}/status`, { status: 'completed' });
    notif.success('Project marked as completed successfully!');
    fetchProject();
  } catch(e) { notif.error('Failed to complete project. Ensure all milestones are done.'); }
}

async function toggleTask(task) {
  try {
    const newStatus = task.status?.name === 'done' ? 6 : 7; // Status ID mapping (dummy)
    await api.put(`/tasks/${task.id}`, { status_id: newStatus });
    fetchProject();
  } catch(e) { notif.error('Failed modifying task'); }
}

async function addTask(m) {
  if (!newTaskTitles[m.id]) return;
  try {
    await api.post('/tasks', { milestone_id: m.id, title: newTaskTitles[m.id], status_id: 6 }); // 6 = not_started
    newTaskTitles[m.id] = '';
    fetchProject();
  } catch(e) { notif.error('Failed generating task'); }
}

async function fetchProject() {
  loading.value = true
  try { const { data } = await api.get(`/projects/${route.params.id}`); project.value = data }
  catch (e) { notif.error('Failed to load project details') }
  finally { loading.value = false }
}

async function addMilestone() {
  try {
    await api.post(`/projects/${project.value.id}/milestones`, milestoneForm)
    notif.success('Milestone added!')
    showAddMilestone.value = false; Object.assign(milestoneForm, { title: '', due_date: '', percentage: 10 }); fetchProject()
  } catch (err) { notif.error('Failed to add milestone') }
}

onMounted(fetchProject)
</script>

<style scoped>
.custom-scrollbar::-webkit-scrollbar {
  width: 4px;
}
.custom-scrollbar::-webkit-scrollbar-track {
  background: #f1f5f9;
  border-radius: 4px;
}
.custom-scrollbar::-webkit-scrollbar-thumb {
  background: #cbd5e1;
  border-radius: 4px;
}
</style>
