<template>
  <div class="flex flex-col gap-8 pb-12 animate-fade card">
    <!-- Header -->
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
      <div>
        <h1 class="text-3xl font-black text-slate-900 tracking-tight">News & Events</h1>
        <p class="text-slate-500 font-medium mt-1">Stay updated with research news and workshops.</p>
      </div>
      <button @click="fetchEvents" class="btn btn-secondary h-11 px-6 shadow-sm group">
        <svg class="w-4 h-4 mr-1.5 group-hover:rotate-180 transition-transform duration-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" /></svg>
        Refresh
      </button>
    </div>

    <!-- Content -->
    <div v-if="loading" class="grid grid-cols-1 md:grid-cols-3 gap-6">
      <div v-for="i in 3" :key="i" class="card h-64 animate-pulse bg-slate-50/50 rounded-3xl"></div>
    </div>
    
    <div v-else-if="events.length === 0" class="card">
      <EmptyState icon="📅" title="Nothing to show" description="We'll post research seminars and news here soon." />
    </div>

    <div v-else class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
      <div v-for="event in events" :key="event.id" class="card overflow-hidden flex flex-col group card-hover border-b-4 border-b-brand/10 hover:border-b-brand transition-all">
        <div class="p-8 flex-1">
          <div class="flex justify-between items-start mb-6">
            <span class="px-3 py-1 bg-brand-light text-brand text-[9px] font-black capitalize tracking-widest rounded-lg border border-brand/20 shadow-sm">{{ event.type?.name || 'Workshop' }}</span>
          </div>
          <h3 class="text-xl font-black text-slate-900 group-hover:text-brand transition-colors mb-4 line-clamp-2 leading-tight" :title="event.title">{{ event.title }}</h3>
          <p class="text-sm text-slate-500 font-medium line-clamp-3 leading-relaxed mb-8 italic">{{ event.description }}</p>
          
          <div class="space-y-4">
            <div class="flex items-center gap-3 text-[10px] font-black text-slate-400 capitalize tracking-widest">
              <div class="w-8 h-8 rounded-xl bg-slate-50 flex items-center justify-center text-brand border border-slate-100 shadow-inner">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><rect x="3" y="4" width="18" height="18" rx="2" ry="2" stroke-width="2.5"/><line x1="16" y1="2" x2="16" y2="6" stroke-width="2.5"/><line x1="8" y1="2" x2="8" y2="6" stroke-width="2.5"/></svg>
              </div>
              {{ formatDate(event.start_date) }}
            </div>
            <div class="flex items-center gap-3 text-[10px] font-black text-slate-400 capitalize tracking-widest">
              <div class="w-8 h-8 rounded-xl bg-slate-50 flex items-center justify-center text-emerald-500 border border-slate-100 shadow-inner">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" /><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z" /></svg>
              </div>
              {{ event.location || 'Online' }}
            </div>
            <div class="flex items-center gap-3 text-[10px] font-black text-slate-400 capitalize tracking-widest">
              <div class="w-8 h-8 rounded-xl bg-slate-50 flex items-center justify-center text-amber-500 border border-slate-100 shadow-inner">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M17 21v-2a4 4 0 00-4-4H5a4 4 0 00-4 4v2" /><circle cx="9" cy="7" r="4" stroke-width="2.5"/></svg>
              </div>
              {{ event.registrations_count || 0 }} Attending
            </div>
          </div>
        </div>
        
        <div class="p-5 bg-slate-50/50 border-t border-slate-100 flex gap-3">
          <button @click="viewEvent(event)" class="flex-1 btn btn-secondary justify-center text-[10px] font-black capitalize tracking-widest py-3">Open</button>
          <button v-if="!event.is_registered" @click="registerForEvent(event)" class="flex-1 btn btn-primary justify-center text-[10px] font-black capitalize tracking-widest py-3 shadow-lg shadow-blue-500/20">Join Now</button>
          <span v-else class="flex-1 text-center py-3 text-[10px] font-black capitalize tracking-widest text-emerald-600 bg-emerald-50 rounded-xl border border-emerald-100 shadow-sm">Going ✓</span>
        </div>
      </div>
    </div>

    <!-- Detail Modal -->
    <Modal :show="!!selectedEvent" :title="selectedEvent?.title" size="lg" @close="selectedEvent = null">
      <div v-if="selectedEvent" class="flex flex-col gap-8">
        <p class="text-base text-slate-600 font-medium whitespace-pre-line leading-relaxed italic border-l-4 border-brand/20 pl-6 py-4">{{ selectedEvent.description }}</p>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6 p-8 bg-slate-900 rounded-3xl border border-white/5 shadow-2xl relative overflow-hidden group">
          <div class="absolute inset-0 bg-brand/5 opacity-0 group-hover:opacity-100 transition-opacity duration-700"></div>
          <div class="relative z-10">
            <p class="text-[9px] font-black text-slate-500 capitalize tracking-widest mb-2">Begins</p>
            <p class="text-sm font-black text-white tracking-tight">{{ formatDate(selectedEvent.start_date) }}</p>
          </div>
          <div class="relative z-10">
            <p class="text-[9px] font-black text-slate-500 capitalize tracking-widest mb-2">Ends</p>
            <p class="text-sm font-black text-white tracking-tight">{{ formatDate(selectedEvent.end_date) }}</p>
          </div>
          <div class="relative z-10">
            <p class="text-[9px] font-black text-slate-500 capitalize tracking-widest mb-2">Where</p>
            <p class="text-sm font-black text-white tracking-tight">{{ selectedEvent.location || 'Online / Remote' }}</p>
          </div>
          <div class="relative z-10">
            <p class="text-[9px] font-black text-slate-500 capitalize tracking-widest mb-2">Max Capacity</p>
            <p class="text-sm font-black text-white tracking-tight">{{ selectedEvent.max_participants || 'Unlimited' }}</p>
          </div>
        </div>
        <div v-if="selectedEvent.organizer" class="pt-8 border-t border-slate-100 flex items-center justify-between">
          <div class="flex items-center gap-3">
            <div class="w-10 h-10 rounded-full bg-slate-100 flex items-center justify-center text-slate-400 font-bold">@</div>
            <div>
              <p class="text-[10px] font-black text-slate-400 capitalize tracking-widest">Posted by</p>
              <p class="text-sm font-black text-slate-900 tracking-tight">{{ selectedEvent.organizer.name }}</p>
            </div>
          </div>
          <button @click="selectedEvent = null" class="btn btn-secondary px-8 h-11 text-[11px] font-black capitalize tracking-widest">Close</button>
        </div>
      </div>
    </Modal>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import api from '@/services/api'
import Modal from '@/components/Modal.vue'
import EmptyState from '@/components/EmptyState.vue'
import { formatDate } from '@/utils/formatters'
import { useNotificationStore } from '@/stores/notification'
const notif = useNotificationStore()
const loading = ref(true); const events = ref([]); const selectedEvent = ref(null)
async function fetchEvents() { try { const { data } = await api.get('/events'); events.value = data.data || data } catch (e) {} finally { loading.value = false } }
function viewEvent(event) { selectedEvent.value = event }
async function registerForEvent(event) { try { await api.post(`/events/${event.id}/register`); notif.success('Registered successfully!'); fetchEvents() } catch (e) { notif.error('Already registered or event full') } }
onMounted(fetchEvents)
</script>
