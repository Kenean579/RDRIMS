<template>
  <div class="flex flex-col gap-12 pb-16">
    <section class="bg-white border-b border-slate-100 pt-12 pb-16">
      <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <h1 class="text-4xl md:text-5xl font-black text-slate-900 tracking-tight mb-3">News & Events</h1>
        <p class="text-lg text-slate-500 font-medium max-w-2xl">Stay updated with the latest academic events, workshops, conferences, and seminars.</p>
      </div>
    </section>

    <section class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 w-full">
      <div v-if="loading" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
        <div v-for="i in 6" :key="i" class="bg-white rounded-2xl border border-slate-200 p-6 h-64 animate-pulse">
          <div class="h-4 w-20 bg-slate-200 rounded mb-4"></div>
          <div class="h-6 w-3/4 bg-slate-100 rounded mb-3"></div>
          <div class="h-20 w-full bg-slate-50 rounded mb-4"></div>
          <div class="h-4 w-1/3 bg-slate-100 rounded"></div>
        </div>
      </div>

      <div v-else-if="events.length === 0" class="text-center py-20">
        <div class="h-20 w-20 bg-slate-100 rounded-full flex items-center justify-center mx-auto mb-6 text-3xl">📅</div>
        <h3 class="text-xl font-black text-slate-700 mb-2">No events scheduled</h3>
        <p class="text-sm text-slate-500 font-medium">Check back later for upcoming events and workshops.</p>
      </div>

      <div v-else class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
        <div v-for="event in events" :key="event.id"
          class="bg-white rounded-2xl border border-slate-200 shadow-sm hover:shadow-lg hover:-translate-y-1 transition-all group overflow-hidden flex flex-col">
          <!-- Date banner -->
          <div class="bg-linear-to-r from-brand to-brand-dark px-6 py-4 text-white flex items-center gap-4">
            <div class="text-center">
              <p class="text-3xl font-black leading-none">{{ getDay(event.start_date) }}</p>
              <p class="text-[10px] font-black uppercase tracking-widest opacity-80">{{ getMonth(event.start_date) }}</p>
            </div>
            <div class="h-10 w-px bg-white/20"></div>
            <div>
              <p class="text-sm font-bold">{{ getTime(event.start_date) }}</p>
              <p class="text-[10px] font-bold opacity-70 uppercase tracking-widest">{{ getYear(event.start_date) }}</p>
            </div>
          </div>

          <div class="p-6 flex flex-col flex-1">
            <span v-if="event.type" class="inline-block px-2.5 py-1 bg-brand/10 text-brand rounded-lg text-[10px] font-black uppercase tracking-widest mb-3 self-start border border-brand/20">
              {{ event.type }}
            </span>
            <h3 class="text-lg font-black text-slate-800 leading-tight mb-3 group-hover:text-brand transition-colors">{{ event.title }}</h3>
            <p class="text-sm text-slate-500 font-medium line-clamp-3 mb-4 leading-relaxed flex-1">{{ event.description }}</p>

            <div class="flex items-center gap-2 text-[10px] font-black text-slate-400 uppercase tracking-widest pt-4 border-t border-slate-100">
              <svg class="w-4 h-4 text-slate-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
              {{ event.location || 'Online' }}
            </div>
          </div>
        </div>
      </div>
    </section>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import api from '@/services/api'

const events = ref([])
const loading = ref(true)

function getDay(d) { return d ? new Date(d).getDate() : '--' }
function getMonth(d) { return d ? new Date(d).toLocaleString(undefined, { month: 'short' }) : '' }
function getYear(d) { return d ? new Date(d).getFullYear() : '' }
function getTime(d) { return d ? new Date(d).toLocaleTimeString(undefined, { hour: '2-digit', minute: '2-digit' }) : '' }

onMounted(async () => {
  try {
    const { data } = await api.get('/events')
    events.value = data.data || data
  } catch (e) {}
  loading.value = false
})
</script>
