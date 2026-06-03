<template>
  <div class="flex flex-col gap-6 card">
    <!-- Header -->
    <div class="section-header">
      <div>
        <h1 class="section-title">Institutional Profile</h1>
        <p class="section-subtitle">Manage your personal information and research credentials</p>
      </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-12 gap-6">
      <!-- Profile Sidebar -->
      <div class="lg:col-span-4 space-y-6">
        <div class="card p-5 text-center flex flex-col items-center">
          <!-- Profile Image / Upload -->
          <div class="relative group mb-6">
            <div class="w-24 h-24 rounded-3xl overflow-hidden bg-blue-600 shadow-xl shadow-blue-500/20 flex items-center justify-center text-2xl font-bold text-white">
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
              class="absolute inset-0 rounded-3xl bg-black/50 flex items-center justify-center opacity-0 group-hover:opacity-100 transition-opacity cursor-pointer"
              title="Change profile photo"
            >
              <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 9a2 2 0 012-2h.93a2 2 0 001.664-.89l.812-1.22A2 2 0 0110.07 4h3.86a2 2 0 011.664.89l.812 1.22A2 2 0 0018.07 7H19a2 2 0 012 2v9a2 2 0 01-2 2H5a2 2 0 01-2-2V9z"/><circle cx="12" cy="13" r="3" stroke-width="2"/></svg>
            </label>
            <input id="profile-photo-input" type="file" accept="image/*" class="hidden" @change="uploadProfilePhoto" />
          </div>
          <h2 class="text-xl font-bold text-slate-800">{{ auth.user?.name }}</h2>
          <p class="text-sm font-medium text-slate-400 mt-1 capitalize tracking-widest">{{ auth.user?.email }}</p>
          
          <div class="mt-6 pt-6 border-t border-slate-50 w-full">
            <p class="text-[10px] font-bold text-slate-400 capitalize tracking-widest mb-3">Assigned Permissions</p>
            <div class="flex flex-wrap justify-center gap-2">
              <span v-for="r in auth.user?.roles" :key="r.id" class="px-3 py-1 rounded-full bg-blue-50 border border-blue-100 text-blue-700 text-[10px] font-bold capitalize tracking-tight">
                {{ r.name }}
              </span>
            </div>
          </div>
        </div>

        <div class="card p-6">
           <h3 class="text-sm font-bold text-slate-800 mb-4">Research Visibility</h3>
           <div class="space-y-4">
              <div class="p-3 bg-slate-50 rounded-xl border border-slate-100/50">
                 <p class="text-[10px] font-bold text-slate-400 capitalize mb-1">ORCID ID</p>
                 <p class="text-xs font-mono font-bold text-blue-600">{{ auth.user?.orcid_id || 'Not Linked' }}</p>
              </div>
           </div>
        </div>
      </div>

      <!-- Settings Form -->
      <div class="lg:col-span-8">
        <div class="card p-5">
          <h2 class="text-lg font-bold text-slate-800 mb-6">Security & Identity Settings</h2>
          <form @submit.prevent="updateProfile" class="flex flex-col gap-6">
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-6">
              <div>
                <label class="block text-[11px] text-slate-500 font-bold capitalize tracking-wider mb-2 ml-1">Full Display Name</label>
                <input v-model="form.name" type="text" class="input" placeholder="Enter your full name" />
              </div>
              <div>
                <label class="block text-[11px] text-slate-500 font-bold capitalize tracking-wider mb-2 ml-1">Email Address</label>
                <input v-model="form.email" type="email" class="input" placeholder="email@institution.edu" />
              </div>
            </div>
            
            <div>
              <label class="block text-[11px] text-slate-500 font-bold capitalize tracking-wider mb-2 ml-1">Persistent Researcher Identifier (ORCID)</label>
              <input v-model="form.orcid_id" type="text" class="input" placeholder="0000-0000-0000-0000" />
            </div>
            
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-6">
              <div>
                <label class="block text-[11px] text-slate-500 font-bold capitalize tracking-wider mb-2 ml-1">Google Scholar ID</label>
                <input v-model="form.google_scholar_id" type="text" class="input" placeholder="Enter ID" />
              </div>
              <div>
                <label class="block text-[11px] text-slate-500 font-bold capitalize tracking-wider mb-2 ml-1">Scopus ID</label>
                <input v-model="form.scopus_id" type="text" class="input" placeholder="Enter ID" />
              </div>
            </div>

            <div>
              <label class="block text-[11px] text-slate-500 font-bold capitalize tracking-wider mb-2 ml-1">LinkedIn Profile URL</label>
              <input v-model="form.linkedin_url" type="url" class="input" placeholder="https://linkedin.com/in/..." />
            </div>
            
            <div>
              <label class="block text-[11px] text-slate-500 font-bold capitalize tracking-wider mb-2 ml-1">Professional Bio & Research Focus</label>
              <textarea v-model="form.bio" rows="4" class="input resize-none" placeholder="Describe your research interests..."></textarea>
            </div>

            <div class="pt-4">
              <button type="submit" class="btn btn-primary px-5">Save Profile Updates</button>
            </div>
          </form>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { reactive, onMounted } from 'vue'
import { useAuthStore } from '@/stores/auth'
import { useNotificationStore } from '@/stores/notification'
import { getInitials, imageUrl } from '@/utils/formatters'
import api from '@/services/api'

const auth = useAuthStore(); const notif = useNotificationStore()
const form = reactive({ name: '', email: '', orcid_id: '', bio: '', google_scholar_id: '', scopus_id: '', linkedin_url: '' })

onMounted(() => { if (auth.user) Object.assign(form, { name: auth.user.name || '', email: auth.user.email || '', orcid_id: auth.user.orcid_id || '', bio: auth.user.bio || '', google_scholar_id: auth.user.google_scholar_id || '', scopus_id: auth.user.scopus_id || '', linkedin_url: auth.user.linkedin_url || '' }) })

async function updateProfile() {
  const ok = await auth.updateProfile(form)
  if (ok) notif.success('Profile updated!')
  else notif.error('Update failed')
}

async function uploadProfilePhoto(event) {
  const file = event.target.files?.[0]
  if (!file) return
  try {
    // Upload file first
    const fd = new FormData()
    fd.append('file', file)
    fd.append('is_public', '1')
    const { data: fileRecord } = await api.post('/files', fd, { headers: { 'Content-Type': 'multipart/form-data' } })
    // Attach to profile
    await api.put('/profile', { profile_image_id: fileRecord.id })
    // Refresh user
    await auth.fetchUser()
    notif.success('Profile photo updated!')
  } catch (e) {
    notif.error('Failed to upload photo')
  }
}
</script>
