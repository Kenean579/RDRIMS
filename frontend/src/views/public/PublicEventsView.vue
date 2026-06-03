<template>
  <div class="flex flex-col gap-5 pb-6 animate-fade">
    <!-- Header -->
    <div class="card p-5 bg-slate-50 border-slate-100 relative overflow-hidden">
      <div class="relative z-10">
        <h1 class="text-xl font-bold text-slate-900 tracking-tight">Upcoming Events</h1>
        <p class="text-slate-500 font-medium mt-1">Conferences, workshops, and academic seminars.</p>
      </div>
      <div class="absolute right-0 top-0 w-32 h-32 bg-brand/5 rounded-full translate-x-8 -translate-y-8"></div>
    </div>

    <!-- Content -->
    <div v-if="loading" class="grid grid-cols-1 md:grid-cols-2 gap-6">
      <div v-for="i in 4" :key="i" class="card h-64 animate-pulse"></div>
    </div>

    <div v-else-if="events.length === 0" class="card p-6 text-center text-slate-400 text-xs font-bold capitalize tracking-widest italic">
      No upcoming events scheduled.
    </div>

    <div v-else class="grid grid-cols-1 md:grid-cols-2 gap-6">
      <div v-for="event in events" :key="event.id" class="card overflow-hidden group card-hover border-l-4 border-l-transparent hover:border-l-brand transition-all flex flex-col">
        <div class="bg-slate-900 p-6 text-white flex items-center justify-between">
          <div class="flex items-center gap-4">
            <div class="text-center bg-white/10 rounded-xl p-2 min-w-[60px]">
              <p class="text-2xl font-bold leading-none">{{ getDay(event.start_date) }}</p>
              <p class="text-[9px] font-bold capitalize tracking-widest opacity-60">{{ getMonth(event.start_date) }}</p>
            </div>
            <div>
              <p class="text-sm font-bold">{{ event.title }}</p>
              <p class="text-[10px] font-bold opacity-60 capitalize tracking-widest">{{ getTime(event.start_date) }}</p>
            </div>
          </div>
          <div class="hidden sm:block">
            <span class="px-2 py-1 bg-brand text-[9px] font-bold capitalize tracking-widest rounded-lg">Featured</span>
          </div>
        </div>
        
        <div class="p-6 flex-1 flex flex-col">
          <p class="text-sm text-slate-500 font-medium line-clamp-3 mb-6">{{ event.description }}</p>
          
          <div class="mt-auto pt-4 border-t border-slate-50 flex items-center justify-between text-[10px] font-bold capitalize tracking-widest text-slate-400">
            <span class="flex items-center gap-2">
              <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/></svg>
              {{ event.venue || 'TBD' }}
            </span>
            <button class="text-brand hover:underline">Learn More →</button>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import api from '@/services/api'

const events = ref([])
const loading = ref(true)

function getDay(d) { return d ? new Date(d).getDate() : '--' }
function getMonth(d) { return d ? new Date(d).toLocaleString(undefined, { month: 'short' }) : '' }
function getTime(d) { return d ? new Date(d).toLocaleTimeString(undefined, { hour: '2-digit', minute: '2-digit', hour12: false }) : '' }

onMounted(async () => {
  try {
    const { data } = await api.get('/events')
    events.value = data.data || data
  } catch (e) {}
  loading.value = false
})
</script>
