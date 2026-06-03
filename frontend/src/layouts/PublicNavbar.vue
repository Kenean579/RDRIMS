  <template>
  <header class="bg-white border-b border-slate-200 sticky top-0 z-50">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-5">
      <div class="flex justify-between h-16">
        <!-- Logo and brand name -->
        <div class="flex items-center">
          <router-link to="/" class="shrink-0 flex items-center gap-2 group">
            <div class="h-8 w-8 rounded-lg flex items-center justify-center text-brand font-bold text-xl shadow-sm group-hover:text-brand-dark transition-colors">
              R
            </div>
            <span class="font-bold text-xl tracking-tight text-slate-800">{{ appName }}</span>
          </router-link>
          
          <!-- Desktop Navigation -->
          <nav class="hidden md:ml-10 md:flex md:space-x-8">
            <router-link 
              v-for="link in localizedNavLinks" 
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
          <button @click="toggleLanguage" class="px-3 py-1.5 rounded-full border border-slate-200 text-xs font-bold text-slate-600 hover:text-brand transition-colors">
            {{ currentLang === 'en' ? 'አማ' : 'EN' }}
          </button>

          <!-- Auth Buttons -->
          <div class="hidden md:flex items-center gap-3">
            <template v-if="auth.isAuthenticated">
              <router-link to="/app/dashboard" class="px-4 py-2 rounded-xl text-sm font-bold text-brand border border-brand hover:text-white hover:bg-brand transition-colors">
                {{ $t('nav.dashboard') }}
              </router-link>
              <button @click="auth.logout()" class="text-sm font-bold text-slate-500 hover:text-rose-600 transition-colors">
                {{ $t('nav.signOut') }}
              </button>
            </template>
            <template v-else>
              <router-link to="/login" class="text-sm font-bold text-slate-600 hover:text-brand transition-colors">
                {{ $t('nav.signIn') }}
              </router-link>
              <router-link v-if="allowRegistration" to="/register" class="px-4 py-2 rounded-xl text-sm font-bold text-brand border-2 border-brand hover:bg-brand hover:text-white transition-colors">
                {{ $t('nav.signUp') }}
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
          v-for="link in localizedNavLinks" 
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
        <router-link to="/app/dashboard" @click="mobileMenuOpen = false" class="block text-center w-full px-4 py-2 text-base font-bold rounded-xl text-brand border border-brand">
          {{ $t('nav.goToDashboard') }}
        </router-link>
        <button @click="auth.logout(); mobileMenuOpen = false" class="block text-center w-full px-4 py-2 text-base font-bold rounded-xl text-slate-500 border border-slate-300">
          {{ $t('nav.signOut') }}
        </button>
      </div>
      <div v-else class="pt-4 pb-3 border-t border-slate-200 px-4 flex flex-col gap-3">
        <router-link to="/login" @click="mobileMenuOpen = false" class="block text-center w-full px-4 py-2 text-base font-bold rounded-xl text-slate-600 border border-slate-300">
          {{ $t('nav.signIn') }}
        </router-link>
        <router-link v-if="allowRegistration" to="/register" @click="mobileMenuOpen = false" class="block text-center w-full px-4 py-2 text-base font-bold rounded-xl text-brand border-2 border-brand">
          {{ $t('nav.signUp') }}
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
import { useI18n } from 'vue-i18n'

const { t, locale } = useI18n()
const auth = useAuthStore()
const lookupStore = useLookupStore()

const mobileMenuOpen = ref(false)

const appName = computed(() => lookupStore.getSetting('app_name', 'RDRIMS'))
const allowRegistration = computed(() => lookupStore.getSetting('allow_public_registration', 'true') === 'true')

const localizedNavLinks = computed(() => [
  { name: t('nav.home'), path: '/' },
  { name: t('nav.calls'), path: '/calls' },
  { name: t('nav.events'), path: '/events' },
  { name: t('nav.publications'), path: '/publications' },
  { name: t('nav.researchers'), path: '/researchers' },
  { name: t('nav.community'), path: '/community' }
])

const currentLang = ref(localStorage.getItem('language') || 'en')

async function toggleLanguage() {
  const newLang = currentLang.value === 'en' ? 'am' : 'en'
  currentLang.value = newLang
  locale.value = newLang
  localStorage.setItem('language', newLang)
  try {
    if (auth.isAuthenticated) {
      await api.put('/language-preference', { locale: newLang })
    }
  } catch(e) {}
}
</script>
