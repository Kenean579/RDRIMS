<template>
  <!-- Global Notifications -->
  <transition name="slide">
    <div v-if="notif.show" :class="notifTypeClass" class="fixed top-20 right-6 z-9999 px-5 py-3 rounded-2xl shadow-xl flex items-center gap-3 min-w-[320px] max-w-md border animate-fade">
      <div class="shrink-0 w-8 h-8 rounded-full flex items-center justify-center bg-white/50 border border-current opacity-80">
        <span class="text-lg">{{ notifIcon }}</span>
      </div>
      <div class="flex-1">
        <h4 class="text-xs font-bold  tracking-wider opacity-60 mb-0.5">{{ notif.type }}</h4>
        <p class="text-[13px] font-semibold leading-snug">{{ notif.message }}</p>
      </div>
      <button @click="notif.show = false" class="text-current opacity-40 hover:opacity-100 transition text-xl p-1">&times;</button>
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

// Always refresh user permissions from backend on app boot.
// Use an async function to ensure sequential fetching and avoid deadlocking the PHP dev server.
const initializeApp = async () => {
  if (auth.isAuthenticated) {
    await auth.fetchUser()
  }
  await lookupStore.initialize()
}
initializeApp()

const notifTypeClass = computed(() => {
  switch(notif.type) {
    case 'success': return 'bg-emerald-50 text-emerald-900 border-emerald-200'
    case 'error':   return 'bg-rose-50 text-rose-900 border-rose-200'
    case 'warning': return 'bg-amber-50 text-amber-900 border-amber-200'
    case 'info':    return 'bg-blue-50 text-blue-900 border-blue-200'
    default:        return 'bg-slate-50 text-slate-900 border-slate-200'
  }
})

const notifIcon = computed(() => {
  switch(notif.type) {
    case 'success': return '✓'
    case 'error':   return '✕'
    case 'warning': return '!'
    case 'info':    return 'i'
    default:        return '•'
  }
})
</script>

<style>
.slide-enter-active, .slide-leave-active { transition: all 0.4s cubic-bezier(0.175, 0.885, 0.32, 1.275); }
.slide-enter-from { transform: translateX(120%) scale(0.9); opacity: 0; }
.slide-leave-to { transform: translateX(120%); opacity: 0; }
</style>
