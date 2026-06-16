<template>
  <div class="flex flex-col gap-6 card animate-fade">
    <!-- Header -->
    <div class="section-header pb-4 border-b border-slate-100">
      <div>
        <h1 class="text-xl font-bold text-slate-900 tracking-tight leading-none ">Institutional Profile</h1>
        <p class="text-xs font-bold text-slate-400 mt-2  tracking-widest flex items-center gap-2">
           <span class="w-1.5 h-1.5 rounded-full bg-blue-500"></span>
           Manage your personal information and research credentials
        </p>
      </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-12 gap-8">
      <!-- Profile Sidebar -->
      <div class="lg:col-span-4 space-y-6">
        <div class="card p-8 text-center flex flex-col items-center group relative overflow-hidden">
          <div class="absolute -right-4 -top-4 w-24 h-24 bg-slate-50 rounded-full group-hover:scale-150 transition-transform duration-700 opacity-50"></div>
          
          <!-- Profile Image / Upload -->
          <div class="relative group mb-6 z-10">
            <div class="w-28 h-28 rounded-3xl overflow-hidden bg-brand shadow-xl shadow-brand/20 flex items-center justify-center text-3xl font-bold text-white transition-transform group-hover:scale-105">
              <img
                v-if="imageUrl(auth.user?.profile_image)"
                :src="imageUrl(auth.user?.profile_image)"
                :alt="auth.user?.name"
                class="w-full h-full object-cover"
              />
              <span v-else>{{ getInitials(auth.user?.name) }}</span>
            </div>
            <!-- Upload overlay -->
            <label
              for="profile-photo-input"
              class="absolute inset-0 rounded-3xl bg-black/50 flex items-center justify-center opacity-0 group-hover:opacity-100 transition-opacity cursor-pointer border-4 border-white/20"
              title="Change profile photo"
            >
              <svg class="w-8 h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M3 9a2 2 0 012-2h.93a2 2 0 001.664-.89l.812-1.22A2 2 0 0110.07 4h3.86a2 2 0 011.664.89l.812 1.22A2 2 0 0018.07 7H19a2 2 0 012 2v9a2 2 0 01-2 2H5a2 2 0 01-2-2V9z"/><circle cx="12" cy="13" r="3" stroke-width="2.5"/></svg>
            </label>
            <input id="profile-photo-input" type="file" accept="image/*" class="hidden" @change="uploadProfilePhoto" />
          </div>
          
          <h2 class="text-xl font-bold text-slate-900 tracking-tight z-10">{{ auth.user?.name }}</h2>
          <p class="text-xs font-bold text-slate-400 mt-1  tracking-widest z-10">{{ auth.user?.email }}</p>
          
          <div class="mt-8 pt-8 border-t border-slate-50 w-full z-10">
            <p class="text-xs font-bold text-slate-300 mb-4  tracking-tighter">Assigned Roles</p>
            <div class="flex flex-wrap justify-center gap-2">
              <span v-for="r in auth.user?.roles" :key="r.id" class="px-3 py-1.5 rounded-xl bg-slate-50 border border-slate-100 text-slate-600 text-xs font-bold  tracking-widest">
                {{ r.name.replace('_', ' ') }}
              </span>
            </div>
          </div>
        </div>

        <!-- Research Identifiers -->
        <div class="card p-8">
           <h3 class="text-xs font-bold text-slate-800  tracking-widest mb-6 flex items-center gap-2">
             <span class="w-1.5 h-1.5 rounded-full bg-brand"></span>
             Research Visibility
           </h3>
           <div class="space-y-4">
              <div class="p-4 bg-slate-50 rounded-2xl border border-slate-100 flex items-center justify-between">
                 <div>
                    <p class="text-xs font-bold text-slate-400  tracking-tighter mb-1">ORCID ID</p>
                    <p class="text-xs font-mono font-bold text-brand">{{ auth.user?.orcid_id || 'NOT LINKED' }}</p>
                 </div>
                 <div class="w-8 h-8 rounded-lg bg-white flex items-center justify-center text-brand border border-slate-100 shadow-sm">ID</div>
              </div>
              <div v-if="auth.user?.google_scholar_id" class="p-4 bg-slate-50 rounded-2xl border border-slate-100 flex items-center justify-between">
                 <div>
                    <p class="text-xs font-bold text-slate-400  tracking-tighter mb-1">Scholar ID</p>
                    <p class="text-xs font-mono font-bold text-slate-700 truncate w-32">{{ auth.user?.google_scholar_id }}</p>
                 </div>
                 <div class="w-8 h-8 rounded-lg bg-white flex items-center justify-center text-emerald-600 border border-slate-100 shadow-sm">GS</div>
              </div>
           </div>
        </div>
      </div>

      <!-- Settings Form -->
      <div class="lg:col-span-8">
        <div class="card p-8">
          <form @submit.prevent="updateProfile" class="flex flex-col gap-8">
            
            <div class="space-y-6">
              <h2 class="text-[13px] font-bold text-slate-900  tracking-widest flex items-center gap-3">
                 Security & Identity
              </h2>
              
              <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                  <label class="block text-xs font-bold text-slate-400  tracking-widest mb-3 ml-1">Full Legal Name</label>
                  <input v-model="form.name" type="text" class="input h-12 font-bold" placeholder="Your full name" />
                </div>
                <div>
                  <label class="block text-xs font-bold text-slate-400  tracking-widest mb-3 ml-1">Institutional Email</label>
                  <input v-model="form.email" type="email" class="input h-12 font-bold" placeholder="email@institution.edu" />
                </div>
              </div>
              
              <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                  <label class="block text-xs font-bold text-slate-400  tracking-widest mb-3 ml-1">ORCID Identifier</label>
                  <input v-model="form.orcid_id" type="text" class="input h-12 font-bold" placeholder="0000-0000-0000-0000" />
                </div>
                <div>
                  <label class="block text-xs font-bold text-slate-400  tracking-widest mb-3 ml-1">LinkedIn Profile</label>
                  <input v-model="form.linkedin_url" type="url" class="input h-12 font-bold" placeholder="https://linkedin.com/..." />
                </div>
              </div>

              <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                  <label class="block text-xs font-bold text-slate-400  tracking-widest mb-3 ml-1">Google Scholar ID</label>
                  <input v-model="form.google_scholar_id" type="text" class="input h-12 font-bold" placeholder="Enter ID" />
                </div>
                <div>
                  <label class="block text-xs font-bold text-slate-400  tracking-widest mb-3 ml-1">Scopus Identifier</label>
                  <input v-model="form.scopus_id" type="text" class="input h-12 font-bold" placeholder="Enter ID" />
                </div>
              </div>

              <div>
                <label class="block text-xs font-bold text-slate-400  tracking-widest mb-3 ml-1">Professional Bio & Research Focus</label>
                <textarea v-model="form.bio" rows="4" class="input resize-none p-4 font-medium leading-relaxed" placeholder="Tell us about your research expertise..."></textarea>
              </div>
            </div>

            <!-- Expertise Selection -->
            <div class="space-y-6 pt-4 border-t border-slate-50">
               <h2 class="text-[13px] font-bold text-slate-900  tracking-widest">
                  Domain Expertise
               </h2>
               <p class="text-xs font-medium text-slate-400 mb-2  tracking-wider">
                  Select your primary research areas to improve proposal matches and visibility
               </p>
               
               <div class="flex flex-wrap gap-2 min-h-[100px] p-6 bg-slate-50 rounded-3xl border border-slate-100">
                  <button 
                    v-for="e in allExpertise" :key="e.id"
                    type="button"
                    @click="toggleExpertise(e.id)"
                    class="px-4 py-2 rounded-xl text-xs font-bold  tracking-widest transition-all border-2"
                    :class="form.expertise.includes(e.id) 
                      ? 'bg-brand border-brand text-white shadow-lg shadow-brand/20' 
                      : 'bg-white border-slate-200 text-slate-400 hover:border-brand/30'"
                  >
                    {{ e.name }}
                  </button>
                  <div v-if="loadingExpertise" class="flex items-center gap-2 text-xs font-bold text-slate-400 italic">
                     <span class="animate-spin w-3 h-3 border-2 border-brand border-b-transparent rounded-full font-bold"></span>
                     Fetching taxonomy...
                  </div>
               </div>
            </div>

            <!-- Notification Preferences -->
            <div class="space-y-6 pt-4 border-t border-slate-50">
               <h2 class="text-[13px] font-bold text-slate-900 tracking-widest flex items-center gap-3">
                  Notification Preferences
               </h2>
               
               <div class="space-y-4 p-6 bg-slate-50 rounded-3xl border border-slate-100">
                  <label class="flex items-start gap-3 cursor-pointer">
                    <div class="flex items-center h-5">
                      <input type="checkbox" v-model="form.email_notifications" class="w-5 h-5 rounded border-slate-300 text-brand focus:ring-brand">
                    </div>
                    <div>
                      <p class="text-sm font-bold text-slate-700">Email Notifications (Master Toggle)</p>
                      <p class="text-xs text-slate-500 mt-0.5">Receive critical system emails (password resets, proposal decisions, etc.). Turning this off disables ALL emails.</p>
                    </div>
                  </label>
                  
                  <div class="pl-8 space-y-4 mt-4" :class="{ 'opacity-50 pointer-events-none': !form.email_notifications }">
                    <label class="flex items-start gap-3 cursor-pointer">
                      <div class="flex items-center h-5">
                        <input type="checkbox" v-model="form.email_important" class="w-5 h-5 rounded border-slate-300 text-brand focus:ring-brand">
                      </div>
                      <div>
                        <p class="text-sm font-bold text-slate-700">Important Updates</p>
                        <p class="text-xs text-slate-500 mt-0.5">Receive emails for review deadlines, action items, and status changes.</p>
                      </div>
                    </label>

                    <label class="flex items-start gap-3 cursor-pointer">
                      <div class="flex items-center h-5">
                        <input type="checkbox" v-model="form.email_informational" class="w-5 h-5 rounded border-slate-300 text-brand focus:ring-brand">
                      </div>
                      <div>
                        <p class="text-sm font-bold text-slate-700">Informational</p>
                        <p class="text-xs text-slate-500 mt-0.5">Receive emails for new call announcements, general events, and system news.</p>
                      </div>
                    </label>
                  </div>
               </div>
            </div>

            <div class="pt-6 flex justify-end">
              <button type="submit" :disabled="saving" class="btn btn-primary h-14 px-10 text-xs font-bold  tracking-widest shadow-xl shadow-brand/20">
                {{ saving ? 'Propagating Updates...' : 'Synchronize Profile' }}
              </button>
            </div>
          </form>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, reactive, onMounted } from 'vue'
import { useAuthStore } from '@/stores/auth'
import { useNotificationStore } from '@/stores/notification'
import { getInitials, imageUrl } from '@/utils/formatters'
import api from '@/services/api'

const auth = useAuthStore()
const notif = useNotificationStore()
const saving = ref(false)
const loadingExpertise = ref(false)
const allExpertise = ref([])

const form = reactive({ 
  name: '', 
  email: '', 
  orcid_id: '', 
  bio: '', 
  google_scholar_id: '', 
  scopus_id: '', 
  linkedin_url: '',
  expertise: [],
  email_notifications: true,
  email_important: false,
  email_informational: false
})

async function fetchExpertise() {
  loadingExpertise.value = true
  try {
    const { data } = await api.get('/expertise')
    allExpertise.value = Array.isArray(data) ? data : (data.data || [])
  } catch (e) {
    notif.error('Failed to load expertise taxonomy')
  } finally {
    loadingExpertise.value = false
  }
}

function toggleExpertise(id) {
  const index = form.expertise.indexOf(id)
  if (index === -1) form.expertise.push(id)
  else form.expertise.splice(index, 1)
}

onMounted(async () => {
  fetchExpertise()
  if (auth.user) {
    Object.assign(form, { 
      name: auth.user.name || '', 
      email: auth.user.email || '', 
      orcid_id: auth.user.orcid_id || '', 
      bio: auth.user.bio || '', 
      google_scholar_id: auth.user.google_scholar_id || '', 
      scopus_id: auth.user.scopus_id || '', 
      linkedin_url: auth.user.linkedin_url || '',
      expertise: (auth.user.expertise || []).map(e => e.id),
      email_notifications: !!auth.user.email_notifications,
      email_important: !!auth.user.email_important,
      email_informational: !!auth.user.email_informational
    })
  }
})

async function updateProfile() {
  saving.value = true
  try {
    const ok = await auth.updateProfile(form)
    if (ok) notif.success('Institutional profile updated!')
    else notif.error('Update operation failed')
  } catch (e) {
    notif.error('Critical network error during update')
  } finally {
    saving.value = false
  }
}

async function uploadProfilePhoto(event) {
  const file = event.target.files?.[0]
  if (!file) return
  try {
    const fd = new FormData()
    fd.append('file', file)
    fd.append('is_public', '1')
    const { data: fileRecord } = await api.post('/files', fd)
    await api.put('/profile', { profile_image_id: fileRecord.id })
    await auth.fetchUser()
    notif.success('Biometric photo verified and updated')
  } catch (e) {
    notif.error('Photo authentication failed')
  }
}
</script>

