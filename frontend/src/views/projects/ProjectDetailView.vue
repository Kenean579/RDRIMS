<template>
  <div card>
    <div class="mb-6"><router-link to="/projects" class="text-sm text-blue-600 hover:underline mb-2 inline-block">← Back</router-link><h1 class="text-xl font-bold text-gray-800">{{ project.title }}</h1></div>
    <div v-if="loading" class="bg-white rounded-lg p-6 animate-pulse space-y-4 shadow-sm"><div v-for="i in 6" :key="i" class="h-6 bg-gray-200 rounded"></div></div>
    <div v-else class="grid grid-cols-1 lg:grid-cols-3 gap-6 pb-20">
      <div class="lg:col-span-2 space-y-6">
        <!-- Overview -->
        <div class="bg-white rounded-lg shadow-sm border border-gray-100 p-6">
          <div class="flex items-center justify-between mb-4">
            <h2 class="text-base font-semibold text-gray-800 mb-4">Project Overview</h2>
            <button v-if="(isPI || auth.hasRole('super_admin', 'research_admin')) && project.status?.name !== 'completed' && allMilestonesDone && project.milestones?.length > 0" @click="completeProject" class="btn bg-brand hover:bg-brand-dark text-white text-[11px] font-black uppercase tracking-widest px-6 shadow-lg shadow-blue-500/20">Mark Project Completed</button>
          </div>
          <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 text-sm mb-6">
            <div><p class="text-gray-500">Status</p><div class="mt-1"><StatusBadge :status="project.status?.name || 'active'" /></div></div>
            <div><p class="text-gray-500">Duration</p><p class="text-gray-800 mt-1">{{ formatDate(project.start_date) }} to {{ formatDate(project.end_date) }}</p></div>
            <div><p class="text-gray-500">Total Budget</p><p class="text-gray-800 mt-1">{{ formatCurrency(project.total_budget) }}</p></div>
            <div><p class="text-gray-500">Remaining Budget</p><p class="text-gray-800 mt-1">{{ formatCurrency(project.remaining_budget) }}</p></div>
          </div>
          <div class="space-y-4 text-sm pt-4 border-t border-gray-50">
            <div><p class="text-gray-500 mb-1">Abstract</p><p class="text-gray-800 line-clamp-3">{{ project.proposal?.abstract }}</p></div>
          </div>
        </div>

        <!-- Milestones -->
        <div class="bg-white rounded-lg shadow-sm border border-gray-100 p-6">
          <div class="flex items-center justify-between mb-4">
            <h2 class="text-base font-semibold text-gray-800">Project Milestones</h2>
            <button v-if="isPI || auth.hasRole('super_admin', 'research_admin')" @click="showAddMilestone = true" class="text-sm text-blue-600 font-medium hover:underline">+ Add Milestone</button>
          </div>
          <div v-if="project.milestones?.length" class="space-y-3">
            <div v-for="m in project.milestones" :key="m.id" class="p-4 border border-gray-100 rounded-lg group hover:border-blue-200 transition">
              <div class="flex items-center gap-4">
                 <div class="w-8 h-8 rounded-full flex items-center justify-center font-bold text-xs" :class="m.status?.name === 'completed' ? 'bg-green-100 text-green-700' : 'bg-blue-100 text-blue-700'">{{ m.status?.name === 'completed' ? '✓' : '•' }}</div>
                 <div class="flex-1"><p class="text-sm font-medium text-gray-800">{{ m.title }}</p><p class="text-xs text-gray-500 mt-0.5">Due: {{ formatDate(m.due_date) }} | {{ m.percentage }}% Weight</p></div>
                 <div class="flex flex-col items-end gap-1">
                    <StatusBadge :status="m.status?.name" />
                    <button @click="m.showTasks = !m.showTasks" class="text-[10px] text-blue-600 font-bold hover:underline">TASKS ({{ m.tasks?.length || 0 }})</button>
                 </div>
              </div>
              <div v-if="m.showTasks" class="mt-3 pl-12 pr-4 pb-3 space-y-2 border-t border-slate-100 pt-3">
                 <div v-for="t in (m.tasks || [])" :key="t.id" class="flex items-center justify-between bg-slate-50 p-2 rounded-lg border border-slate-100">
                    <div class="flex items-center gap-3">
                       <input type="checkbox" :checked="t.status?.name === 'done'" @change="toggleTask(t)" class="rounded text-brand focus:ring-brand w-4 h-4 cursor-pointer" />
                       <span class="text-xs font-bold text-slate-700" :class="{'line-through text-slate-400': t.status?.name === 'done'}">{{ t.title }}</span>
                    </div>
                    <span class="text-[9px] font-black uppercase text-slate-400">{{ t.assigned_to?.name || 'Unassigned' }}</span>
                 </div>
                 <div v-if="isPI || auth.hasRole('super_admin', 'research_admin')" class="flex items-center gap-2 mt-2">
                    <input v-model="newTaskTitles[m.id]" type="text" placeholder="New task..." class="flex-1 border border-slate-200 rounded px-2 py-1 text-xs outline-none focus:border-brand" @keyup.enter="addTask(m)" />
                    <button @click="addTask(m)" class="btn btn-secondary px-3 py-1 text-[10px] font-black uppercase">Add</button>
                 </div>
              </div>
            </div>
          </div>
          <p v-else class="text-sm text-gray-400 text-center py-8 bg-gray-50 rounded-lg border border-dashed border-gray-200">No milestones defined yet.</p>
        </div>
      </div>

      <!-- Sidebar -->
      <div class="flex flex-col gap-6">
        <div class="bg-white rounded-lg shadow-sm border border-gray-100 p-5">
           <h2 class="text-base font-semibold text-gray-800 mb-3">Finance Summary</h2>
           <div class="space-y-3 text-sm">
             <div class="flex justify-between"><span>Spent</span><span class="font-bold text-red-600">{{ formatCurrency(project.spent_amount) }}</span></div>
             <div class="flex justify-between text-gray-400"><span>Disbursed</span><span>{{ formatCurrency(project.disbursed_amount) }}</span></div>
             <div class="w-full bg-gray-100 h-2 rounded-full overflow-hidden mt-2"><div class="bg-blue-600 h-full" :style="{ width: spendRatio + '%' }"></div></div>
             <p class="text-[10px] text-gray-500">{{ spendRatio }}% of budget utilized</p>
           </div>
        </div>

        <div class="bg-white rounded-lg shadow-sm border border-gray-100 p-5">
           <h2 class="text-base font-semibold text-gray-800 mb-3">Project Team</h2>
           <div class="space-y-3">
              <div v-for="inv in project.investigators" :key="inv.id" class="flex items-center gap-3">
                 <div class="w-8 h-8 bg-blue-100 rounded-full flex items-center justify-center font-bold text-blue-700 text-xs">{{ getInitials(inv.user?.name) }}</div>
                 <div><p class="text-sm font-medium text-gray-800">{{ inv.user?.name }}</p><p class="text-[10px] text-gray-400 capitalize">{{ inv.role?.name }}</p></div>
              </div>
           </div>
        </div>
      </div>
    </div>

    <!-- Add Milestone Modal -->
    <Modal :show="showAddMilestone" title="Add Milestone" @close="showAddMilestone = false">
      <form @submit.prevent="addMilestone" class="space-y-4">
         <div><label class="block text-sm font-medium text-gray-700 mb-1">Title *</label><input v-model="milestoneForm.title" type="text" required class="w-full border border-gray-300 rounded-lg p-2.5 text-sm focus:ring-2 focus:ring-blue-500 outline-none" /></div>
         <div class="grid grid-cols-2 gap-3">
            <div><label class="block text-sm font-medium text-gray-700 mb-1">Due Date *</label><input v-model="milestoneForm.due_date" type="date" required class="w-full border border-gray-300 rounded-lg p-2.5 text-sm focus:ring-2 focus:ring-blue-500 outline-none" /></div>
            <div><label class="block text-sm font-medium text-gray-700 mb-1">Weight (%)</label><input v-model.number="milestoneForm.percentage" type="number" required min="1" max="100" class="w-full border border-gray-300 rounded-lg p-2.5 text-sm focus:ring-2 focus:ring-blue-500 outline-none" /></div>
         </div>
         <div class="flex justify-end gap-3 pt-2"><button type="button" @click="showAddMilestone = false" class="px-4 py-2 text-sm border border-gray-300 rounded-lg">Cancel</button><button type="submit" class="px-4 py-2 text-sm bg-blue-600 text-white rounded-lg">Create</button></div>
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

const spendRatio = computed(() => {
  if (!project.value.total_budget) return 0
  return Math.round((project.value.spent_amount / project.value.total_budget) * 100)
})

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

function openTasks(m) {
  // Navigate to or open task list for milestone
  notif.info('Milestone tasks module loading...')
}

onMounted(fetchProject)
</script>
