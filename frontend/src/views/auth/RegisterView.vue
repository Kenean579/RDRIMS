<template>
  <div class="min-h-screen bg-slate-50 flex flex-col items-center justify-center p-6 relative overflow-hidden">
    <div class="absolute top-0 right-0 w-1/3 h-1/3 bg-brand/5 rounded-full blur-[120px] -translate-y-1/2 translate-x-1/2"></div>
    <div class="absolute bottom-0 left-0 w-1/4 h-1/4 bg-brand/5 rounded-full blur-[100px] translate-y-1/3 -translate-x-1/4"></div>

    <div class="w-full max-w-lg animate-fade">
      <div class="flex flex-col items-center mb-5">
        <div class="h-14 w-14 bg-brand rounded-2xl flex items-center justify-center text-white font-bold text-2xl mb-6 hover:rotate-6 transition-transform cursor-pointer" @click="router.push('/')">
          R
        </div>
        <h1 class="text-xl font-bold text-slate-800 tracking-tight">Create Account</h1>
        <p class="text-slate-500 font-medium text-sm mt-1">Join the {{ appName }} Research Community</p>
      </div>

      <div class="bg-white rounded-[32px] border border-slate-100/60 shadow-2xl shadow-slate-200/50 p-5 relative overflow-hidden">
        <div class="absolute top-0 left-0 w-full h-1.5 bg-brand"></div>
        
        <form @submit.prevent="handleRegister" class="grid grid-cols-1 md:grid-cols-2 gap-6">
          <div v-if="auth.error" class="md:col-span-2 bg-rose-50 border border-rose-100 text-rose-600 px-5 py-4 rounded-2xl text-xs font-bold flex items-center gap-3">
            <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" /></svg>
            {{ auth.error }}
          </div>

          <div class="space-y-2 md:col-span-2">
            <label class="block text-xs font-medium text-slate-400 ml-1">Full Name *</label>
            <input v-model="form.name" type="text" required placeholder="Dr. Abebe Kebede" class="w-full bg-slate-50 border border-slate-100 rounded-2xl px-4 py-4 text-sm font-medium focus:bg-white focus:border-brand focus:ring-4 focus:ring-brand/10 transition-all outline-none" />
          </div>

          <div class="space-y-2 md:col-span-2">
            <label class="block text-xs font-medium text-slate-400 ml-1">E-mail Address *</label>
            <input v-model="form.email" type="email" required placeholder="name@university.edu" class="w-full bg-slate-50 border border-slate-100 rounded-2xl px-4 py-4 text-sm font-medium focus:bg-white focus:border-brand focus:ring-4 focus:ring-brand/10 transition-all outline-none" />
          </div>

          <!-- Cascading Department -->
          <div class="space-y-2 md:col-span-2">
            <label class="block text-xs font-medium text-slate-400 ml-1">Affiliated Department</label>
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-3">
              <select v-model="form.university_id" class="input font-bold" @change="onUniversityChange">
                <option value="">University</option>
                <option v-for="u in universities" :key="u.id" :value="u.id">{{ u.name }}</option>
              </select>
              <select v-model="form.campus_id" class="input font-bold" :disabled="!form.university_id" @change="onCampusChange">
                <option value="">Campus</option>
                <option v-for="c in filteredCampuses" :key="c.id" :value="c.id">{{ c.name }}</option>
              </select>
              <select v-model="form.faculty_id" class="input font-bold" :disabled="!form.campus_id" @change="onFacultyChange">
                <option value="">Faculty</option>
                <option v-for="f in filteredFaculties" :key="f.id" :value="f.id">{{ f.name }}</option>
              </select>
              <select v-model="form.department_id" class="input font-bold" :disabled="!form.faculty_id">
                <option value="">Department</option>
                <option v-for="d in filteredDepartments" :key="d.id" :value="d.id">{{ d.name }}</option>
              </select>
            </div>
          </div>

          <!-- Password -->
          <div class="space-y-2 md:col-span-2">
            <label class="block text-xs font-medium text-slate-400 ml-1">Password *</label>
            <div class="relative">
              <input v-model="form.password" :type="showPass ? 'text' : 'password'" required minlength="8" placeholder="••••••••" class="w-full bg-slate-50 border border-slate-100 rounded-2xl px-4 py-4 text-sm font-medium focus:bg-white focus:border-brand focus:ring-4 focus:ring-brand/10 transition-all outline-none pr-12" @input="updateStrength" />
              <button type="button" @click="showPass = !showPass" class="absolute right-4 top-1/2 -translate-y-1/2 text-slate-300 hover:text-slate-500">
                <svg v-if="!showPass" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                <svg v-else class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l18 18"/></svg>
              </button>
            </div>
            <div class="flex gap-1 mt-1">
              <div v-for="i in 4" :key="i" class="h-1.5 flex-1 rounded-full transition-all" :class="passwordStrength >= i ? (passwordStrength <= 2 ? 'bg-rose-400' : passwordStrength === 3 ? 'bg-amber-400' : 'bg-emerald-400') : 'bg-slate-200'"></div>
            </div>
            <p class="text-[10px] font-bold tracking-wider" :class="passwordStrength <= 2 ? 'text-rose-500' : passwordStrength === 3 ? 'text-amber-500' : 'text-emerald-500'">
              {{ strengthLabel }}
            </p>
          </div>

          <!-- Confirm Password -->
          <div class="space-y-2 md:col-span-2">
            <label class="block text-xs font-medium text-slate-400 ml-1">Confirm Password *</label>
            <input v-model="form.password_confirmation" type="password" required minlength="8" placeholder="••••••••" class="w-full bg-slate-50 border border-slate-100 rounded-2xl px-4 py-4 text-sm font-medium focus:bg-white focus:border-brand focus:ring-4 focus:ring-brand/10 transition-all outline-none" />
            <p v-if="form.password_confirmation && form.password !== form.password_confirmation" class="text-[10px] font-bold text-rose-500">Passwords do not match</p>
            <p v-else-if="form.password_confirmation && form.password === form.password_confirmation" class="text-[10px] font-bold text-emerald-500">Passwords match</p>
          </div>

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
          <p class="text-xs font-medium text-slate-400">
            Already have an account? 
            <router-link to="/login" class="text-brand font-bold hover:underline underline-offset-4">Sign In Instead</router-link>
          </p>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { reactive, ref, computed, onMounted } from 'vue'
import { useRouter } from 'vue-router'
import { useAuthStore } from '@/stores/auth'
import { useLookupStore } from '@/stores/lookup'
import api from '@/services/api'

const router = useRouter()
const auth = useAuthStore()
const lookupStore = useLookupStore()

const appName = computed(() => lookupStore.getSetting('app_name', 'RDRIMS'))
const showPass = ref(false)
const passwordStrength = ref(0)

const universities = ref([])
const campuses = ref([])
const faculties = ref([])
const departments = ref([])

const form = reactive({ 
  name: '', 
  email: '', 
  password: '', 
  password_confirmation: '', 
  department_id: '',
  university_id: '',
  campus_id: '',
  faculty_id: ''
})

const filteredCampuses = computed(() => campuses.value.filter(c => String(c.university_id) === String(form.university_id)))
const filteredFaculties = computed(() => faculties.value.filter(f => String(f.campus_id) === String(form.campus_id)))
const filteredDepartments = computed(() => departments.value.filter(d => String(d.faculty_id) === String(form.faculty_id)))

function onUniversityChange() { form.campus_id = ''; form.faculty_id = ''; form.department_id = '' }
function onCampusChange() { form.faculty_id = ''; form.department_id = '' }
function onFacultyChange() { form.department_id = '' }

const strengthLabel = computed(() => {
  if (passwordStrength.value === 0) return ''
  if (passwordStrength.value <= 1) return 'Weak'
  if (passwordStrength.value === 2) return 'Fair'
  if (passwordStrength.value === 3) return 'Good'
  return 'Strong'
})

function updateStrength() {
  const pw = form.password
  let score = 0
  if (pw.length >= 8) score++
  if (/[A-Z]/.test(pw) && /[a-z]/.test(pw)) score++
  if (/\d/.test(pw)) score++
  if (/[^A-Za-z0-9]/.test(pw)) score++
  passwordStrength.value = score
}

onMounted(async () => { 
  try { 
    const u = await api.get('/universities')
    const c = await api.get('/campuses')
    const f = await api.get('/faculties')
    const d = await api.get('/departments')
    universities.value = (u.data.data || u.data)
    campuses.value = (c.data.data || c.data)
    faculties.value = (f.data.data || f.data)
    departments.value = (d.data.data || d.data)
  } catch (e) {} 
})

async function handleRegister() { 
  if (form.password !== form.password_confirmation) { 
    auth.error = 'Passwords do not match.'
    return 
  }
  
  const success = await auth.register({ 
    name: form.name,
    email: form.email,
    password: form.password,
    password_confirmation: form.password_confirmation,
    university_id: form.university_id || null,
    department_id: form.department_id || null 
  })
  
  if (success) {
    router.push('/app/dashboard')
  }
}
</script>
