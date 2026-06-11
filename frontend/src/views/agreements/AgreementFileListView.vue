<template>
  <div class="flex flex-col gap-5 pb-6 animate-fade card">
    <!-- Header -->
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
      <div>
        <h1 class="text-xl font-bold text-slate-900 tracking-tight">Agreement Files</h1>
        <p class="text-slate-500 font-medium mt-1">Manage files for agreements and licenses.</p>
      </div>
      <button @click="fetchFiles" class="btn btn-secondary h-11 px-6 shadow-sm group">
        <svg class="w-4 h-4 mr-1.5 group-hover:rotate-180 transition-transform duration-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" /></svg>
        Refresh
      </button>
    </div>

    <!-- Content -->
    <div v-if="loading" class="card p-8 flex flex-col justify-center items-center gap-4">
      <div class="animate-spin rounded-full h-10 w-10 border-b-2 border-brand"></div>
      <p class="text-xs font-medium text-slate-400">Loading files...</p>
    </div>

    <div v-else-if="files.length === 0" class="card">
      <EmptyState icon="📎" title="No files found" description="Upload agreement files from the agreement details page." />
    </div>

    <div v-else class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
      <div v-for="file in files" :key="file.id" class="card p-8 flex flex-col group card-hover relative border-l-4 border-l-transparent transition-all">
        <div class="flex items-start gap-4 mb-5">
          <div class="w-12 h-12 rounded-2xl bg-brand-light text-brand flex items-center justify-center shadow-sm group-hover:bg-brand group-hover:text-white transition-all duration-300 shrink-0">
             <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M15.172 7l-6.586 6.586a2 2 0 102.828 2.828l6.414-6.586a4 4 0 00-5.656-5.656l-6.415 6.585a6 6 0 108.486 8.486L20.5 13" /></svg>
          </div>
          <div class="min-w-0">
            <p class="text-sm font-bold text-slate-900 group-hover:text-brand transition-colors truncate mb-1" :title="file.file?.file_path?.split('/').pop()">
              {{ file.file?.file_path?.split('/').pop() || 'Agreement File' }}
            </p>
            <div class="flex flex-wrap items-center gap-2">
              <span class="px-2 py-0.5 bg-slate-100 text-slate-600 rounded text-xs font-medium">{{ file.parent_type?.name || 'File' }}</span>
              <span class="text-xs font-medium text-slate-400">ID: {{ String(file.parent_id).padStart(4, '0') }}</span>
            </div>
          </div>
        </div>
        
        <div class="flex items-center justify-between gap-3 pt-5 border-t border-slate-50 mt-auto">
          <button @click="downloadFile(file)" class="btn btn-ghost hover:bg-brand-light hover:text-brand flex-1 justify-center text-xs font-medium py-2">
             <svg class="w-3.5 h-3.5 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4" /></svg>
             Download
          </button>
          <div class="w-px h-4 bg-slate-100"></div>
          <button @click="confirmDelete(file)" class="btn btn-ghost text-red-400 hover:bg-red-50 hover:text-red-600 flex-1 justify-center text-xs font-medium py-2">Delete</button>
        </div>
      </div>
    </div>

    <ConfirmDialog :show="showDelete" title="Delete File" message="Are you sure you want to delete this file? This cannot be undone." confirmText="Delete Now" variant="danger" @confirm="deleteFile" @cancel="showDelete = false" />
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import { useNotificationStore } from '@/stores/notification'
import api from '@/services/api'
import LoadingSkeleton from '@/components/LoadingSkeleton.vue'
import ConfirmDialog from '@/components/ConfirmDialog.vue'
import EmptyState from '@/components/EmptyState.vue'

const notif = useNotificationStore()
const files = ref([]); const loading = ref(true)
const showDelete = ref(false); const deletingFile = ref(null)

async function fetchFiles() {
  loading.value = true
  try { const { data } = await api.get('/agreement-files'); files.value = data.data || data }
  catch (e) {} finally { loading.value = false }
}

function confirmDelete(file) { deletingFile.value = file; showDelete.value = true }

async function deleteFile() {
  try {
    await api.delete(`/agreement-files/${deletingFile.value.id}`)
    notif.success('File detached successfully!')
    showDelete.value = false; fetchFiles()
  } catch (err) { notif.error('Failed to delete') }
}

async function downloadFile(file) {
  try {
    const response = await api.get(`/files/${file.file_id}/download`, { responseType: 'blob' })
    const url = URL.createObjectURL(new Blob([response.data]))
    const a = document.createElement('a')
    a.href = url; a.download = file.file?.file_path?.split('/').pop() || 'download'; a.click()
    URL.revokeObjectURL(url)
  } catch (err) { notif.error('Download failed') }
}

onMounted(fetchFiles)
</script>
