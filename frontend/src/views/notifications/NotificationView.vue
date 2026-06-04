<template>
  <div class="flex flex-col gap-6 card">
    <!-- Header -->
    <div class="section-header">
      <div>
        <h1 class="section-title">Notifications</h1>
        <p class="section-subtitle">System alerts, updates, and activity notifications</p>
      </div>
      <button v-if="notifications.length" @click="markAllRead" class="btn btn-secondary">
        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" /></svg>
        Mark All Read
      </button>
    </div>

    <!-- Content -->
    <div v-if="loading" class="card p-8"><LoadingSkeleton :rows="4" /></div>
    <div v-else-if="error" class="card border-red-100 bg-red-50/30 p-8 text-center">
      <p class="text-sm text-red-600 font-medium">{{ error }}</p>
    </div>
    <div v-else-if="notifications.length === 0" class="card">
      <EmptyState icon="🔔" title="No notifications" description="You're all caught up! New alerts will appear here." />
    </div>

    <div v-else class="space-y-3">
      <div v-for="n in notifications" :key="n.id"
        :class="[!n.read_at ? 'border-blue-200 bg-blue-50/30' : '']"
        class="card p-4 group card-hover cursor-pointer" @click="markRead(n)">
        <div class="flex items-start gap-3">
          <div class="w-9 h-9 rounded-2xl flex items-center justify-center text-lg flex-shrink-0"
               :class="!n.read_at ? 'bg-blue-100' : 'bg-slate-50'">
            {{ getIcon(n.type) }}
          </div>
          <div class="flex-1 min-w-0">
            <p class="text-sm text-slate-700 leading-relaxed" :class="{ 'font-bold': !n.read_at }">{{ n.message }}</p>
            <p class="text-[11px] text-slate-400 font-medium mt-1.5">{{ formatDateTime(n.created_at) }}</p>
          </div>
          <span v-if="!n.read_at" class="w-2.5 h-2.5 bg-blue-600 rounded-full mt-2 flex-shrink-0 ring-4 ring-blue-100"></span>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import { useNotificationStore } from '@/stores/notification'
import api from '@/services/api'
import LoadingSkeleton from '@/components/LoadingSkeleton.vue'
import EmptyState from '@/components/EmptyState.vue'
import { formatDateTime } from '@/utils/formatters'

const notif = useNotificationStore()
const notifications = ref([]); const loading = ref(true); const error = ref(null)

function getIcon(type) { return type === 'mou_expiring' ? '📋' : type === 'license_expiring' ? '📜' : '📬' }

async function fetchNotifications() {
  loading.value = true; error.value = null
  try { const { data } = await api.get('/notifications'); notifications.value = data.data || data }
  catch (err) { error.value = err.response?.data?.message || 'Failed' } finally { loading.value = false }
}

async function markRead(n) { if (!n.read_at) { try { await api.put(`/notifications/${n.id}/read`); n.read_at = new Date().toISOString() } catch (e) {} } }

async function markAllRead() {
  try { for (const n of notifications.value) { if (!n.read_at) await api.put(`/notifications/${n.id}/read`) }; fetchNotifications(); notif.success('All marked read') }
  catch (err) { notif.error('Failed') }
}

onMounted(fetchNotifications)
</script>
