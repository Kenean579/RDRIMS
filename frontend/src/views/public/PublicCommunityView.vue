<template>
  <div class="max-w-3xl mx-auto animate-fade pb-8">
    <div class="card p-8 md:p-12">
      <h1 class="text-3xl font-bold text-slate-900 mb-2">Community Problems</h1>
      <p class="text-slate-500 font-medium mb-8">
        Report a real-world problem in your community for university researchers to study and help solve.
      </p>

      <div v-if="!allowSubmission" class="bg-amber-50 border border-amber-200 text-amber-800 px-6 py-5 rounded-2xl text-sm font-medium">
        Public submission is currently disabled. Please <router-link to="/login" class="text-brand font-bold hover:underline">sign in</router-link> to submit a community problem.
      </div>

      <form v-else @submit.prevent="submitProblem" class="space-y-6">
        <div>
          <label class="block text-sm font-bold text-slate-700 mb-2">Problem Title <span class="text-rose-500">*</span></label>
          <input v-model="form.title" type="text" required maxlength="255"
            class="w-full border border-slate-300 rounded-2xl px-4 py-3 text-sm focus:ring-2 focus:ring-brand/30 focus:border-brand outline-none transition-all"
            placeholder="e.g., 'Water Shortage in Dessie Town'" />
        </div>
        <div>
          <label class="block text-sm font-bold text-slate-700 mb-2">Description <span class="text-rose-500">*</span></label>
          <textarea v-model="form.description" required rows="5" minlength="50"
            class="w-full border border-slate-300 rounded-2xl px-4 py-3 text-sm focus:ring-2 focus:ring-brand/30 focus:border-brand outline-none resize-none transition-all"
            placeholder="Describe the problem in detail. What is happening? Who is affected? How long has this been going on?"></textarea>
          <p class="text-xs text-slate-400 mt-1">{{ form.description?.length || 0 }} / 50 minimum characters</p>
        </div>

        <!-- Searchable Research Centre Dropdown -->
        <div>
          <label class="block text-sm font-bold text-slate-700 mb-2">Select Research Centre <span class="text-rose-500">*</span></label>
          <div class="relative">
            <input
              v-model="rcSearch"
              type="text"
              placeholder="Search by name or keyword..."
              class="w-full border border-slate-300 rounded-2xl px-4 py-3 text-sm focus:ring-2 focus:ring-brand/30 focus:border-brand outline-none transition-all"
              @focus="showRcDropdown = true"
              @blur="scheduleHideDropdown"
            />
            <ul
              v-if="showRcDropdown && filteredResearchCentres.length > 0"
              class="absolute z-10 mt-1 w-full bg-white border border-slate-200 rounded-2xl shadow-lg max-h-48 overflow-y-auto"
            >
              <li
                v-for="rc in filteredResearchCentres"
                :key="rc.id"
                class="px-4 py-2 text-sm hover:bg-brand-light hover:text-brand cursor-pointer transition-colors"
                :class="{ 'bg-brand/10 text-brand font-bold': form.research_center_id === rc.id }"
                @mousedown.prevent="selectResearchCentre(rc)"
              >
                {{ rc.name }}
              </li>
            </ul>
            <p v-if="showRcDropdown && filteredResearchCentres.length === 0" class="absolute z-10 mt-1 w-full bg-white border border-slate-200 rounded-2xl shadow-lg p-4 text-sm text-slate-400 text-center">
              No matching research centres found.
            </p>
          </div>
          <p v-if="selectedRcName" class="text-xs text-slate-500 mt-1 ml-1">
            Selected: <span class="font-bold text-slate-700">{{ selectedRcName }}</span>
          </p>
        </div>

        <div>
          <label class="block text-sm font-bold text-slate-700 mb-2">Location <span class="text-rose-500">*</span></label>
          <input v-model="form.location" type="text" required
            class="w-full border border-slate-300 rounded-2xl px-4 py-3 text-sm focus:ring-2 focus:ring-brand/30 focus:border-brand outline-none transition-all"
            placeholder="e.g., 'Dessie, South Wollo'" />
        </div>
        <div>
          <label class="block text-sm font-bold text-slate-700 mb-2">Contact Information (optional)</label>
          <input v-model="form.contact_info" type="text"
            class="w-full border border-slate-300 rounded-2xl px-4 py-3 text-sm focus:ring-2 focus:ring-brand/30 focus:border-brand outline-none transition-all"
            placeholder="Email or phone so researchers can follow up if needed" />
        </div>
        <div class="flex items-center gap-2">
          <input type="checkbox" v-model="form.is_anonymous" class="rounded border-slate-300 text-brand focus:ring-brand" />
          <label class="text-sm text-slate-600">Submit anonymously (your name will not be shown publicly)</label>
        </div>

        <div v-if="success" class="bg-emerald-50 border border-emerald-200 text-emerald-800 px-6 py-4 rounded-2xl text-sm font-bold flex items-center gap-3">
          <span class="text-xl">✓</span>
          <div>
            <p>Problem submitted successfully! Researchers will review it.</p>
            <button type="button" @click="resetForm" class="text-brand font-bold hover:underline mt-1">Submit Another Problem</button>
          </div>
        </div>

        <button type="submit" :disabled="submitting" class="w-full px-6 py-3 bg-brand text-white font-bold rounded-2xl hover:shadow-brand/50 transition-all disabled:opacity-60">
          {{ submitting ? 'Submitting...' : 'Submit Problem' }}
        </button>
      </form>
    </div>
  </div>
</template>

<script setup>
import { ref, reactive, computed, onMounted } from 'vue'
import { useNotificationStore } from '@/stores/notification'
import { useLookupStore } from '@/stores/lookup'
import api from '@/services/api'

const notif = useNotificationStore()
const lookupStore = useLookupStore()

const submitting = ref(false)
const success = ref(false)
const allowSubmission = ref(true)

const form = reactive({
  title: '',
  description: '',
  research_center_id: '',
  location: '',
  contact_info: '',
  is_anonymous: false
})

const researchCentres = ref([])
const rcSearch = ref('')
const showRcDropdown = ref(false)

// Filtered list based on search text
const filteredResearchCentres = computed(() => {
  const q = rcSearch.value.toLowerCase().trim()
  if (!q) return researchCentres.value
  return researchCentres.value.filter(rc => rc.name.toLowerCase().includes(q))
})

// Display name of selected centre
const selectedRcName = computed(() => {
  if (!form.research_center_id) return ''
  const found = researchCentres.value.find(rc => rc.id == form.research_center_id)
  return found ? found.name : ''
})

// Delay hiding dropdown so clicks on options register
let hideTimer = null
function scheduleHideDropdown() {
  hideTimer = setTimeout(() => {
    showRcDropdown.value = false
  }, 200)
}
function cancelHide() {
  clearTimeout(hideTimer)
}

function selectResearchCentre(rc) {
  form.research_center_id = rc.id
  rcSearch.value = rc.name
  showRcDropdown.value = false
}

function resetForm() {
  Object.assign(form, { title: '', description: '', research_center_id: '', location: '', contact_info: '', is_anonymous: false })
  rcSearch.value = ''
  success.value = false
}

async function submitProblem() {
  if (form.description.length < 50) {
    notif.warning('Description must be at least 50 characters')
    return
  }
  if (!form.research_center_id) {
    notif.warning('Please select a research centre')
    return
  }
  submitting.value = true
  try {
    await api.post('/community-problems', form)
    success.value = true
    notif.success('Problem submitted successfully! Researchers will review it.')
  } catch (err) {
    notif.error(err.response?.data?.message || 'Failed to submit problem')
  } finally {
    submitting.value = false
  }
}

onMounted(async () => {
  try {
    await lookupStore.initialize()
    allowSubmission.value = lookupStore.getSetting('allow_public_problem_submission', 'true') !== 'false'
    const { data } = await api.get('/research-centers')
    researchCentres.value = data.data || data
  } catch (e) {
    allowSubmission.value = true
  }
})
</script>