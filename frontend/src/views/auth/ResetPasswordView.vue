<template>
  <div class="min-h-screen flex items-center justify-center bg-slate-50 py-12 px-4 sm:px-6 lg:px-8">
    <div class="max-w-md w-full space-y-8 bg-white p-10 rounded-3xl shadow-xl border border-slate-100">
      <div>
        <h2 class="mt-2 text-center text-3xl font-black text-slate-900 tracking-tight">Set New Password</h2>
        <p class="mt-3 text-center text-sm font-medium text-slate-500">Must be at least 8 characters.</p>
      </div>
      <form class="mt-8 space-y-6" @submit.prevent="submit">
        <div class="space-y-4">
          <div>
            <label class="block text-xs font-black text-slate-400 uppercase tracking-widest mb-2 ml-1">Email address</label>
            <input v-model="form.email" type="email" required class="appearance-none relative block w-full px-4 py-4 border border-slate-200 placeholder-slate-400 text-slate-900 rounded-2xl focus:ring-brand font-bold text-sm bg-slate-50 opacity-70 cursor-not-allowed" readonly />
          </div>
          <div>
            <label class="block text-xs font-black text-slate-400 uppercase tracking-widest mb-2 ml-1">New Password</label>
            <input v-model="form.password" type="password" required class="appearance-none relative block w-full px-4 py-4 border border-slate-200 placeholder-slate-400 text-slate-900 rounded-2xl focus:outline-none focus:ring-2 focus:ring-brand focus:border-brand font-bold text-sm bg-slate-50 hover:bg-slate-100 transition-colors" placeholder="••••••••" />
          </div>
          <div>
            <label class="block text-xs font-black text-slate-400 uppercase tracking-widest mb-2 ml-1">Confirm Password</label>
            <input v-model="form.password_confirmation" type="password" required class="appearance-none relative block w-full px-4 py-4 border border-slate-200 placeholder-slate-400 text-slate-900 rounded-2xl focus:outline-none focus:ring-2 focus:ring-brand focus:border-brand font-bold text-sm bg-slate-50 hover:bg-slate-100 transition-colors" placeholder="••••••••" />
          </div>
        </div>
        <div>
          <button type="submit" :disabled="loading" class="group relative w-full flex justify-center py-4 px-4 border border-transparent text-[11px] font-black uppercase tracking-widest rounded-2xl text-white bg-brand hover:bg-brand-dark focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-brand transition-all shadow-lg shadow-brand/25 disabled:opacity-60 disabled:cursor-not-allowed">
            {{ loading ? 'Resetting...' : 'Reset Password' }}
          </button>
        </div>
      </form>
    </div>
  </div>
</template>

<script setup>
import { ref, reactive, onMounted } from 'vue'
import { useRouter, useRoute } from 'vue-router'
import { useNotificationStore } from '@/stores/notification'
import api from '@/services/api'

const router = useRouter()
const route = useRoute()
const notif = useNotificationStore()
const loading = ref(false)

const form = reactive({
  email: '',
  password: '',
  password_confirmation: '',
  token: ''
})

onMounted(() => {
  form.email = route.query.email || ''
  form.token = route.query.token || route.params.token || ''
})

async function submit() {
  if(form.password !== form.password_confirmation) {
     notif.error('Passwords do not match');
     return;
  }
  loading.value = true
  try {
    await api.post('/reset-password', form)
    notif.success('Password has been successfully reset!')
    router.push('/login')
  } catch (err) {
    notif.error(err.response?.data?.message || 'Failed to reset password')
  } finally {
    loading.value = false
  }
}
</script>
