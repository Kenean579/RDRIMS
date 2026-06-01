<template>
  <div class="space-y-8 animate-fade pb-16">
    <!-- Header -->
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-6 bg-white rounded-3xl border border-slate-200 shadow-sm p-8 relative overflow-hidden">
      <div class="absolute right-0 top-0 w-48 h-48 bg-brand/5 rounded-full -translate-y-1/3 translate-x-1/4"></div>
      <div class="relative z-10">
        <h1 class="text-3xl font-black text-slate-800 tracking-tight mb-1">Notifications</h1>
        <p class="text-slate-500 text-sm">System alerts, status updates, and activity notifications.</p>
      </div>
      <button
        v-if="unreadCount > 0"
        @click="markAllRead"
        class="relative z-10 flex items-center gap-2 px-6 py-3 bg-brand/10 text-brand rounded-2xl border border-brand/20 text-[11px] font-black uppercase tracking-widest hover:bg-brand hover:text-white transition-all shadow-sm"
      >
        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7" /></svg>
        Mark All Read
        <span class="px-2 py-0.5 bg-brand text-white rounded-full text-[9px] font-black">{{ unreadCount }}</span>
      </button>
    </div>

    <!-- Loading State -->
    <div v-if="loading" class="space-y-4">
      <div v-for="i in 5" :key="i" class="bg-white rounded-3xl border border-slate-100 p-6 h-20 animate-pulse shadow-sm"></div>
    </div>

    <!-- Error State -->
    <div v-else-if="error" class="bg-rose-50 border border-rose-200 rounded-3xl p-12 text-center">
      <div class="w-16 h-16 bg-rose-100 text-rose-500 rounded-2xl flex items-center justify-center mx-auto mb-4">
        <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" /></svg>
      </div>
      <p class="text-sm font-black text-rose-700">{{ error }}</p>
    </div>

    <!-- Empty State -->
    <div v-else-if="notifications.length === 0" class="bg-white rounded-3xl border border-slate-200 p-20 text-center shadow-sm">
      <div class="w-20 h-20 bg-slate-50 text-slate-300 rounded-full flex items-center justify-center mx-auto mb-6 border-2 border-dashed border-slate-200">
        <svg class="w-10 h-10" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9" /></svg>
      </div>
      <p class="text-sm font-black text-slate-400 mb-1 uppercase tracking-widest">All Clear</p>
      <p class="text-xs text-slate-300 font-medium">You're fully up to date. New alerts will appear here.</p>
    </div>

    <!-- Notifications Feed -->
    <div v-else class="space-y-3">
      <div
        v-for="n in notifications"
        :key="n.id"
        @click="markRead(n)"
        class="group bg-white rounded-3xl border shadow-sm p-6 cursor-pointer transition-all hover:shadow-md relative overflow-hidden"
        :class="!n.read_at ? 'border-brand/30 bg-brand/[0.01]' : 'border-slate-200 hover:border-slate-300'"
      >
        <!-- Unread accent -->
        <div v-if="!n.read_at" class="absolute left-0 top-0 bottom-0 w-1 bg-brand rounded-l-3xl"></div>

        <div class="flex items-start gap-5">
          <div
            class="w-12 h-12 rounded-2xl flex items-center justify-center text-xl shrink-0 border transition-all"
            :class="!n.read_at ? 'bg-brand/10 border-brand/20 group-hover:bg-brand/20' : 'bg-slate-50 border-slate-100 group-hover:bg-slate-100'"
          >
            {{ getIcon(n.type) }}
          </div>

          <div class="flex-1 min-w-0">
            <p
              class="text-sm leading-relaxed transition-colors"
              :class="!n.read_at ? 'font-black text-slate-800' : 'font-medium text-slate-600 group-hover:text-slate-800'"
            >
              {{ n.message }}
            </p>
            <p class="text-[10px] font-black text-slate-400 uppercase tracking-widest mt-2">
              {{ formatDateTime(n.created_at) }}
            </p>
          </div>

          <!-- Unread dot -->
          <div v-if="!n.read_at" class="shrink-0 mt-1">
            <span class="w-2.5 h-2.5 bg-brand rounded-full block ring-4 ring-brand/20 animate-pulse"></span>
          </div>
          <!-- Read check -->
          <div v-else class="shrink-0 mt-1 opacity-30 group-hover:opacity-60 transition-opacity">
            <svg class="w-4 h-4 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7" /></svg>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue'
import { useNotificationStore } from '@/stores/notification'
import api from '@/services/api'
import { formatDateTime } from '@/utils/formatters'

const notif = useNotificationStore()
const notifications = ref([])
const loading = ref(true)
const error = ref(null)

const unreadCount = computed(() => notifications.value.filter(n => !n.read_at).length)

function getIcon(type) {
  const icons = {
    mou_expiring: '📋',
    license_expiring: '📜',
    proposal_submitted: '📝',
    proposal_approved: '✅',
    proposal_rejected: '❌',
    review_assigned: '🔍',
    project_milestone: '🏁',
    payment_received: '💰',
  }
  return icons[type] || '📬'
}

async function fetchNotifications() {
  loading.value = true; error.value = null
  try {
    const { data } = await api.get('/notifications')
    notifications.value = data.data || data
  } catch (err) {
    error.value = err.response?.data?.message || 'Failed to load notifications'
  } finally {
    loading.value = false
  }
}

async function markRead(n) {
  if (!n.read_at) {
    try {
      await api.put(`/notifications/${n.id}/read`)
      n.read_at = new Date().toISOString()
    } catch (e) {}
  }
}

async function markAllRead() {
  try {
    for (const n of notifications.value) {
      if (!n.read_at) await api.put(`/notifications/${n.id}/read`)
    }
    notif.success('All notifications marked as read')
    fetchNotifications()
  } catch (err) {
    notif.error('Failed to mark all as read')
  }
}

onMounted(fetchNotifications)
</script>
