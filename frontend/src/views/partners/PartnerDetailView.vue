<template>
  <div class="card p-8">
    <div class="mb-6">
      <router-link to="/app/partners" class="text-sm text-blue-600 hover:underline mb-2 inline-block">← Back to Partners</router-link>
      <h1 class="text-xl font-bold text-gray-800">{{ partner.name || 'Partner Detail' }}</h1>
    </div>

    <div v-if="loading" class="card p-8"><LoadingSkeleton :rows="6" /></div>

    <template v-else>
      <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <div>
          <div class="bg-white rounded-2xl shadow-sm border border-slate-100 p-6">
            <div class="w-16 h-16 bg-blue-100 rounded-2xl flex items-center justify-center text-2xl mx-auto mb-4">🤝</div>
            <h2 class="text-lg font-semibold text-gray-800 text-center">{{ partner.name }}</h2>
            <div class="mt-4 space-y-2 text-sm">
              <p><span class="text-gray-500">Sector:</span> <span class="text-gray-800">{{ partner.sector }}</span></p>
              <p><span class="text-gray-500">Email:</span> <span class="text-blue-600">{{ partner.contact_email }}</span></p>
              <p v-if="partner.website"><span class="text-gray-500">Website:</span> <a :href="partner.website" target="_blank" class="text-blue-600 hover:underline">{{ partner.website }}</a></p>
            </div>
          </div>
        </div>

        <div class="lg:col-span-2 space-y-6">
          <!-- MoUs -->
          <div class="bg-white rounded-2xl shadow-sm border border-slate-100 p-6">
            <div class="flex items-center justify-between mb-4">
              <h2 class="text-base font-semibold text-gray-800">MoUs ({{ partner.mo_us?.length || 0 }})</h2>
              <button @click="showMoUForm = true" class="text-sm text-blue-600 hover:text-blue-800 font-medium">+ Add MoU</button>
            </div>
            <div v-if="partner.mo_us?.length" class="space-y-2">
              <div v-for="mou in partner.mo_us" :key="mou.id" class="flex items-center justify-between p-3 border border-slate-100 rounded-2xl group hover:border-slate-200 transition-colors">
                <div>
                  <p class="text-sm text-gray-800">{{ formatDate(mou.start_date) }} – {{ formatDate(mou.end_date) }}</p>
                </div>
                <ActionMenu :actions="[
                  { key: 'delete', label: 'Delete MoU', handler: () => deleteMoU(mou) }
                ]" size="sm" />
              </div>
            </div>
            <p v-else class="text-sm text-gray-400">No MoUs yet.</p>
          </div>

          <!-- Linked Outputs -->
          <div class="bg-white rounded-2xl shadow-sm border border-slate-100 p-6">
            <h2 class="text-base font-semibold text-gray-800 mb-4">Linked Outputs</h2>
            <div v-if="partner.outputs?.length" class="space-y-2">
              <div v-for="o in partner.outputs" :key="o.id" class="p-3 border border-slate-100 rounded-2xl">
                <p class="text-sm font-medium text-gray-800">{{ o.title }}</p>
                <StatusBadge :status="o.status?.name || 'draft'" />
              </div>
            </div>
            <p v-else class="text-sm text-gray-400">No outputs linked.</p>
          </div>
        </div>
      </div>
    </template>

    <Modal :show="showMoUForm" title="Add MoU" @close="showMoUForm = false">
      <form @submit.prevent="saveMoU" class="space-y-4">
        <div class="grid grid-cols-2 gap-3">
          <div><label class="block text-sm font-medium text-gray-700 mb-1">Start Date *</label><input v-model="mouForm.start_date" type="date" required class="w-full border border-gray-300 rounded-2xl px-4 py-2.5 text-sm focus:ring-2 focus:ring-blue-500 outline-none" /></div>
          <div><label class="block text-sm font-medium text-gray-700 mb-1">End Date *</label><input v-model="mouForm.end_date" type="date" required class="w-full border border-gray-300 rounded-2xl px-4 py-2.5 text-sm focus:ring-2 focus:ring-blue-500 outline-none" /></div>
        </div>
        <div class="flex justify-end gap-3"><button type="button" @click="showMoUForm = false" class="px-4 py-2 text-sm border border-gray-300 rounded-2xl">Cancel</button><button type="submit" class="px-4 py-2 text-sm bg-blue-600 text-white rounded-2xl">Save</button></div>
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
const partner = ref({}); const loading = ref(true)
const showMoUForm = ref(false)
const mouForm = reactive({ start_date: '', end_date: '' })

async function fetchPartner() {
  loading.value = true
  try { const { data } = await api.get(`/partners/${route.params.id}`); partner.value = data }
  catch (e) {} finally { loading.value = false }
}

async function saveMoU() {
  try { await api.post(`/partners/${partner.value.id}/mo-us`, mouForm); notif.success('MoU added!'); showMoUForm.value = false; fetchPartner() }
  catch (err) { notif.error('Failed') }
}

async function deleteMoU(mou) {
  try { await api.delete(`/mo-us/${mou.id}`); notif.success('Deleted!'); fetchPartner() }
  catch (err) { notif.error('Failed') }
}

onMounted(fetchPartner)
</script>
