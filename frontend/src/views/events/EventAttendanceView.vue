<template>
  <div card>
    <div class="mb-6">
      <router-link :to="`/events/${event.id}`" class="text-sm text-blue-600 hover:underline mb-2 inline-block">← Back to Event</router-link>
      <h1 class="text-xl font-bold text-gray-800">Attendance: {{ event.title }}</h1>
      <p class="text-sm text-gray-500 mt-1">Mark attendance and generate certificates</p>
    </div>

    <div v-if="loading" class="bg-white rounded-lg shadow-sm p-6"><LoadingSkeleton :rows="6" /></div>

    <template v-else>
      <div class="bg-white rounded-lg shadow-sm border border-gray-100 overflow-hidden mb-6">
        <div class="p-5 border-b border-gray-200 flex justify-between items-center">
          <h2 class="text-base font-semibold text-gray-800">Registrations ({{ registrations.length }})</h2>
          <button @click="generateAllCertificates" class="bg-green-600 text-white px-4 py-2 rounded-lg hover:bg-green-700 text-sm font-medium">
            Generate All Certificates
          </button>
        </div>
        <div class="overflow-x-auto">
          <table class="min-w-full">
            <thead class="bg-gray-50"><tr>
              <th class="text-left px-5 py-3 text-xs font-medium text-gray-500 capitalize">Participant</th>
              <th class="text-left px-5 py-3 text-xs font-medium text-gray-500 capitalize">Email</th>
              <th class="text-left px-5 py-3 text-xs font-medium text-gray-500 capitalize">Status</th>
              <th class="text-left px-5 py-3 text-xs font-medium text-gray-500 capitalize">Actions</th>
            </tr></thead>
            <tbody class="divide-y divide-gray-100">
              <tr v-for="reg in registrations" :key="reg.id" class="hover:bg-gray-50">
                <td class="px-5 py-3">
                  <div class="flex items-center gap-3">
                    <!-- Profile image or initials avatar -->
                    <div class="w-8 h-8 rounded-full overflow-hidden shrink-0 bg-brand-light border border-brand/20">
                      <img
                        v-if="imageUrl(reg.user?.profile_image)"
                        :src="imageUrl(reg.user?.profile_image)"
                        :alt="reg.user?.name"
                        class="w-full h-full object-cover"
                      />
                      <div v-else class="w-full h-full flex items-center justify-center text-brand text-xs font-bold">
                        {{ reg.user?.name?.charAt(0) || '?' }}
                      </div>
                    </div>
                    <span class="text-sm font-medium text-gray-800">{{ reg.user?.name }}</span>
                  </div>
                </td>
                <td class="px-5 py-3 text-sm text-gray-600">{{ reg.user?.email }}</td>
                <td class="px-5 py-3">
                  <span :class="reg.attended ? 'bg-green-100 text-green-700' : 'bg-amber-100 text-amber-700'" class="px-2 py-0.5 rounded-full text-xs font-medium">
                    {{ reg.attended ? 'Present' : 'Absent' }}
                  </span>
                </td>
                <td class="px-5 py-3">
                  <div class="flex gap-2">
                    <button v-if="!reg.attended" @click="markPresent(reg)" class="text-green-600 text-sm font-medium hover:underline">Mark Present</button>
                    <button v-if="reg.attended" @click="generateSingleCertificate(reg)" class="text-blue-600 text-sm hover:underline">Certificate</button>
                    <button @click="removeRegistration(reg)" class="text-red-600 text-sm hover:underline">Remove</button>
                  </div>
                </td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>
    </template>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import { useRoute } from 'vue-router'
import { useNotificationStore } from '@/stores/notification'
import api from '@/services/api'
import LoadingSkeleton from '@/components/LoadingSkeleton.vue'
import { imageUrl } from '@/utils/formatters'

const route = useRoute(); const notif = useNotificationStore()
const event = ref({}); const registrations = ref([]); const loading = ref(true)

async function fetchData() {
  loading.value = true
  try {
    const { data } = await api.get(`/events/${route.params.id}`)
    event.value = data
    registrations.value = data.registrations || []
  } catch (e) {} finally { loading.value = false }
}

async function markPresent(reg) {
  try {
    await api.put(`/events/${event.value.id}/attendance`, { user_id: reg.user_id })
    notif.success('Marked present!')
    fetchData()
  } catch (err) { notif.error('Failed') }
}

async function generateSingleCertificate(reg) {
  try {
    await api.post(`/events/${event.value.id}/certificates`, { user_id: reg.user_id })
    notif.success('Certificate generated!')
  } catch (err) { notif.error('Failed') }
}

async function generateAllCertificates() {
  try {
    await api.post(`/events/${event.value.id}/certificates`)
    notif.success('All certificates generated!')
  } catch (err) { notif.error('Failed') }
}

async function removeRegistration(reg) {
  try {
    await api.delete(`/events/${event.value.id}/registrations/${reg.id}`)
    notif.success('Removed!')
    fetchData()
  } catch (err) { notif.error('Failed') }
}

onMounted(fetchData)
</script>
