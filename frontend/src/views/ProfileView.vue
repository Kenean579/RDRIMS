<template>
  <div class="space-y-8 animate-fade pb-16">
    <!-- Header -->
    <div class="bg-white rounded-3xl border border-slate-200 shadow-sm p-8 relative overflow-hidden">
      <div class="absolute right-0 top-0 w-64 h-64 bg-brand/5 rounded-full -translate-y-1/2 translate-x-1/3"></div>
      <div class="relative z-10">
        <h1 class="text-3xl font-black text-slate-800 tracking-tight mb-2">Institutional Profile</h1>
        <p class="text-slate-500 text-sm leading-relaxed">Manage your personal information, research credentials, and academic identifiers.</p>
      </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-12 gap-8">
      <!-- Profile Sidebar -->
      <div class="lg:col-span-4 space-y-6">
        <!-- Avatar Card -->
        <div class="bg-white rounded-3xl border border-slate-200 shadow-sm p-8 text-center flex flex-col items-center relative overflow-hidden">
          <div class="absolute right-0 bottom-0 w-32 h-32 bg-slate-50 rounded-full translate-x-1/2 translate-y-1/2"></div>
          <div class="w-24 h-24 rounded-3xl bg-brand shadow-xl shadow-brand/30 flex items-center justify-center text-4xl font-black text-white mb-6 rotate-3 relative z-10">
            {{ getInitials(auth.user?.name) }}
          </div>
          <h2 class="text-xl font-black text-slate-800 relative z-10">{{ auth.user?.name }}</h2>
          <p class="text-[11px] font-black text-slate-400 uppercase tracking-widest mt-1 relative z-10">{{ auth.user?.email }}</p>

          <div class="mt-6 pt-6 border-t border-slate-100 w-full relative z-10">
            <p class="text-[10px] font-black text-slate-400 uppercase tracking-widest mb-3">System Roles</p>
            <div class="flex flex-wrap justify-center gap-2">
              <span
                v-for="r in auth.user?.roles"
                :key="r.id"
                class="px-3 py-1 rounded-full bg-brand/10 border border-brand/20 text-brand text-[10px] font-black uppercase tracking-widest"
              >
                {{ r.name }}
              </span>
              <span v-if="!auth.user?.roles?.length" class="text-xs font-black text-slate-300 italic">No roles assigned</span>
            </div>
          </div>
        </div>

        <!-- Academic Identifiers -->
        <div class="bg-white rounded-3xl border border-slate-200 shadow-sm p-6">
          <h3 class="text-[10px] font-black text-slate-400 uppercase tracking-[0.2em] mb-5 flex items-center gap-2">
            <span class="w-1 h-3 bg-brand rounded-full"></span> Academic Identifiers
          </h3>
          <div class="space-y-4">
            <div>
              <p class="text-[10px] font-black text-slate-400 uppercase tracking-widest mb-1.5">ORCID iD</p>
              <div class="font-black text-sm bg-slate-50 border border-slate-100 rounded-xl p-3 truncate" :class="auth.user?.orcid_id ? 'text-brand' : 'text-slate-300 italic'">
                {{ auth.user?.orcid_id || 'Not linked' }}
              </div>
            </div>
            <div>
              <p class="text-[10px] font-black text-slate-400 uppercase tracking-widest mb-1.5">Google Scholar</p>
              <div class="font-black text-sm bg-slate-50 border border-slate-100 rounded-xl p-3 truncate" :class="auth.user?.google_scholar_id ? 'text-slate-700' : 'text-slate-300 italic'">
                {{ auth.user?.google_scholar_id || 'Not linked' }}
              </div>
            </div>
            <div>
              <p class="text-[10px] font-black text-slate-400 uppercase tracking-widest mb-1.5">Scopus ID</p>
              <div class="font-black text-sm bg-slate-50 border border-slate-100 rounded-xl p-3 truncate" :class="auth.user?.scopus_id ? 'text-slate-700' : 'text-slate-300 italic'">
                {{ auth.user?.scopus_id || 'Not linked' }}
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- Edit Form -->
      <div class="lg:col-span-8">
        <div class="bg-white rounded-3xl border border-slate-200 shadow-sm p-8">
          <h2 class="text-[10px] font-black text-slate-400 uppercase tracking-[0.2em] mb-8 flex items-center gap-2">
            <span class="w-1 h-3 bg-brand rounded-full"></span> Security & Identity Settings
          </h2>

          <form @submit.prevent="updateProfile" class="space-y-6">
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-6">
              <div>
                <label class="block text-[11px] font-black text-slate-400 uppercase tracking-widest mb-2 ml-1">Full Display Name</label>
                <input v-model="form.name" type="text" class="input" placeholder="Enter your full name" />
              </div>
              <div>
                <label class="block text-[11px] font-black text-slate-400 uppercase tracking-widest mb-2 ml-1">Email Address</label>
                <input v-model="form.email" type="email" class="input" placeholder="email@institution.edu" />
              </div>
            </div>

            <div>
              <label class="block text-[11px] font-black text-slate-400 uppercase tracking-widest mb-2 ml-1">ORCID iD</label>
              <input v-model="form.orcid_id" type="text" class="input" placeholder="0000-0000-0000-0000" />
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-6">
              <div>
                <label class="block text-[11px] font-black text-slate-400 uppercase tracking-widest mb-2 ml-1">Google Scholar ID</label>
                <input v-model="form.google_scholar_id" type="text" class="input" placeholder="Enter Scholar ID" />
              </div>
              <div>
                <label class="block text-[11px] font-black text-slate-400 uppercase tracking-widest mb-2 ml-1">Scopus ID</label>
                <input v-model="form.scopus_id" type="text" class="input" placeholder="Enter Scopus ID" />
              </div>
            </div>

            <div>
              <label class="block text-[11px] font-black text-slate-400 uppercase tracking-widest mb-2 ml-1">LinkedIn Profile URL</label>
              <input v-model="form.linkedin_url" type="url" class="input" placeholder="https://linkedin.com/in/..." />
            </div>

            <div>
              <label class="block text-[11px] font-black text-slate-400 uppercase tracking-widest mb-2 ml-1">Professional Bio & Research Focus</label>
              <textarea v-model="form.bio" rows="5" class="input resize-none" placeholder="Describe your research interests and academic specialization..."></textarea>
            </div>

            <div class="pt-4 flex items-center gap-4 border-t border-slate-100">
              <button type="submit" class="btn btn-primary px-8 h-12 shadow-lg shadow-brand/20">
                Save Profile Updates
              </button>
              <p class="text-xs text-slate-400 font-medium">Changes are reflected immediately across all RDRIMS modules.</p>
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

const auth = useAuthStore()
const notif = useNotificationStore()

const form = reactive({
  name: '',
  email: '',
  orcid_id: '',
  bio: '',
  google_scholar_id: '',
  scopus_id: '',
  linkedin_url: ''
})

onMounted(() => {
  if (auth.user) {
    Object.assign(form, {
      name: auth.user.name || '',
      email: auth.user.email || '',
      orcid_id: auth.user.orcid_id || '',
      bio: auth.user.bio || '',
      google_scholar_id: auth.user.google_scholar_id || '',
      scopus_id: auth.user.scopus_id || '',
      linkedin_url: auth.user.linkedin_url || ''
    })
  }
})

async function updateProfile() {
  const ok = await auth.updateProfile(form)
  if (ok) notif.success('Profile updated successfully!')
  else notif.error('Profile update failed — please try again.')
}
</script>
