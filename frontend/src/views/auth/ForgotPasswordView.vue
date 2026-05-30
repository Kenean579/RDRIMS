<template>
  <div class="min-h-screen flex items-center justify-center bg-slate-50 py-12 px-4 sm:px-6 lg:px-8">
    <div class="max-w-md w-full space-y-8 bg-white p-10 rounded-3xl shadow-xl border border-slate-100">
      <div>
        <h2 class="mt-2 text-center text-3xl font-black text-slate-900 tracking-tight">Forgot Password?</h2>
        <p class="mt-3 text-center text-sm font-medium text-slate-500">No worries, we'll send you reset instructions.</p>
      </div>
      <form class="mt-8 space-y-6" @submit.prevent="submit">
        <div>
          <label for="email" class="block text-xs font-black text-slate-400 uppercase tracking-widest mb-2 ml-1">Email address</label>
          <input id="email" v-model="email" name="email" type="email" autocomplete="email" required class="appearance-none relative block w-full px-4 py-4 border border-slate-200 placeholder-slate-400 text-slate-900 rounded-2xl focus:outline-none focus:ring-2 focus:ring-brand focus:border-brand font-bold text-sm bg-slate-50 hover:bg-slate-100 transition-colors" placeholder="Enter your email" />
        </div>
        <div>
          <button type="submit" :disabled="loading" class="group relative w-full flex justify-center py-4 px-4 border border-transparent text-[11px] font-black uppercase tracking-widest rounded-2xl text-white bg-brand hover:bg-brand-dark focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-brand transition-all shadow-lg shadow-brand/25 disabled:opacity-60 disabled:cursor-not-allowed">
            <span class="absolute left-0 inset-y-0 flex items-center pl-3 text-brand-darker">
              <svg class="h-5 w-5 group-hover:text-white transition-colors" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" /></svg>
            </span>
            {{ loading ? 'Sending...' : 'Send Reset Link' }}
          </button>
        </div>
        <div class="text-center mt-6">
          <router-link to="/login" class="font-bold text-sm text-brand hover:text-brand-dark transition-colors">← Back to Login</router-link>
        </div>
      </form>
    </div>
  </div>
</template>

<script setup>
import { ref } from 'vue'
import { useRouter } from 'vue-router'
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
