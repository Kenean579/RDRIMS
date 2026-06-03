<template>
  <!-- Global Notifications -->
  <transition name="slide">
    <div v-if="notif.show" :class="notifBg" class="fixed top-20 right-6 z-9999 text-white px-6 py-4 rounded-2xl shadow-2xl flex items-center gap-4 min-w-[300px] max-w-md border border-white/20 backdrop-blur-sm">
      <div class="text-2xl">{{ notifIcon }}</div>
      <div class="flex-1">
        <p class="font-bold text-xs capitalize tracking-widest opacity-70">{{ notif.type }}</p>
        <p class="text-sm font-semibold">{{ notif.message }}</p>
      </div>
      <button @click="notif.show = false" class="text-white/50 hover:text-white transition text-2xl">&times;</button>
    </div>
  </transition>

  <router-view />
</template>

<script setup>
import { computed } from 'vue'
import { useAuthStore } from '@/stores/auth'
import { useNotificationStore } from '@/stores/notification'
import { useLookupStore } from '@/stores/lookup'

const auth = useAuthStore()
const notif = useNotificationStore()
const lookupStore = useLookupStore()

if (auth.isAuthenticated && !auth.user) auth.fetchUser()
lookupStore.initialize()

const notifBg = computed(() => {
  switch(notif.type) {
    case 'success': return 'bg-emerald-600'
    case 'error': return 'bg-rose-600'
    case 'warning': return 'bg-amber-600'
    case 'info': return 'bg-blue-600'
    default: return 'bg-slate-700'
  }
})

const notifIcon = computed(() => {
  switch(notif.type) {
    case 'success': return '✅'
    case 'error': return '❌'
    case 'warning': return '⚠️'
    case 'info': return 'ℹ️'
    default: return '🔔'
  }
})
</script>

<style>
.slide-enter-active, .slide-leave-active { transition: all 0.4s cubic-bezier(0.175, 0.885, 0.32, 1.275); }
.slide-enter-from { transform: translateX(120%) scale(0.9); opacity: 0; }
.slide-leave-to { transform: translateX(120%); opacity: 0; }

.font-inter { font-family: 'Inter', sans-serif; }
</style>