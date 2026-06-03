<template>
  <div class="min-h-screen bg-slate-50 flex flex-col items-center justify-center p-6 relative overflow-hidden">
    <!-- Subtle Background Elements -->
    <div class="absolute top-0 right-0 w-1/3 h-1/3 bg-brand/5 rounded-full blur-[120px] -translate-y-1/2 translate-x-1/2"></div>
    <div class="absolute bottom-0 left-0 w-1/4 h-1/4 bg-brand/5 rounded-full blur-[100px] translate-y-1/3 -translate-x-1/4"></div>

    <div class="w-full max-w-md animate-fade">
      <!-- Branding / Logo -->
      <div class="flex flex-col items-center mb-10">
        <div class="h-14 w-14 bg-brand rounded-2xl flex items-center justify-center text-white font-black text-2xl shadow-lg shadow-brand/20 mb-6 group hover:rotate-6 transition-transform">
          R
        </div>
        <h1 class="text-3xl font-black text-slate-800 tracking-tight">Set New Password</h1>
        <p class="text-slate-500 font-medium text-sm mt-1 capitalize tracking-widest text-center">Security Update</p>
      </div>

      <!-- Reset Password Card -->
      <div class="bg-white rounded-[32px] border border-slate-200/60 shadow-2xl shadow-slate-200/50 p-10 relative overflow-hidden">
        <div class="absolute top-0 left-0 w-full h-1.5 bg-brand"></div>
        
        <form @submit.prevent="submit" class="space-y-6">
          <div class="space-y-2">
            <label class="block text-[10px] font-black text-slate-400 capitalize tracking-widest ml-1">Account Email</label>
            <input v-model="form.email" type="email" required readonly class="w-full bg-slate-50 border border-slate-100 rounded-2xl px-4 py-4 text-sm font-bold text-slate-400 cursor-not-allowed outline-none" />
          </div>

          <div class="space-y-2">
            <label class="block text-[10px] font-black text-slate-400 capitalize tracking-widest ml-1">New Password</label>
            <input v-model="form.password" type="password" required minlength="8" placeholder="••••••••" class="w-full bg-slate-50 border border-slate-200 rounded-2xl px-4 py-4 text-sm font-medium focus:bg-white focus:border-brand focus:ring-4 focus:ring-brand/10 transition-all outline-none" />
          </div>

          <div class="space-y-2">
            <label class="block text-[10px] font-black text-slate-400 capitalize tracking-widest ml-1">Confirm New Password</label>
            <input v-model="form.password_confirmation" type="password" required minlength="8" placeholder="••••••••" class="w-full bg-slate-50 border border-slate-200 rounded-2xl px-4 py-4 text-sm font-medium focus:bg-white focus:border-brand focus:ring-4 focus:ring-brand/10 transition-all outline-none" />
          </div>

          <button 
            type="submit" 
            :disabled="loading"
            class="w-full bg-brand text-white font-black capitalize tracking-widest text-xs py-5 rounded-2xl shadow-xl shadow-brand/20 hover:bg-brand-hover hover:scale-[1.02] active:scale-[0.98] transition-all disabled:opacity-70 flex items-center justify-center gap-3"
          >
            <span v-if="loading" class="w-5 h-5 border-3 border-white/30 border-t-white rounded-full animate-spin"></span>
            <span>{{ loading ? 'Updating...' : 'Update Password' }}</span>
          </button>
        </form>

        <div class="mt-10 pt-8 border-t border-slate-100 text-center">
          <router-link to="/login" class="text-[11px] font-black text-slate-400 capitalize tracking-widest hover:text-brand transition-colors">
            Cancel and Sign In
          </router-link>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { reactive, ref, onMounted } from 'vue'
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
