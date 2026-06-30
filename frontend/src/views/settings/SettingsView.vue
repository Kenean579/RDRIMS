<template>
  <div class="flex flex-col gap-5 pb-6 animate-fade card">
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
      <div>
        <h1 class="text-xl font-bold text-slate-900 tracking-tight">System Settings</h1>
        <p class="text-slate-500 font-medium mt-1 break-words">Configure global platform variables and institutional branding.</p>
      </div>
      <div class="flex gap-3">
        <router-link to="/app/settings/lookups" class="btn btn-secondary h-11 px-6 text-xs font-medium border border-slate-100">
          Advanced Lookups
        </router-link>
        <button @click="saveAll" class="btn btn-primary h-11 px-5 text-xs font-medium hover:bg-blue-600 transition duration-300">
          Save All Changes
        </button>
      </div>
    </div>

    <div v-if="loading" class="card p-8 flex justify-center"><div class="animate-spin rounded-full h-10 w-10 border-b-2 border-brand"></div></div>
    
    <div v-else class="grid grid-cols-1 lg:grid-cols-3 gap-5">
      <!-- System Health Banner -->
      <div class="lg:col-span-3 space-y-6">
        <div class="card p-4 bg-yellow-50 border border-yellow-200 rounded-lg" v-if="systemHealth.warnings && systemHealth.warnings.length">
          <h3 class="text-sm font-bold text-yellow-800">System Warnings</h3>
          <ul class="list-disc list-inside text-xs text-yellow-700 mt-1">
            <li v-for="warn in systemHealth.warnings" :key="warn">{{ warn }}</li>
          </ul>
        </div>
      </div>

      <!-- Branding -->
      <div class="lg:col-span-1 space-y-8 font-bold">
        <div class="card p-8">
          <h2 class="text-xs font-medium text-slate-400 mb-6 flex items-center gap-2">
            <span class="w-1 h-3 bg-brand rounded-full"></span>
            Platform Branding
          </h2>
          <div class="space-y-6">
            <div>
              <label class="block text-xs text-slate-500 mb-2 ml-1">Platform Name</label>
              <input v-model="settings.app_name" type="text" class="input h-12 font-bold" />
            </div>
            <div>
              <label class="block text-xs text-slate-500 mb-2 ml-1">Institution Domain</label>
              <input v-model="settings.institution_domain" type="text" placeholder="university.edu" class="input h-12 font-bold" />
            </div>
            <div>
              <label class="block text-xs text-slate-500 mb-2 ml-1">Support Email</label>
              <input v-model="settings.support_email" type="email" class="input h-12 font-bold" />
            </div>
          </div>
        </div>
      </div>

      <!-- General Settings -->
      <div class="lg:col-span-2 space-y-8 font-bold">
        <div class="card p-8">
          <h2 class="text-xs font-medium text-slate-400 mb-6 flex items-center gap-2">
            <span class="w-1 h-3 bg-brand rounded-full"></span>
            Operational Tuning
          </h2>
          <div class="grid grid-cols-1 md:grid-cols-2 gap-5">

            <div class="md:col-span-2">
               <label class="flex items-center gap-3 p-4 bg-slate-50 border border-slate-100 rounded-2xl cursor-pointer hover:bg-white transition-all">
                 <input v-model="settings.allow_public_registration" type="checkbox" class="w-5 h-5 rounded border-slate-300 text-brand focus:ring-brand" />
                 <div>
                   <p class="text-sm font-bold text-slate-800">Enable Public Researcher Registration</p>
                   <p class="text-xs text-slate-400 font-medium">Allow external researchers to create accounts</p>
                 </div>
               </label>
            </div>
            <div class="md:col-span-2">
               <label class="flex items-center gap-3 p-4 bg-slate-50 border border-slate-100 rounded-2xl cursor-pointer hover:bg-white transition-all">
                 <input v-model="settings.maintenance_mode" type="checkbox" class="w-5 h-5 rounded border-slate-300 text-rose-500 focus:ring-rose-500" />
                 <div>
                   <p class="text-sm font-bold text-slate-800">Maintenance Mode</p>
                   <p class="text-xs text-slate-400 font-medium">Disable system access for non-admin users</p>
                 </div>
               </label>
            </div>
          </div>
        </div>
        <div class="card p-8">
          <h2 class="text-xs font-medium text-slate-400 mb-6 flex items-center gap-2">
            <span class="w-1 h-3 bg-indigo-500 rounded-full"></span>
            PlagiarismCheck.org Integration
          </h2>
          <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
            <div>
              <label class="block text-xs text-slate-500 mb-2 ml-1">Group Token</label>
              <input v-model="settings.plagiarismcheck_group_token" type="password" class="input h-12 font-bold" placeholder="Enter your group token" />
            </div>
            <div>
              <label class="block text-xs text-slate-500 mb-2 ml-1">Author Email</label>
              <input v-model="settings.plagiarismcheck_author_email" type="email" class="input h-12 font-bold" placeholder="author@institution.edu" />
            </div>
            <div class="md:col-span-2">
              <label class="block text-xs text-slate-500 mb-2 ml-1">API Base URL</label>
              <input v-model="settings.plagiarismcheck_api_base_url" type="text" class="input h-12 font-bold" placeholder="https://plagiarismcheck.org/api/v1" />
            </div>
          </div>
        </div>

        <!-- Raw Metadata -->
        
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, reactive, onMounted } from 'vue'
import { useNotificationStore } from '@/stores/notification'
import api from '@/services/api'

const notif = useNotificationStore()
const loading = ref(true)
const settings = reactive({
  app_name: 'RDRIMS',
  institution_domain: '',
  support_email: '',
  allow_public_registration: true,
  maintenance_mode: false,
  plagiarismcheck_group_token: '',
  plagiarismcheck_author_email: '',
  plagiarismcheck_api_base_url: 'https://plagiarismcheck.org/api/v1'
})

// System health data (including SMTP status)
const systemHealth = ref({})

const rawSettings = ref({})

async function fetchSettings() {
  loading.value = true
  try {
    // Load system health first
    const healthRes = await api.get('/system-health')
    systemHealth.value = healthRes.data || healthRes

    const { data } = await api.get('/settings')
    const items = Array.isArray(data) ? data : (data.data || [])
    
    // Map to reactive object
    items.forEach(s => {
      if (settings[s.key] !== undefined) {
        if (typeof settings[s.key] === 'boolean') settings[s.key] = s.value === '1' || s.value === 'true' || s.value === true
        else settings[s.key] = s.value
      }
      rawSettings.value[s.key] = s.value
    })
  } catch (err) {
    if (err.response && err.response.status === 403) {
      notif.error('Access denied: you do not have permission to view system settings.')
    } else {
      notif.error('Failed to load settings')
    }
  } finally {
    loading.value = false
  }
}

async function saveAll() {
  try {
    const payload = Object.keys(settings).map(key => ({
      key,
      value: String(settings[key])
    }))
    
    await api.post('/settings/bulk', { settings: payload })
    notif.success('System settings updated!')
    fetchSettings()
  } catch (err) {
    const validationErrors = err.response?.data?.errors
    if (validationErrors) {
      const firstError = Object.values(validationErrors)[0]?.[0]
      notif.error(firstError || 'Failed to save settings')
    } else {
      notif.error(err.response?.data?.message || 'Failed to save settings')
    }
  }
}

onMounted(fetchSettings)
</script>
