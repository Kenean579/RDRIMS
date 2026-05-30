<template>
  <div card>
    <div class="mb-6">
      <router-link to="/outputs" class="text-sm text-blue-600 hover:underline mb-2 inline-block">← Back to Outputs</router-link>
      <h1 class="text-xl font-bold text-gray-800">{{ output.title || 'Output Detail' }}</h1>
    </div>

    <div v-if="loading" class="bg-white rounded-lg shadow-sm p-6"><LoadingSkeleton :rows="6" /></div>

    <template v-else>
      <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <div class="lg:col-span-2 space-y-6">
          <div class="bg-white rounded-lg shadow-sm border border-gray-100 p-6">
            <h2 class="text-base font-semibold text-gray-800 mb-4">Details</h2>
            <dl class="grid grid-cols-1 sm:grid-cols-2 gap-4 text-sm">
              <div><dt class="text-gray-500">Title</dt><dd class="font-medium text-gray-800 mt-0.5">{{ output.title }}</dd></div>
              <div><dt class="text-gray-500">Status</dt><dd class="mt-0.5"><StatusBadge :status="output.status?.name || 'draft'" /></dd></div>
              <div><dt class="text-gray-500">Category</dt><dd class="text-gray-800 mt-0.5">{{ output.category?.name }}</dd></div>
              <div><dt class="text-gray-500">Subtype</dt><dd class="text-gray-800 mt-0.5">{{ output.subtype?.name }}</dd></div>
              <div v-if="output.student_level"><dt class="text-gray-500">Student Level</dt><dd class="text-gray-800 mt-0.5">{{ output.student_level?.name }}</dd></div>
              <div v-if="output.project"><dt class="text-gray-500">Project</dt><dd class="text-gray-800 mt-0.5">{{ output.project?.title }}</dd></div>
              <div v-if="output.budget"><dt class="text-gray-500">Budget</dt><dd class="text-gray-800 mt-0.5">{{ formatCurrency(output.budget) }}</dd></div>
            </dl>
            <div class="mt-4">
              <p class="text-gray-500 text-sm mb-1">Abstract</p>
              <p class="text-sm text-gray-800 whitespace-pre-line">{{ output.abstract }}</p>
            </div>
            <div v-if="output.feedback" class="mt-4 bg-amber-50 border border-amber-200 rounded-lg p-3">
              <p class="text-xs text-gray-500">Feedback</p>
              <p class="text-sm text-amber-700">{{ output.feedback }}</p>
            </div>
          </div>

          <div class="bg-white rounded-lg shadow-sm border border-gray-100 p-6">
            <h2 class="text-base font-semibold text-gray-800 mb-4">Approval Workflow</h2>
            <div class="flex items-center gap-3">
              <span class="text-xs text-gray-500 font-bold uppercase tracking-widest">Current Status:</span>
              <StatusBadge :status="output.status?.name || 'draft'" />
            </div>
            
            <div class="flex gap-3 mt-5">
               <button v-if="output.status?.name === 'draft'" @click="changeStatus(2)" class="btn bg-brand hover:bg-brand-dark text-white text-[11px] font-black uppercase px-6">Submit Output</button>
               <button v-if="output.status?.name === 'submitted'" @click="changeStatus(3)" class="btn bg-emerald-500 hover:bg-emerald-600 text-white text-[11px] font-black uppercase px-6">Supervisor Approve</button>
               <button v-if="output.status?.name === 'approved_by_supervisor' || output.status?.name === 'submitted'" @click="changeStatus(4)" class="btn bg-teal-600 hover:bg-teal-700 text-white text-[11px] font-black uppercase px-6">Head Approve</button>
               <button v-if="output.status?.name === 'submitted' || output.status?.name === 'approved_by_supervisor'" @click="changeStatus(5)" class="btn bg-rose-500 hover:bg-rose-600 text-white text-[11px] font-black uppercase px-6">Reject</button>
            </div>
          </div>
        </div>

        <div class="flex flex-col gap-6">
          <div class="bg-white rounded-lg shadow-sm border border-gray-100 p-5">
            <div class="flex items-center justify-between mb-3">
              <h2 class="text-base font-semibold text-gray-800">Participants</h2>
              <button @click="showAddParticipant = true" class="text-sm text-blue-600 hover:text-blue-800 font-medium">+ Add</button>
            </div>
            <div v-if="output.participants?.length" class="space-y-2">
              <div v-for="p in output.participants" :key="p.id" class="flex items-center justify-between p-2 rounded hover:bg-gray-50">
                <div>
                  <p class="text-sm font-medium text-gray-800">{{ p.user?.name }}</p>
                  <p class="text-xs text-gray-500">{{ p.participant_type?.name }}</p>
                </div>
                <button @click="removeParticipant(p)" class="text-red-500 text-xs hover:underline">Remove</button>
              </div>
            </div>
            <p v-else class="text-sm text-gray-400">No participants.</p>
          </div>
        </div>
      </div>
    </template>

    <Modal :show="showStatusChange" title="Change Status" @close="showStatusChange = false">
      <form @submit.prevent="changeStatus" class="space-y-4">
        <div>
          <label class="block text-sm font-medium text-gray-700 mb-1">New Status</label>
          <select v-model="newStatusId" required class="w-full border border-gray-300 rounded-lg px-4 py-2.5 text-sm focus:ring-2 focus:ring-blue-500 outline-none bg-white">
            <option value="">Select</option>
            <option v-for="s in outputStatuses" :key="s.id" :value="s.id">{{ formatStatusName(s.name) }}</option>
          </select>
        </div>
        <div class="flex justify-end gap-3"><button type="button" @click="showStatusChange = false" class="px-4 py-2 text-sm border border-gray-300 rounded-lg">Cancel</button><button type="submit" class="px-4 py-2 text-sm bg-blue-600 text-white rounded-lg">Change</button></div>
      </form>
    </Modal>

    <Modal :show="showAddParticipant" title="Add Participant" @close="showAddParticipant = false">
      <form @submit.prevent="addParticipant" class="space-y-4">
        <div><label class="block text-sm font-medium text-gray-700 mb-1">User *</label><select v-model="participantForm.user_id" required class="w-full border border-gray-300 rounded-lg px-4 py-2.5 text-sm focus:ring-2 focus:ring-blue-500 outline-none bg-white"><option value="">Select</option><option v-for="u in users" :key="u.id" :value="u.id">{{ u.name }}</option></select></div>
        <div><label class="block text-sm font-medium text-gray-700 mb-1">Role *</label><select v-model="participantForm.participant_type_id" required class="w-full border border-gray-300 rounded-lg px-4 py-2.5 text-sm focus:ring-2 focus:ring-blue-500 outline-none bg-white"><option value="">Select</option><option v-for="t in participantTypes" :key="t.id" :value="t.id">{{ t.name }}</option></select></div>
        <div class="flex justify-end gap-3"><button type="button" @click="showAddParticipant = false" class="px-4 py-2 text-sm border border-gray-300 rounded-lg">Cancel</button><button type="submit" class="px-4 py-2 text-sm bg-blue-600 text-white rounded-lg">Add</button></div>
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

const route = useRoute(); const notif = useNotificationStore()
const output = ref({}); const loading = ref(true)
const showStatusChange = ref(false); const newStatusId = ref('')
const outputStatuses = ref([])
const showAddParticipant = ref(false)
const participantForm = ref({ user_id: '', participant_type_id: '' })
const users = ref([]); const participantTypes = ref([])

async function fetchOutput() {
  loading.value = true
  try { const { data } = await api.get(`/outputs/${route.params.id}`); output.value = data }
  catch (e) {} finally { loading.value = false }
}

async function changeStatus(statusId) {
  try { 
    await api.post(`/outputs/${output.value.id}/status`, { status_id: statusId }); 
    notif.success('Status updated!'); 
    fetchOutput() 
  } catch (err) { 
    notif.error('Failed to update status') 
  }
}

async function addParticipant() {
  try { await api.post(`/outputs/${output.value.id}/participants`, participantForm.value); notif.success('Participant added!'); showAddParticipant.value = false; fetchOutput() }
  catch (err) { notif.error('Failed') }
}

async function removeParticipant(p) {
  try { await api.delete(`/outputs/${output.value.id}/participants/${p.id}`); notif.success('Removed!'); fetchOutput() }
  catch (err) { notif.error('Failed') }
}

onMounted(async () => {
  await fetchOutput()
  try {
    const [ss, us, pts] = await Promise.all([api.get('/lookups/output_statuses'), api.get('/users',{params:{per_page:200}}), api.get('/lookups/participant_types')])
    outputStatuses.value = ss.data; users.value = us.data.data || us.data; participantTypes.value = pts.data
  } catch (e) {}
})
</script>
