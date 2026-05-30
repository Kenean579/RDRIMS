<template>
  <div class="flex flex-col gap-6 card">
    <!-- Header -->
    <div class="section-header">
      <div>
        <h1 class="section-title">Files Repository</h1>
        <p class="section-subtitle">Manage uploaded documents and institutional file assets</p>
      </div>
      <div class="flex gap-2">
        <input ref="fileInput" type="file" class="hidden" @change="handleUpload" />
        <button @click="$refs.fileInput.click()" class="btn btn-primary">
          <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12" /></svg>
          Upload File
        </button>
      </div>
    </div>

    <!-- Content -->
    <div v-if="loading" class="card p-8"><LoadingSkeleton :rows="4" /></div>
    <div v-else-if="files.length === 0" class="card">
      <EmptyState icon="📂" title="No files uploaded" description="Upload documents to the institutional repository for centralized access." action-label="Upload File" @action="$refs.fileInput.click()" />
    </div>

    <div v-else class="space-y-3">
      <div v-for="f in files" :key="f.id" class="card p-4 group card-hover flex items-center justify-between">
        <div class="flex items-center gap-4">
          <div class="w-10 h-10 bg-slate-50 rounded-xl flex items-center justify-center text-slate-400 group-hover:text-blue-600 group-hover:bg-blue-50 transition duration-300">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z" /></svg>
          </div>
          <div>
            <p class="text-sm font-bold text-slate-800 group-hover:text-blue-600 transition truncate max-w-xs">{{ f.file_path?.split('/').pop() }}</p>
            <div class="flex items-center gap-3 mt-1">
              <span class="text-[10px] font-bold text-slate-400 uppercase tracking-widest">v{{ f.version }}</span>
              <span :class="f.is_public ? 'badge badge-green' : 'badge badge-gray'" style="font-size: 9px">{{ f.is_public ? 'Public' : 'Private' }}</span>
            </div>
          </div>
        </div>
        <div class="flex gap-2">
          <button @click="downloadFile(f)" class="btn btn-ghost text-blue-600 font-bold" style="padding: 6px 10px; font-size: 11px">
            <svg class="w-3.5 h-3.5 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4" /></svg>
            Download
          </button>
          <button @click="confirmDelete(f)" class="btn btn-ghost text-red-500 hover:bg-red-50" style="padding: 6px 10px; font-size: 11px">Delete</button>
        </div>
      </div>
      <div class="card p-4">
        <Pagination :current-page="pagination.current_page" :total-pages="pagination.last_page" :total="pagination.total" @page-change="fetchFiles" />
      </div>
    </div>

    <ConfirmDialog :show="showDelete" title="Delete File" message="Are you sure you want to permanently delete this file?" confirmText="Delete" variant="danger" @confirm="deleteFile" @cancel="showDelete = false" />
  </div>
</template>

<script setup>
import { ref, reactive, onMounted } from 'vue'
import { useNotificationStore } from '@/stores/notification'
import api from '@/services/api'
import Pagination from '@/components/Pagination.vue'
import ConfirmDialog from '@/components/ConfirmDialog.vue'
import LoadingSkeleton from '@/components/LoadingSkeleton.vue'
import EmptyState from '@/components/EmptyState.vue'

const notif = useNotificationStore()
const files = ref([]); const loading = ref(true)
const pagination = reactive({ current_page: 1, last_page: 1, total: 0 })
const showDelete = ref(false); const deletingFile = ref(null)

async function fetchFiles(page = 1) {
  loading.value = true
  try { const { data } = await api.get('/files', { params: { page } }); files.value = data.data; Object.assign(pagination, { current_page: data.current_page, last_page: data.last_page, total: data.total }) }
  catch (e) {} finally { loading.value = false }
}

async function handleUpload(e) {
  const file = e.target.files[0]
  if (!file) return
  const formData = new FormData()
  formData.append('file', file)
  try { await api.post('/files/upload', formData, { headers: { 'Content-Type': 'multipart/form-data' } }); notif.success('Uploaded!'); fetchFiles() }
  catch (err) { notif.error('Upload failed') }
}

async function downloadFile(f) {
  try { const res = await api.get(`/files/${f.id}/download`, { responseType: 'blob' }); const url = URL.createObjectURL(new Blob([res.data])); const a = document.createElement('a'); a.href = url; a.download = f.file_path.split('/').pop(); a.click(); URL.revokeObjectURL(url) }
  catch (err) { notif.error('Download failed') }
}

function confirmDelete(f) { deletingFile.value = f; showDelete.value = true }

async function deleteFile() {
  try { await api.delete(`/files/${deletingFile.value.id}`); notif.success('Deleted!'); showDelete.value = false; fetchFiles() }
  catch (err) { notif.error('Failed') }
}

onMounted(() => fetchFiles())
</script>
