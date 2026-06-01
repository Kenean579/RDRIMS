<template>
  <header class="bg-white border-b border-slate-200 sticky top-0 z-50">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
      <div class="flex justify-between h-16">
        <!-- Logo and brand name -->
        <div class="flex items-center">
          <router-link to="/" class="shrink-0 flex items-center gap-2 group">
            <div class="h-8 w-8 bg-brand rounded-lg flex items-center justify-center text-white font-black text-xl shadow-sm group-hover:bg-brand-dark transition-colors">
              R
            </div>
            <span class="font-black text-xl tracking-tight text-slate-800">{{ appName }}</span>
          </router-link>
          
          <!-- Desktop Navigation -->
          <nav class="hidden md:ml-10 md:flex md:space-x-8">
            <router-link 
              v-for="link in navLinks" 
              :key="link.path" 
              :to="link.path"
              class="inline-flex items-center px-1 pt-1 border-b-2 text-sm font-bold transition-colors"
              :class="[
                $route.path === link.path 
                  ? 'border-brand text-brand' 
                  : 'border-transparent text-slate-500 hover:text-slate-900 hover:border-slate-300'
              ]"
            >
              {{ link.name }}
            </router-link>
          </nav>
        </div>

        <!-- Right side actions -->
        <div class="flex items-center gap-4">
          <!-- Language Toggle -->
          <button @click="toggleLanguage" class="px-3 py-1.5 rounded-full border border-slate-200 bg-slate-50 text-xs font-bold text-slate-600 hover:bg-slate-100 transition-colors">
            {{ currentLang === 'en' ? 'አማ' : 'EN' }}
          </button>

          <!-- Auth Buttons -->
          <div class="hidden md:flex items-center gap-3">
            <template v-if="auth.isAuthenticated">
              <router-link to="/app/dashboard" class="px-4 py-2 rounded-xl text-sm font-bold text-brand bg-brand/10 hover:bg-brand/20 transition-colors">
                Dashboard
              </router-link>
              <button @click="auth.logout()" class="text-sm font-bold text-slate-500 hover:text-rose-600 transition-colors">
                Sign Out
              </button>
            </template>
            <template v-else>
              <router-link to="/login" class="text-sm font-bold text-slate-600 hover:text-brand transition-colors">
                Sign In
              </router-link>
              <router-link v-if="allowRegistration" to="/register" class="px-4 py-2 rounded-xl text-sm font-bold text-white bg-brand hover:bg-brand-dark shadow-sm transition-colors">
                Sign Up
              </router-link>
            </template>
          </div>

          <!-- Mobile menu button -->
          <button @click="mobileMenuOpen = !mobileMenuOpen" class="md:hidden p-2 rounded-md text-slate-400 hover:text-slate-500 hover:bg-slate-100">
            <svg v-if="!mobileMenuOpen" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
            </svg>
            <svg v-else class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
            </svg>
          </button>
        </div>
      </div>
    </div>

    <!-- Mobile Navigation -->
    <div v-show="mobileMenuOpen" class="md:hidden border-t border-slate-200 bg-white">
      <div class="pt-2 pb-4 space-y-1">
        <router-link 
          v-for="link in navLinks" 
          :key="link.path"
          :to="link.path"
          @click="mobileMenuOpen = false"
          class="block pl-3 pr-4 py-2 border-l-4 text-base font-bold transition-colors"
          :class="[
            $route.path === link.path 
              ? 'border-brand text-brand bg-brand/5' 
              : 'border-transparent text-slate-500 hover:text-slate-900 hover:bg-slate-50 hover:border-slate-300'
          ]"
        >
          {{ link.name }}
        </router-link>
      </div>
      <div v-if="auth.isAuthenticated" class="pt-4 pb-3 border-t border-slate-200 px-4 flex flex-col gap-3">
        <router-link to="/app/dashboard" @click="mobileMenuOpen = false" class="block text-center w-full px-4 py-2 text-base font-bold rounded-xl text-brand bg-brand/10">
          Go to Dashboard
        </router-link>
        <button @click="auth.logout(); mobileMenuOpen = false" class="block text-center w-full px-4 py-2 text-base font-bold rounded-xl text-slate-500 bg-slate-50">
          Sign Out
        </button>
      </div>
      <div v-else class="pt-4 pb-3 border-t border-slate-200 px-4 flex flex-col gap-3">
        <router-link to="/login" @click="mobileMenuOpen = false" class="block text-center w-full px-4 py-2 text-base font-bold rounded-xl text-slate-600 bg-slate-100">
          Sign In
        </router-link>
        <router-link v-if="allowRegistration" to="/register" @click="mobileMenuOpen = false" class="block text-center w-full px-4 py-2 text-base font-bold rounded-xl text-white bg-brand shadow-sm">
          Sign Up
        </router-link>
      </div>
    </div>
  </header>
</template>

<script setup>
import { ref, computed } from 'vue'
import { useAuthStore } from '@/stores/auth'
import { useLookupStore } from '@/stores/lookup'
import api from '@/services/api'

const auth = useAuthStore()
const lookupStore = useLookupStore()

const mobileMenuOpen = ref(false)

const appName = computed(() => lookupStore.getSetting('app_name', 'RDRIMS'))
const allowRegistration = computed(() => lookupStore.getSetting('allow_public_registration', 'true') === 'true')

const navLinks = [
  { name: 'Home', path: '/' },
  { name: 'Research Calls', path: '/calls' },
  { name: 'Events', path: '/events' },
  { name: 'Publications', path: '/publications' },
  { name: 'Researchers', path: '/researchers' },
  { name: 'Community', path: '/community' }
]

const currentLang = ref(localStorage.getItem('language') || 'en')

async function toggleLanguage() {
  const newLang = currentLang.value === 'en' ? 'am' : 'en'
  currentLang.value = newLang
  localStorage.setItem('language', newLang)
  try {
    if (auth.isAuthenticated) {
      await api.put('/language-preference', { language: newLang })
    }
  } catch(e) {}
}
</script>
