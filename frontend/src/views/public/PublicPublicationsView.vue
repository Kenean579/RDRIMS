<template>
  <div class="flex flex-col gap-5 pb-6 animate-fade">
    <div class="card p-8 bg-slate-50 border-slate-100 relative overflow-hidden">
      <div class="relative z-10">
        <h1 class="text-xl font-bold text-slate-900 tracking-tight">Research Repository</h1>
        <p class="text-slate-500 font-medium mt-1">Institutional publications, journal papers, and technical reports.</p>
      </div>
      <div class="absolute right-0 top-0 w-32 h-32 bg-brand/5 rounded-full translate-x-8 -translate-y-8"></div>
    </div>

    <div class="card p-8 flex flex-col gap-5">
      <div class="flex flex-col md:flex-row gap-5 items-end">
        <div class="flex-1 w-full relative">
          <label class="block text-xs text-slate-500 font-medium mb-2 ml-1">Search Publications</label>
          <div class="relative group">
            <span class="absolute left-3.5 top-1/2 -translate-y-1/2 text-slate-400">
              <svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
            </span>
            <input v-model="search" type="text" placeholder="Search by title, author, or journal..." class="input pl-10" @input="debounceSearch" />
          </div>
        </div>
        <div class="w-full md:w-48">
          <label class="block text-xs text-slate-500 font-medium mb-2 ml-1">Year</label>
          <select v-model="yearFilter" class="input font-bold" @change="fetchPublications(1)">
            <option value="">All Years</option>
            <option v-for="y in years" :key="y" :value="y">{{ y }}</option>
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
          <select v-model="filters.department_id" class="input font-bold" :disabled="!filters.faculty_id" @change="fetchPublications(1)">
            <option value="">All Departments</option>
            <option v-for="d in filteredDepartments" :key="d.id" :value="d.id">{{ d.name }}</option>
          </select>
        </div>
        <!-- Research Centre (independent) -->
        <div>
          <label class="block text-xs text-slate-500 font-medium mb-2 ml-1">Research Centre</label>
          <select v-model="filters.research_center_id" class="input font-bold" @change="fetchPublications(1)">
            <option value="">All Centres</option>
            <option v-for="rc in researchCentres" :key="rc.id" :value="rc.id">{{ rc.name }}</option>
          </select>
        </div>
      </div>
    </div>

    <div v-if="loading" class="space-y-6">
      <div v-for="i in 3" :key="i" class="card h-40 animate-pulse"></div>
    </div>

    <div v-else-if="publications.length === 0" class="card p-8 text-center text-slate-400 text-xs italic">
      No publications found in our repository.
    </div>

    <div v-else class="space-y-4">
      <div v-for="pub in publications" :key="pub.id" class="card p-4 flex flex-col md:flex-row gap-4 group card-hover relative border-l-4 border-l-transparent transition-all">
        <div class="w-16 h-20 rounded-2xl overflow-hidden shrink-0 bg-blue-50 border border-slate-100 shadow-sm flex items-center justify-center">
          <div class="w-full h-full flex items-center justify-center bg-linear-to-br from-indigo-50 to-blue-100">
            <svg class="w-6 h-6 text-indigo-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/></svg>
          </div>
        </div>
        
        <div class="flex-1 min-w-0">
          <h3 class="text-lg font-bold text-slate-800 leading-tight mb-2 group-hover:text-brand transition-colors">{{ pub.title }}</h3>
          <p class="text-sm font-medium text-slate-500 mb-4">{{ formatAuthors(pub.authors) }}</p>
          
          <div class="flex flex-wrap items-center gap-4 text-xs font-medium text-slate-400">
            <span v-if="pub.journal" class="p-2 bg-slate-50 rounded-2xl border border-slate-100 text-slate-600">{{ pub.journal }}</span>
            <span class="flex items-center gap-1.5"><svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg> {{ formatDate(pub.publication_date) }}</span>
            <span v-if="pub.doi" class="text-brand">DOI: {{ pub.doi }}</span>
          </div>
        </div>

        <div class="flex items-end">
          <a v-if="pub.doi || pub.scholar_url" :href="pub.scholar_url || `https://doi.org/${pub.doi}`" target="_blank" class="btn btn-secondary text-xs font-medium px-4 h-9">
            Read Online
          </a>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue'
import api from '@/services/api'
import { formatDate } from '@/utils/formatters'

const publications = ref([])
const loading = ref(true)
const search = ref('')
const yearFilter = ref('')
const years = ref([])
const filters = ref({ university_id: '', campus_id: '', faculty_id: '', department_id: '', research_center_id: '' })
const universities = ref([])
const campuses = ref([])
const faculties = ref([])
const departments = ref([])
const researchCentres = ref([])

const filteredCampuses = computed(() => campuses.value.filter(c => String(c.university_id) === String(filters.value.university_id)))
const filteredFaculties = computed(() => faculties.value.filter(f => String(f.campus_id) === String(filters.value.campus_id)))
const filteredDepartments = computed(() => departments.value.filter(d => String(d.faculty_id) === String(filters.value.faculty_id)))

function onUniversityChange() { filters.value.campus_id = ''; filters.value.faculty_id = ''; filters.value.department_id = ''; fetchPublications(1) }
function onCampusChange() { filters.value.faculty_id = ''; filters.value.department_id = ''; fetchPublications(1) }
function onFacultyChange() { filters.value.department_id = ''; fetchPublications(1) }

let searchTimer = null
function debounceSearch() { clearTimeout(searchTimer); searchTimer = setTimeout(() => fetchPublications(1), 400) }

function formatAuthors(authors) {
  if (!authors) return 'Not specified'
  if (typeof authors === 'string') {
    const list = authors.split(',').map(a => a.trim())
    return list.length <= 3 ? authors : `${list.slice(0, 3).join(', ')} et al.`
  }
  if (Array.isArray(authors)) {
    const names = authors.map(a => a.user?.name || a.external_author_name).filter(Boolean)
    return names.length === 0 ? 'Not specified' : names.length <= 3 ? names.join(', ') : `${names.slice(0, 3).join(', ')} et al.`
  }
  return authors
}

async function fetchPublications(page = 1) {
  loading.value = true
  try {
    const params = { page }
    if (search.value) params.search = search.value
    if (yearFilter.value) params.year = yearFilter.value
    if (filters.value.university_id) params.university_id = filters.value.university_id
    if (filters.value.campus_id) params.campus_id = filters.value.campus_id
    if (filters.value.faculty_id) params.faculty_id = filters.value.faculty_id
    if (filters.value.department_id) params.department_id = filters.value.department_id
    if (filters.value.research_center_id) params.research_center_id = filters.value.research_center_id
    const { data } = await api.get('/publications', { params })
    publications.value = data.data || data
  } catch (e) {} finally { loading.value = false }
}

onMounted(async () => {
  try {
    const u = await api.get('/universities')
    const c = await api.get('/campuses')
    const f = await api.get('/faculties')
    const d = await api.get('/departments')
    const rc = await api.get('/research-centers')
    const pubs = await api.get('/publications', { params: { per_page: 200 } })
    universities.value = (u.data.data || u.data)
    campuses.value = (c.data.data || c.data)
    faculties.value = (f.data.data || f.data)
    departments.value = (d.data.data || d.data)
    researchCentres.value = (rc.data.data || rc.data)
    const pubData = pubs.data.data || pubs.data
    const yearSet = new Set(pubData.map(p => p.publication_date?.substring(0, 4)).filter(Boolean))
    years.value = Array.from(yearSet).sort((a, b) => b - a)
  } catch (e) {}
  fetchPublications()
})
</script>