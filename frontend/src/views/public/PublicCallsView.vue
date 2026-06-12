<template>
  <div class="flex flex-col gap-5 pb-6 animate-fade">
    <!-- Header -->
    <div class="card p-8 bg-slate-50 border-slate-100 relative overflow-hidden">
      <div class="relative z-10">
        <h1 class="text-xl font-bold text-slate-900 tracking-tight">Call for Proposals</h1>
        <p class="text-slate-500 font-medium mt-1">Open research calls across all research-centers.</p>
      </div>
      <div class="absolute right-0 top-0 w-32 h-32 bg-brand/5 rounded-full translate-x-8 -translate-y-8"></div>
    </div>

    <!-- Filters -->
    <div class="card p-8 flex flex-col gap-5">
      <div class="flex flex-col md:flex-row gap-5 items-end">
        <div class="flex-1 w-full relative">
          <label class="block text-xs text-slate-500 font-medium mb-2 ml-1">Search Database</label>
          <div class="relative group">
            <span class="absolute left-3.5 top-1/2 -translate-y-1/2 text-slate-400">
              <svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
            </span>
            <input v-model="search" type="text" placeholder="Search by title or keyword..." class="input pl-10" @input="debounceSearch" />
          </div>
        </div>
        <div class="w-full md:w-56">
          <label class="block text-xs text-slate-500 font-medium mb-2 ml-1">Call Status</label>
          <select v-model="statusFilter" class="input font-bold" @change="fetchCalls(1)">
            <option value="">All Statuses</option>
            <option v-for="s in callStatuses" :key="s.id" :value="s.name">{{ formatStatusName(s.name) }}</option>
          </select>
        </div>
      </div>

      <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-5 gap-4">
        <div>
          <label class="block text-xs text-slate-500 font-medium mb-2 ml-1">University</label>
          <select v-model="filters.university_id" class="input font-bold" @change="onUniversityChange">
            <option value="">All Universities</option>
            <option v-for="u in universities" :key="u.id" :value="u.id">{{ u.name }}</option>
          </select>
        </div>
        <div>
          <label class="block text-xs text-slate-500 font-medium mb-2 ml-1">Campus</label>
          <select v-model="filters.campus_id" class="input font-bold" :disabled="!filters.university_id" @change="onCampusChange">
            <option value="">All Campuses</option>
            <option v-for="c in filteredCampuses" :key="c.id" :value="c.id">{{ c.name }}</option>
          </select>
        </div>
        <div>
          <label class="block text-xs text-slate-500 font-medium mb-2 ml-1">Faculty</label>
          <select v-model="filters.faculty_id" class="input font-bold" :disabled="!filters.campus_id" @change="onFacultyChange">
            <option value="">All Faculties</option>
            <option v-for="f in filteredFaculties" :key="f.id" :value="f.id">{{ f.name }}</option>
          </select>
        </div>
        <div>
          <label class="block text-xs text-slate-500 font-medium mb-2 ml-1">Department</label>
          <select v-model="filters.department_id" class="input font-bold" :disabled="!filters.faculty_id" @change="fetchCalls(1)">
            <option value="">All Departments</option>
            <option v-for="d in filteredDepartments" :key="d.id" :value="d.id">{{ d.name }}</option>
          </select>
        </div>
        <!-- NEW: Research Centre (independent) -->
        <div>
          <label class="block text-xs text-slate-500 font-medium mb-2 ml-1">Research Centre</label>
          <select v-model="filters.research_center_id" class="input font-bold" @change="fetchCalls(1)">
            <option value="">All Centres</option>
            <option v-for="rc in researchCentres" :key="rc.id" :value="rc.id">{{ rc.name }}</option>
          </select>
        </div>
      </div>
    </div>

    <!-- Content -->
    <div v-if="loading" class="grid grid-cols-1 md:grid-cols-2 gap-6">
      <div v-for="i in 4" :key="i" class="card h-48 animate-pulse"></div>
    </div>

    <div v-else-if="filteredCalls.length === 0" class="card p-8 text-center">
      <p class="text-sm font-medium text-slate-400 italic">No matching calls found.</p>
    </div>

    <div v-else class="grid grid-cols-1 md:grid-cols-2 gap-6">
      <router-link v-for="call in filteredCalls" :key="call.id" :to="`/calls/${call.id}`"
        class="card p-8 flex flex-col group card-hover border-l-4 border-l-transparent transition-all"
      >
        <div class="flex items-center gap-2 mb-4">
          <span class="px-2 py-0.5 bg-brand-light text-brand text-xs font-medium rounded border border-brand/10">{{ call.status?.name || 'Open' }}</span>
          <span v-if="isUrgent(call.deadline)" class="px-2 py-0.5 bg-rose-50 text-rose-600 text-xs font-medium rounded border border-rose-100">Expiring Soon</span>
        </div>

        <h3 class="text-lg font-bold text-slate-900 group-hover:text-brand transition-colors mb-2">{{ call.title }}</h3>
        <div class="flex-1 mb-6 min-h-0">
          <p class="text-sm text-slate-500 font-medium line-clamp-2">{{ call.description }}</p>
        </div>

        <div class="flex items-center justify-between pt-4 border-t border-slate-50 text-xs font-medium text-slate-400">
          <span class="flex items-center gap-1.5">
            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
            Deadline: {{ formatDate(call.deadline) }}
          </span>
          <span class="text-brand">View Details →</span>
        </div>
      </router-link>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, onMounted, watch } from 'vue'
import api from '@/services/api'
import { formatDate } from '@/utils/formatters'
import { formatStatusName } from '@/utils/colors'

const calls = ref([])
const loading = ref(true)
const search = ref('')
const statusFilter = ref('')
const callStatuses = ref([])
const filters = ref({ university_id: '', campus_id: '', faculty_id: '', department_id: '', research_center_id: '' })

const universities = ref([])
const campuses = ref([])
const faculties = ref([])
const departments = ref([])
const researchCentres = ref([])  // NEW

const filteredCampuses = computed(() => campuses.value.filter(c => String(c.university_id) === String(filters.value.university_id)))
const filteredFaculties = computed(() => faculties.value.filter(f => String(f.campus_id) === String(filters.value.campus_id)))
const filteredDepartments = computed(() => departments.value.filter(d => String(d.faculty_id) === String(filters.value.faculty_id)))

function onUniversityChange() { filters.value.campus_id = ''; filters.value.faculty_id = ''; filters.value.department_id = ''; fetchCalls(1) }
function onCampusChange() { filters.value.faculty_id = ''; filters.value.department_id = ''; fetchCalls(1) }
function onFacultyChange() { filters.value.department_id = ''; fetchCalls(1) }

let searchTimer = null
function debounceSearch() { clearTimeout(searchTimer); searchTimer = setTimeout(() => fetchCalls(1), 400) }

const filteredCalls = computed(() => {
  return calls.value.filter(c => {
    const q = search.value.toLowerCase()
    const matchSearch = !q || c.title?.toLowerCase().includes(q) || c.description?.toLowerCase().includes(q)
    const matchStatus = !statusFilter.value || c.status?.name === statusFilter.value
    return matchSearch && matchStatus
  })
})

function isUrgent(dateStr) {
  if (!dateStr) return false
  const diff = new Date(dateStr) - new Date()
  return diff > 0 && diff < 7 * 24 * 60 * 60 * 1000
}

async function fetchCalls(page = 1) {
  loading.value = true
  try {
    const params = { page }
    if (search.value) params.search = search.value
    if (statusFilter.value) params.status = statusFilter.value
    if (filters.value.university_id) params.university_id = filters.value.university_id
    if (filters.value.campus_id) params.campus_id = filters.value.campus_id
    if (filters.value.faculty_id) params.faculty_id = filters.value.faculty_id
    if (filters.value.department_id) params.department_id = filters.value.department_id
    if (filters.value.research_center_id) params.research_center_id = filters.value.research_center_id   // NEW
    const { data } = await api.get('/calls', { params })
    calls.value = data.data || data
  } catch (e) {} finally { loading.value = false }
}

onMounted(async () => {
  try {
    const cs = await api.get('/lookups/call_statuses')
    const u = await api.get('/universities')
    const c = await api.get('/campuses')
    const f = await api.get('/faculties')
    const d = await api.get('/departments')
    const rc = await api.get('/research-centers')   // NEW
    callStatuses.value = cs.data
    universities.value = (u.data.data || u.data)
    campuses.value = (c.data.data || c.data)
    faculties.value = (f.data.data || f.data)
    departments.value = (d.data.data || d.data)
    researchCentres.value = (rc.data.data || rc.data)   // NEW
  } catch (e) {}
  fetchCalls()
})
</script>