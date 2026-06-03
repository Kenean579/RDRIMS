<template>
  <div class="flex flex-col gap-8 pb-12 animate-fade card">
    <!-- Header -->
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
      <div>
        <h1 class="text-3xl font-black text-slate-900 tracking-tight">Publications</h1>
        <p class="text-slate-500 font-medium mt-1">Your research papers and articles.</p>
      </div>
      <button @click="showAdd = true" class="btn btn-primary h-11 px-6 shadow-lg shadow-blue-500/20">
        <svg class="w-4 h-4 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 4v16m8-8H4" /></svg>
        Add New Publication
      </button>
    </div>

    <!-- Filters -->
    <div class="card p-5 bg-slate-50/50">
      <div class="flex flex-col sm:flex-row gap-5 items-start">
        <div class="flex-1 w-full relative">
          <label class="block text-[11px] text-slate-500 font-black capitalize tracking-widest mb-2 ml-1">Search</label>
          <div class="relative group">
            <span class="absolute left-3.5 top-1/2 -translate-y-1/2 text-slate-400 group-focus-within:text-brand transition-colors">
              <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><circle cx="11" cy="11" r="8"/><path d="m21 21-4.35-4.35"/></svg>
            </span>
            <input v-model="filters.search" type="text" placeholder="Search by title or author..." class="input pl-10" @input="debounceSearch" />
          </div>
        </div>
        <div class="w-full sm:w-56">
          <label class="block text-[11px] text-slate-500 font-black capitalize tracking-widest mb-2 ml-1">Category</label>
          <select v-model="filters.type" @change="fetchPublications(1)" class="input font-bold">
            <option value="">All Categories</option>
            <option v-for="t in types" :key="t" :value="t">{{ t }}</option>
          </select>
        </div>
      </div>
    </div>

    <div v-if="loading" class="card p-24 flex flex-col justify-center items-center gap-4">
      <div class="animate-spin rounded-full h-10 w-10 border-b-2 border-brand"></div>
      <p class="text-xs font-bold text-slate-400 capitalize tracking-widest">Loading publications...</p>
    </div>

    <div v-else-if="publications.length === 0" class="card">
      <EmptyState icon="📄" title="No publications found" description="You haven't added any research papers yet." action-label="Add First Publication" action-icon="add" @action="showAdd = true" />
    </div>

    <div v-else class="grid grid-cols-1 lg:grid-cols-2 gap-6">
      <div v-for="pub in publications" :key="pub.id" class="card p-6 flex flex-col group card-hover relative overflow-hidden border-l-4 border-l-brand hover:border-l-indigo-600 transition-all">
        <div class="flex items-start justify-between mb-4">
          <div class="flex-1 pr-4 min-w-0">
            <div class="flex items-center gap-3 mb-2.5">
              <span class="px-2.5 py-0.5 bg-indigo-50 text-indigo-600 border border-indigo-100 text-[10px] font-black capitalize tracking-widest rounded-md">{{ pub.type?.name || 'Journal Article' }}</span>
              <span v-if="pub.doi" class="text-[9px] text-slate-400 font-black tracking-widest capitalize truncate max-w-[150px]">DOI: {{ pub.doi }}</span>
            </div>
            <h3 class="text-base font-black text-slate-900 mb-2 leading-tight group-hover:text-brand transition-colors line-clamp-2 min-h-10">{{ pub.title }}</h3>
            <div class="flex items-center gap-2 text-sm text-slate-500 font-bold">
              <div class="w-7 h-7 rounded-full bg-slate-100 flex items-center justify-center text-slate-400 shrink-0 border border-slate-200">
                <i class="fas fa-user-graduate text-[10px]"></i>
              </div>
              <span class="truncate text-xs">{{ pub.authors }}</span>
            </div>
          </div>
          <div class="w-12 h-12 rounded-2xl bg-linear-to-br from-indigo-500 to-blue-600 text-white flex items-center justify-center font-black shadow-lg shadow-indigo-500/30 shrink-0">
             <i class="fas fa-book-open"></i>
          </div>
        </div>
        
        <div class="mt-auto pt-4 border-t border-slate-100 flex items-center justify-between">
           <div class="flex flex-col gap-1">
             <span class="flex items-center gap-1.5 text-[9px] font-black capitalize tracking-widest text-brand">
                <i class="fas fa-university opacity-70"></i>
                {{ pub.journal_name || pub.conference_name || 'RDRIMS Repository' }}
             </span>
             <span class="flex items-center gap-1.5 text-[9px] font-black capitalize tracking-widest text-slate-400">
                <i class="far fa-calendar-alt opacity-70"></i>
                Published: {{ pub.publication_year }}
             </span>
           </div>
           
           <div class="flex gap-2">
             <a v-if="pub.url" :href="pub.url" target="_blank" class="btn btn-ghost text-[10px] font-black capitalize tracking-widest py-1.5 px-3 text-brand hover:bg-brand-light">
               Read Link
             </a>
             <button @click="$router.push(`/app/publications/${pub.id}`)" class="w-8 h-8 rounded-full flex items-center justify-center bg-slate-50 text-slate-400 group-hover:bg-brand group-hover:text-white transition-all duration-300">
               <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M9 5l7 7-7 7" /></svg>
             </button>
           </div>
        </div>
      </div>
      <div class="lg:col-span-2 px-8 py-4 bg-slate-50/50 rounded-2xl border border-slate-100 overflow-hidden mt-2">
        <Pagination :current-page="pagination.current_page" :total-pages="pagination.last_page" :total="pagination.total" @page-change="fetchPublications" />
      </div>
    </div>

    <!-- Add Publication Modal -->
    <Modal :show="showAdd" title="Add New Publication" size="lg" @close="showAdd = false">
      <form @submit.prevent="savePublication" class="space-y-6">
        <div>
          <label class="block text-[11px] text-slate-500 font-black capitalize tracking-widest mb-2 ml-1">Publication Title *</label>
          <input v-model="form.title" type="text" required class="input h-12 font-bold" placeholder="e.g. New Research in Science" />
        </div>
        
        <div>
          <label class="block text-[11px] text-slate-500 font-black capitalize tracking-widest mb-2 ml-1">Authors *</label>
          <input v-model="form.authors" type="text" required placeholder="Names (e.g. Abebe K., Smith J.)" class="input h-12 font-bold" />
          <p class="text-[10px] text-slate-400 font-bold mt-2 ml-1 capitalize tracking-widest">Separate names with commas</p>
        </div>
        
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-6">
          <div>
            <label class="block text-[11px] text-slate-500 font-black capitalize tracking-widest mb-2 ml-1">Year published *</label>
            <input v-model.number="form.publication_year" type="number" required class="input h-12 font-bold" />
          </div>
          <div>
            <label class="block text-[11px] text-slate-500 font-black capitalize tracking-widest mb-2 ml-1">Category *</label>
            <select v-model="form.type_id" required class="input h-12 font-bold text-slate-700">
              <option value="">Select category</option>
              <option v-for="t in typeOptions" :key="t.id" :value="t.id">{{ t.name }}</option>
            </select>
          </div>
        </div>
        
        <div>
          <label class="block text-[11px] text-slate-500 font-black capitalize tracking-widest mb-2 ml-1">Journal or Conference Name</label>
          <input v-model="form.journal_name" type="text" class="input h-12 font-bold" placeholder="e.g. International Science Journal" />
        </div>
        
        <div class="flex justify-end gap-3 pt-6 mt-6 border-t border-slate-100">
          <button type="button" @click="showAdd = false" class="btn btn-secondary px-6 font-black tracking-widest capitalize text-[11px]">Cancel</button>
          <button type="submit" class="btn btn-primary px-10 shadow-lg shadow-blue-500/20 font-black tracking-widest capitalize text-[11px]">Save Publication</button>
        </div>
      </form>
    </Modal>
  </div>
</template>

<script setup>
import { ref, reactive, onMounted } from 'vue'
import api from '@/services/api'
import Pagination from '@/components/Pagination.vue'
import Modal from '@/components/Modal.vue'
import EmptyState from '@/components/EmptyState.vue'
import { useNotificationStore } from '@/stores/notification'
const notif = useNotificationStore()
const loading = ref(true); const publications = ref([]); const showAdd = ref(false)
const types = ref(['Journal Article', 'Conference Paper', 'Book Chapter', 'Book', 'Other'])
const typeOptions = ref([])
const filters = reactive({ search: '', type: '' })
const pagination = reactive({ current_page: 1, last_page: 1, total: 0 })
const form = reactive({ title: '', authors: '', publication_year: new Date().getFullYear(), journal_name: '', type_id: '' })
let timer = null
async function fetchPublications(page = 1) { loading.value = true; try { const { data } = await api.get('/publications', { params: { page, ...filters } }); publications.value = data.data; Object.assign(pagination, { current_page: data.current_page, last_page: data.last_page, total: data.total }) } catch (e) {} finally { loading.value = false } }
function debounceSearch() { clearTimeout(timer); timer = setTimeout(() => fetchPublications(1), 400) }
async function savePublication() { try { await api.post('/publications', form); notif.success('Publication added!'); showAdd.value = false; fetchPublications(1) } catch (e) { notif.error('Failed to save.') } }
onMounted(async () => { fetchPublications(); try { const { data } = await api.get('/lookups/publication_access_types'); typeOptions.value = data } catch (e) {} })
</script>
