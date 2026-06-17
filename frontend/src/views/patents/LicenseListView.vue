<template>
  <div class="card p-8">
    <div class="mb-6">
      <router-link to="/app/patents" class="text-sm text-blue-600 hover:underline mb-2 inline-block">← Back to Patents</router-link>
      <h1 class="text-xl font-bold text-gray-800">Licenses</h1>
      <p class="text-sm text-gray-500 mt-1">Patent: {{ patent?.title }}</p>
    </div>

    <div class="flex justify-between items-center mb-4">
      <h2 class="text-base font-semibold text-gray-800">License Agreements</h2>
      <button @click="showCreate = true" class="bg-blue-600 text-white px-4 py-2 rounded-2xl hover:bg-blue-700 text-sm font-medium">+ Add License</button>
    </div>

    <div v-if="loading" class="card p-8"><div class="space-y-4 animate-pulse"><div v-for="i in 3" :key="i" class="h-6 bg-gray-200 rounded"></div></div></div>
    <div v-else-if="licenses.length === 0" class="card p-8 text-center"><p class="text-2xl mb-3">📜</p><h3 class="text-base font-medium text-gray-800">No licenses</h3></div>

    <div v-else class="space-y-3">
      <div v-for="l in licenses" :key="l.id" class="bg-white rounded-2xl shadow-sm border border-slate-100 p-5">
        <div class="flex items-center justify-between">
          <div>
            <p class="text-sm font-medium text-gray-800">{{ l.company_name }}</p>
            <p class="text-xs text-gray-500">{{ formatDate(l.start_date) }} – {{ formatDate(l.end_date) }}</p>
            <p class="text-xs text-gray-500">Royalty: {{ l.royalty_rate }}%</p>
          </div>
          <ActionMenu :actions="[
            { key: 'edit', label: 'Edit', handler: () => editLicense(l) },
            { separator: true },
            { key: 'delete', label: 'Delete', handler: () => confirmDelete(l) }
          ]" />
        </div>
      </div>
    </div>

    <Modal :show="showCreate || !!editingLicense" :title="editingLicense ? 'Edit License' : 'Add License'" @close="closeModal">
      <form @submit.prevent="saveLicense" class="space-y-4">
        <div><label class="block text-sm font-medium text-gray-700 mb-1">Company *</label><input v-model="form.company_name" type="text" required class="w-full border border-gray-300 rounded-2xl px-4 py-2.5 text-sm focus:ring-2 focus:ring-blue-500 outline-none" /></div>
        <div class="grid grid-cols-2 gap-3">
          <div><label class="block text-sm font-medium text-gray-700 mb-1">Start *</label><input v-model="form.start_date" type="date" required class="w-full border border-gray-300 rounded-2xl px-4 py-2.5 text-sm focus:ring-2 focus:ring-blue-500 outline-none" /></div>
          <div><label class="block text-sm font-medium text-gray-700 mb-1">End *</label><input v-model="form.end_date" type="date" required class="w-full border border-gray-300 rounded-2xl px-4 py-2.5 text-sm focus:ring-2 focus:ring-blue-500 outline-none" /></div>
        </div>
        <div><label class="block text-sm font-medium text-gray-700 mb-1">Royalty Rate (%) *</label><input v-model.number="form.royalty_rate" type="number" required min="0" max="100" step="0.01" class="w-full border border-gray-300 rounded-2xl px-4 py-2.5 text-sm focus:ring-2 focus:ring-blue-500 outline-none" /></div>
        <div class="flex justify-end gap-3"><button type="button" @click="closeModal" class="px-4 py-2 text-sm border border-gray-300 rounded-2xl">Cancel</button><button type="submit" class="px-4 py-2 text-sm bg-blue-600 text-white rounded-2xl">{{ editingLicense ? 'Update' : 'Add' }}</button></div>
      </form>
    </Modal>

    <ConfirmDialog :show="showDelete" title="Delete License" message="Delete this license?" confirmText="Delete" variant="danger" @confirm="deleteLicense" @cancel="showDelete = false" />
  </div>
</template>

<script setup>
import { ref, reactive, onMounted } from 'vue'
import { useRoute } from 'vue-router'
import { useNotificationStore } from '@/stores/notification'
import api from '@/services/api'
import Modal from '@/components/Modal.vue'
import ConfirmDialog from '@/components/ConfirmDialog.vue'
import ActionMenu from '@/components/ActionMenu.vue'
import { formatDate } from '@/utils/formatters'

const route = useRoute(); const notif = useNotificationStore()
const patent = ref({}); const licenses = ref([]); const loading = ref(true)
const showCreate = ref(false); const editingLicense = ref(null); const showDelete = ref(false); const deletingLicense = ref(null)
const form = reactive({ company_name: '', start_date: '', end_date: '', royalty_rate: null })

async function fetchData() {
  loading.value = true
  try {
    const pRes = await api.get(`/patents/${route.params.id}`)
    const lRes = await api.get(`/patents/${route.params.id}/licenses`)
    patent.value = pRes.data; licenses.value = lRes.data.data || lRes.data
  } catch (e) {} finally { loading.value = false }
}

function editLicense(l) { editingLicense.value = l; Object.assign(form, { company_name: l.company_name, start_date: l.start_date, end_date: l.end_date, royalty_rate: l.royalty_rate }) }
function closeModal() { showCreate.value = false; editingLicense.value = null; Object.assign(form, { company_name: '', start_date: '', end_date: '', royalty_rate: null }) }
function confirmDelete(l) { deletingLicense.value = l; showDelete.value = true }

async function saveLicense() {
  try {
    if (editingLicense.value) { await api.put(`/licenses/${editingLicense.value.id}`, form); notif.success('Updated!') }
    else { await api.post(`/patents/${route.params.id}/licenses`, form); notif.success('Added!') }
    closeModal(); fetchData()
  } catch (err) { notif.error(err.response?.data?.message || 'Failed') }
}

async function deleteLicense() {
  try { await api.delete(`/licenses/${deletingLicense.value.id}`); notif.success('Deleted!'); showDelete.value = false; fetchData() }
  catch (err) { notif.error(err.response?.data?.message || 'Failed') }
}

onMounted(fetchData)
</script>
