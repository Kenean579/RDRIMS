<template>
  <div class="flex flex-col gap-5 pb-6 animate-fade card">
    <!-- Header -->
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
      <div>
        <h1 class="text-xl font-bold text-slate-900 tracking-tight">News & Events</h1>
        <p class="text-slate-500 font-medium mt-1">Stay updated with research seminars, announcements and workshops.</p>
      </div>
      <div class="flex items-center gap-2">
        <button v-if="auth.hasRole('super_admin', 'research_admin', 'campus_admin', 'faculty_admin', 'director', 'department_head')" @click="showAdd = true" class="btn btn-primary h-11 px-5 text-xs font-bold">
          <svg class="w-4 h-4 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 4v16m8-8H4"/></svg>
          Post Announcement
        </button>
        <button @click="fetchEvents" class="btn btn-secondary h-11 px-4 shadow-sm group">
          <svg class="w-4 h-4 group-hover:rotate-180 transition-transform duration-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" /></svg>
        </button>
      </div>
    </div>

    <!-- Content -->
    <div v-if="loading" class="grid grid-cols-1 md:grid-cols-3 gap-6">
      <div v-for="i in 3" :key="i" class="card h-64 animate-pulse bg-slate-50/50 rounded-3xl"></div>
    </div>
    
    <div v-else-if="events.length === 0" class="card">
      <EmptyState icon="📅" title="Nothing to show" description="We'll post research seminars and news here soon." action-label="Post First News" v-if="auth.hasRole('super_admin', 'research_admin', 'campus_admin', 'faculty_admin', 'director', 'department_head')" @action="showAdd = true" />
      <EmptyState icon="📅" title="Nothing to show" description="We'll post research seminars and news here soon." v-else />
    </div>

    <div v-else class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-5">
      <div v-for="event in events" :key="event.id" class="card overflow-hidden flex flex-col group card-hover border-b-4 border-b-brand/10 hover:border-b-brand transition-all">
        <!-- Banner Image -->
        <div class="relative h-44 overflow-hidden bg-slate-900 shrink-0">
          <img
            v-if="imageUrl(event.image_file)"
            :src="imageUrl(event.image_file)"
            :alt="event.title"
            class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-500 opacity-90"
          />
          <div v-else class="w-full h-full bg-linear-to-br from-slate-800 to-slate-900 flex items-center justify-center p-4">
            <p class="text-white/30 text-xs font-bold text-center line-clamp-2">{{ event.title }}</p>
          </div>
          <span class="absolute top-3 left-3 px-3 py-1 bg-brand text-white text-xs font-bold rounded-md shadow-lg">{{ event.type?.name || 'Academic' }}</span>
          
          <!-- Admin Quick Actions -->
          <div v-if="auth.hasRole('super_admin', 'research_admin', 'campus_admin', 'faculty_admin', 'director', 'department_head')" class="absolute top-3 right-3 flex gap-1 opacity-0 group-hover:opacity-100 transition-opacity">
            <button @click.stop="editEvent(event)" class="w-8 h-8 rounded-lg bg-white/20 backdrop-blur-md text-white hover:bg-white hover:text-brand flex items-center justify-center shadow-lg transition-all">
              <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"/></svg>
            </button>
            <button @click.stop="confirmDelete(event)" class="w-8 h-8 rounded-lg bg-white/20 backdrop-blur-md text-white hover:bg-rose-500 hover:text-white flex items-center justify-center shadow-lg transition-all">
              <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M6 18L18 6M6 6l12 12"/></svg>
            </button>
          </div>
        </div>

        <div class="p-6 flex-1 flex flex-col">
          <h3 class="text-base font-bold text-slate-900 group-hover:text-brand transition-colors mb-3 line-clamp-2 leading-tight min-h-12" :title="event.title">{{ event.title }}</h3>
          <p class="text-xs text-slate-500 font-medium line-clamp-2 leading-relaxed mb-6 italic">{{ event.description || 'Institutional announcement for the research community.' }}</p>
          
          <div class="space-y-3 mt-auto">
            <div class="flex items-center gap-3 text-xs font-bold text-slate-400">
              <div class="w-8 h-8 rounded-2xl bg-slate-50 flex items-center justify-center text-brand border border-slate-100 shrink-0">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><rect x="3" y="4" width="18" height="18" rx="2" ry="2" stroke-width="2.5"/><line x1="16" y1="2" x2="16" y2="6" stroke-width="2.5"/><line x1="8" y1="2" x2="8" y2="6" stroke-width="2.5"/></svg>
              </div>
              <div class="flex flex-col">
                <span class="text-slate-500">{{ formatDate(event.start_date) }}</span>
                <span class="text-[8px] opacity-70">Event Date</span>
              </div>
            </div>
            <div class="flex items-center gap-3 text-xs font-bold text-slate-400">
              <div class="w-8 h-8 rounded-2xl bg-slate-50 flex items-center justify-center text-emerald-500 border border-slate-100 shrink-0">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" /><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z" /></svg>
              </div>
              <div class="flex flex-col">
                <span class="text-slate-500 truncate max-w-[150px]">{{ event.location || 'Online' }}</span>
                <span class="text-[8px] opacity-70">Deployment Location</span>
              </div>
            </div>
          </div>
        </div>
        
        <div class="px-6 pb-6 flex gap-3">
          <button @click="viewEvent(event)" class="btn btn-secondary flex-1 justify-center text-xs font-bold h-10 border-slate-200">View Info</button>
          <button v-if="!event.is_registered" @click="registerForEvent(event)" class="btn btn-primary flex-[1.5] justify-center text-xs font-bold h-10 shadow-lg shadow-brand/10">Join Initiative</button>
          <span v-else class="flex-[1.5] text-center p-2.5 text-xs font-bold text-emerald-600 bg-emerald-50 rounded-2xl border border-emerald-100 shadow-sm flex items-center justify-center gap-2">
            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"/></svg>
            Registered
          </span>
        </div>
      </div>
    </div>

    <!-- Upsert Modal -->
    <Modal :show="showAdd || !!editingEvent" :title="editingEvent ? 'Modify Announcement' : 'Post New Announcement'" size="lg" @close="closeUpsert">
      <form @submit.prevent="saveEvent" class="space-y-6">
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
          <div class="space-y-4">
            <div>
              <label class="block text-xs text-slate-500 font-bold mb-2 ml-1">Event Title *</label>
              <input v-model="form.title" type="text" required class="input h-12 font-bold" placeholder="e.g. Annual Research Symposium" />
            </div>
            <div>
              <label class="block text-xs text-slate-500 font-bold mb-2 ml-1">Event Type</label>
              <select v-model="form.type_id" class="input h-12 font-bold">
                <option value="">General Announcement</option>
                <option v-for="t in types" :key="t.id" :value="t.id">{{ t.name }}</option>
              </select>
            </div>
            <div>
              <label class="block text-xs text-slate-500 font-bold mb-2 ml-1">Location / Platform</label>
              <input v-model="form.location" type="text" class="input h-12 font-bold" placeholder="e.g. Main Hall or Zoom Link" />
            </div>
          </div>
          
          <div class="space-y-4">
             <div class="grid grid-cols-2 gap-4">
                <div>
                  <label class="block text-xs text-slate-500 font-bold mb-2 ml-1">Start Date *</label>
                  <input v-model="form.start_date" type="date" required class="input h-12 font-bold" />
                </div>
                <div>
                  <label class="block text-xs text-slate-500 font-bold mb-2 ml-1">End Date</label>
                  <input v-model="form.end_date" type="date" class="input h-12 font-bold" />
                </div>
             </div>
             <div>
              <label class="block text-xs text-slate-500 font-bold mb-2 ml-1">Max Participants</label>
              <input v-model.number="form.max_participants" type="number" class="input h-12 font-bold" placeholder="0 for unlimited" />
             </div>
             <div>
                <label class="block text-xs text-slate-500 font-bold mb-2 ml-1">Banner Image</label>
                <div class="flex items-center gap-4 p-4 bg-slate-50 rounded-2xl border border-slate-100">
                  <div class="w-16 h-10 rounded-lg bg-white border border-slate-200 flex items-center justify-center overflow-hidden shrink-0">
                    <img v-if="imageUrl(form.image_file)" :src="imageUrl(form.image_file)" class="w-full h-full object-cover" />
                    <span v-else class="text-[10px] text-slate-300 font-bold">BANNER</span>
                  </div>
                  <div class="flex-1">
                    <input type="file" accept="image/*" class="hidden" id="event-banner-input" @change="uploadBanner" />
                    <label for="event-banner-input" class="btn btn-secondary h-9 px-4 text-xs font-bold cursor-pointer">
                       {{ uploadingImage ? 'Processing...' : 'Upload Image' }}
                    </label>
                  </div>
                </div>
             </div>
          </div>
        </div>

        <div>
          <label class="block text-xs text-slate-500 font-bold mb-2 ml-1">Message / Content *</label>
          <textarea v-model="form.description" required rows="5" class="input p-4 font-medium resize-none" placeholder="Provide full details about the event..."></textarea>
        </div>

        <div class="flex justify-end gap-3 pt-6 border-t border-slate-100">
          <button type="button" @click="closeUpsert" class="btn btn-secondary px-6 font-bold text-xs">Discard</button>
          <button type="submit" class="btn btn-primary px-8 font-bold text-xs h-12 shadow-xl shadow-brand/20">
            {{ editingEvent ? 'Update Announcement' : 'Publish to Feed' }}
          </button>
        </div>
      </form>
    </Modal>

    <!-- Detail Modal -->
    <Modal :show="!!selectedEvent" :title="'Event Details'" size="lg" @close="selectedEvent = null">
      <div v-if="selectedEvent" class="flex flex-col gap-6">
        <div class="h-48 rounded-3xl overflow-hidden bg-slate-900 shadow-inner">
           <img v-if="imageUrl(selectedEvent.image_file)" :src="imageUrl(selectedEvent.image_file)" class="w-full h-full object-cover opacity-80" />
           <div v-else class="w-full h-full flex items-center justify-center bg-brand/10 text-brand font-bold text-xl  tracking-tighter">{{ selectedEvent.title.substring(0,3) }}</div>
        </div>

        <div class="px-2">
          <h2 class="text-xl font-bold text-slate-900 mb-3 tracking-tight">{{ selectedEvent.title }}</h2>
          <p class="text-base text-slate-500 font-medium whitespace-pre-line leading-relaxed italic border-l-4 border-brand/20 pl-6 py-2">{{ selectedEvent.description }}</p>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-6 p-6 bg-slate-900 rounded-3xl border border-white/5 shadow-2xl relative overflow-hidden group">
          <div class="absolute inset-0 bg-brand/5 opacity-0 group-hover:opacity-100 transition-opacity duration-700"></div>
          <div class="relative z-10">
            <p class="text-xs font-bold text-slate-500 mb-2  tracking-widest">Begins</p>
            <p class="text-sm font-bold text-white tracking-tight">{{ formatDate(selectedEvent.start_date) }}</p>
          </div>
          <div class="relative z-10">
            <p class="text-xs font-bold text-slate-500 mb-2  tracking-widest">Ends</p>
            <p class="text-sm font-bold text-white tracking-tight">{{ formatDate(selectedEvent.end_date) || '—' }}</p>
          </div>
          <div class="relative z-10">
            <p class="text-xs font-bold text-slate-500 mb-2  tracking-widest">Where</p>
            <p class="text-sm font-bold text-white tracking-tight">{{ selectedEvent.location || 'Online / Remote' }}</p>
          </div>
          <div class="relative z-10">
            <p class="text-xs font-bold text-slate-500 mb-2  tracking-widest">Max Capacity</p>
            <p class="text-sm font-bold text-white tracking-tight">{{ selectedEvent.max_participants || 'Unlimited' }}</p>
          </div>
        </div>

        <div class="pt-4 border-t border-slate-100 flex items-center justify-between">
          <div class="flex items-center gap-3">
            <div class="w-10 h-10 rounded-2xl bg-brand/10 flex items-center justify-center text-brand font-bold text-xs">PI</div>
            <div>
              <p class="text-xs font-medium text-slate-400">Institutional Feed</p>
              <p class="text-xs font-bold text-slate-900 tracking-tight">University Research Office</p>
            </div>
          </div>
          <div class="flex gap-2">
            <button v-if="!selectedEvent.is_registered" @click="registerForEvent(selectedEvent)" class="btn btn-primary px-8 h-12 text-xs font-bold shadow-xl shadow-brand/20">Register for Event</button>
            <button @click="selectedEvent = null" class="btn btn-secondary px-6 h-12 text-xs font-bold">Close Dialog</button>
          </div>
        </div>
      </div>
    </Modal>

    <ConfirmDialog :show="showDelete" title="Permanently Delete Event" :message="'Are you sure you want to remove \'' + (deletingEvent?.title) + '\'? This will also cancel all registrations.'" confirmText="Delete Event" variant="danger" @confirm="deleteEvent" @cancel="showDelete = false" />
  </div>
</template>

<script setup>
import { ref, reactive, onMounted } from 'vue'
import api from '@/services/api'
import Modal from '@/components/Modal.vue'
import ConfirmDialog from '@/components/ConfirmDialog.vue'
import EmptyState from '@/components/EmptyState.vue'
import { formatDate, imageUrl } from '@/utils/formatters'
import { useNotificationStore } from '@/stores/notification'
import { useAuthStore } from '@/stores/auth'

const notif = useNotificationStore()
const auth = useAuthStore()

const loading = ref(true)
const events = ref([])
const types = ref([])
const selectedEvent = ref(null)
const showAdd = ref(false)
const editingEvent = ref(null)
const showDelete = ref(false)
const deletingEvent = ref(null)

const form = reactive({
  title: '',
  description: '',
  type_id: '',
  location: '',
  start_date: '',
  end_date: '',
  max_participants: 0,
  image_file: null,
  image_file_id: null
})

const uploadingImage = ref(false)

async function fetchEvents() { 
  loading.value = true
  try { 
    const { data } = await api.get('/events')
    events.value = data.data || data 
  } catch (e) {
    notif.error('Failed to load activity feed')
  } finally { 
    loading.value = false 
  } 
}

async function fetchTypes() {
  try {
    const { data } = await api.get('/lookups/event_types')
    types.value = data
  } catch (e) {}
}

function viewEvent(event) { selectedEvent.value = event }

function editEvent(event) {
  editingEvent.value = event
  Object.assign(form, {
    title: event.title,
    description: event.description,
    type_id: event.type_id || '',
    location: event.location || '',
    start_date: event.start_date?.split('T')[0] || '',
    end_date: event.end_date?.split('T')[0] || '',
    max_participants: event.max_participants || 0,
    image_file: event.image_file || event.banner_file || null,
    image_file_id: event.image_file_id || event.banner_file_id || null
  })
}

function closeUpsert() {
  showAdd.value = false
  editingEvent.value = null
  Object.assign(form, { title: '', description: '', type_id: '', location: '', start_date: '', end_date: '', max_participants: 0, image_file: null, image_file_id: null })
}

async function saveEvent() {
  try {
    const payload = { ...form }
    if (editingEvent.value) {
      await api.put(`/events/${editingEvent.value.id}`, payload)
      notif.success('Event updated')
    } else {
      await api.post('/events', payload)
      notif.success('Event published')
    }
    closeUpsert()
    fetchEvents()
  } catch (e) {
    notif.error(e.response?.data?.message || 'Failed to save announcement')
  }
}

async function uploadBanner(event) {
  const file = event.target.files?.[0]
  if (!file) return
  uploadingImage.value = true
  try {
    const fd = new FormData()
    fd.append('file', file)
    fd.append('is_public', '1')
    const { data } = await api.post('/files', fd)
    form.image_file_id = data.id
    form.image_file = data
    notif.success('Banner uploaded successfully')
  } catch (e) {
    notif.error('Failed to upload image')
  } finally {
    uploadingImage.value = false
  }
}

function confirmDelete(event) {
  deletingEvent.value = event
  showDelete.value = true
}

async function deleteEvent() {
  try {
    await api.delete(`/events/${deletingEvent.value.id}`)
    notif.success('Event removed')
    showDelete.value = false
    fetchEvents()
  } catch (e) {
    notif.error('Failed to delete')
  }
}

async function registerForEvent(event) { 
  try { 
    await api.post(`/events/${event.id}/register`)
    notif.success('Registered successfully!')
    if (selectedEvent.value) selectedEvent.value.is_registered = true
    fetchEvents() 
  } catch (e) { 
    notif.error('Already registered or event full') 
  } 
}

onMounted(() => {
  fetchEvents()
  if (auth.hasRole('super_admin', 'research_admin')) fetchTypes()
})
</script>

