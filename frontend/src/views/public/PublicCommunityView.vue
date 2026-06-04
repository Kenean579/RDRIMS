<template>
  <div class="max-w-3xl mx-auto px-4 sm:px-6 lg:px-5 py-6">
    
    <!-- Loading State -->
    <div v-if="loading" class="flex items-center justify-center py-24">
      <div class="animate-spin rounded-full h-12 w-12 border-4 border-brand border-t-transparent"></div>
    </div>

    <!-- Sign In Required State -->
    <div v-else-if="!allowPublicSubmission" class="bg-white rounded-2xl border border-slate-100 p-6 text-center">
      <div class="w-16 h-16 bg-slate-50 rounded-full flex items-center justify-center mx-auto mb-6 text-xl">🔒</div>
      <h2 class="text-2xl font-bold text-slate-800 mb-4">Sign In Required</h2>
      <p class="text-slate-500 mb-5">Please sign in to submit a community problem.</p>
      <router-link to="/login" class="inline-block px-5 py-3 bg-brand text-white font-bold rounded-2xl hover:bg-brand-dark transition-colors">
        Sign In
      </router-link>
    </div>

    <!-- Form State -->
    <div v-else class="space-y-8">
      
      <!-- Success Banner -->
      <div v-if="showSuccess" class="bg-emerald-50 border border-emerald-200 rounded-2xl p-6 flex items-center gap-4">
        <div class="w-10 h-10 bg-emerald-500 rounded-full flex items-center justify-center text-white shrink-0">✓</div>
        <div class="flex-1">
          <p class="text-emerald-800 font-bold">✅ Problem submitted successfully! Researchers will review it.</p>
        </div>
        <button @click="showSuccess = false" class="text-emerald-600 hover:text-emerald-800">
          <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" /></svg>
        </button>
      </div>

      <!-- Server Error Banner -->
      <div v-if="serverError" class="bg-rose-50 border border-rose-200 rounded-2xl p-6 flex items-center gap-4">
        <div class="w-10 h-10 bg-rose-500 rounded-full flex items-center justify-center text-white shrink-0">✕</div>
        <div class="flex-1">
          <p class="text-rose-800 font-bold">{{ serverError }}</p>
        </div>
        <button @click="serverError = ''" class="text-rose-600 hover:text-rose-800">
          <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" /></svg>
        </button>
      </div>

      <!-- Section Heading -->
      <div>
        <h1 class="text-xl md:text-2xl font-bold text-slate-800 tracking-tight mb-3">Community Problems</h1>
        <p class="text-lg text-slate-500">Report a real-world problem for university researchers to study and help solve.</p>
      </div>

      <!-- Submission Form -->
      <div class="bg-white rounded-2xl border border-slate-100 p-5">
        <form @submit.prevent="submitProblem" class="space-y-6">
          
          <!-- Problem Title -->
          <div>
            <label class="block text-sm font-bold text-slate-700 mb-2">Problem Title <span class="text-rose-500">*</span></label>
            <input 
              v-model="form.title" 
              type="text" 
              required
              maxlength="255"
              :class="{ 'border-rose-500': errors.title }"
              class="w-full border border-slate-300 rounded-2xl px-4 py-3 text-sm focus:ring-2 focus:ring-brand/30 focus:border-brand outline-none transition-colors"
              placeholder="e.g., Water Shortage in Dessie Town"
            />
            <p v-if="errors.title" class="text-xs text-rose-500 mt-1">{{ errors.title }}</p>
          </div>

          <!-- Description -->
          <div>
            <label class="block text-sm font-bold text-slate-700 mb-2">Description <span class="text-rose-500">*</span></label>
            <textarea 
              v-model="form.description" 
              required
              rows="5"
              minlength="50"
              :class="{ 'border-rose-500': errors.description }"
              class="w-full border border-slate-300 rounded-2xl px-4 py-3 text-sm focus:ring-2 focus:ring-brand/30 focus:border-brand outline-none resize-none transition-colors"
              placeholder="Describe the problem in detail. What is happening? Who is affected? How long has this been going on?"
            ></textarea>
            <div class="flex items-center justify-between mt-1">
              <p v-if="errors.description" class="text-xs text-rose-500">{{ errors.description }}</p>
              <p v-else class="text-xs text-slate-400">{{ form.description?.length || 0 }} / 50 minimum</p>
            </div>
          </div>

          <!-- Location -->
          <div>
            <label class="block text-sm font-bold text-slate-700 mb-2">Location <span class="text-rose-500">*</span></label>
            <input 
              v-model="form.location" 
              type="text" 
              required
              maxlength="255"
              :class="{ 'border-rose-500': errors.location }"
              class="w-full border border-slate-300 rounded-2xl px-4 py-3 text-sm focus:ring-2 focus:ring-brand/30 focus:border-brand outline-none transition-colors"
              placeholder="e.g., Dessie, South Wollo, Ethiopia"
            />
            <p v-if="errors.location" class="text-xs text-rose-500 mt-1">{{ errors.location }}</p>
          </div>

          <!-- Contact Information -->
          <div>
            <label class="block text-sm font-bold text-slate-700 mb-2">Contact Information (optional)</label>
            <input 
              v-model="form.contact_info" 
              type="text" 
              maxlength="255"
              class="w-full border border-slate-300 rounded-2xl px-4 py-3 text-sm focus:ring-2 focus:ring-brand/30 focus:border-brand outline-none transition-colors"
              placeholder="Email or phone so researchers can contact you for follow-up"
            />
          </div>

          <!-- Submit Anonymously -->
          <div class="flex items-center gap-3">
            <input 
              type="checkbox" 
              v-model="form.is_anonymous" 
              id="anonymous"
              class="w-5 h-5 rounded border-slate-300 text-brand focus:ring-brand"
            />
            <label for="anonymous" class="text-sm text-slate-600 cursor-pointer">Submit anonymously (your name will not be shown publicly)</label>
          </div>

          <!-- Submit Button -->
          <button 
            type="submit" 
            :disabled="submitting"
            class="w-full px-6 py-3 bg-brand text-white font-bold rounded-2xl hover:shadow-brand/50 hover:-translate-y-0.5 transition-all disabled:opacity-60 disabled:cursor-not-allowed disabled:shadow-none disabled:translate-y-0 flex items-center justify-center gap-2"
          >
            <svg v-if="submitting" class="animate-spin h-5 w-5 border-2 border-white border-t-transparent rounded-full" viewBox="0 0 24 24"></svg>
            {{ submitting ? 'Submitting...' : 'Submit Problem' }}
          </button>

          <!-- Submit Another Button (shown after success) -->
          <button 
            v-if="showSuccess"
            type="button"
            @click="resetForm"
            class="w-full px-6 py-3 bg-white text-slate-700 font-bold rounded-2xl border border-slate-300 hover:border-brand hover:text-brand transition-all"
          >
            Submit Another Problem
          </button>

        </form>
      </div>

      <!-- Info Box -->
      <div class="bg-blue-50 rounded-2xl border border-blue-200 p-6">
        <h3 class="text-sm font-bold text-slate-800 mb-4">💡 How it works:</h3>
        <ul class="text-sm text-slate-600 space-y-2">
          <li class="flex items-start gap-2">
            <span class="text-blue-500">•</span>
            <span>Submit a problem affecting your community</span>
          </li>
          <li class="flex items-start gap-2">
            <span class="text-blue-500">•</span>
            <span>University researchers review and may claim the problem</span>
          </li>
          <li class="flex items-start gap-2">
            <span class="text-blue-500">•</span>
            <span>Researchers study the issue and propose solutions</span>
          </li>
          <li class="flex items-start gap-2">
            <span class="text-blue-500">•</span>
            <span>You'll be notified when progress is made (if contact provided)</span>
          </li>
        </ul>
      </div>

    </div>

  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import api from '@/services/api'

const loading = ref(true)
const allowPublicSubmission = ref(false)
const submitting = ref(false)
const showSuccess = ref(false)
const serverError = ref('')

const form = ref({
  title: '',
  description: '',
  location: '',
  contact_info: '',
  is_anonymous: false
})

const errors = ref({})

onMounted(async () => {
  try {
    const res = await api.get('/settings')
    const settings = res.data?.data || res.data || []
    const setting = settings.find(s => s.key === 'allow_public_problem_submission')
    // Allow public submission if setting is true or not set at all
    allowPublicSubmission.value = setting?.value === 'true' || !setting
  } catch (e) {
    console.error('Failed to load settings', e)
    // Default to allowing submission if settings fail to load
    allowPublicSubmission.value = true
  } finally {
    loading.value = false
  }
})

async function submitProblem() {
  // Clear previous errors
  errors.value = {}
  serverError.value = ''

  // Basic validation
  if (!form.value.title?.trim()) {
    errors.value.title = 'Problem title is required'
  }
  if (!form.value.description?.trim() || form.value.description.length < 50) {
    errors.value.description = 'Description must be at least 50 characters'
  }
  if (!form.value.location?.trim()) {
    errors.value.location = 'Location is required'
  }

  // If validation fails, return
  if (Object.keys(errors.value).length > 0) {
    return
  }

  submitting.value = true
  try {
    await api.post('/community-problems', {
      title: form.value.title,
      description: form.value.description,
      location: form.value.location,
      contact_info: form.value.contact_info,
      is_anonymous: form.value.is_anonymous
    })
    
    showSuccess.value = true
    resetForm()
  } catch (err) {
    console.error('Failed to submit problem', err)
    if (err.response?.data?.errors) {
      errors.value = err.response.data.errors
    } else {
      serverError.value = err.response?.data?.message || 'Something went wrong. Please try again.'
    }
  } finally {
    submitting.value = false
  }
}

function resetForm() {
  form.value = {
    title: '',
    description: '',
    location: '',
    contact_info: '',
    is_anonymous: false
  }
  errors.value = {}
  serverError.value = ''
}
</script>
