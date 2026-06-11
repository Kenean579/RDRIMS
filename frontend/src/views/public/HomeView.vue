<template>
  <div class="flex flex-col gap-16 pb-4">
    
    <!-- HERO SECTION -->
    <section class="relative bg-white pt-20 pb-24 overflow-hidden border-b border-slate-100">
      <div class="absolute inset-0 z-0 bg-[radial-gradient(circle_at_top_right,var(--tw-gradient-stops))] from-brand/5 via-transparent to-transparent"></div>
      
      <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-5 relative z-10 text-center">
        <h1 class="text-2xl md:text-xl font-bold text-slate-900 tracking-tight leading-tight mb-6">
          Transforming Higher Education
        </h1>
        <p class="text-xl md:text-2xl font-bold text-slate-800 mb-5">
          The Central Hub for <span class="text-transparent bg-clip-text bg-gradient-to-r from-brand to-indigo-600">Academic Research</span>
        </p>
        <p class="mt-4 text-lg text-slate-500 max-w-2xl mx-auto font-medium leading-relaxed mb-5">
          Discover ground-breaking projects, browse the latest publications, explore open research calls, and track our ongoing community impact across universities.
        </p>
        <div class="flex items-center justify-center gap-4">
          <a href="#calls-section" class="px-5 py-3.5 bg-brand text-white font-bold rounded-2xl hover:shadow-brand/50 hover:-translate-y-0.5 transition-all">
            Explore Open Calls
          </a>
          <router-link to="/publications" class="px-5 py-3.5 bg-white text-slate-700 font-bold rounded-2xl shadow-sm border border-slate-100 hover:border-brand hover:text-brand transition-all">
            Browse Publications
          </router-link>
        </div>
      </div>
    </section>

    <!-- STATS BAR -->
    <section class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-5 -mt-24 relative z-20">
      <div class="bg-white rounded-2xl shadow-xl shadow-slate-200/50 border border-slate-100 p-5">
        <div class="grid grid-cols-2 md:grid-cols-4 gap-5 divide-x divide-slate-100">
          <div class="text-center px-4">
            <p class="text-2xl font-bold text-slate-800 mb-2 tracking-tight">{{ stats.universities }}</p>
            <p class="text-xs font-medium text-slate-500">Universities</p>
          </div>
          <div class="text-center px-4">
            <p class="text-2xl font-bold text-brand mb-2 tracking-tight">{{ stats.openCalls }}</p>
            <p class="text-xs font-medium text-slate-500">Open Calls</p>
          </div>
          <div class="text-center px-4">
            <p class="text-2xl font-bold text-slate-800 mb-2 tracking-tight">{{ stats.publications }}</p>
            <p class="text-xs font-medium text-slate-500">Publications</p>
          </div>
          <div class="text-center px-4">
            <p class="text-2xl font-bold text-emerald-500 mb-2 tracking-tight">{{ stats.problemsSolved }}</p>
            <p class="text-xs font-medium text-slate-500">Problems Solved</p>
          </div>
        </div>
      </div>
    </section>

    <!-- OPEN RESEARCH CALLS SECTION -->
    <section id="calls-section" class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-5 w-full">
      <div class="flex justify-between items-end mb-5">
        <div>
          <h2 class="text-2xl font-bold text-slate-800 tracking-tight">Open Research Calls</h2>
          <p class="text-sm text-slate-500 mt-1 font-medium">Opportunities for call for proposal and collaboration</p>
        </div>
        <router-link to="/calls" class="text-sm font-bold text-brand hover:text-brand-dark flex items-center gap-1 group">
          View all 
          <svg class="w-4 h-4 group-hover:translate-x-1 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" /></svg>
        </router-link>
      </div>
      
      <div v-if="loading.calls" class="grid grid-cols-1 md:grid-cols-3 gap-6">
        <div v-for="i in 3" :key="i" class="bg-white rounded-2xl border border-slate-100 p-6 animate-pulse h-48"></div>
      </div>
      <div v-else-if="calls.length === 0" class="bg-white rounded-2xl border border-slate-100 p-6 text-center">
        <p class="text-slate-500 font-medium">No open calls at this time. Check back soon.</p>
      </div>
      <div v-else class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
        <router-link v-for="call in calls" :key="call.id" :to="`/calls/${call.id}`" class="bg-white rounded-2xl border border-slate-100 p-6 shadow-sm hover:shadow-md hover:-translate-y-1 transition-all group relative">
          <p class="text-xs font-bold text-brand mb-3">{{ call.university?.name || 'Central' }}</p>
          <h3 class="text-lg font-bold text-slate-800 leading-tight mb-3 group-hover:text-brand transition-colors line-clamp-2">
            {{ call.title }}
          </h3>
          <p class="text-sm text-slate-500 line-clamp-3 mb-6 font-medium leading-relaxed">{{ call.description }}</p>
          
          <div class="flex items-center gap-3 pt-6 border-t border-slate-100">
            <div class="h-10 w-10 rounded-2xl bg-rose-50 flex items-center justify-center text-rose-500">
              <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
            </div>
            <div>
              <p class="text-xs font-medium text-slate-400">Deadline</p>
              <p class="text-sm font-bold text-slate-700" :class="{ 'text-rose-600': isUrgent(call.deadline) }">
                {{ formatDate(call.deadline) }}
              </p>
            </div>
          </div>
        </router-link>
      </div>
    </section>

    <!-- UPCOMING EVENTS SECTION -->
    <section class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-5 w-full">
      <div class="flex justify-between items-end mb-5">
        <div>
          <h2 class="text-2xl font-bold text-slate-800 tracking-tight">Upcoming Events</h2>
          <p class="text-sm text-slate-500 mt-1 font-medium">Conferences, workshops, and academic seminars</p>
        </div>
        <router-link to="/events" class="text-sm font-bold text-brand hover:text-brand-dark flex items-center gap-1 group">
          View all 
          <svg class="w-4 h-4 group-hover:translate-x-1 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" /></svg>
        </router-link>
      </div>
      
      <div v-if="loading.events" class="flex gap-6 overflow-x-auto pb-4">
        <div v-for="i in 3" :key="i" class="bg-white rounded-2xl border border-slate-100 p-6 animate-pulse w-80 h-48 flex-shrink-0"></div>
      </div>
      <div v-else-if="events.length === 0" class="bg-white rounded-2xl border border-slate-100 p-6 text-center">
        <p class="text-slate-500 font-medium">No upcoming events scheduled. Check back soon!</p>
      </div>
      <div v-else class="flex gap-6 overflow-x-auto pb-4">
        <div v-for="event in events" :key="event.id" class="bg-white rounded-2xl border border-slate-100 p-6 shadow-sm hover:shadow-md transition-all w-80 flex-shrink-0">
          <div class="text-xs font-medium text-slate-500 mb-2">{{ formatDate(event.start_date) }}</div>
          <h3 class="text-base font-bold text-slate-800 leading-tight mb-2">{{ event.title }}</h3>
          <p class="text-sm text-slate-500">{{ event.venue }}</p>
        </div>
      </div>
    </section>

    <!-- LATEST PUBLICATIONS SECTION -->
    <section class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-5 w-full">
      <div class="flex justify-between items-end mb-5">
        <div>
          <h2 class="text-2xl font-bold text-slate-800 tracking-tight">Latest Research Publications</h2>
        </div>
        <router-link to="/publications" class="text-sm font-bold text-brand hover:text-brand-dark flex items-center gap-1 group">
          View all 
          <svg class="w-4 h-4 group-hover:translate-x-1 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" /></svg>
        </router-link>
      </div>
      
      <div v-if="loading.publications" class="space-y-4">
        <div v-for="i in 5" :key="i" class="bg-white rounded-2xl border border-slate-100 p-6 animate-pulse h-24"></div>
      </div>
      <div v-else-if="publications.length === 0" class="bg-white rounded-2xl border border-slate-100 p-6 text-center">
        <p class="text-slate-500 font-medium">No publications yet.</p>
      </div>
      <div v-else class="space-y-4">
        <div v-for="pub in publications" :key="pub.id" class="bg-white rounded-2xl border border-slate-100 p-6">
          <div class="flex items-start gap-4">
            <div class="h-12 w-12 bg-brand/10 rounded-2xl flex items-center justify-center text-brand font-bold shrink-0">
              📄
            </div>
            <div class="flex-1 min-w-0">
              <h3 class="text-base font-bold text-slate-800 leading-tight mb-2">{{ pub.title }}</h3>
              <p class="text-xs text-slate-500 mb-2">Authors: {{ formatAuthors(pub.authors) }}</p>
              <div class="flex items-center gap-4 text-xs text-slate-500">
                <span>{{ pub.journal || 'Journal' }}</span>
                <span>· {{ formatDate(pub.publication_date) }}</span>
              </div>
            </div>
          </div>
        </div>
      </div>
    </section>

    <!-- COMMUNITY PROBLEMS SECTION (SUBMIT FORM ONLY) -->
    <section class="max-w-3xl mx-auto px-4 sm:px-6 lg:px-5 w-full">
      <div class="mb-5">
        <h2 class="text-2xl font-bold text-slate-800 tracking-tight">Community Problems</h2>
        <p class="text-sm text-slate-500 mt-1 font-medium">Report a real-world problem in your community for university researchers to study and help solve</p>
      </div>
      
      <div class="bg-white rounded-2xl border border-slate-100 p-5">
        <form @submit.prevent="submitProblem" class="space-y-6">
          <div>
            <label class="block text-sm font-bold text-slate-700 mb-2">Problem Title <span class="text-rose-500">*</span></label>
            <input v-model="problemForm.title" type="text" required
              class="w-full border border-slate-300 rounded-2xl px-4 py-3 text-sm focus:ring-2 focus:ring-brand/30 focus:border-brand outline-none"
              placeholder="e.g., 'Water Shortage in Dessie Town'" />
          </div>
          <div>
            <label class="block text-sm font-bold text-slate-700 mb-2">Description <span class="text-rose-500">*</span></label>
            <textarea v-model="problemForm.description" required rows="5" minlength="50"
              class="w-full border border-slate-300 rounded-2xl px-4 py-3 text-sm focus:ring-2 focus:ring-brand/30 focus:border-brand outline-none resize-none"
              placeholder="Describe the problem in detail. What is happening? Who is affected? How long has this been going on?"></textarea>
            <p class="text-xs text-slate-400 mt-1">{{ problemForm.description?.length || 0 }} / 50 minimum characters</p>
          </div>
          <div>
            <label class="block text-sm font-bold text-slate-700 mb-2">Location <span class="text-rose-500">*</span></label>
            <input v-model="problemForm.location" type="text" required
              class="w-full border border-slate-300 rounded-2xl px-4 py-3 text-sm focus:ring-2 focus:ring-brand/30 focus:border-brand outline-none"
              placeholder="e.g., 'Dessie, South Wollo'" />
          </div>
          <div>
            <label class="block text-sm font-bold text-slate-700 mb-2">Contact Information (optional)</label>
            <input v-model="problemForm.contact_info" type="text"
              class="w-full border border-slate-300 rounded-2xl px-4 py-3 text-sm focus:ring-2 focus:ring-brand/30 focus:border-brand outline-none"
              placeholder="Email or phone so researchers can follow up if needed" />
          </div>
          <div class="flex items-center gap-2">
            <input type="checkbox" v-model="problemForm.is_anonymous" class="rounded border-slate-300 text-brand focus:ring-brand" />
            <label class="text-sm text-slate-600">Submit anonymously (your name will not be shown publicly)</label>
          </div>
          <button type="submit" :disabled="submitting" class="w-full px-6 py-3 bg-brand text-white font-bold rounded-2xl hover:shadow-brand/50 transition-all disabled:opacity-60">
            {{ submitting ? 'Submitting...' : 'Submit Problem' }}
          </button>
        </form>
      </div>
    </section>

    <!-- PARTNER INSTITUTIONS SECTION -->
    <section class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-5 w-full">
      <div class="mb-5">
        <h2 class="text-2xl font-bold text-slate-800 tracking-tight">Partner Institutions</h2>
      </div>
      
      <div v-if="loading.partners" class="grid grid-cols-1 md:grid-cols-3 gap-6">
        <div v-for="i in 3" :key="i" class="bg-white rounded-2xl border border-slate-100 p-6 animate-pulse h-32"></div>
      </div>
      <div v-else class="grid grid-cols-1 md:grid-cols-3 gap-6">
        <div v-for="uni in universities" :key="uni.id" class="bg-white rounded-2xl border border-slate-100 p-6 text-center">
          <h3 class="text-lg font-bold text-slate-800 mb-2">{{ uni.name }}</h3>
          <span class="text-xs font-medium text-slate-500 bg-slate-100 rounded px-2 py-1">{{ uni.code || 'CODE' }}</span>
          <div class="mt-4 text-xs text-slate-500">
            {{ (uni.campuses?.length || uni.campuses_count || 0) }} Campuses · {{ (uni.researchCenters?.length || uni.research_centers_count || 0) }} Research Ctrs
          </div>
        </div>
      </div>
    </section>
    
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import { useNotificationStore } from '@/stores/notification'
import api from '@/services/api'

const notif = useNotificationStore()

const stats = ref({
  universities: 0,
  openCalls: 0,
  publications: 0,
  problemsSolved: 0
})

const calls = ref([])
const events = ref([])
const publications = ref([])
const universities = ref([])
const settings = ref({})
const loading = ref({
  stats: true,
  calls: true,
  events: true,
  publications: true,
  partners: true
})

const problemForm = ref({
  title: '',
  description: '',
  location: '',
  contact_info: '',
  is_anonymous: false
})

const submitting = ref(false)

onMounted(async () => {
  // Use sequential fetching to prevent deadlocking the PHP built-in dev server on Windows
  await fetchStats()
  await fetchCalls()
  await fetchEvents()
  await fetchPublications()
  await fetchUniversities()
  await fetchSettings()
})

async function fetchStats() {
  loading.value.stats = true
  try {
    const uniRes = await api.get('/universities')
    const callsRes = await api.get('/calls', { params: { status: 'open', per_page: 1 } })
    const pubRes = await api.get('/publications', { params: { per_page: 1 } })
    const commRes = await api.get('/community-problems', { params: { status: 'completed', per_page: 1 } })
    
    stats.value.universities = uniRes.data?.data?.length || uniRes.data?.length || 0
    stats.value.openCalls = callsRes.data?.meta?.total || callsRes.data?.total || 0
    stats.value.publications = pubRes.data?.meta?.total || pubRes.data?.total || 0
    stats.value.problemsSolved = commRes.data?.meta?.total || commRes.data?.total || 0
  } catch (err) {
    console.error('Failed to load stats', err)
  } finally {
    loading.value.stats = false
  }
}

async function fetchCalls() {
  loading.value.calls = true
  try {
    const res = await api.get('/calls', { params: { status: 'open', per_page: 6 } })
    calls.value = res.data?.data || res.data || []
  } catch (e) {
    console.error('Failed to load calls', e)
  } finally {
    loading.value.calls = false
  }
}

async function fetchEvents() {
  loading.value.events = true
  try {
    const res = await api.get('/events', { params: { upcoming: true, per_page: 4 } })
    events.value = res.data?.data || res.data || []
  } catch (e) {
    console.error('Failed to load events', e)
  } finally {
    loading.value.events = false
  }
}

async function fetchPublications() {
  loading.value.publications = true
  try {
    const res = await api.get('/publications', { params: { per_page: 5, sort: 'publication_date', order: 'desc' } })
    publications.value = res.data?.data || res.data || []
  } catch (e) {
    console.error('Failed to load publications', e)
  } finally {
    loading.value.publications = false
  }
}

async function fetchUniversities() {
  loading.value.partners = true
  try {
    const res = await api.get('/universities')
    universities.value = res.data?.data || res.data || []
  } catch (e) {
    console.error('Failed to load universities', e)
  } finally {
    loading.value.partners = false
  }
}

async function fetchSettings() {
  try {
    const res = await api.get('/settings')
    settings.value = res.data?.data || res.data || {}
  } catch (e) {
    console.error('Failed to load settings', e)
  }
}

async function submitProblem() {
  submitting.value = true
  try {
    await api.post('/community-problems', problemForm.value)
    notif.success('Problem submitted successfully!')
    problemForm.value = { title: '', description: '', location: '', contact_info: '', is_anonymous: false }
  } catch (err) {
    notif.error(err.response?.data?.message || 'Failed to submit problem')
  } finally {
    submitting.value = false
  }
}

function formatDate(val) {
  if (!val) return 'N/A'
  return new Date(val).toLocaleDateString(undefined, { month: 'short', day: 'numeric', year: 'numeric' })
}

function isUrgent(dateStr) {
  if (!dateStr) return false
  const diffTime = Math.abs(new Date(dateStr) - new Date())
  const diffDays = Math.ceil(diffTime / (1000 * 60 * 60 * 24))
  return diffDays < 7
}

function formatAuthors(authors) {
  if (!authors) return 'Not specified'
  if (typeof authors === 'string') {
    const authorList = authors.split(',').map(a => a.trim())
    if (authorList.length <= 3) return authors
    return `${authorList.slice(0, 3).join(', ')} et al.`
  }
  if (Array.isArray(authors)) {
    const names = authors.map(a => a.user?.name || a.external_author_name).filter(Boolean)
    if (names.length === 0) return 'Not specified'
    if (names.length <= 3) return names.join(', ')
    return `${names.slice(0, 3).join(', ')} et al.`
  }
  return authors
}
</script>