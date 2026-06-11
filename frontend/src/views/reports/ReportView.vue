<template>
  <div class="flex flex-col gap-6 card">

    <!-- Header -->
    <div class="section-header">
      <div>
        <h1 class="section-title">Reports</h1>
        <p class="section-subtitle">Generate and download institutional research reports</p>
      </div>
    </div>

    <!-- Content Grid -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">

      <!-- Generate Report Card -->
      <div class="card p-8 flex flex-col gap-5">
        <div class="flex items-center gap-3">
          <div class="w-10 h-10 rounded-2xl bg-blue-50 flex items-center justify-center shrink-0">
            <svg class="w-5 h-5 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
            </svg>
          </div>
          <div>
            <h2 class="text-sm font-bold text-slate-800">Generate New Report</h2>
            <p class="text-xs text-slate-400 mt-0.5">Export institutional data as PDF</p>
          </div>
        </div>

        <form @submit.prevent="generateReport" class="flex flex-col gap-4">
          <div>
            <label class="block text-xs text-slate-500 font-medium  tracking-wider mb-1.5">Report Name *</label>
            <input v-model="reportForm.name" type="text" required placeholder="e.g. Q1 Research Summary" class="input" />
          </div>
          <div>
            <label class="block text-xs text-slate-500 font-medium  tracking-wider mb-1.5">Report Type *</label>
            <select v-model="reportForm.type" required class="input">
              <option value="">Select report type...</option>
              <option v-for="t in reportTypes" :key="t.id" :value="t.name">{{ formatStatusName(t.name) }}</option>
            </select>
          </div>
          <button type="submit" :disabled="generating" class="btn btn-primary w-full justify-center">
            <svg v-if="!generating" class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3M3 17V7a2 2 0 012-2h6l2 2h6a2 2 0 012 2v8a2 2 0 01-2 2H5a2 2 0 01-2-2z"/>
            </svg>
            <svg v-else class="w-4 h-4 animate-spin" fill="none" viewBox="0 0 24 24">
              <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/>
              <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"/>
            </svg>
            {{ generating ? 'Generating...' : 'Generate PDF Report' }}
          </button>
        </form>
      </div>

      <!-- Generated Reports Card -->
      <div class="card flex flex-col overflow-hidden animate-fade">
        <div class="px-5 py-4 border-b border-slate-100 flex items-center justify-between bg-slate-50/50">
          <div>
            <h2 class="text-sm font-bold text-slate-800">Generated Reports</h2>
            <p class="text-xs text-slate-400 mt-0.5">{{ reports.length }} report{{ reports.length !== 1 ? 's' : '' }} available</p>
          </div>
          <svg class="w-5 h-5 text-slate-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 8h14M5 8a2 2 0 110-4h14a2 2 0 110 4M5 8v10a2 2 0 002 2h10a2 2 0 002-2V8m-9 4h4"/>
          </svg>
        </div>

        <div v-if="reports.length === 0" class="flex flex-col items-center justify-center py-24 text-center px-6">
          <div class="w-16 h-16 rounded-2xl bg-white border border-slate-100 shadow-sm flex items-center justify-center mb-4">
            <svg class="w-8 h-8 text-slate-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
            </svg>
          </div>
          <p class="text-sm font-bold text-slate-600">No reports generated yet</p>
          <p class="text-xs text-slate-400 mt-1 max-w-[200px]">Historical records will appear here for management review</p>
        </div>

        <div v-else class="divide-y divide-slate-100">
          <div v-for="r in reports" :key="r.id"
            class="flex items-center gap-3 px-5 py-4 hover:bg-slate-50 transition-all group">
            <div class="w-10 h-10 rounded-2xl bg-blue-50 border border-blue-100 flex items-center justify-center shrink-0 group-hover:bg-blue-100 transition-colors">
              <svg class="w-4 h-4 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
              </svg>
            </div>
            <div class="flex-1 min-w-0">
              <p class="text-sm font-bold text-slate-800 truncate group-hover:text-blue-600 transition-colors">{{ r.name }}</p>
              <p class="text-xs text-slate-400 mt-0.5 font-medium">{{ formatDateTime(r.generated_at) }}</p>
            </div>
            <button @click="downloadReport(r)"
              class="btn btn-secondary border-slate-100 hover:border-blue-300 hover:text-blue-600 shrink-0 shadow-sm"
              style="padding: 6px 14px; font-size: 11px;">
              <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/>
              </svg>
              Download
            </button>
            <button @click="confirmDeleteReport(r)"
              class="btn btn-ghost text-rose-500 hover:bg-rose-50 shrink-0"
              style="padding: 6px 14px; font-size: 11px;">
              <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
              </svg>
              Delete
            </button>
          </div>
        </div>
      </div>

    </div>

    <ConfirmDialog
      :show="showDelete"
      title="Delete Report"
      :message="`Are you sure you want to delete '${deletingReport?.name}'? This cannot be undone.`"
      confirmText="Delete"
      variant="danger"
      @confirm="deleteReport"
      @cancel="showDelete = false"
    />
  </div>
</template>

<script setup>
import { ref, reactive, onMounted } from 'vue'
import { useNotificationStore } from '@/stores/notification'
import ConfirmDialog from '@/components/ConfirmDialog.vue'
import api from '@/services/api'
import { formatDateTime } from '@/utils/formatters'
import { formatStatusName } from '@/utils/colors'

const notif      = useNotificationStore()
const reports    = ref([])
const generating = ref(false)
const reportForm = reactive({ name: '', type: '' })
const reportTypes = ref([])
const showDelete = ref(false)
const deletingReport = ref(null)

async function fetchReports() {
  try {
    const { data } = await api.get('/reports')
    reports.value = data.data || data
  } catch (e) {}
}

function confirmDeleteReport(r) {
  deletingReport.value = r
  showDelete.value = true
}

async function deleteReport() {
  try {
    await api.delete(`/reports/${deletingReport.value.id}`)
    notif.success('Report deleted')
    showDelete.value = false
    fetchReports()
  } catch (e) {
    notif.error('Failed to delete report')
  }
}

async function generateReport() {
  generating.value = true
  try {
    await api.post('/reports/generate', { name: reportForm.name, type: reportForm.type, filters: '{}' })
    notif.success('Report generated successfully!')
    reportForm.name = ''
    reportForm.type = ''
    fetchReports()
  } catch (err) {
    notif.error(err.response?.data?.message || 'Failed to generate report')
  } finally {
    generating.value = false
  }
}

async function downloadReport(report) {
  try {
    const { data } = await api.get(`/reports/${report.id}/download`, { responseType: 'blob' })
    const url = URL.createObjectURL(new Blob([data]))
    const a = document.createElement('a')
    a.href = url; a.download = report.name + '.pdf'; a.click()
    URL.revokeObjectURL(url)
  } catch (err) {
    notif.error('Download failed — report may not be ready yet')
  }
}

onMounted(async () => {
  fetchReports()
  try { const { data } = await api.get('/lookups/report_types'); reportTypes.value = data } catch (e) {}
})
</script>
