<template>
  <div class="flex flex-col gap-5 pb-6 animate-fade">
    <div class="card p-8 bg-slate-50 border-slate-100 relative overflow-hidden">
      <div class="relative z-10">
        <h1 class="text-xl font-bold text-slate-900 tracking-tight">Student Research & Innovation</h1>
        <p class="text-slate-500 font-medium mt-1">Discover outstanding research projects and outputs from our undergraduate and postgraduate students.</p>
      </div>
      <div class="absolute right-0 top-0 w-32 h-32 bg-brand/5 rounded-full translate-x-8 -translate-y-8"></div>
    </div>

    <div class="card p-8 flex flex-col gap-5">
      <div class="flex flex-col md:flex-row gap-5 items-end">
        <div class="flex-1 w-full relative">
          <label class="block text-xs text-slate-500 font-medium mb-2 ml-1">Search Research Items</label>
          <div class="relative group">
            <span class="absolute left-3.5 top-1/2 -translate-y-1/2 text-slate-400">
              <svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
            </span>
            <input v-model="search" type="text" placeholder="Search by title, student name, or category..." class="input pl-10" @input="debounceSearch" />
          </div>
        </div>
      </div>
      <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
        <div>
          <label class="block text-xs text-slate-500 font-medium mb-2 ml-1">Academic Level</label>
          <LookupSelect v-model="filters.student_level" lookup-key="student_levels" placeholder="All Levels" @change="fetchOutputs(1)" />
        </div>
        <div>
          <label class="block text-xs text-slate-500 font-medium mb-2 ml-1">Category</label>
          <LookupSelect v-model="filters.category" lookup-key="output_categories" placeholder="All Categories" @change="fetchOutputs(1)" />
        </div>
        <div>
          <label class="block text-xs text-slate-500 font-medium mb-2 ml-1">Department</label>
          <select v-model="filters.department_id" class="input font-bold" @change="fetchOutputs(1)">
            <option value="">All Departments</option>
            <option v-for="d in departments" :key="d.id" :value="d.id">{{ d.name }}</option>
          </select>
        </div>
        <div>
           <label class="block text-xs text-slate-500 font-medium mb-2 ml-1">Academic Year</label>
           <select v-model="filters.academic_year" class="input font-bold" @change="fetchOutputs(1)">
              <option value="">All Years</option>
              <option v-for="ay in academicYears" :key="ay.id" :value="ay.name">{{ ay.name }}</option>
           </select>
        </div>
      </div>
    </div>

    <div v-if="loading" class="grid grid-cols-1 md:grid-cols-2 gap-6">
      <div v-for="i in 4" :key="i" class="card h-48 animate-pulse bg-slate-50/50"></div>
    </div>

    <div v-else-if="outputs.length === 0" class="card p-12 text-center">
      <div class="text-4xl mb-4">🎓</div>
      <p class="text-slate-500 font-medium">No student research items found matching your filters.</p>
    </div>

    <div v-else class="grid grid-cols-1 md:grid-cols-2 gap-6">
      <div v-for="out in outputs" :key="out.id" class="card p-6 group hover:border-brand/30 transition-all flex flex-col">
        <div class="flex items-center justify-between mb-4">
          <span class="px-2.5 py-1 rounded-lg bg-brand/10 text-brand text-[10px] font-bold uppercase tracking-wider">
            {{ out.category?.name || 'Research' }}
          </span>
          <span class="text-[10px] font-bold text-slate-400">
            {{ formatDate(out.created_at) }}
          </span>
        </div>
        
        <h3 class="text-lg font-bold text-slate-800 leading-tight mb-3 group-hover:text-brand transition-colors flex-1">{{ out.title }}</h3>
        
        <div class="flex items-center gap-3 mb-5 p-3 bg-slate-50 rounded-2xl border border-slate-100/50">
          <div class="h-9 w-9 bg-white rounded-xl shadow-xs flex items-center justify-center text-brand font-bold text-sm">
            {{ getStudentName(out).charAt(0) }}
          </div>
          <div class="min-w-0">
             <p class="text-xs font-bold text-slate-700 truncate">{{ getStudentName(out) }}</p>
             <p class="text-[10px] font-medium text-slate-400 capitalize">{{ out.student_level?.name || 'Student' }}</p>
          </div>
        </div>

        <div class="flex items-center justify-between mt-auto pt-4 border-t border-slate-100">
            <span class="text-[10px] font-medium text-slate-400 italic">
               {{ out.participant_entries?.length > 1 ? '+' + (out.participant_entries.length - 1) + ' contributors' : 'Individual Work' }}
            </span>
            <router-link :to="`/app/outputs/${out.id}`" class="text-xs font-bold text-brand hover:underline">
              View Details →
            </router-link>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, reactive, onMounted, computed } from 'vue'
import api from '@/services/api'
import { formatDate } from '@/utils/formatters'
import LookupSelect from '@/components/LookupSelect.vue'

const outputs = ref([])
const loading = ref(true)
const search = ref('')
const departments = ref([])
const academicYears = ref([])

const filters = reactive({
  student_level: '',
  category: '',
  department_id: '',
  academic_year: ''
})

let searchTimer = null
function debounceSearch() {
  clearTimeout(searchTimer)
  searchTimer = setTimeout(() => fetchOutputs(1), 400)
}

function getStudentName(output) {
  const student = output.participant_entries?.find(p => p.participant_type?.name === 'student' || !p.participant_type)
  return student?.user?.name || student?.name || 'Anonymous Student'
}

async function fetchOutputs(page = 1) {
  loading.value = true
  try {
    const params = { 
        page,
        search: search.value,
        student_level: filters.student_level,
        category: filters.category,
        department: filters.department_id,
        academic_year: filters.academic_year
    }
    const { data } = await api.get('/student-outputs', { params })
    outputs.value = data.data || data
  } catch (e) {
  } finally {
    loading.value = false
  }
}

onMounted(async () => {
  try {
    const [dRes, yRes] = await Promise.all([
      api.get('/departments'),
      api.get('/academic-years')
    ])
    departments.value = dRes.data.data || dRes.data
    academicYears.value = yRes.data.data || yRes.data
  } catch (e) {}
  fetchOutputs()
})
</script>
