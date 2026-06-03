<template>
  <div class="min-h-screen bg-slate-50 flex flex-col items-center justify-center p-6 relative overflow-hidden">
    <!-- Subtle Background Elements -->
    <div class="absolute top-0 right-0 w-1/3 h-1/3 bg-brand/5 rounded-full blur-[120px] -translate-y-1/2 translate-x-1/2"></div>
    <div class="absolute bottom-0 left-0 w-1/4 h-1/4 bg-brand/5 rounded-full blur-[100px] translate-y-1/3 -translate-x-1/4"></div>

    <div class="w-full max-w-md animate-fade">
      <!-- Branding / Logo -->
      <div class="flex flex-col items-center mb-5">
        <div class="h-14 w-14 bg-brand rounded-2xl flex items-center justify-center text-white font-bold text-2xl shadow-lg shadow-brand/20 mb-6 group hover:rotate-6 transition-transform">
          R
        </div>
        <h1 class="text-xl font-bold text-slate-800 tracking-tight">Recover Account</h1>
        <p class="text-slate-500 font-medium text-sm mt-1 capitalize tracking-widest text-center">We'll help you back in</p>
      </div>

      <!-- Forgot Password Card -->
      <div class="bg-white rounded-[32px] border border-slate-200/60 shadow-2xl shadow-slate-200/50 p-5 relative overflow-hidden">
        <div class="absolute top-0 left-0 w-full h-1.5 bg-brand"></div>
        
        <form @submit.prevent="submit" class="space-y-8">
          <p class="text-xs text-slate-500 font-medium text-center px-4 leading-relaxed">
            Enter your email address and we'll send you a secure link to reset your password.
          </p>

          <div class="space-y-2">
            <label class="block text-[10px] font-bold text-slate-400 capitalize tracking-widest ml-1">E-mail Address</label>
            <div class="relative group">
              <div class="absolute left-4 top-1/2 -translate-y-1/2 text-slate-400 group-focus-within:text-brand transition-colors">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" /></svg>
              </div>
              <input 
                v-model="email" 
                type="email" 
                required 
                placeholder="name@university.edu"
                class="w-full bg-slate-50 border border-slate-200 rounded-2xl pl-12 pr-4 py-4 text-sm font-medium focus:bg-white focus:border-brand focus:ring-4 focus:ring-brand/10 transition-all outline-none" 
              />
            </div>
          </div>

          <button 
            type="submit" 
            :disabled="loading"
            class="w-full bg-brand text-white font-bold capitalize tracking-widest text-xs py-5 rounded-2xl shadow-xl shadow-brand/20 hover:bg-brand-hover hover:scale-[1.02] active:scale-[0.98] transition-all disabled:opacity-70 flex items-center justify-center gap-3"
          >
            <span v-if="loading" class="w-5 h-5 border-3 border-white/30 border-t-white rounded-full animate-spin"></span>
            <span>{{ loading ? 'Sending Mail...' : 'Send Recovery Link' }}</span>
          </button>
        </form>

        <div class="mt-5 pt-4 border-t border-slate-100 text-center">
          <router-link to="/login" class="text-[11px] font-bold text-brand capitalize tracking-widest hover:underline underline-offset-4 flex items-center justify-center gap-2">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M10 19l-7-7m0 0l7-7m-7 7h18" /></svg>
            Back to Sign In
          </router-link>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref } from 'vue'
import { useNotificationStore } from '@/stores/notification'
import api from '@/services/api'

const email = ref('')
const loading = ref(false)
const notif = useNotificationStore()

async function submit() {
  loading.value = true
  try {
    await api.post('/forgot-password', { email: email.value })
    notif.success('Password reset link sent to your email!')
    email.value = ''
  } catch (err) {
    notif.error(err.response?.data?.message || 'Failed to send reset link')
  } finally {
    loading.value = false
  }
}
</script>
