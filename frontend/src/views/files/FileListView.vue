<template>
  <div class="flex flex-col gap-8 pb-12 animate-fade card">
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
      <div>
        <h1 class="text-3xl font-black text-slate-900 tracking-tight">File Registry</h1>
        <p class="text-slate-500 font-medium mt-1">Global management of institutional research documents and datasets.</p>
      </div>
    </div>

    <!-- Filters -->
    <div class="card p-5 flex flex-col md:flex-row gap-5 items-end">
      <div class="flex-1 w-full relative">
        <label class="block text-[11px] text-slate-500 font-black uppercase tracking-widest mb-2 ml-1">Search Files</label>
        <div class="relative group">
          <span class="absolute left-3.5 top-1/2 -translate-y-1/2 text-slate-400">
            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><circle cx="11" cy="11" r="8"/><path d="m21 21-4.35-4.35"/></svg>
          </span>
          <input v-model="searchQuery" type="text" placeholder="Search by filename or content type..." class="input pl-11 h-12" />
        </div>
      </div>
       <div class="w-full md:w-64">
        <label class="block text-[11px] text-slate-500 font-black uppercase tracking-widest mb-2 ml-1">Mime Group</label>
        <select v-model="mimeFilter" class="input h-12 font-bold">
          <option value="">All Types</option>
          <option value="application/pdf">PDF Documents</option>
          <option value="image/">Images</option>
          <option value="application/vnd.openxmlformats-officedocument">MS Office</option>
        </select>
      </div>
    </div>

    <div v-if="loading" class="card p-24 flex justify-center"><div class="animate-spin rounded-full h-10 w-10 border-b-2 border-brand"></div></div>
    
    <div v-else class="card overflow-hidden">
      <div class="overflow-x-auto">
        <table class="table-auto">
          <thead>
            <tr>
              <th class="pl-8 py-4 text-[10px] font-black uppercase tracking-widest text-slate-400">Filename</th>
              <th class="py-4 text-[10px] font-black uppercase tracking-widest text-slate-400">Size</th>
              <th class="py-4 text-[10px] font-black uppercase tracking-widest text-slate-400">Mime Type</th>
              <th class="py-4 text-[10px] font-black uppercase tracking-widest text-slate-400">Stored On</th>
              <th class="pr-8 py-4 text-[10px] font-black uppercase tracking-widest text-slate-400 text-right">Actions</th>
            </tr>
          </thead>
          <tbody class="divide-y divide-slate-50">
            <tr v-for="file in filteredFiles" :key="file.id" class="group hover:bg-slate-50/50 transition-colors">
              <td class="pl-8 py-4">
                <div class="flex items-center gap-3">
                  <div class="w-9 h-9 rounded-xl bg-slate-50 flex items-center justify-center text-slate-400 border border-slate-100 shadow-inner group-hover:bg-brand-light group-hover:text-brand transition-all">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z"/></svg>
                  </div>
                  <div class="min-w-0">
                    <p class="text-sm font-black text-slate-800 truncate">{{ file.original_name }}</p>
                    <p class="text-[9px] font-black text-slate-400 uppercase tracking-widest">{{ file.path }}</p>
                  </div>
                </div>
              </td>
              <td class="py-4 text-xs font-bold text-slate-600">{{ formatSize(file.size) }}</td>
              <td class="py-4 font-black">
                <span class="px-2 py-0.5 bg-slate-100 text-slate-500 rounded text-[9px] uppercase tracking-widest border border-slate-200">{{ file.mime_type }}</span>
              </td>
              <td class="py-4 text-xs font-bold text-slate-400">{{ formatDate(file.created_at) }}</td>
              <td class="pr-8 py-4 text-right">
                 <button @click="downloadFile(file)" class="btn btn-ghost text-brand text-[10px] font-black uppercase tracking-widest py-1.5 px-4 h-auto border border-slate-100 hover:bg-brand hover:text-white transition-all shadow-sm">
                   Download
                 </button>
              </td>
            </tr>
          </tbody>
        </table>
      </div>
      <div v-if="filteredFiles.length === 0" class="p-24 text-center">
        <p class="text-sm font-black text-slate-400 uppercase tracking-widest italic">No files found in the registry.</p>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue'
import { useNotificationStore } from '@/stores/notification'
import api from '@/services/api'
import { formatDate } from '@/utils/formatters'

const notif = useNotificationStore()
const files = ref([]); const loading = ref(true)
const searchQuery = ref(''); const mimeFilter = ref('')

const filteredFiles = computed(() => {
  return files.value.filter(f => {
    const s = searchQuery.value.toLowerCase()
    const matchSearch = !s || f.original_name?.toLowerCase().includes(s) || f.path?.toLowerCase().includes(s)
    const matchMime = !mimeFilter.value || (f.mime_type && f.mime_type.startsWith(mimeFilter.value))
    return matchSearch && matchMime
  })
})

async function fetchFiles() {
  loading.value = true
  try {
    const { data } = await api.get('/files')
    files.value = data.data || data
  } catch (err) {
    notif.error('Failed to load registry')
  } finally {
    loading.value = false
  }
}

function formatSize(bytes) {
  if (!bytes) return '0 B'
  const k = 1024
  const sizes = ['B', 'KB', 'MB', 'GB']
  const i = Math.floor(Math.log(bytes) / Math.log(k))
  return parseFloat((bytes / Math.pow(k, i)).toFixed(2)) + ' ' + sizes[i]
}

async function downloadFile(file) {
  try {
    const { data } = await api.get(`/files/${file.id}/download`, { responseType: 'blob' })
    const url = URL.createObjectURL(new Blob([data]))
    const a = document.createElement('a')
    a.href = url; a.download = file.original_name; a.click()
    URL.revokeObjectURL(url)
  } catch (err) {
    notif.error('Download failed')
  }
}

onMounted(fetchFiles)
</script>
