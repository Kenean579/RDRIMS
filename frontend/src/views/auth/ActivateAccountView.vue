<template>
  <div class="min-h-screen bg-slate-50 flex flex-col items-center justify-center p-6 relative overflow-hidden">
    <!-- Background accents -->
    <div class="absolute top-0 right-0 w-1/3 h-1/3 bg-brand/5 rounded-full blur-[120px] -translate-y-1/2 translate-x-1/2"></div>
    <div class="absolute bottom-0 left-0 w-1/4 h-1/4 bg-brand/5 rounded-full blur-[100px] translate-y-1/3 -translate-x-1/4"></div>

    <div class="w-full max-w-md animate-fade">
      <!-- Branding -->
      <div class="flex flex-col items-center mb-6">
        <div class="h-14 w-14 bg-brand rounded-2xl flex items-center justify-center text-white font-bold text-2xl mb-6 hover:rotate-6 transition-transform">
          R
        </div>
        <h1 class="text-2xl font-bold text-slate-800 tracking-tight">Activate Your Account</h1>
        <p class="text-slate-500 font-medium text-sm mt-1 text-center">
          Welcome to RDRIMS — set your password to get started
        </p>
      </div>

      <!-- Success state (after activation) -->
      <div
        v-if="activated"
        class="bg-white rounded-[32px] border border-slate-100/60 shadow-2xl shadow-slate-200/50 p-8 text-center"
      >
        <div class="w-16 h-16 bg-emerald-50 rounded-2xl flex items-center justify-center mx-auto mb-4">
          <svg class="w-8 h-8 text-emerald-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7" />
          </svg>
        </div>
        <h2 class="text-lg font-bold text-slate-800 mb-2">Account Activated!</h2>
        <p class="text-sm text-slate-500 mb-6">Your password has been set. You'll be redirected to login shortly.</p>
        <router-link
          to="/login"
          class="inline-block w-full bg-brand text-white font-bold text-sm py-4 rounded-2xl shadow-xl shadow-brand/20 hover:bg-brand-hover transition-all text-center"
        >
          Sign In Now
        </router-link>
      </div>

      <!-- Activation form -->
      <div
        v-else
        class="bg-white rounded-[32px] border border-slate-100/60 shadow-2xl shadow-slate-200/50 p-6 relative overflow-hidden"
      >
        <!-- Top accent bar -->
        <div class="absolute top-0 left-0 w-full h-1.5 bg-brand"></div>

        <!-- Invitation badge -->
        <div class="flex items-center gap-2 bg-brand/5 border border-brand/10 rounded-xl px-4 py-3 mb-6 mt-2">
          <svg class="w-4 h-4 text-brand flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
          </svg>
          <p class="text-xs font-medium text-brand">
            An account was created for you. Set your password to activate access.
          </p>
        </div>

        <form @submit.prevent="submit" class="space-y-5">
          <!-- Email (read-only, pre-filled from URL) -->
          <div class="space-y-2">
            <label class="block text-xs font-medium text-slate-400 ml-1">Your Email</label>
            <input
              v-model="form.email"
              type="email"
              readonly
              class="w-full bg-slate-50 border border-slate-100 rounded-2xl px-4 py-4 text-sm font-medium text-slate-400 cursor-not-allowed outline-none"
            />
          </div>

          <!-- Password -->
          <div class="space-y-2">
            <label class="block text-xs font-medium text-slate-400 ml-1">Create Password</label>
            <div class="relative">
              <input
                v-model="form.password"
                :type="showPassword ? 'text' : 'password'"
                required
                minlength="8"
                placeholder="Minimum 8 characters"
                class="w-full bg-slate-50 border border-slate-100 rounded-2xl px-4 py-4 pr-12 text-sm font-medium focus:bg-white focus:border-brand focus:ring-4 focus:ring-brand/10 transition-all outline-none"
              />
              <button
                type="button"
                @click="showPassword = !showPassword"
                class="absolute right-4 top-1/2 -translate-y-1/2 text-slate-400 hover:text-slate-600 transition-colors"
                tabindex="-1"
              >
                <svg v-if="!showPassword" class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" /><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                </svg>
                <svg v-else class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l3.59 3.59m0 0A9.953 9.953 0 0112 5c4.478 0 8.268 2.943 9.543 7a10.025 10.025 0 01-4.132 5.411m0 0L21 21" />
                </svg>
              </button>
            </div>
            <!-- Password strength indicator -->
            <div v-if="form.password" class="flex gap-1 mt-1">
              <div
                v-for="n in 4"
                :key="n"
                class="h-1 flex-1 rounded-full transition-all"
                :class="passwordStrength >= n ? strengthColor : 'bg-slate-100'"
              ></div>
            </div>
            <p v-if="form.password" class="text-xs ml-1" :class="strengthTextColor">{{ strengthLabel }}</p>
          </div>

          <!-- Confirm Password -->
          <div class="space-y-2">
            <label class="block text-xs font-medium text-slate-400 ml-1">Confirm Password</label>
            <input
              v-model="form.password_confirmation"
              type="password"
              required
              minlength="8"
              placeholder="Repeat your password"
              class="w-full bg-slate-50 border border-slate-100 rounded-2xl px-4 py-4 text-sm font-medium focus:bg-white focus:border-brand focus:ring-4 focus:ring-brand/10 transition-all outline-none"
              :class="{ 'border-rose-300 focus:border-rose-400 focus:ring-rose-100': mismatch }"
            />
            <p v-if="mismatch" class="text-xs text-rose-500 ml-1">Passwords do not match</p>
          </div>

          <!-- Submit -->
          <button
            type="submit"
            :disabled="loading || mismatch || !form.password || !form.password_confirmation"
            class="w-full bg-brand text-white font-bold text-sm py-5 rounded-2xl shadow-xl shadow-brand/20 hover:bg-brand-hover hover:scale-[1.02] active:scale-[0.98] transition-all disabled:opacity-60 disabled:cursor-not-allowed flex items-center justify-center gap-3 mt-2"
          >
            <span v-if="loading" class="w-5 h-5 border-2 border-white/30 border-t-white rounded-full animate-spin"></span>
            <span>{{ loading ? 'Activating...' : 'Activate Account & Set Password' }}</span>
          </button>
        </form>

        <div class="mt-5 pt-4 border-t border-slate-100 text-center">
          <router-link to="/login" class="text-xs font-medium text-slate-400 hover:text-brand transition-colors">
            Already activated? Sign In
          </router-link>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { reactive, ref, computed, onMounted } from 'vue'
import { useRouter, useRoute } from 'vue-router'
import { useNotificationStore } from '@/stores/notification'
import api from '@/services/api'

const router  = useRouter()
const route   = useRoute()
const notif   = useNotificationStore()

const loading     = ref(false)
const activated   = ref(false)
const showPassword = ref(false)

const form = reactive({
  email:                '',
  token:                '',
  password:             '',
  password_confirmation:'',
})

// ── Password Strength ──────────────────────────────────────────────────────
const passwordStrength = computed(() => {
  const p = form.password
  if (!p) return 0
  let score = 0
  if (p.length >= 8)  score++
  if (p.length >= 12) score++
  if (/[A-Z]/.test(p) && /[a-z]/.test(p)) score++
  if (/[0-9]/.test(p) || /[^A-Za-z0-9]/.test(p)) score++
  return score
})

const strengthColor = computed(() => {
  const c = { 1: 'bg-rose-400', 2: 'bg-amber-400', 3: 'bg-yellow-400', 4: 'bg-emerald-500' }
  return c[passwordStrength.value] || 'bg-slate-100'
})

const strengthTextColor = computed(() => {
  const c = { 1: 'text-rose-500', 2: 'text-amber-500', 3: 'text-yellow-600', 4: 'text-emerald-600' }
  return c[passwordStrength.value] || 'text-slate-400'
})

const strengthLabel = computed(() => {
  const l = { 1: 'Weak', 2: 'Fair', 3: 'Good', 4: 'Strong' }
  return l[passwordStrength.value] || ''
})

const mismatch = computed(() =>
  form.password_confirmation.length > 0 && form.password !== form.password_confirmation
)

// ── Lifecycle ──────────────────────────────────────────────────────────────
onMounted(() => {
  form.email = route.query.email || ''
  form.token = route.query.token || route.params.token || ''

  if (!form.token || !form.email) {
    notif.error('Invalid or expired activation link. Please contact your administrator.')
    router.push('/login')
  }
})

// ── Submit ─────────────────────────────────────────────────────────────────
async function submit() {
  if (mismatch.value) return

  loading.value = true
  try {
    // Reuse the existing /reset-password endpoint — it accepts the
    // Password Broker token and sets the user's password securely.
    await api.post('/reset-password', {
      email:                form.email,
      token:                form.token,
      password:             form.password,
      password_confirmation: form.password_confirmation,
    })

    activated.value = true
    notif.success('Account activated successfully! Please sign in.')

    // Redirect to login after a brief moment
    setTimeout(() => router.push('/login'), 2500)
  } catch (err) {
    const msg = err.response?.data?.message || 'Activation failed. The link may have expired — contact your administrator.'
    notif.error(msg)
  } finally {
    loading.value = false
  }
}
</script>
