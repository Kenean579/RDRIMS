<template>
  <div class="flex flex-col gap-5 pb-6 animate-fade card">
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
      <div>
        <h1 class="text-xl font-bold text-slate-900 tracking-tight">File Registry</h1>
        <p class="text-slate-500 font-medium mt-1">Global management of institutional research documents and datasets.</p>
      </div>
    </div>

    <!-- Upload Zone -->
    <div
      class="border-2 border-dashed rounded-2xl p-8 text-center transition-all"
      :class="dragOver ? 'border-brand bg-brand/5' : 'border-slate-200 hover:border-brand/50'"
      @dragover.prevent="dragOver = true"
      @dragleave.prevent="dragOver = false"
      @drop.prevent="handleDrop"
    >
      <input type="file" ref="fileInput" class="hidden" @change="handleUpload" />
      <svg class="w-10 h-10 text-slate-300 mx-auto mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12"/></svg>
      <p class="text-sm font-bold text-slate-700 mb-1">Drop files here or <button type="button" @click="$refs.fileInput.click()" class="text-brand hover:underline">browse</button></p>
      <p class="text-xs text-slate-400">Upload documents, datasets, or media files</p>
    </div>

    <!-- Filters -->
    <div class="card p-6 flex flex-col md:flex-row gap-4 items-end">
      <div class="flex-1 w-full relative">
        <label class="block text-xs text-slate-500 font-medium mb-2 ml-1">Search Files</label>
        <div class="relative group">
          <span class="absolute left-3.5 top-1/2 -translate-y-1/2 text-slate-400">
            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><circle cx="11" cy="11" r="8"/><path d="m21 21-4.35-4.35"/></svg>
          </span>
          <input v-model="searchQuery" type="text" placeholder="Search by filename..." class="input pl-11 h-10" />
        </div>
      </div>
      <div class="flex gap-2">
        <button v-for="t in fileFilters" :key="t.value" @click="fileFilter = t.value" class="px-4 py-2 rounded-xl text-xs font-bold border transition-all" :class="fileFilter === t.value ? 'bg-brand text-white border-brand' : 'bg-white text-slate-600 border-slate-200 hover:border-brand/30'">{{ t.label }}</button>
      </div>
    </div>

    <div v-if="loading" class="card p-8 flex justify-center"><div class="animate-spin rounded-full h-10 w-10 border-b-2 border-brand"></div></div>
    
    <div v-else class="card overflow-hidden">
      <div class="overflow-x-auto">
        <table class="table-auto">
          <thead>
            <tr>
              <th class="pl-8 py-4 text-xs font-medium text-slate-400">Filename</th>
              <th class="py-4 text-xs font-medium text-slate-400">Size</th>
              <th class="py-4 text-xs font-medium text-slate-400">Mime Type</th>
              <th class="py-4 text-xs font-medium text-slate-400">Stored On</th>
              <th class="py-4 text-xs font-medium text-slate-400">Visibility</th>
              <th class="pr-8 py-4 text-xs font-medium text-slate-400 text-right">Actions</th>
            </tr>
          </thead>
          <tbody class="divide-y divide-slate-50">
            <tr v-for="file in filteredFiles" :key="file.id" class="group hover:bg-slate-50/50 transition-colors">
              <td class="pl-8 py-4">
                <div class="flex items-center gap-3">
                  <div class="w-9 h-9 rounded-2xl bg-slate-50 flex items-center justify-center text-slate-400 border border-slate-100 group-hover:bg-brand-light group-hover:text-brand transition-all">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z"/></svg>
                  </div>
                  <div class="min-w-0">
                    <p class="text-sm font-bold text-slate-800 truncate">{{ file.original_name }}</p>
                    <p class="text-xs font-medium text-slate-400">{{ file.uploader?.name || 'System' }}</p>
                  </div>
                </div>
              </td>
              <td class="py-4 text-xs font-bold text-slate-600">{{ formatSize(file.size) }}</td>
              <td class="py-4 font-bold">
                <span class="px-2 py-0.5 bg-slate-100 text-slate-500 rounded text-xs border border-slate-100">{{ file.mime_type }}</span>
              </td>
              <td class="py-4 text-xs font-medium text-slate-400">{{ formatDate(file.created_at) }}</td>
              <td class="py-4">
                <button @click="toggleVisibility(file)" class="px-3 py-1 rounded-lg text-xs font-bold border transition-all" :class="file.is_public ? 'bg-emerald-50 text-emerald-700 border-emerald-200' : 'bg-slate-100 text-slate-600 border-slate-200'">
                  {{ file.is_public ? 'Public' : 'Private' }}
                </button>
              </td>
              <td class="pr-8 py-4 text-right">
                <button @click="downloadFile(file)" class="btn btn-ghost text-brand text-xs font-medium py-1.5 px-4 h-auto border border-brand hover:bg-brand hover:text-white transition-all shadow-sm">
                  Download
                </button>
                <button @click="confirmDelete(file)" class="btn btn-ghost text-rose-500 text-xs font-medium py-1.5 px-3 h-auto hover:bg-rose-50 ml-1">Delete</button>
              </td>
            </tr>
          </tbody>
        </table>
      </div>
      <div v-if="filteredFiles.length === 0" class="p-5 text-center">
        <p class="text-sm font-medium text-slate-400 italic">No files found in the registry.</p>
      </div>
    </div>

    <ConfirmDialog
      :show="showDelete"
      title="Delete File"
      :message="`Are you sure you want to permanently delete '${deletingFile?.original_name}'?`"
      confirmText="Delete"
      variant="danger"
      @confirm="deleteFile"
      @cancel="showDelete = false"
    />
  </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue'
import { useNotificationStore } from '@/stores/notification'
import ConfirmDialog from '@/components/ConfirmDialog.vue'
import api from '@/services/api'
import { formatDate } from '@/utils/formatters'

const notif = useNotificationStore()
const files = ref([]); const loading = ref(true)
const searchQuery = ref(''); const fileFilter = ref('all')
const dragOver = ref(false)
const showDelete = ref(false); const deletingFile = ref(null)

const fileFilters = [
  { value: 'all', label: 'All' },
  { value: 'public', label: 'Public' },
  { value: 'private', label: 'Private' },
  { value: 'mine', label: 'My Files' },
]

const filteredFiles = computed(() => {
  const s = searchQuery.value.toLowerCase()
  return files.value.filter(f => {
    const matchSearch = !s || f.original_name?.toLowerCase().includes(s)
    const matchFilter = fileFilter.value === 'all' ||
      (fileFilter.value === 'public' && f.is_public) ||
      (fileFilter.value === 'private' && !f.is_public) ||
      (fileFilter.value === 'mine' && f.uploader?.id === JSON.parse(localStorage.getItem('rdrims_user') || '{}').id)
    return matchSearch && matchFilter
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

async function handleUpload(e) {
  const file = e.target.files[0]
  if (!file) return
  const fd = new FormData()
  fd.append('file', file)
  try {
    await api.post('/files', fd, { headers: { 'Content-Type': 'multipart/form-data' } })
    notif.success('File uploaded')
    fetchFiles()
  } catch (e) { notif.error('Upload failed') }
  e.target.value = ''
}

function handleDrop(e) {
  dragOver.value = false
  const file = e.dataTransfer.files[0]
  if (!file) return
  const fd = new FormData()
  fd.append('file', file)
  api.post('/files', fd, { headers: { 'Content-Type': 'multipart/form-data' } })
    .then(() => { notif.success('File uploaded'); fetchFiles() })
    .catch(() => notif.error('Upload failed'))
}

async function toggleVisibility(file) {
  try {
    await api.put(`/files/${file.id}`, { is_public: !file.is_public })
    file.is_public = !file.is_public
    notif.success('Visibility updated')
  } catch (e) { notif.error('Failed to update') }
}

function confirmDelete(file) {
  deletingFile.value = file
  showDelete.value = true
}

async function deleteFile() {
  try {
    await api.delete(`/files/${deletingFile.value.id}`)
    files.value = files.value.filter(f => f.id !== deletingFile.value.id)
    notif.success('File deleted')
    showDelete.value = false
  } catch (e) { notif.error('Delete failed') }
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
