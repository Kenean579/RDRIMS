<template>
  <div class="flex flex-col gap-8 pb-8 animate-fade">
    <!-- Header -->
    <div class="card p-8 flex flex-col md:flex-row md:items-center justify-between gap-6 border-b-4 border-b-brand/10">
      <div>
        <router-link to="/app/projects" class="flex items-center gap-2 text-brand font-semibold text-xs mb-4 hover:translate-x-1 transition-transform">
          <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M10 19l-7-7m0 0l7-7m-7 7h18" /></svg>
          Back to Projects
        </router-link>
        <h1 class="text-2xl font-bold text-slate-900 tracking-tight leading-tight max-w-2xl">{{ project.title || 'Project Loading...' }}</h1>
        <p class="text-slate-500 font-medium mt-2 text-xs flex items-center gap-2">
          Project ID: {{ project.id ? String(project.id).padStart(4, '0') : '...' }}
          <span class="w-1.5 h-1.5 rounded-full bg-slate-200"></span>
          Original Proposal ID: {{ project.proposal_id || '...' }}
        </p>
      </div>
      <div v-if="!loading" class="flex items-center gap-3">
        <StatusBadge :status="project.status?.name" size="lg" />
        <button v-if="(isPI || auth.hasRole('super_admin', 'research_admin', 'campus_admin', 'faculty_admin', 'director', 'department_head')) && project.status?.name !== 'completed' && allMilestonesDone && project.milestones?.length > 0" 
          @click="completeProject" 
          class="btn bg-brand hover:bg-brand-dark text-white text-xs font-medium h-11 px-6">
          Mark Completed
        </button>
      </div>
    </div>

    <!-- Loading State -->
    <div v-if="loading" class="grid grid-cols-1 lg:grid-cols-3 gap-5">
      <div class="lg:col-span-2 space-y-8">
        <div class="card p-8 h-48 animate-pulse bg-slate-50"></div>
        <div class="card p-8 h-96 animate-pulse bg-slate-50"></div>
      </div>
      <div class="card p-8 h-64 animate-pulse bg-slate-50"></div>
    </div>

    <!-- Main Content -->
    <div v-else class="grid grid-cols-1 lg:grid-cols-3 gap-5 font-bold">
      
      <!-- Tabs -->
      <div class="lg:col-span-3 card p-1.5 bg-slate-50/50 border border-slate-100 inline-flex gap-2">
        <button v-for="t in projectTabs" :key="t.key" @click="projectTab = t.key"
          class="px-5 py-2 rounded-xl text-xs font-bold transition-all"
          :class="projectTab === t.key ? 'bg-brand text-white shadow-lg shadow-brand/20' : 'bg-white text-slate-600 border border-slate-200 hover:text-brand'">
          {{ t.label }}
        </button>
      </div>

      <!-- Left Column: Primary Details & Tasks -->
      <div class="lg:col-span-2 space-y-8">
        <!-- Overview Grid -->
        <div class="card p-8">
          <h2 class="text-xs font-bold text-slate-400 mb-5 flex items-center gap-2  tracking-widest">
            <span class="w-1 h-3 bg-brand rounded-full"></span>
            Project Overview
          </h2>
          <div class="grid grid-cols-2 sm:grid-cols-4 gap-6 text-sm">
            <div>
              <p class="text-xs font-bold text-slate-400 mb-1.5 ml-1 ">Start Date</p>
              <div class="font-bold text-slate-700 bg-slate-50 p-3 rounded-2xl border border-slate-100">{{ formatDate(project.start_date) }}</div>
            </div>
            <div>
              <p class="text-xs font-bold text-slate-400 mb-1.5 ml-1 ">Target End</p>
              <div class="font-bold text-slate-700 bg-slate-50 p-3 rounded-2xl border border-slate-100">{{ formatDate(project.end_date) }}</div>
            </div>
            <div>
              <p class="text-xs font-bold text-slate-400 mb-1.5 ml-1 ">Total Budget</p>
              <div class="font-bold text-slate-700 bg-slate-50 p-3 rounded-2xl border border-slate-100">{{ formatCurrency(project.total_budget) }}</div>
            </div>
            <div>
              <p class="text-xs font-bold text-slate-400 mb-1.5 ml-1 ">Remaining</p>
              <div class="font-bold text-emerald-600 bg-emerald-50 p-3 rounded-2xl border border-emerald-100">{{ formatCurrency(project.remaining_budget || (Number(project.total_budget) - Number(project.spent_amount))) }}</div>
            </div>
          </div>
          <div class="mt-6 pt-6 border-t border-slate-100">
            <p class="text-xs font-bold text-slate-400 mb-2 ml-1  tracking-widest">Abstract Reference</p>
            <p class="text-xs font-medium text-slate-600 leading-relaxed bg-slate-50 p-4 rounded-2xl border border-slate-100 line-clamp-3 hover:line-clamp-none transition-all">
              {{ project.proposal?.abstract || 'No abstract provided in original proposal.' }}
            </p>
          </div>
        </div>

        <!-- Milestones Panel -->
        <div v-show="projectTab === 'milestones'" class="card p-8">
          <div class="flex items-center justify-between mb-5">
            <h2 class="text-xs font-bold text-slate-400 flex items-center gap-2  tracking-widest">
              <span class="w-1 h-3 bg-brand rounded-full"></span>
              Execution Milestones
            </h2>
            <button v-if="isPI || auth.hasRole('super_admin', 'research_admin', 'campus_admin', 'faculty_admin', 'director', 'department_head')" @click="showAddMilestone = true"
              class="inline-flex items-center gap-1.5 px-4 py-2 text-xs font-bold text-brand bg-brand/10 rounded-2xl hover:bg-brand/20 transition-colors">
              <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/></svg>
              Add Milestone
            </button>
          </div>

          <div v-if="project.milestones?.length" class="space-y-6">
            <div v-for="m in project.milestones" :key="m.id" 
              class="border border-slate-100 rounded-2xl group transition-all"
              :class="m.status?.name === 'completed' ? 'bg-emerald-50/20 border-emerald-100' : 'bg-white shadow-sm hover:shadow-md'">
              
              <!-- Milestone Header -->
              <div class="p-5 flex items-start gap-4">
                <div class="w-10 h-10 rounded-2xl flex items-center justify-center shrink-0 border"
                  :class="m.status?.name === 'completed' ? 'bg-emerald-100 text-emerald-600 border-emerald-200' : 'bg-slate-50 text-slate-400 border-slate-100'">
                  <svg v-if="m.status?.name === 'completed'" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"/></svg>
                  <span v-else class="text-xs font-bold">{{ m.percentage }}%</span>
                </div>
                
                <div class="flex-1 min-w-0 pt-0.5">
                  <div class="flex items-center justify-between mb-1">
                    <h3 class="text-base font-bold text-slate-800 leading-tight" :class="{ 'line-through opacity-80': m.status?.name === 'completed' }">
                      {{ m.title }}
                    </h3>
                    <div class="flex items-center gap-1 opacity-0 group-hover:opacity-100 transition-opacity">
                       <button v-if="isPI || auth.hasRole('super_admin', 'research_admin', 'campus_admin', 'faculty_admin', 'director', 'department_head')" @click="editMilestone(m)" class="w-7 h-7 flex items-center justify-center text-slate-300 hover:text-brand hover:bg-brand/5 rounded-lg transition-colors">
                         <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"/></svg>
                       </button>
                       <button v-if="isPI || auth.hasRole('super_admin', 'research_admin', 'campus_admin', 'faculty_admin', 'director', 'department_head')" @click="confirmDeleteMilestone(m)" class="w-7 h-7 flex items-center justify-center text-slate-300 hover:text-rose-500 hover:bg-rose-50 rounded-lg transition-colors">
                         <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M6 18L18 6M6 6l12 12"/></svg>
                       </button>
                    </div>
                  </div>
                  <p v-if="m.description" class="text-xs font-medium text-slate-500 mb-3 line-clamp-2 leading-relaxed italic">{{ m.description }}</p>
                  <div class="flex items-center gap-3 text-xs font-bold text-slate-400">
                    <span class="flex items-center gap-1">
                      <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                      {{ formatDate(m.due_date) }}
                    </span>
                    <span class="w-1 h-1 rounded-full bg-slate-300"></span>
                    <span>Order: {{ m.display_order || 'N/A' }}</span>
                  </div>
                </div>

                <div class="flex flex-col items-end gap-2 shrink-0 ml-4">
                  <StatusBadge :status="m.status?.name" />
                  <button @click="m.showTasks = !m.showTasks" class="text-xs font-bold text-brand hover:underline px-3 py-1.5 bg-brand/5 rounded-2xl border border-brand/10">
                    Show Tasks ({{ m.tasks?.length || 0 }})
                  </button>
                </div>
              </div>

              <!-- Tasks List -->
              <div v-show="m.showTasks" class="border-t border-slate-100 bg-slate-50/50 p-5 rounded-b-2xl">
                <div class="space-y-2.5 mb-4 max-h-64 overflow-y-auto pr-2 custom-scrollbar">
                  <div v-for="t in (m.tasks || [])" :key="t.id" 
                    class="flex items-center gap-3 bg-white p-3 rounded-2xl border transition-colors shadow-sm group/task"
                    :class="t.status?.name === 'done' ? 'border-emerald-100 bg-emerald-50/30' : 'border-slate-100 hover:border-brand/30'">
                    
                    <input type="checkbox" :checked="t.status?.name === 'done'" @change="toggleTask(t)" 
                      class="w-5 h-5 rounded border-slate-300 text-brand focus:ring-brand cursor-pointer focus:ring-offset-0" />
                    
                    <div class="flex-1 min-w-0">
                      <p class="text-sm font-bold text-slate-700 leading-tight" :class="{'line-through text-slate-400': t.status?.name === 'done'}">
                        {{ t.title }}
                      </p>
                    </div>

                    <div class="flex items-center gap-2 opacity-0 group-hover/task:opacity-100 transition-opacity">
                       <button @click="confirmDeleteTask(t)" class="w-6 h-6 flex items-center justify-center text-slate-300 hover:text-rose-500 rounded-lg">
                          <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M6 18L18 6M6 6l12 12"/></svg>
                       </button>
                    </div>
                    
                    <span class="px-2 py-1 bg-slate-100 rounded-2xl text-xs font-bold text-slate-500 shrink-0  tracking-tighter">
                      {{ t.assigned_to?.name ? getInitials(t.assigned_to.name) : 'UN' }}
                    </span>
                  </div>
                  
                  <p v-if="!m.tasks?.length" class="text-xs font-bold text-slate-400 text-center py-4 italic">
                    No action items defined for this phase.
                  </p>
                </div>

                <!-- Add Task Input -->
                <div v-if="isPI || auth.hasRole('super_admin', 'research_admin', 'campus_admin', 'faculty_admin', 'director', 'department_head')" class="flex items-center gap-3">
                  <input v-model="newTaskTitles[m.id]" type="text" placeholder="Type a new task and press enter..." 
                    class="flex-1 border border-slate-300 rounded-2xl px-4 py-2.5 text-xs font-bold text-slate-700 outline-none focus:ring-2 focus:ring-brand/30 focus:border-brand transition-all bg-white shadow-inner" 
                    @keyup.enter="addTask(m)" />
                  <button @click="addTask(m)" :disabled="!newTaskTitles[m.id]" 
                    class="btn bg-brand hover:bg-brand-dark text-white px-5 h-[38px] text-xs font-bold disabled:opacity-50 shadow-lg shadow-brand/10">
                    Add Task
                  </button>
                </div>
              </div>
            </div>
          </div>
          
          <div v-else class="text-center py-12 border-2 border-dashed border-slate-100 rounded-3xl">
            <div class="h-16 w-16 bg-slate-50 rounded-2xl flex items-center justify-center mx-auto mb-4 text-slate-300 border border-slate-100">
              <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/></svg>
            </div>
            <p class="text-sm font-bold text-slate-600 mb-1">Execution Roadmap Missing</p>
            <p class="text-xs font-medium text-slate-400">Break down the project lifecycle into manageable milestones.</p>
          </div>
        </div>

        <!-- Timeline Panel -->
        <div v-show="projectTab === 'timeline'" class="card p-8">
          <h2 class="text-xs font-bold text-slate-400 mb-6 flex items-center gap-2  tracking-widest">
            <span class="w-1 h-3 bg-brand rounded-full"></span>
            Project Timeline
          </h2>
          <div v-if="!project.milestones?.length" class="text-center py-10">
            <p class="text-sm font-bold text-slate-500">No milestones to display on timeline.</p>
          </div>
          <div v-else class="space-y-4">
            <div v-for="(m, idx) in sortedMilestones" :key="m.id" class="relative flex items-start gap-4">
              <div class="flex flex-col items-center">
                <div class="w-4 h-4 rounded-full border-2"
                  :class="m.status?.name === 'completed' ? 'bg-emerald-500 border-emerald-600' : 'bg-brand border-brand-dark'"></div>
                <div v-if="idx < sortedMilestones.length - 1" class="w-0.5 h-full bg-slate-200 mt-1"></div>
              </div>
              <div class="flex-1 pb-6">
                <div class="flex items-center justify-between p-4 bg-white border border-slate-100 rounded-2xl">
                  <div>
                    <h4 class="text-sm font-bold text-slate-800" :class="{ 'line-through opacity-80': m.status?.name === 'completed' }">{{ m.title }}</h4>
                    <p class="text-xs text-slate-500 mt-1">{{ formatDate(m.due_date) }}</p>
                  </div>
                  <StatusBadge :status="m.status?.name" />
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- Right Column: Finance & Team -->
      <div class="flex flex-col gap-5">
        
        <!-- Finance Tracking -->
        <div class="card p-8">
          <h2 class="text-xs font-bold text-slate-400 mb-6 flex items-center gap-2  tracking-widest">
            <span class="w-1 h-3 bg-brand rounded-full"></span>
            Financial Health
          </h2>
          
          <!-- Radial Progress / Spend Ratio -->
          <div class="flex items-center justify-center mb-6">
             <div class="relative w-36 h-36 flex items-center justify-center rounded-full bg-slate-50 border-8 border-slate-100 shadow-inner">
                <svg class="absolute inset-0 w-full h-full transform -rotate-90" viewBox="0 0 100 100">
                   <circle cx="50" cy="50" r="44" fill="none" stroke="currentColor" stroke-width="8" class="text-slate-100/50" />
                   <circle cx="50" cy="50" r="44" fill="none" stroke="currentColor" stroke-width="8" class="text-brand transition-all duration-1000 ease-out" 
                    :stroke-dasharray="276" :stroke-dashoffset="276 - (276 * spendRatio / 100)" stroke-linecap="round" />
                </svg>
                <div class="text-center absolute z-10 flex flex-col items-center">
                  <span class="text-2xl font-black text-slate-800 leading-none">{{ spendRatio }}<span class="text-lg opacity-40">%</span></span>
                  <span class="text-xs font-bold text-slate-400 mt-1  tracking-tighter">Budget Utilization</span>
                </div>
             </div>
          </div>

          <div class="space-y-4 text-sm mt-4 font-bold">
            <div class="flex justify-between items-center p-4 rounded-2xl bg-rose-50 border border-rose-100">
              <span class="text-xs text-rose-500  tracking-widest">Spent</span>
              <span class="text-rose-600 tracking-tight">{{ formatCurrency(project.spent_amount) }}</span>
            </div>
            <div class="flex justify-between items-center p-4 rounded-2xl bg-slate-50 border border-slate-100">
              <span class="text-xs text-slate-500  tracking-widest">Disbursed</span>
              <span class="text-slate-700 tracking-tight">{{ formatCurrency(project.disbursed_amount) }}</span>
            </div>
            
            <div class="pt-4 mt-2 border-t border-slate-100">
              <router-link :to="`/app/projects/${project.id}/finance`" class="btn bg-white border border-slate-100 hover:border-brand w-full h-11 text-xs font-bold text-slate-600 hover:text-brand transition-all shadow-sm">
                View Financial Audit
              </router-link>
            </div>
          </div>
        </div>

        <!-- Project Team -->
        <div class="card p-8">
          <h2 class="text-xs font-bold text-slate-400 mb-6 flex items-center gap-2  tracking-widest">
            <span class="w-1 h-3 bg-brand rounded-full"></span>
            Execution Team
          </h2>
          <div class="flex flex-col gap-4">
            <!-- PI First -->
            <div v-if="project.pi" class="flex items-center gap-4 p-4 rounded-2xl bg-brand/5 border border-brand/10 shadow-sm relative overflow-hidden">
              <div class="absolute right-0 top-0 w-20 h-20 bg-brand/5 rounded-bl-full z-0"></div>
              <div class="w-12 h-12 bg-brand text-white rounded-2xl flex items-center justify-center text-sm font-bold  shrink-0 z-10 overflow-hidden shadow-lg shadow-brand/10">
                <img v-if="imageUrl(project.pi?.profile_image)" :src="imageUrl(project.pi?.profile_image)" class="w-full h-full object-cover"/>
                <span v-else>{{ getInitials(project.pi?.name) }}</span>
              </div>
              <div class="z-10 min-w-0">
                <p class="text-sm font-bold text-slate-800 truncate leading-tight">{{ project.pi?.name }}</p>
                <p class="text-xs font-bold text-brand mt-1  tracking-widest">Lead PI</p>
              </div>
            </div>
            
            <!-- Other Investigators -->
            <div v-for="inv in project.investigators" :key="inv.id" class="flex items-center gap-4 p-4 rounded-2xl bg-slate-50/80 border border-slate-100 hover:bg-white transition-all card-hover">
              <div class="w-11 h-11 bg-white text-slate-500 border border-slate-100 rounded-2xl flex items-center justify-center text-xs font-bold  shrink-0 overflow-hidden shadow-sm">
                <img v-if="imageUrl(inv.user?.profile_image)" :src="imageUrl(inv.user?.profile_image)" class="w-full h-full object-cover"/>
                <span v-else>{{ getInitials(inv.user?.name) }}</span>
              </div>
              <div class="min-w-0">
                <p class="text-xs font-bold text-slate-700 truncate leading-tight">{{ inv.user?.name }}</p>
                <p class="text-xs font-bold text-slate-400 mt-1  tracking-widest">{{ inv.role?.name || 'Co-PI' }}</p>
              </div>
            </div>
          </div>
        </div>

      </div>
    </div>

    <!-- Upsert Milestone Modal -->
    <Modal :show="showAddMilestone || !!editingMilestone" :title="editingMilestone ? 'Modify Phase Details' : 'Design Action Phase'" size="md" @close="closeMilestoneModal">
      <form @submit.prevent="saveMilestone" class="space-y-6">
         <div>
            <label class="block text-xs font-bold text-slate-500 mb-2 ml-1 ">Objective Title *</label>
            <input v-model="milestoneForm.title" type="text" required placeholder="e.g. Scientific Data Analysis"
              class="input h-12 font-bold" />
         </div>
         <div class="grid grid-cols-2 gap-6">
            <div>
              <label class="block text-xs font-bold text-slate-500 mb-2 ml-1 ">Target Deadline *</label>
              <input v-model="milestoneForm.due_date" type="date" required 
                class="input h-12 font-bold" />
            </div>
            <div>
              <label class="block text-xs font-bold text-slate-500 mb-2 ml-1 ">Roadmap Weight (%) *</label>
              <input v-model.number="milestoneForm.percentage" type="number" required min="1" max="100" 
                class="input h-12 font-bold" />
            </div>
         </div>
         <div>
            <label class="block text-xs font-bold text-slate-500 mb-2 ml-1 ">Execution Sequence</label>
            <input v-model.number="milestoneForm.display_order" type="number" 
              class="input h-12 font-bold" placeholder="Priority order (1, 2, ...)" />
         </div>
         <div>
            <label class="block text-xs font-bold text-slate-500 mb-2 ml-1 ">Scope Outline</label>
            <textarea v-model="milestoneForm.description" rows="3" class="input p-4 text-xs font-medium resize-none shadow-inner" placeholder="Brief technical summary..."></textarea>
         </div>
         <div class="flex justify-end gap-3 pt-6 border-t border-slate-100">
           <button type="button" @click="closeMilestoneModal" class="btn btn-secondary px-6 font-bold text-xs">Discard</button>
           <button type="submit" class="btn btn-primary px-8 font-bold text-xs h-12 shadow-xl shadow-brand/20">
             {{ editingMilestone ? 'Synchronize Phase' : 'Activate Phase' }}
           </button>
         </div>
      </form>
    </Modal>

    <!-- Confirm Dialogs -->
    <ConfirmDialog :show="showDeleteMilestone" title="Collapse Lifecycle Phase" message="Are you sure you want to remove this milestone? All associated tasks will be permanently purged from the roadmap." confirmText="Purge Milestone" variant="danger" @confirm="deleteMilestone" @cancel="showDeleteMilestone = false" />
    <ConfirmDialog :show="showDeleteTask" title="Purge Action Item" message="Remove this task from the execution queue?" confirmText="Confirm Purge" variant="danger" @confirm="deleteTask" @cancel="showDeleteTask = false" />
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
import ConfirmDialog from '@/components/ConfirmDialog.vue'
import { formatDate, formatCurrency, getInitials, imageUrl } from '@/utils/formatters'

const route = useRoute(); const auth = useAuthStore(); const notif = useNotificationStore()
const project = ref({}); const loading = ref(true)
const projectTab = ref('milestones')
const showAddMilestone = ref(false)
const editingMilestone = ref(null)
const milestoneForm = reactive({ title: '', due_date: '', percentage: 10, display_order: 1, description: '' })
const newTaskTitles = reactive({})

const showDeleteMilestone = ref(false); const deletingMilestone = ref(null)
const showDeleteTask = ref(false); const deletingTask = ref(null)

const projectTabs = [
  { key: 'milestones', label: 'Milestones' },
  { key: 'timeline', label: 'Timeline' },
]

const isPI = computed(() => auth.user?.id === project.value.pi_id)

const allMilestonesDone = computed(() => {
  if (!project.value.milestones || project.value.milestones.length === 0) return false;
  return project.value.milestones.every(m => m.status?.name === 'completed' || m.status?.name === 'done');
})

const spendRatio = computed(() => {
  const total = Number(project.value.total_budget) || 0
  const spent = Number(project.value.spent_amount) || 0
  if (total <= 0) return 0
  return Math.round((spent / total) * 100)
})

const sortedMilestones = computed(() => {
  return [...(project.value.milestones || [])].sort((a, b) => {
    const ad = a.due_date ? new Date(a.due_date) : new Date(0)
    const bd = b.due_date ? new Date(b.due_date) : new Date(0)
    return ad - bd
  })
})

async function fetchProject() {
  loading.value = true
  try { 
    const { data } = await api.get(`/projects/${route.params.id}`)
    project.value = data 
  }
  catch (e) { notif.error('Failed to load project details') }
  finally { loading.value = false }
}

async function completeProject() {
  try {
    await api.put(`/projects/${project.value.id}/status`, { status: 'completed' });
    notif.success('Project Lifecycle Finalized!');
    fetchProject();
  } catch(e) { notif.error('Finalization blocked. Complete all roadmap items.'); }
}

// Milestone Logic
function editMilestone(m) {
  editingMilestone.value = m
  Object.assign(milestoneForm, {
    title: m.title,
    due_date: m.due_date?.split('T')[0] || '',
    percentage: m.percentage,
    display_order: m.display_order,
    description: m.description || ''
  })
}

function closeMilestoneModal() {
  showAddMilestone.value = false
  editingMilestone.value = null
  Object.assign(milestoneForm, { title: '', due_date: '', percentage: 10, display_order: 1, description: '' })
}

async function saveMilestone() {
  try {
    const payload = { ...milestoneForm, project_id: project.value.id }
    if (editingMilestone.value) {
      await api.put(`/projects/${project.value.id}/milestones/${editingMilestone.value.id}`, payload)
      notif.success('Phase updated!')
    } else {
      await api.post(`/projects/${project.value.id}/milestones`, payload)
      notif.success('Phase activated!')
    }
    closeMilestoneModal(); fetchProject()
  } catch (err) { notif.error('Operational failure during sync') }
}

function confirmDeleteMilestone(m) { deletingMilestone.value = m; showDeleteMilestone.value = true }
async function deleteMilestone() {
  try {
    await api.delete(`/projects/${project.value.id}/milestones/${deletingMilestone.value.id}`)
    notif.success('Phase purged'); showDeleteMilestone.value = false; fetchProject()
  } catch (e) { notif.error('Failed to purge phase') }
}

// Task Logic
async function toggleTask(task) {
  try {
    // 6 = not_started, 7 = done (assuming from earlier inspection)
    const newStatusId = task.status?.name === 'done' ? 6 : 7;
    await api.put(`/tasks/${task.id}`, { status_id: newStatusId });
    fetchProject();
  } catch(e) { notif.error('Synchronization failed'); }
}

async function addTask(m) {
  if (!newTaskTitles[m.id]) return;
  try {
    await api.post('/tasks', { milestone_id: m.id, title: newTaskTitles[m.id], status_id: 6 });
    newTaskTitles[m.id] = '';
    fetchProject();
  } catch(e) { notif.error('Task generation failed'); }
}

function confirmDeleteTask(t) { deletingTask.value = t; showDeleteTask.value = true }
async function deleteTask() {
  try {
    await api.delete(`/tasks/${deletingTask.value.id}`)
    notif.success('Action item purged'); showDeleteTask.value = false; fetchProject()
  } catch (e) { notif.error('Failed to purge item') }
}

onMounted(fetchProject)
</script>

<style scoped>
.custom-scrollbar::-webkit-scrollbar { width: 4px; }
.custom-scrollbar::-webkit-scrollbar-track { background: #f8fafc; }
.custom-scrollbar::-webkit-scrollbar-thumb { background: #e2e8f0; border-radius: 10px; }
</style>

