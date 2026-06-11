<template>
  <div class="flex flex-col gap-5 pb-6 animate-fade card">
    <!-- Header -->
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
      <div>
        <router-link to="/app/files" class="flex items-center gap-2 text-brand font-semibold text-xs mb-2 hover:translate-x-1 transition-transform">
          <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M10 19l-7-7m0 0l7-7m-7 7h18" /></svg>
          Back to File Registry
        </router-link>
        <h1 class="text-xl font-bold text-slate-900 tracking-tight">File Details</h1>
        <p class="text-slate-500 font-medium mt-1">View and manage file versions.</p>
      </div>
    </div>

    <div v-if="loading" class="card p-8 flex flex-col justify-center items-center gap-4">
      <div class="animate-spin rounded-full h-10 w-10 border-b-2 border-brand"></div>
      <p class="text-xs font-medium text-slate-400">Loading file details...</p>
    </div>

    <template v-else>
      <!-- File Info -->
      <div class="card p-8">
        <div class="flex items-start gap-6">
          <div class="w-16 h-16 rounded-2xl bg-slate-50 flex items-center justify-center text-slate-400 border border-slate-100 flex-shrink-0">
            <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z"/></svg>
          </div>
          <div class="flex-1 min-w-0">
            <h2 class="text-lg font-bold text-slate-900 mb-2 truncate">{{ file.original_name }}</h2>
            <div class="grid grid-cols-2 md:grid-cols-4 gap-4 text-sm">
              <div>
                <p class="text-xs font-medium text-slate-400 mb-1">File Size</p>
                <p class="text-sm font-bold text-slate-700">{{ formatSize(file.size) }}</p>
              </div>
              <div>
                <p class="text-xs font-medium text-slate-400 mb-1">MIME Type</p>
                <p class="text-sm font-bold text-slate-700 font-mono">{{ file.mime_type }}</p>
              </div>
              <div>
                <p class="text-xs font-medium text-slate-400 mb-1">Uploaded By</p>
                <p class="text-sm font-bold text-slate-700">{{ file.uploader?.name || 'System' }}</p>
              </div>
              <div>
                <p class="text-xs font-medium text-slate-400 mb-1">Uploaded On</p>
                <p class="text-sm font-bold text-slate-700">{{ formatDate(file.created_at) }}</p>
              </div>
            </div>
          </div>
          <div class="flex flex-col gap-2">
            <button @click="downloadFile" class="btn btn-primary h-11 px-5 text-xs font-bold">
              <svg class="w-4 h-4 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/></svg>
              Download
            </button>
            <button @click="showUploadVersion = true" class="btn btn-secondary h-11 px-5 text-xs font-bold">
              <svg class="w-4 h-4 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"/></svg>
              Upload New Version
            </button>
          </div>
        </div>
      </div>

      <!-- File Versions -->
      <div class="card p-8">
        <h3 class="text-sm font-bold text-slate-900 mb-4 flex items-center gap-2">
          <svg class="w-5 h-5 text-brand" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"/></svg>
          File Versions
        </h3>

        <div v-if="versionsLoading" class="text-center py-8">
          <div class="animate-spin rounded-full h-8 w-8 border-b-2 border-brand mx-auto"></div>
        </div>

        <div v-else-if="versions.length === 0" class="text-center py-8">
          <p class="text-sm font-medium text-slate-400 italic">No previous versions available.</p>
        </div>

        <div v-else class="space-y-3">
          <div v-for="version in versions" :key="version.id" 
            class="flex items-center justify-between p-4 rounded-xl border transition-all"
            :class="version.id === file.id ? 'bg-brand/10 border-brand' : 'bg-white border-slate-100 hover:border-brand/30'">
            <div class="flex items-center gap-4">
              <div class="w-10 h-10 rounded-full flex items-center justify-center text-xs font-bold"
                :class="version.id === file.id ? 'bg-brand text-white' : 'bg-slate-100 text-slate-600'">
                V{{ version.version }}
              </div>
              <div>
                <p class="text-sm font-bold text-slate-800">{{ version.original_name }}</p>
                <p class="text-xs font-medium text-slate-400">
                  {{ formatSize(version.size) }} • Uploaded {{ formatDate(version.created_at) }}
                  <span v-if="version.id === file.id" class="text-brand font-bold ml-2">(Current)</span>
                </p>
              </div>
            </div>
            <button @click="downloadVersion(version)" class="btn btn-ghost text-brand text-xs font-medium py-2 px-4 h-auto border border-brand hover:bg-brand hover:text-white transition-all">
              Download
            </button>
          </div>
        </div>
      </div>
    </template>

    <!-- Upload Version Modal -->
    <Modal :show="showUploadVersion" title="Upload New Version" @close="showUploadVersion = false">
      <form @submit.prevent="uploadNewVersion" class="space-y-5 px-1 py-1">
        <div>
          <label class="block text-xs text-slate-500 font-medium mb-2 ml-1">Select File *</label>
          <input type="file" ref="versionFileInput" required class="input h-12" />
        </div>
        <div class="flex justify-end gap-3 pt-6 mt-6 border-t border-slate-100">
          <button type="button" @click="showUploadVersion = false" class="btn btn-secondary px-6 font-bold tracking-widest text-xs">Cancel</button>
          <button type="submit" class="btn btn-primary px-5 font-bold tracking-widest text-xs" :disabled="uploading">
            {{ uploading ? 'Uploading...' : 'Upload Version' }}
          </button>
        </div>
      </form>
    </Modal>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import { useRoute } from 'vue-router'
import { useNotificationStore } from '@/stores/notification'
import Modal from '@/components/Modal.vue'
import api from '@/services/api'
import { formatDate } from '@/utils/formatters'

const route = useRoute()
const notif = useNotificationStore()
const loading = ref(true)
const versionsLoading = ref(true)
const uploading = ref(false)
const file = ref({})
const versions = ref([])
const showUploadVersion = ref(false)

async function fetchFile() {
  loading.value = true
  try {
    const { data } = await api.get(`/files/${route.params.id}`)
    file.value = data
  } catch (err) {
    notif.error('Failed to load file details')
  } finally {
    loading.value = false
  }
}

async function fetchVersions() {
  versionsLoading.value = true
  try {
    const { data } = await api.get(`/files/${route.params.id}/versions`)
    versions.value = data || []
  } catch (err) {
    console.error('Failed to load versions:', err)
  } finally {
    versionsLoading.value = false
  }
}

async function downloadFile() {
  try {
    const response = await api.get(`/files/${route.params.id}/download`, { responseType: 'blob' })
    const url = window.URL.createObjectURL(new Blob([response.data]))
    const link = document.createElement('a')
    link.href = url
    link.setAttribute('download', file.value.original_name)
    document.body.appendChild(link)
    link.click()
    link.remove()
    window.URL.revokeObjectURL(url)
  } catch (err) {
    notif.error('Download failed')
  }
}

async function downloadVersion(version) {
  try {
    const response = await api.get(`/files/${version.id}/download`, { responseType: 'blob' })
    const url = window.URL.createObjectURL(new Blob([response.data]))
    const link = document.createElement('a')
    link.href = url
    link.setAttribute('download', version.original_name)
    document.body.appendChild(link)
    link.click()
    link.remove()
    window.URL.revokeObjectURL(url)
  } catch (err) {
    notif.error('Download failed')
  }
}

async function uploadNewVersion() {
  const fileInput = document.querySelector('input[type="file"]')
  const uploadedFile = fileInput.files[0]
  
  if (!uploadedFile) {
    notif.error('Please select a file')
    return
  }

  uploading.value = true
  const formData = new FormData()
  formData.append('file', uploadedFile)

  try {
    await api.post(`/files/${route.params.id}/versions`, formData, {
      headers: { 'Content-Type': 'multipart/form-data' }
    })
    notif.success('New version uploaded successfully')
    showUploadVersion.value = false
    await fetchFile()
    await fetchVersions()
  } catch (err) {
    notif.error('Failed to upload version')
  } finally {
    uploading.value = false
  }
}

function formatSize(bytes) {
  if (!bytes) return '0 B'
  const k = 1024
  const sizes = ['B', 'KB', 'MB', 'GB']
  const i = Math.floor(Math.log(bytes) / Math.log(k))
  return parseFloat((bytes / Math.pow(k, i)).toFixed(2)) + ' ' + sizes[i]
}

onMounted(async () => {
  await fetchFile()
  await fetchVersions()
})
</script>
