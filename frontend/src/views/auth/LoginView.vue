<template>
  <div class="min-h-screen bg-[#eff4f8] flex items-center justify-center p-4 card">
    <div class="w-full max-w-[440px] bg-white rounded-[24px] shadow-2xl overflow-hidden flex flex-col">
      <!-- Header -->
      <div class="bg-[#004e8d] p-8 text-white text-center relative">
        <div class="absolute top-0 left-0 w-full h-full opacity-10 pointer-events-none">
           <!-- Subtle pattern could go here -->
        </div>
        <div class="w-16 h-16 bg-white/20 rounded-2xl mx-auto mb-4 flex items-center justify-center text-3xl">🧬</div>
        <h1 class="text-2xl font-bold">RDRIMS Portal</h1>
        <p class="text-blue-200 text-xs mt-2 font-medium">Research & Development Management System</p>
      </div>

      <!-- Form -->
      <div class="p-8 lg:p-10">
        <form @submit.prevent="handleLogin" class="flex flex-col gap-6">
          <div v-if="auth.error" class="bg-rose-50 border border-rose-100 text-rose-600 px-4 py-3 rounded-xl text-xs font-bold animate-pulse">
             ⚠️ {{ auth.error }}
          </div>

          <div>
            <label class="block text-[11px] font-bold text-gray-400 uppercase tracking-widest mb-2 ml-1">E-mail Address</label>
            <div class="relative group">
              <span class="absolute left-4 top-1/2 -translate-y-1/2 text-gray-400 transition-colors group-focus-within:text-[#004e8d]">📧</span>
              <input v-model="form.email" type="email" required placeholder="name@example.com"
                class="w-full bg-gray-50 border border-gray-200 rounded-xl pl-12 pr-4 py-3.5 text-sm focus:outline-none focus:ring-2 focus:ring-[#004e8d]/20 focus:border-[#004e8d] transition-all" />
            </div>
          </div>

          <div>
            <div class="flex justify-between items-center mb-2 ml-1">
              <label class="text-[11px] font-bold text-gray-400 uppercase tracking-widest">Password</label>
              <a href="#" class="text-[11px] font-bold text-[#004e8d] hover:underline">Forgot?</a>
            </div>
            <div class="relative group">
              <span class="absolute left-4 top-1/2 -translate-y-1/2 text-gray-400 transition-colors group-focus-within:text-[#004e8d]">🔒</span>
              <input v-model="form.password" :type="showPass ? 'text' : 'password'" required placeholder="••••••••"
                class="w-full bg-gray-50 border border-gray-200 rounded-xl pl-12 pr-12 py-3.5 text-sm focus:outline-none focus:ring-2 focus:ring-[#004e8d]/20 focus:border-[#004e8d] transition-all" />
              <button type="button" @click="showPass = !showPass" class="absolute right-4 top-1/2 -translate-y-1/2 text-gray-400 hover:text-gray-600">
                {{ showPass ? '👁️' : '🕶️' }}
              </button>
            </div>
          </div>

          <button type="submit" :disabled="loading"
            class="w-full bg-[#004e8d] text-white font-bold py-4 rounded-xl shadow-lg hover:bg-[#003d6e] transform active:scale-[0.98] transition-all disabled:opacity-70 flex items-center justify-center gap-2">
            <span v-if="loading" class="w-5 h-5 border-2 border-white/30 border-t-white rounded-full animate-spin"></span>
            {{ loading ? 'Authenticating...' : 'Sign In to Dashboard' }}
          </button>
        </form>

        <div class="mt-8 pt-6 border-t border-gray-100 text-center">
          <p class="text-xs text-gray-500 font-medium">
            Don't have an investigator account? 
            <router-link to="/register" class="text-[#004e8d] font-bold hover:underline">Request Account</router-link>
          </p>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, reactive } from 'vue'
import { useRouter } from 'vue-router'
import { useAuthStore } from '@/stores/auth'

const auth = useAuthStore()
const router = useRouter()
const loading = ref(false)
const showPass = ref(false)

const form = reactive({ email: '', password: '' })

async function handleLogin() {
  loading.value = true
  const success = await auth.login(form.email, form.password)
  loading.value = false
  if (success) router.push('/dashboard')
}
</script>
