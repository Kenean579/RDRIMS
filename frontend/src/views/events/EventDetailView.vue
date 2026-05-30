<template>
  <div card>
    <div class="mb-6">
      <router-link to="/events" class="text-sm text-blue-600 hover:underline mb-2 inline-block">← Back to Events</router-link>
      <h1 class="text-xl font-bold text-gray-800">{{ event.title || 'Event Detail' }}</h1>
    </div>

    <div v-if="loading" class="bg-white rounded-lg shadow-sm p-6"><LoadingSkeleton :rows="6" /></div>

    <template v-else>
      <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <div class="lg:col-span-2">
          <div class="bg-white rounded-lg shadow-sm border border-gray-100 p-6 mb-6">
            <div class="flex justify-between items-start mb-4">
               <h2 class="text-base font-semibold text-gray-800">Event Details</h2>
               <!-- Registration Action -->
               <template v-if="!isRegistered">
                 <button v-if="canRegister" @click="registerForEvent" class="btn bg-brand hover:bg-brand-dark text-white px-6 font-black uppercase tracking-widest text-[11px] shadow-lg shadow-blue-500/20">Register</button>
                 <span v-else class="text-[10px] text-rose-500 font-black uppercase tracking-widest py-2">Registration Closed</span>
               </template>
               <template v-else>
                 <span class="btn bg-emerald-100 text-emerald-700 px-6 font-black uppercase tracking-widest text-[11px] pointer-events-none">Registered ✓</span>
               </template>
            </div>
            <dl class="grid grid-cols-1 sm:grid-cols-2 gap-4 text-sm">
              <div><dt class="text-gray-500">Title</dt><dd class="font-medium text-gray-800 mt-0.5">{{ event.title }}</dd></div>
              <div><dt class="text-gray-500">Venue</dt><dd class="text-gray-800 mt-0.5">{{ event.venue }}</dd></div>
              <div><dt class="text-gray-500">Start</dt><dd class="text-gray-800 mt-0.5">{{ formatDateTime(event.start_date) }}</dd></div>
              <div><dt class="text-gray-500">End</dt><dd class="text-gray-800 mt-0.5">{{ formatDateTime(event.end_date) }}</dd></div>
              <div><dt class="text-gray-500">Capacity</dt><dd class="text-gray-800 mt-0.5">{{ event.capacity || 'Unlimited' }}</dd></div>
              <div><dt class="text-gray-500">Registered</dt><dd class="text-gray-800 mt-0.5">{{ event.registrations?.length || 0 }}</dd></div>
            </dl>
            <p class="text-sm text-gray-600 mt-4">{{ event.description }}</p>
          </div>
        </div>

        <div>
          <div class="bg-white rounded-lg shadow-sm border border-gray-100 p-5">
            <h2 class="text-base font-semibold text-gray-800 mb-4">Registrations</h2>
            <div v-if="event.registrations?.length" class="space-y-2">
              <div v-for="reg in event.registrations" :key="reg.id" class="flex items-center justify-between p-2 rounded hover:bg-gray-50">
                <div class="flex items-center gap-2">
                  <span class="text-sm text-gray-800">{{ reg.user?.name }}</span>
                  <span :class="reg.attended ? 'bg-green-100 text-green-700' : 'bg-gray-100 text-gray-500'" class="text-xs px-1.5 py-0.5 rounded-full">{{ reg.attended ? 'Attended' : 'Not attended' }}</span>
                </div>
                <button v-if="!reg.attended" @click="markAttendance(reg)" class="text-xs text-blue-600 hover:underline">Mark Present</button>
              </div>
            </div>
            <p v-else class="text-sm text-gray-400">No registrations yet.</p>
          </div>
        </div>
      </div>
    </template>
  </div>
</template>

<script setup>
import { ref, onMounted, computed } from 'vue'
import { useRoute } from 'vue-router'
import { useAuthStore } from '@/stores/auth'
import { useNotificationStore } from '@/stores/notification'
import api from '@/services/api'
import LoadingSkeleton from '@/components/LoadingSkeleton.vue'
import { formatDateTime } from '@/utils/formatters'

const route = useRoute(); const notif = useNotificationStore()
const event = ref({}); const loading = ref(true)
const { user } = useAuthStore()

const isRegistered = computed(() => {
  if(!event.value.registrations) return false;
  return event.value.registrations.some(r => r.user_id === user?.id)
})

const canRegister = computed(() => {
  const e = event.value;
  if(!e) return false;
  if(e.capacity && e.registrations && e.registrations.length >= e.capacity) return false;
  if(e.registration_deadline && new Date(e.registration_deadline) < new Date()) return false;
  return true;
})

async function registerForEvent() {
  try {
      await api.post(`/events/${event.value.id}/register`)
     notif.success('Successfully registered!')
     fetchEvent()
  } catch(e) { notif.error('Failed to register') }
}

async function fetchEvent() {
  loading.value = true
  try { const { data } = await api.get(`/events/${route.params.id}`); event.value = data }
  catch (e) {} finally { loading.value = false }
}

async function markAttendance(reg) {
  try { await api.put(`/events/${event.value.id}/attendance`, { user_id: reg.user_id }); notif.success('Marked present!'); fetchEvent() }
  catch (err) { notif.error('Failed') }
}

onMounted(fetchEvent)
</script>
