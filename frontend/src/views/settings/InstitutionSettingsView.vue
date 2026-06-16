<template>
  <div class="flex flex-col gap-5 pb-6 animate-fade card">
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
      <div>
        <h1 class="text-xl font-bold text-slate-900 tracking-tight">Institution Settings</h1>
        <p class="text-slate-500 font-medium mt-1">Configure institution-specific research settings (budget caps, review periods, limits, etc.).</p>
      </div>
      <div class="flex gap-3">
        <button @click="saveAll" class="btn btn-primary h-11 px-5 text-xs font-medium">Save All Changes</button>
      </div>
    </div>

    <div v-if="loading" class="card p-8 flex justify-center">
      <div class="animate-spin rounded-full h-10 w-10 border-b-2 border-brand"></div>
    </div>

    <div v-else class="grid grid-cols-1 lg:grid-cols-2 gap-5">
      <div class="card p-8">
        <h2 class="text-xs font-medium text-slate-400 mb-6 flex items-center gap-2">
          <span class="w-1 h-3 bg-brand rounded-full"></span>
          Operational Settings
        </h2>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
          <div>
            <label class="block text-xs text-slate-500 mb-2 ml-1">Default Budget Cap (ETB)</label>
            <input v-model="settings.default_budget_cap" type="number" class="input h-12 font-bold" />
          </div>
          <div>
            <label class="block text-xs text-slate-500 mb-2 ml-1">Review Period (Days)</label>
            <input v-model="settings.review_period_days" type="number" class="input h-12 font-bold" />
          </div>
          <div class="md:col-span-2">
            <label class="flex items-center gap-3 p-4 bg-slate-50 border border-slate-100 rounded-2xl cursor-pointer hover:bg-white transition-all">
              <input v-model="settings.allow_public_registration" type="checkbox" class="w-5 h-5 rounded border-slate-300 text-brand focus:ring-brand" />
              <div>
                <p class="text-sm font-bold text-slate-800">Enable Public Researcher Registration</p>
                <p class="text-xs text-slate-400 font-medium">Allow external researchers to create accounts</p>
              </div>
            </label>
          </div>
        </div>
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
  default_budget_cap: 500000,
  review_period_days: 14,
  allow_public_registration: true,
})

async function fetchSettings() {
  loading.value = true
  try {
    const { data } = await api.get('/institution-settings')
    // assume response is array of {key, value}
    data.forEach(s => {
      if (settings[s.key] !== undefined) settings[s.key] = s.value
    })
  } catch (e) {
    notif.error('Failed to load institution settings')
  } finally {
    loading.value = false
  }
}

async function saveAll() {
  try {
    const payload = Object.keys(settings).map(k => ({key: k, value: String(settings[k])}))
    await api.post('/institution-settings/bulk', { settings: payload })
    notif.success('Institution settings saved')
    fetchSettings()
  } catch (e) {
    notif.error('Failed to save institution settings')
  }
}

onMounted(fetchSettings)
</script>

<style scoped>
/* inherit styling from existing cards */
</style>
