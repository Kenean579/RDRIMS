<template>
  <div class="flex flex-col gap-5 pb-6 animate-fade">
    <div class="card p-8 bg-slate-50 border-slate-100 relative overflow-hidden">
      <div class="relative z-10">
        <h1 class="text-xl font-bold text-slate-900 tracking-tight">Researchers</h1>
        <p class="text-slate-500 font-medium mt-1">Connect with experts and principal researchers across our research centers.</p>
      </div>
      <div class="absolute right-0 top-0 w-32 h-32 bg-brand/5 rounded-full translate-x-8 -translate-y-8"></div>
    </div>

    <div class="card p-8 flex flex-col gap-5">
      <div class="flex flex-col md:flex-row gap-5 items-end">
        <div class="flex-1 w-full relative">
          <label class="block text-xs text-slate-500 font-medium mb-2 ml-1">Search Experts</label>
          <div class="relative group">
            <span class="absolute left-3.5 top-1/2 -translate-y-1/2 text-slate-400">
              <svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
            </span>
            <input v-model="search" type="text" placeholder="Search by name, expertise, or department..." class="input pl-10" @input="debounceSearch" />
          </div>
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
          <select v-model="filters.department_id" class="input font-bold" :disabled="!filters.faculty_id" @change="fetchResearchers">
            <option value="">All Departments</option>
            <option v-for="d in filteredDepartments" :key="d.id" :value="d.id">{{ d.name }}</option>
          </select>
        </div>
        <!-- NEW: Research Centre (independent) -->
        <div>
          <label class="block text-xs text-slate-500 font-medium mb-2 ml-1">Research Centre</label>
          <select v-model="filters.research_center_id" class="input font-bold" @change="fetchResearchers">
            <option value="">All Centres</option>
            <option v-for="rc in researchCentres" :key="rc.id" :value="rc.id">{{ rc.name }}</option>
          </select>
        </div>
      </div>
    </div>

    <div v-if="loading" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
      <div v-for="i in 6" :key="i" class="card h-40 animate-pulse"></div>
    </div>

    <div v-else-if="researchers.length === 0" class="card p-8 text-center text-slate-400 text-xs italic">
      No researchers found matching your criteria.
    </div>

    <div v-else class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
      <div v-for="user in researchers" :key="user.id" class="card p-8 flex flex-col group card-hover relative/10 transition-all">
        <div class="flex items-center gap-4 mb-4">
           <div class="w-12 h-12 rounded-2xl overflow-hidden bg-brand-light border border-brand/20 shrink-0 shadow-sm">
             <img
               v-if="imageUrl(user.profile_image)"
               :src="imageUrl(user.profile_image)"
               :alt="user.name"
               class="w-full h-full object-cover"
             />
             <div v-else class="w-full h-full flex items-center justify-center text-brand font-bold text-sm ">
               {{ user.name.charAt(0) }}
             </div>
           </div>
           <div class="min-w-0">
             <h3 class="text-sm font-bold text-slate-900 leading-tight truncate">{{ user.name }}</h3>
             <p class="text-xs font-medium text-slate-400 mt-1">{{ user.department?.name || 'Researcher' }}</p>
           </div>
        </div>
        
        <div class="flex flex-wrap gap-1.5 mb-6">
          <span v-for="ex in user.expertise?.slice(0, 3)" :key="ex.id" class="px-2 py-0.5 bg-slate-50 text-slate-500 text-xs font-medium rounded border border-slate-100">
            {{ ex.name }}
          </span>
          <span v-if="user.expertise?.length > 3" class="text-xs font-medium text-slate-300 self-center">+{{ user.expertise.length - 3 }}</span>
        </div>

        <div class="mt-auto pt-4 border-t border-slate-50 flex items-center justify-between">
           <a :href="`mailto:${user.email}`" class="text-xs font-medium text-brand hover:underline">Contact</a>
           <div class="flex gap-2">
             <a v-if="user.orcid_id" :href="`https://orcid.org/${user.orcid_id}`" target="_blank" class="text-slate-300 hover:text-brand transition-colors" title="ORCID Profile">
               <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                 <path stroke-linecap="round" stroke-linejoin="round" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14" />
               </svg>
             </a>
           </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue'
import { imageUrl } from '@/utils/formatters'
import api from '@/services/api'

const researchers = ref([])
const loading = ref(true)
const search = ref('')
const filters = ref({ university_id: '', campus_id: '', faculty_id: '', department_id: '', research_center_id: '' })
const universities = ref([])
const campuses = ref([])
const faculties = ref([])
const departments = ref([])
const researchCentres = ref([])   // NEW

const filteredCampuses = computed(() => campuses.value.filter(c => String(c.university_id) === String(filters.value.university_id)))
const filteredFaculties = computed(() => faculties.value.filter(f => String(f.campus_id) === String(filters.value.campus_id)))
const filteredDepartments = computed(() => departments.value.filter(d => String(d.faculty_id) === String(filters.value.faculty_id)))

function onUniversityChange() { filters.value.campus_id = ''; filters.value.faculty_id = ''; filters.value.department_id = ''; fetchResearchers() }
function onCampusChange() { filters.value.faculty_id = ''; filters.value.department_id = ''; fetchResearchers() }
function onFacultyChange() { filters.value.department_id = ''; fetchResearchers() }

let searchTimer = null
function debounceSearch() { clearTimeout(searchTimer); searchTimer = setTimeout(() => fetchResearchers(), 400) }

async function fetchResearchers() {
  loading.value = true
  try {
    const params = {}
    if (search.value) params.search = search.value
    if (filters.value.university_id) params.university_id = filters.value.university_id
    if (filters.value.campus_id) params.campus_id = filters.value.campus_id
    if (filters.value.faculty_id) params.faculty_id = filters.value.faculty_id
    if (filters.value.department_id) params.department_id = filters.value.department_id
    if (filters.value.research_center_id) params.research_center_id = filters.value.research_center_id   // NEW
    const { data } = await api.get('/public/researchers', { params })
    researchers.value = data.data || data
  } catch (e) {} finally { loading.value = false }
}

onMounted(async () => {
  try {
    const u = await api.get('/universities')
    const c = await api.get('/campuses')
    const f = await api.get('/faculties')
    const d = await api.get('/departments')
    const rc = await api.get('/research-centers')   // NEW
    universities.value = (u.data.data || u.data)
    campuses.value = (c.data.data || c.data)
    faculties.value = (f.data.data || f.data)
    departments.value = (d.data.data || d.data)
    researchCentres.value = (rc.data.data || rc.data)   // NEW
  } catch (e) {}
  fetchResearchers()
})
</script>