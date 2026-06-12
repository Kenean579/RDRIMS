<template>
  <div class="flex flex-col gap-8 animate-fade pb-8">
    <!-- Header -->
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-6 px-1">
      <div>
        <h1 class="text-2xl font-bold text-slate-900 tracking-tight leading-tight">Community Help Desk</h1>
        <p class="text-slate-500 font-medium mt-2 text-xs flex items-center gap-2  tracking-widest">
          <span class="w-2 h-2 rounded-full bg-brand"></span>
          Report a problem or help solve existing ones
        </p>
      </div>
      <button @click="showCreate = true" class="btn btn-primary h-11 px-6 text-xs font-bold gap-2 shrink-0">
        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M12 4v16m8-8H4" /></svg>
        Report Issue
      </button>
    </div>

    <!-- Filters -->
    <div class="flex items-center gap-3 px-1 flex-wrap">
      <button
        v-for="s in statusFilters" :key="s.value"
        @click="activeFilter = s.value; fetchProblems()"
        class="px-4 py-2 rounded-2xl text-xs font-bold border transition-all"
        :class="activeFilter === s.value
          ? 'bg-brand text-white border-brand shadow-lg shadow-brand/20'
          : 'bg-white text-slate-500 border-slate-200 hover:border-brand/30 hover:text-brand'"
      >{{ s.label }}</button>
    </div>

    <!-- Loading -->
    <div v-if="loading" class="grid grid-cols-1 md:grid-cols-2 gap-6">
      <div v-for="i in 4" :key="i" class="card h-52 animate-pulse bg-slate-50/50 rounded-3xl"></div>
    </div>

    <!-- Error -->
    <div v-else-if="error" class="card border-rose-100 bg-rose-50/30 p-10 text-center">
      <div class="w-16 h-16 bg-rose-100 text-rose-600 rounded-2xl flex items-center justify-center mx-auto mb-5 text-2xl">⚠️</div>
      <h3 class="text-lg font-bold text-slate-800 mb-2">System Error</h3>
      <p class="text-xs text-rose-600 font-bold mb-6 leading-relaxed">{{ error }}</p>
      <button @click="fetchProblems" class="btn btn-primary px-6 h-11 text-xs font-bold">Retry</button>
    </div>

    <!-- Empty -->
    <div v-else-if="problems.length === 0" class="card p-16 text-center">
      <div class="w-20 h-20 bg-slate-50 rounded-3xl flex items-center justify-center mx-auto mb-6 text-4xl border border-slate-100">🏘️</div>
      <h3 class="text-lg font-bold text-slate-800 mb-2">No issues reported</h3>
      <p class="text-sm text-slate-500 font-medium mb-8">Know about a problem in your area? Let us know so we can help.</p>
      <button @click="showCreate = true" class="btn btn-primary px-8 h-11 text-xs font-bold shadow-lg shadow-brand/20">Report First Issue</button>
    </div>

    <!-- Problem Cards -->
    <div v-else class="grid grid-cols-1 md:grid-cols-2 gap-6">
      <div v-for="p in problems" :key="p.id"
        class="bg-white rounded-3xl border border-slate-100 hover:border-brand/20 hover:shadow-xl hover:shadow-brand/5 transition-all overflow-hidden flex flex-col group"
      >
        <!-- Card Header -->
        <div class="p-6 flex-1">
          <div class="flex items-start justify-between gap-4 mb-4">
            <h3 class="text-base font-bold text-slate-800 group-hover:text-brand transition-colors leading-tight">{{ p.title }}</h3>
            <span
              class="px-3 py-1 rounded-full text-xs font-bold shrink-0"
              :class="{
                'bg-blue-100 text-blue-700': p.status?.name === 'open',
                'bg-amber-100 text-amber-700': p.status?.name === 'claimed',
                'bg-emerald-100 text-emerald-700': p.status?.name === 'completed',
              }"
            >{{ p.status?.name || 'open' }}</span>
          </div>

          <p class="text-sm text-slate-500 font-medium leading-relaxed mb-5 line-clamp-2 italic">{{ p.description }}</p>

          <div class="flex flex-col gap-2.5">
            <div class="flex items-center gap-2.5 text-xs font-medium text-slate-500 bg-slate-50 px-3 py-2.5 rounded-2xl border border-slate-100">
              <svg class="w-4 h-4 text-brand shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" /><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z" /></svg>
              {{ p.location }}
            </div>
            <div v-if="p.claimed_by" class="flex items-center gap-2.5 text-xs font-medium text-slate-500 bg-emerald-50 px-3 py-2.5 rounded-2xl border border-emerald-100">
              <svg class="w-4 h-4 text-emerald-500 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" /></svg>
              Helper: <span class="font-bold text-emerald-700">{{ p.claimed_by?.name }}</span>
            </div>
            <div v-if="p.rating" class="flex items-center gap-2.5 text-xs font-medium text-slate-500 bg-amber-50 px-3 py-2.5 rounded-2xl border border-amber-100">
              <span class="text-amber-500 text-sm">★</span>
              Rating: <span class="font-bold text-amber-700">{{ p.rating }} / 5</span>
              <span v-if="p.feedback" class="ml-auto italic text-xs text-slate-400 line-clamp-1">{{ p.feedback }}</span>
            </div>
          </div>
        </div>

        <!-- Card Actions -->
        <div class="px-6 py-4 bg-slate-50/60 border-t border-slate-100 flex gap-2 flex-wrap">
          <!-- Claim button: only for open problems that user hasn't submitted -->
          <button
            v-if="p.status?.name === 'open'"
            @click="claimProblem(p)"
            class="flex-1 btn btn-primary justify-center text-xs font-bold h-10 min-w-[100px]"
          >Help Out</button>

  <!-- Complete button: only for the person who claimed it -->
  <button
    v-if="p.status?.name === 'claimed' && p.claimed_by?.id === auth.user?.id"
    @click="completeProblem(p)"
    class="flex-1 btn btn-primary justify-center text-xs font-bold h-10 min-w-[100px]"
  >Mark Finished</button>

  <router-link
    v-if="p.status?.name === 'claimed' && p.claimed_by?.id === auth.user?.id && !p.linked_project_id"
    :to="`/app/calls?community_problem_id=${p.id}`"
    class="flex-1 btn btn-secondary justify-center text-xs font-bold h-10 min-w-[100px]"
  >Initiate Call</router-link>

  <!-- Link to project if available -->
  <router-link
    v-if="p.linked_project_id"
    :to="`/app/projects/${p.linked_project_id}`"
    class="flex-1 btn btn-secondary justify-center text-xs font-bold h-10 min-w-[100px]"
  >View Linked Project</router-link>

          <!-- Feedback button: completed, no feedback yet, submitted by current user -->
          <button
            v-if="p.status?.name === 'completed' && !p.feedback && p.submitted_by?.id === auth.user?.id"
            @click="openFeedback(p)"
            class="flex-1 btn btn-secondary justify-center text-xs font-bold h-10 min-w-[100px]"
          >Leave Feedback</button>

          <!-- Completed with feedback -->
          <span
            v-if="p.status?.name === 'completed' && p.feedback"
            class="flex-1 text-center py-2 text-xs font-bold text-emerald-700 bg-emerald-50 rounded-2xl border border-emerald-100 flex items-center justify-center gap-2"
          >
            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"/></svg>
            Resolved
          </span>

          <!-- Delete (admin only) -->
          <button
            v-if="auth.hasRole('super_admin', 'research_admin')"
            @click="confirmDelete(p)"
            class="w-10 h-10 rounded-2xl flex items-center justify-center text-slate-300 hover:text-rose-500 hover:bg-rose-50 border border-slate-200 transition-all"
          >
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" /></svg>
          </button>
        </div>
      </div>
    </div>

    <!-- Report Issue Modal -->
    <Modal :show="showCreate" title="Report a Community Issue" size="md" @close="closeCreate">
      <form @submit.prevent="submitProblem" class="space-y-5">
        <div>
          <label class="block text-xs text-slate-500 font-bold mb-2 ml-1">Problem Title *</label>
          <input v-model="form.title" type="text" required class="input h-12 font-bold" placeholder="Give it a clear, specific title..." />
        </div>
        <div>
          <label class="block text-xs text-slate-500 font-bold mb-2 ml-1">Description *</label>
          <textarea v-model="form.description" required rows="4" class="input resize-none pt-4 font-medium" placeholder="Explain what is happening, who is affected, and urgency level..."></textarea>
        </div>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
          <div>
            <label class="block text-xs text-slate-500 font-bold mb-2 ml-1">Research Centre *</label>
            <select v-model="form.research_center_id" required class="input h-12 font-bold appearance-none bg-white">
              <option value="" disabled>Select handling centre...</option>
              <option v-for="rc in researchCentres" :key="rc.id" :value="rc.id">{{ rc.name }}</option>
            </select>
          </div>
          <div>
            <label class="block text-xs text-slate-500 font-bold mb-2 ml-1">Location *</label>
            <input v-model="form.location" type="text" required class="input h-12 font-bold" placeholder="e.g. Zone 4, Addis Ababa" />
          </div>
        </div>
        <div>
          <label class="block text-xs text-slate-500 font-bold mb-2 ml-1">Contact Info</label>
          <input v-model="form.contact_info" type="text" class="input h-12 font-bold" placeholder="Phone or email" />
        </div>
        <div class="flex items-center gap-3 p-4 bg-slate-50 rounded-2xl border border-slate-100">
          <input id="anonymous" v-model="form.is_anonymous" type="checkbox" class="w-4 h-4 accent-brand rounded" />
          <label for="anonymous" class="text-xs font-bold text-slate-600 cursor-pointer">Submit anonymously (your name will be hidden)</label>
        </div>
        <div class="flex justify-end gap-3 pt-4 border-t border-slate-100">
          <button type="button" @click="closeCreate" class="btn btn-secondary px-6 h-11 text-xs font-bold">Cancel</button>
          <button type="submit" class="btn btn-primary px-8 h-11 text-xs font-bold shadow-lg shadow-brand/20">Submit Report</button>
        </div>
      </form>
    </Modal>

    <!-- Feedback Modal -->
    <Modal :show="showFeedback" title="Leave Feedback" size="sm" @close="closeFeedback">
      <form @submit.prevent="submitFeedback" class="space-y-5">
        <div>
          <label class="block text-xs text-slate-500 font-bold mb-3 ml-1">How would you rate the help?</label>
          <div class="flex justify-center gap-3 px-4 py-5 bg-slate-50 rounded-2xl border border-slate-100">
            <button
              v-for="i in 5" :key="i"
              type="button"
              @click="feedbackForm.rating = i"
              class="text-2xl transition-all duration-200 transform"
              :class="i <= feedbackForm.rating ? 'scale-125' : 'scale-90 opacity-30'"
            >⭐</button>
          </div>
        </div>
        <div>
          <label class="block text-xs text-slate-500 font-bold mb-2 ml-1">Feedback *</label>
          <textarea v-model="feedbackForm.feedback" required rows="3" class="input resize-none pt-4 font-medium" placeholder="How was the issue resolved? Any comments..."></textarea>
        </div>
        <div class="flex justify-end gap-3 pt-4 border-t border-slate-100">
          <button type="button" @click="closeFeedback" class="btn btn-secondary px-6 h-11 text-xs font-bold">Cancel</button>
          <button type="submit" class="btn btn-primary px-8 h-11 text-xs font-bold shadow-lg shadow-brand/20">Submit Feedback</button>
        </div>
      </form>
    </Modal>

    <!-- Delete Confirm -->
    <ConfirmDialog
      :show="showDelete"
      title="Delete Issue Report"
      :message="`Are you sure you want to permanently remove '${deletingProblem?.title}'?`"
      confirmText="Delete"
      variant="danger"
      @confirm="deleteProblem"
      @cancel="showDelete = false"
    />
  </div>
</template>

<script setup>
import { ref, reactive, onMounted } from 'vue'
import { useAuthStore } from '@/stores/auth'
import { useNotificationStore } from '@/stores/notification'
import api from '@/services/api'
import Modal from '@/components/Modal.vue'
import EmptyState from '@/components/EmptyState.vue'
import ConfirmDialog from '@/components/ConfirmDialog.vue'

const auth = useAuthStore()
const notif = useNotificationStore()

const problems = ref([])
const loading = ref(true)
const error = ref(null)
const activeFilter = ref('')

const showCreate = ref(false)
const showFeedback = ref(false)
const showDelete = ref(false)

const feedbackProblem = ref(null)
const deletingProblem = ref(null)

const form = reactive({
  title: '',
  description: '',
  location: '',
  contact_info: '',
  is_anonymous: false,
  research_center_id: '',
})

const researchCentres = ref([])

const feedbackForm = reactive({
  rating: 3,
  feedback: '',   // matches backend field name
})

const statusFilters = [
  { value: '', label: 'All Issues' },
  { value: 'open', label: 'Open' },
  { value: 'claimed', label: 'In Progress' },
  { value: 'completed', label: 'Resolved' },
]

async function fetchProblems() {
  loading.value = true
  error.value = null
  try {
    const params = activeFilter.value ? { status: activeFilter.value } : {}
    const { data } = await api.get('/community-problems', { params })
    problems.value = data.data || data
  } catch (err) {
    error.value = err.response?.data?.message || 'Failed to load issues'
  } finally {
    loading.value = false
  }
}

function closeCreate() {
  showCreate.value = false
  Object.assign(form, { title: '', description: '', location: '', contact_info: '', is_anonymous: false, research_center_id: '' })
}

async function submitProblem() {
  try {
    await api.post('/community-problems', form)
    notif.success('Issue reported successfully!')
    closeCreate()
    fetchProblems()
  } catch (err) {
    notif.error(err.response?.data?.message || 'Failed to submit')
  }
}

async function claimProblem(p) {
  try {
    await api.post(`/community-problems/${p.id}/claim`)
    notif.success('You have claimed this issue — thank you!')
    fetchProblems()
  } catch (err) {
    notif.error(err.response?.data?.message || 'Failed to claim')
  }
}

async function completeProblem(p) {
  try {
    await api.post(`/community-problems/${p.id}/complete`)
    notif.success('Issue marked as completed!')
    fetchProblems()
  } catch (err) {
    notif.error(err.response?.data?.message || 'Failed to complete')
  }
}

function openFeedback(p) {
  feedbackProblem.value = p
  feedbackForm.rating = 3
  feedbackForm.feedback = ''
  showFeedback.value = true
}

function closeFeedback() {
  showFeedback.value = false
  feedbackProblem.value = null
}

async function submitFeedback() {
  try {
    // Backend expects: feedback + rating
    await api.post(`/community-problems/${feedbackProblem.value.id}/feedback`, {
      feedback: feedbackForm.feedback,
      rating: feedbackForm.rating,
    })
    notif.success('Feedback submitted!')
    closeFeedback()
    fetchProblems()
  } catch (err) {
    notif.error(err.response?.data?.message || 'Failed to submit feedback')
  }
}

function confirmDelete(p) {
  deletingProblem.value = p
  showDelete.value = true
}

async function deleteProblem() {
  try {
    await api.delete(`/community-problems/${deletingProblem.value.id}`)
    notif.success('Issue deleted')
    showDelete.value = false
    fetchProblems()
  } catch (err) {
    notif.error(err.response?.data?.message || 'Failed to delete')
  }
}

async function fetchResearchCentres() {
  try {
    const { data } = await api.get('/research-centers')
    researchCentres.value = data.data || data
  } catch (err) {}
}

onMounted(() => {
  fetchProblems()
  fetchResearchCentres()
})
</script>
