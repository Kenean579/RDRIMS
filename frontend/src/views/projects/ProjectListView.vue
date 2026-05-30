<template>
  <div class="flex flex-col gap-8 pb-12 animate-fade card">
    <!-- Header -->
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
      <div>
        <h1 class="text-3xl font-black text-slate-900 tracking-tight">Research Projects</h1>
        <p class="text-slate-500 font-medium mt-1">Track the progress of all ongoing and finished research projects.</p>
      </div>
      <div class="flex items-center gap-3">
        <button @click="fetchProjects(1)" class="btn btn-secondary h-11 px-6 shadow-sm group">
          <svg class="w-4 h-4 group-hover:rotate-180 transition-transform duration-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" /></svg>
          Refresh
        </button>
      </div>
    </div>

    <!-- Filters -->
    <div class="card p-5 bg-slate-50/50">
      <div class="flex flex-col sm:flex-row gap-5 items-start">
        <div class="flex-1 w-full relative">
          <label class="block text-[11px] text-slate-500 font-bold uppercase tracking-widest mb-2 ml-1">Search</label>
          <div class="relative group">
            <span class="absolute left-3.5 top-1/2 -translate-y-1/2 text-slate-400 group-focus-within:text-brand transition-colors">
              <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><circle cx="11" cy="11" r="8"/><path d="m21 21-4.35-4.35"/></svg>
            </span>
            <input v-model="search" type="text" placeholder="Search by title or researcher..." class="input pl-10" @input="debounceFetch" />
          </div>
        </div>
        <div class="w-full sm:w-56">
          <label class="block text-[11px] text-slate-500 font-bold uppercase tracking-widest mb-2 ml-1">Status</label>
          <select v-model="status" @change="fetchProjects(1)" class="input font-semibold">
            <option value="">All Statuses</option>
            <option value="active">Active</option>
            <option value="completed">Completed</option>
            <option value="suspended">Suspended</option>
          </select>
        </div>
      </div>
    </div>

    <!-- Content -->
    <div v-if="loading" class="card p-24 flex flex-col justify-center items-center gap-4">
      <div class="animate-spin rounded-full h-10 w-10 border-b-2 border-brand"></div>
      <p class="text-xs font-bold text-slate-400 uppercase tracking-widest">Loading projects...</p>
    </div>
    
    <div v-else-if="projects.length === 0" class="card">
       <EmptyState icon="📁" title="No projects found" description="There are no research projects matching your search." />
    </div>

    <div v-else class="card overflow-hidden">
      <div class="overflow-x-auto">
        <table class="table-auto">
          <thead>
            <tr>
              <th class="px-6 py-4">Title & ID</th>
              <th>Lead Researcher</th>
              <th>Status</th>
              <th style="width:160px">Progress</th>
              <th class="text-right px-6">Ends</th>
            </tr>
          </thead>
          <tbody>
            <tr v-for="p in projects" :key="p.id" @click="$router.push(`/projects/${p.id}`)" class="cursor-pointer group hover:bg-slate-50/50 transition-colors">
              <td class="px-6 py-5">
                <div class="max-w-md">
                  <p class="font-black text-slate-900 group-hover:text-brand transition-colors text-sm leading-snug line-clamp-2">{{ p.title }}</p>
                  <p class="text-[10px] text-slate-400 mt-2 font-black uppercase tracking-widest">ID: {{ String(p.id).padStart(4, '0') }}</p>
                </div>
              </td>
              <td>
                <div class="flex items-center gap-3">
                   <div class="w-8 h-8 rounded-xl bg-slate-100 flex items-center justify-center text-[10px] font-black text-slate-500 border border-slate-200 group-hover:bg-brand-light group-hover:text-brand group-hover:border-brand/20 transition-all">{{ getInitials(p.proposal?.submitted_by?.name) }}</div>
                   <span class="text-xs font-bold text-slate-700">{{ p.proposal?.submitted_by?.name || 'Researcher' }}</span>
                </div>
              </td>
              <td><StatusBadge :status="p.status?.name || 'active'" /></td>
              <td>
                <div class="flex flex-col gap-2">
                  <div class="w-full h-2 bg-slate-100 rounded-full overflow-hidden shadow-inner">
                    <div class="h-full bg-brand rounded-full transition-all duration-1000 ease-out" :style="{ width: calculateProgress(p) + '%' }"></div>
                  </div>
                  <p class="text-[10px] font-black text-slate-400 uppercase tracking-tighter">{{ calculateProgress(p) }}% Done</p>
                </div>
              </td>
              <td class="text-right px-6">
                <div class="flex items-center justify-end gap-2 text-xs font-bold text-slate-500 uppercase tracking-tight">
                  <svg class="w-3.5 h-3.5 text-slate-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" /></svg>
                  {{ formatDate(p.end_date) }}
                </div>
              </td>
            </tr>
          </tbody>
        </table>
      </div>
      <div class="px-6 py-4 bg-slate-50/50 border-t border-slate-100 overflow-hidden">
        <Pagination :current-page="pagination.current_page" :total-pages="pagination.last_page" :total="pagination.total" @page-change="fetchProjects" />
      </div>
    </div>
  </div>
</template>
<script setup>
import { ref, reactive, onMounted } from 'vue'
import api from '@/services/api'
import StatusBadge from '@/components/StatusBadge.vue'
import Pagination from '@/components/Pagination.vue'
import EmptyState from '@/components/EmptyState.vue'
import { formatDate, getInitials } from '@/utils/formatters'
const loading = ref(true); const projects = ref([]); const search = ref(''); const status = ref('')
const pagination = reactive({ current_page: 1, last_page: 1, total: 0 })
let timer = null
async function fetchProjects(page = 1) { loading.value = true; try { const { data } = await api.get('/projects', { params: { page, search: search.value, status: status.value } }); projects.value = data.data; Object.assign(pagination, { current_page: data.current_page, last_page: data.last_page, total: data.total }) } catch (e) {} finally { loading.value = false } }
function calculateProgress(p) { if (!p.milestones || p.milestones.length === 0) return 0; const comp = p.milestones.filter(m => m.status === 'completed').length; return Math.round((comp / p.milestones.length) * 100) }
function debounceFetch() { clearTimeout(timer); timer = setTimeout(() => fetchProjects(1), 400) }
onMounted(() => fetchProjects())
</script>
