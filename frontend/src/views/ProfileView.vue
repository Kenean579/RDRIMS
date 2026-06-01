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
        <div class="card p-8 text-center flex flex-col items-center">
          <div class="w-24 h-24 rounded-3xl bg-blue-600 shadow-xl shadow-blue-500/20 flex items-center justify-center text-4xl font-black text-white mb-6 transform rotate-3">
            {{ getInitials(auth.user?.name) }}
          </div>
          <h2 class="text-xl font-bold text-slate-800">{{ auth.user?.name }}</h2>
          <p class="text-sm font-medium text-slate-400 mt-1 uppercase tracking-widest">{{ auth.user?.email }}</p>
          
          <div class="mt-6 pt-6 border-t border-slate-50 w-full">
            <p class="text-[10px] font-bold text-slate-400 uppercase tracking-widest mb-3">Assigned Permissions</p>
            <div class="flex flex-wrap justify-center gap-2">
              <span v-for="r in auth.user?.roles" :key="r.id" class="px-3 py-1 rounded-full bg-blue-50 border border-blue-100 text-blue-700 text-[10px] font-bold uppercase tracking-tight">
                {{ r.name }}
              </span>
            </div>
          </div>
        </div>

        <div class="card p-6">
           <h3 class="text-sm font-bold text-slate-800 mb-4">Research Visibility</h3>
           <div class="space-y-4">
              <div class="p-3 bg-slate-50 rounded-xl border border-slate-100/50">
                 <p class="text-[10px] font-bold text-slate-400 uppercase mb-1">ORCID ID</p>
                 <p class="text-xs font-mono font-bold text-blue-600">{{ auth.user?.orcid_id || 'Not Linked' }}</p>
              </div>
           </div>
        </div>
      </div>

      <!-- Settings Form -->
      <div class="lg:col-span-8">
        <div class="card p-8">
          <h2 class="text-lg font-bold text-slate-800 mb-6">Security & Identity Settings</h2>
          <form @submit.prevent="updateProfile" class="flex flex-col gap-6">
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-6">
              <div>
                <label class="block text-[11px] text-slate-500 font-bold uppercase tracking-wider mb-2 ml-1">Full Display Name</label>
                <input v-model="form.name" type="text" class="input" placeholder="Enter your full name" />
              </div>
              <div>
                <label class="block text-[11px] text-slate-500 font-bold uppercase tracking-wider mb-2 ml-1">Email Address</label>
                <input v-model="form.email" type="email" class="input" placeholder="email@institution.edu" />
              </div>
            </div>
            
            <div>
              <label class="block text-[11px] text-slate-500 font-bold uppercase tracking-wider mb-2 ml-1">Persistent Researcher Identifier (ORCID)</label>
              <input v-model="form.orcid_id" type="text" class="input" placeholder="0000-0000-0000-0000" />
            </div>
            
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-6">
              <div>
                <label class="block text-[11px] text-slate-500 font-bold uppercase tracking-wider mb-2 ml-1">Google Scholar ID</label>
                <input v-model="form.google_scholar_id" type="text" class="input" placeholder="Enter ID" />
              </div>
              <div>
                <label class="block text-[11px] text-slate-500 font-bold uppercase tracking-wider mb-2 ml-1">Scopus ID</label>
                <input v-model="form.scopus_id" type="text" class="input" placeholder="Enter ID" />
              </div>
            </div>

            <div>
              <label class="block text-[11px] text-slate-500 font-bold uppercase tracking-wider mb-2 ml-1">LinkedIn Profile URL</label>
              <input v-model="form.linkedin_url" type="url" class="input" placeholder="https://linkedin.com/in/..." />
            </div>
            
            <div>
              <label class="block text-[11px] text-slate-500 font-bold uppercase tracking-wider mb-2 ml-1">Professional Bio & Research Focus</label>
              <textarea v-model="form.bio" rows="4" class="input resize-none" placeholder="Describe your research interests..."></textarea>
            </div>

            <div class="pt-4">
              <button type="submit" class="btn btn-primary px-10">Save Profile Updates</button>
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
import { getInitials } from '@/utils/formatters'

const auth = useAuthStore(); const notif = useNotificationStore()
const form = reactive({ name: '', email: '', orcid_id: '', bio: '', google_scholar_id: '', scopus_id: '', linkedin_url: '' })

onMounted(() => { if (auth.user) Object.assign(form, { name: auth.user.name || '', email: auth.user.email || '', orcid_id: auth.user.orcid_id || '', bio: auth.user.bio || '', google_scholar_id: auth.user.google_scholar_id || '', scopus_id: auth.user.scopus_id || '', linkedin_url: auth.user.linkedin_url || '' }) })

async function updateProfile() {
  const ok = await auth.updateProfile(form)
  if (ok) notif.success('Profile updated!')
  else notif.error('Update failed')
}
</script>
