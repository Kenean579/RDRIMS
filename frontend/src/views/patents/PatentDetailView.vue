<template>
  <div class="card p-8">
    <div class="mb-6">
      <router-link to="/app/patents" class="text-sm text-blue-600 hover:underline mb-2 inline-block">← Back to Patents</router-link>
      <h1 class="text-xl font-bold text-gray-800">{{ patent.title || 'Patent Detail' }}</h1>
    </div>

    <div v-if="loading" class="card p-8"><LoadingSkeleton :rows="6" /></div>

    <template v-else>
      <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <div class="lg:col-span-2 space-y-6">
          <div class="bg-white rounded-2xl shadow-sm border border-slate-100 p-6">
            <div class="flex items-center gap-2 mb-4">
              <h2 class="text-base font-semibold text-gray-800">Patent Details</h2>
              <StatusBadge :status="patent.status?.name || 'pending'" />
            </div>
            <dl class="grid grid-cols-1 sm:grid-cols-2 gap-4 text-sm">
              <div class="sm:col-span-2"><dt class="text-gray-500">Title</dt><dd class="font-medium text-gray-800 mt-0.5">{{ patent.title }}</dd></div>
              <div><dt class="text-gray-500">Inventors</dt><dd class="text-gray-800 mt-0.5">{{ patent.inventors }}</dd></div>
              <div><dt class="text-gray-500">Filing Date</dt><dd class="text-gray-800 mt-0.5">{{ formatDate(patent.filing_date) }}</dd></div>
              <div><dt class="text-gray-500">Patent Number</dt><dd class="text-gray-800 mt-0.5">{{ patent.patent_number || 'N/A' }}</dd></div>
              <div v-if="patent.project"><dt class="text-gray-500">Project</dt><dd class="text-gray-800 mt-0.5">{{ patent.project?.title }}</dd></div>
            </dl>
          </div>
        </div>

        <div>
          <div class="bg-white rounded-2xl shadow-sm border border-slate-100 p-5">
            <div class="flex items-center justify-between mb-3">
              <h2 class="text-base font-semibold text-gray-800">Licenses ({{ patent.licenses?.length || 0 }})</h2>
              <button @click="showAddLicense = true" class="text-sm text-blue-600 hover:text-blue-800 font-medium">+ Add</button>
            </div>
            <div v-if="patent.licenses?.length" class="space-y-2">
              <div v-for="lic in patent.licenses" :key="lic.id" class="p-3 border border-slate-100 rounded-2xl flex items-start justify-between group hover:border-slate-200 transition-colors">
                <div class="flex-1">
                  <p class="text-sm font-medium text-gray-800">{{ lic.company_name }}</p>
                  <p class="text-xs text-gray-500">{{ formatDate(lic.start_date) }} – {{ formatDate(lic.end_date) }}</p>
                  <p class="text-xs text-gray-500">Royalty: {{ lic.royalty_rate }}%</p>
                </div>
                <ActionMenu :actions="[
                  { key: 'delete', label: 'Delete License', handler: () => deleteLicense(lic) }
                ]" size="sm" />
              </div>
            </div>
            <p v-else class="text-sm text-gray-400">No licenses.</p>
          </div>
        </div>
      </div>
    </template>

    <Modal :show="showAddLicense" title="Add License" @close="showAddLicense = false">
      <form @submit.prevent="addLicense" class="space-y-4">
        <div><label class="block text-sm font-medium text-gray-700 mb-1">Company *</label><input v-model="licenseForm.company_name" type="text" required class="w-full border border-gray-300 rounded-2xl px-4 py-2.5 text-sm focus:ring-2 focus:ring-blue-500 outline-none" /></div>
        <div class="grid grid-cols-2 gap-3"><div><label class="block text-sm font-medium text-gray-700 mb-1">Start *</label><input v-model="licenseForm.start_date" type="date" required class="w-full border border-gray-300 rounded-2xl px-4 py-2.5 text-sm focus:ring-2 focus:ring-blue-500 outline-none" /></div><div><label class="block text-sm font-medium text-gray-700 mb-1">End *</label><input v-model="licenseForm.end_date" type="date" required class="w-full border border-gray-300 rounded-2xl px-4 py-2.5 text-sm focus:ring-2 focus:ring-blue-500 outline-none" /></div></div>
        <div><label class="block text-sm font-medium text-gray-700 mb-1">Royalty Rate (%) *</label><input v-model.number="licenseForm.royalty_rate" type="number" required min="0" max="100" step="0.01" class="w-full border border-gray-300 rounded-2xl px-4 py-2.5 text-sm focus:ring-2 focus:ring-blue-500 outline-none" /></div>
        <div class="flex justify-end gap-3"><button type="button" @click="showAddLicense = false" class="px-4 py-2 text-sm border border-gray-300 rounded-2xl">Cancel</button><button type="submit" class="px-4 py-2 text-sm bg-blue-600 text-white rounded-2xl">Add</button></div>
      </form>
    </Modal>
  </div>
</template>

<script setup>
import { ref, reactive, onMounted } from 'vue'
import { useRoute } from 'vue-router'
import { useNotificationStore } from '@/stores/notification'
import api from '@/services/api'
import StatusBadge from '@/components/StatusBadge.vue'
import LoadingSkeleton from '@/components/LoadingSkeleton.vue'
import Modal from '@/components/Modal.vue'
import ActionMenu from '@/components/ActionMenu.vue'
import { formatDate } from '@/utils/formatters'

const route = useRoute(); const notif = useNotificationStore()
const patent = ref({}); const loading = ref(true)
const showAddLicense = ref(false)
const licenseForm = reactive({ company_name: '', start_date: '', end_date: '', royalty_rate: null })

async function fetchPatent() {
  loading.value = true
  try { const { data } = await api.get(`/patents/${route.params.id}`); patent.value = data }
  catch (e) {} finally { loading.value = false }
}

async function addLicense() {
  try { await api.post(`/patents/${patent.value.id}/licenses`, licenseForm); notif.success('License added!'); showAddLicense.value = false; fetchPatent() }
  catch (err) { notif.error('Failed') }
}

async function deleteLicense(lic) {
  try { await api.delete(`/licenses/${lic.id}`); notif.success('Deleted!'); fetchPatent() }
  catch (err) { notif.error('Failed') }
}

onMounted(fetchPatent)
</script>
