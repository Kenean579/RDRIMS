<template>
  <div class="min-h-screen bg-slate-50 flex flex-col items-center justify-center p-6 relative overflow-hidden">
    <!-- Subtle Background Elements -->
    <div class="absolute top-0 right-0 w-1/3 h-1/3 bg-brand/5 rounded-full blur-[120px] -translate-y-1/2 translate-x-1/2"></div>
    <div class="absolute bottom-0 left-0 w-1/4 h-1/4 bg-brand/5 rounded-full blur-[100px] translate-y-1/3 -translate-x-1/4"></div>

    <div class="w-full max-w-lg animate-fade">
      <!-- Branding / Logo -->
      <div class="flex flex-col items-center mb-5">
        <div class="h-14 w-14 bg-brand rounded-2xl flex items-center justify-center text-white font-bold text-2xl mb-6 hover:rotate-6 transition-transform cursor-pointer" @click="router.push('/')">
          R
        </div>
        <h1 class="text-xl font-bold text-slate-800 tracking-tight">Create Account</h1>
        <p class="text-slate-500 font-medium text-sm mt-1">Join the {{ appName }} Research Community</p>
      </div>

      <!-- Register Card -->
      <div class="bg-white rounded-[32px] border border-slate-100/60 shadow-2xl shadow-slate-200/50 p-5 relative overflow-hidden">
        <div class="absolute top-0 left-0 w-full h-1.5 bg-brand"></div>
        
        <form @submit.prevent="handleRegister" class="grid grid-cols-1 md:grid-cols-2 gap-6">
          <!-- Error Alert (Full Width) -->
          <div v-if="auth.error" class="md:col-span-2 bg-rose-50 border border-rose-100 text-rose-600 px-5 py-4 rounded-2xl text-xs font-bold flex items-center gap-3">
            <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" /></svg>
            {{ auth.error }}
          </div>

          <!-- Full Name -->
          <div class="space-y-2 md:col-span-2">
            <label class="block text-[10px] font-medium text-slate-400 ml-1">Full Name *</label>
            <input v-model="form.name" type="text" required placeholder="Dr. Abebe Kebede" class="w-full bg-slate-50 border border-slate-100 rounded-2xl px-4 py-4 text-sm font-medium focus:bg-white focus:border-brand focus:ring-4 focus:ring-brand/10 transition-all outline-none" />
          </div>

          <!-- Email -->
          <div class="space-y-2 md:col-span-2">
            <label class="block text-[10px] font-medium text-slate-400 ml-1">E-mail Address *</label>
            <input v-model="form.email" type="email" required placeholder="name@university.edu" class="w-full bg-slate-50 border border-slate-100 rounded-2xl px-4 py-4 text-sm font-medium focus:bg-white focus:border-brand focus:ring-4 focus:ring-brand/10 transition-all outline-none" />
          </div>

          <!-- Department -->
          <div class="space-y-2 md:col-span-2">
            <label class="block text-[10px] font-medium text-slate-400 ml-1">Affiliated Department</label>
            <select v-model="form.department_id" class="w-full bg-slate-50 border border-slate-100 rounded-2xl px-4 py-4 text-sm font-medium focus:bg-white focus:border-brand focus:ring-4 focus:ring-brand/10 transition-all outline-none">
              <option value="">Select Department (Optional)</option>
              <option v-for="dept in departments" :key="dept.id" :value="dept.id">{{ dept.name }}</option>
            </select>
          </div>

          <!-- Password -->
          <div class="space-y-2">
            <label class="block text-[10px] font-medium text-slate-400 ml-1">Password *</label>
            <input v-model="form.password" type="password" required minlength="8" placeholder="••••••••" class="w-full bg-slate-50 border border-slate-100 rounded-2xl px-4 py-4 text-sm font-medium focus:bg-white focus:border-brand focus:ring-4 focus:ring-brand/10 transition-all outline-none" />
          </div>

          <!-- Confirm Password -->
          <div class="space-y-2">
            <label class="block text-[10px] font-medium text-slate-400 ml-1">Confirm Password *</label>
            <input v-model="form.password_confirmation" type="password" required minlength="8" placeholder="••••••••" class="w-full bg-slate-50 border border-slate-100 rounded-2xl px-4 py-4 text-sm font-medium focus:bg-white focus:border-brand focus:ring-4 focus:ring-brand/10 transition-all outline-none" />
          </div>

          <!-- Submit -->
          <button 
            type="submit" 
            :disabled="auth.loading"
            class="md:col-span-2 w-full bg-brand text-white font-bold text-xs py-5 rounded-2xl shadow-xl shadow-brand/20 hover:bg-brand-hover hover:scale-[1.01] active:scale-[0.99] transition-all disabled:opacity-70 flex items-center justify-center gap-3 mt-4"
          >
            <span v-if="auth.loading" class="w-5 h-5 border-3 border-white/30 border-t-white rounded-full animate-spin"></span>
            <span>{{ auth.loading ? 'Creating Account...' : 'Continue to Dashboard' }}</span>
          </button>
        </form>

        <div class="mt-5 pt-4 border-t border-slate-100 text-center">
          <p class="text-[11px] font-medium text-slate-400">
            Already have an account? 
            <router-link to="/login" class="text-brand font-bold hover:underline underline-offset-4">Sign In Instead</router-link>
          </p>
        </div>
      </div>

      <!-- Footer Info -->
      <div class="mt-5 text-center">
        <p class="text-[10px] font-medium text-slate-400 flex items-center justify-center gap-4">
          <router-link to="/" class="hover:text-brand">Home</router-link>
          <span class="text-slate-200">•</span>
          <a href="#" class="hover:text-brand">Security Policy</a>
          <span class="text-slate-200">•</span>
          <a href="#" class="hover:text-brand">License Agreement</a>
        </p>
      </div>
    </div>
  </div>
</template>

<script setup>
import { reactive, ref, onMounted, computed } from 'vue'
import { useRouter } from 'vue-router'
import { useAuthStore } from '@/stores/auth'
import { useLookupStore } from '@/stores/lookup'
import api from '@/services/api'

const router = useRouter()
const auth = useAuthStore()
const lookupStore = useLookupStore()

const appName = computed(() => lookupStore.getSetting('app_name', 'RDRIMS'))
const departments = ref([])

const form = reactive({ 
  name: '', 
  email: '', 
  password: '', 
  password_confirmation: '', 
  department_id: '' 
})

onMounted(async () => { 
  try { 
    const { data } = await api.get('/departments')
    departments.value = data.data || data
  } catch (e) {} 
})

async function handleRegister() { 
  if (form.password !== form.password_confirmation) { 
    auth.error = 'Passwords do not match.'
    return 
  }
  
  const success = await auth.register({ 
    ...form, 
    department_id: form.department_id || null 
  })
  
  if (success) {
    router.push('/app/dashboard')
  }
}
</script>
