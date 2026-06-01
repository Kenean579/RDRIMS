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
        <h1 class="text-3xl font-black text-slate-800 tracking-tight">{{ appName }}</h1>
        <p class="text-slate-500 font-medium text-sm mt-2 uppercase tracking-widest">Portal Authentication</p>
      </div>

      <!-- Login Card -->
      <div class="bg-white rounded-[32px] border border-slate-200/60 shadow-2xl shadow-slate-200/50 p-10 relative overflow-hidden">
        <div class="absolute top-0 left-0 w-full h-1.5 bg-brand"></div>
        
        <form @submit.prevent="handleLogin" class="space-y-6">
          <!-- Error Alert -->
          <div v-if="auth.error" class="bg-rose-50 border border-rose-100 text-rose-600 px-5 py-4 rounded-2xl text-xs font-bold flex items-center gap-3">
            <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" /></svg>
            {{ auth.error }}
          </div>

          <!-- Email Field -->
          <div class="space-y-2">
            <label class="block text-[10px] font-black text-slate-400 uppercase tracking-widest ml-1">E-mail Address</label>
            <div class="relative group">
              <div class="absolute left-4 top-1/2 -translate-y-1/2 text-slate-400 group-focus-within:text-brand transition-colors">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 12a4 4 0 10-8 0 4 4 0 008 0zm0 0v1.5a2.5 2.5 0 005 0V12a9 9 0 10-9 9m4.5-1.206a8.959 8.959 0 01-4.5 1.206" /></svg>
              </div>
              <input 
                v-model="form.email" 
                type="email" 
                required 
                placeholder="name@university.edu"
                class="w-full bg-slate-50 border border-slate-200 rounded-2xl pl-12 pr-4 py-4 text-sm font-medium focus:bg-white focus:border-brand focus:ring-4 focus:ring-brand/10 transition-all outline-none" 
              />
            </div>
          </div>

          <!-- Password Field -->
          <div class="space-y-2">
            <div class="flex justify-between items-center px-1">
              <label class="text-[10px] font-black text-slate-400 uppercase tracking-widest">Password</label>
              <router-link to="/forgot-password" class="text-[10px] font-black text-brand uppercase tracking-widest hover:underline">Forgot?</router-link>
            </div>
            <div class="relative group">
              <div class="absolute left-4 top-1/2 -translate-y-1/2 text-slate-400 group-focus-within:text-brand transition-colors">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" /></svg>
              </div>
              <input 
                v-model="form.password" 
                :type="showPass ? 'text' : 'password'" 
                required 
                placeholder="••••••••"
                class="w-full bg-slate-50 border border-slate-200 rounded-2xl pl-12 pr-12 py-4 text-sm font-medium focus:bg-white focus:border-brand focus:ring-4 focus:ring-brand/10 transition-all outline-none" 
              />
              <button 
                type="button" 
                @click="showPass = !showPass" 
                class="absolute right-4 top-1/2 -translate-y-1/2 text-slate-300 hover:text-slate-500 transition-colors"
                title="Toggle Visibility"
              >
                <svg v-if="!showPass" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" /><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" /></svg>
                <svg v-else class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l18 18" /></svg>
              </button>
            </div>
          </div>

          <!-- Submit -->
          <button 
            type="submit" 
            :disabled="loading"
            class="w-full bg-brand text-white font-black uppercase tracking-widest text-xs py-5 rounded-2xl shadow-xl shadow-brand/20 hover:bg-brand-hover hover:scale-[1.02] active:scale-[0.98] transition-all disabled:opacity-70 flex items-center justify-center gap-3 mt-4"
          >
            <span v-if="loading" class="w-5 h-5 border-3 border-white/30 border-t-white rounded-full animate-spin"></span>
            <span>{{ loading ? 'Securing Access...' : 'Sign In to Portal' }}</span>
          </button>
        </form>

        <div class="mt-10 pt-8 border-t border-slate-100 text-center">
          <p class="text-[11px] font-bold text-slate-400 uppercase tracking-widest">
            New investigator? 
            <router-link to="/register" class="text-brand font-black hover:underline underline-offset-4">Request Access</router-link>
          </p>
        </div>
      </div>

      <!-- Footer Links -->
      <div class="flex justify-center gap-8 mt-8">
        <router-link to="/" class="text-[10px] font-black text-slate-400 uppercase tracking-widest hover:text-brand transition-colors">Home</router-link>
        <span class="text-slate-200">•</span>
        <a href="#" class="text-[10px] font-black text-slate-400 uppercase tracking-widest hover:text-brand transition-colors">Terms of Service</a>
        <span class="text-slate-200">•</span>
        <a href="#" class="text-[10px] font-black text-slate-400 uppercase tracking-widest hover:text-brand transition-colors">Privacy Policy</a>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, reactive, computed } from 'vue'
import { useRouter } from 'vue-router'
import { useAuthStore } from '@/stores/auth'
import { useLookupStore } from '@/stores/lookup'

const auth = useAuthStore()
const lookupStore = useLookupStore()
const router = useRouter()

const loading = ref(false)
const showPass = ref(false)
const form = reactive({ email: '', password: '' })

const appName = computed(() => lookupStore.getSetting('app_name', 'RDRIMS'))

async function handleLogin() {
  loading.value = true
  const success = await auth.login(form.email, form.password)
  loading.value = false
  if (success) {
    router.push('/app/dashboard')
  }
}
</script>
