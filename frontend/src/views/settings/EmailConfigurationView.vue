<template>
  <div class="space-y-6">
    <div class="flex items-center justify-between">
      <h2 class="text-2xl font-bold text-slate-800">System Health & Email Configuration</h2>
    </div>

   
    <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
  <div
    v-for="(health, service) in systemHealth"
    :key="service"
    class="bg-white p-4 rounded-xl border border-slate-200 shadow-sm flex items-center gap-4 overflow-hidden"
  >
    <!-- Status Icon -->
    <div
      class="w-10 h-10 rounded-full flex items-center justify-center shrink-0"
      :class="{
        'bg-green-100 text-green-600': health.status === 'ok',
        'bg-yellow-100 text-yellow-600': health.status === 'warning',
        'bg-red-100 text-red-600': health.status === 'error'
      }"
    >
      <!-- Success -->
      <svg
        v-if="health.status === 'ok'"
        class="w-5 h-5"
        fill="none"
        stroke="currentColor"
        viewBox="0 0 24 24"
      >
        <path
          stroke-linecap="round"
          stroke-linejoin="round"
          stroke-width="2"
          d="M5 13l4 4L19 7"
        />
      </svg>

      <!-- Warning -->
      <svg
        v-else-if="health.status === 'warning'"
        class="w-5 h-5"
        fill="none"
        stroke="currentColor"
        viewBox="0 0 24 24"
      >
        <path
          stroke-linecap="round"
          stroke-linejoin="round"
          stroke-width="2"
          d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"
        />
      </svg>

      <!-- Error -->
      <svg
        v-else
        class="w-5 h-5"
        fill="none"
        stroke="currentColor"
        viewBox="0 0 24 24"
      >
        <path
          stroke-linecap="round"
          stroke-linejoin="round"
          stroke-width="2"
          d="M6 18L18 6M6 6l12 12"
        />
      </svg>
    </div>

    <!-- Content -->
    <div class="min-w-0 flex-1">
      <h4 class="text-xs text-slate-500 font-bold uppercase tracking-wider">
        {{ service }}
      </h4>

      <p
        class="text-sm text-slate-900 mt-1 break-words line-clamp-2"
        :title="health.message"
      >
        {{ health.message }}
      </p>
    </div>
  </div>
</div>

    <!-- Email Configuration Form -->
    <div class="bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden">
      <div class="p-6 border-b border-slate-100">
        <h3 class="text-lg font-bold text-slate-800">SMTP Configuration</h3>
        <p class="text-sm text-slate-500 mt-1">Configure the SMTP server for outgoing emails.</p>
      </div>

      <div class="p-6">
        <form @submit.prevent="saveConfig" class="space-y-6">
          <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <div>
              <label class="block text-xs text-slate-500 font-medium mb-2 ml-1">SMTP Host</label>
              <input v-model="form.host" type="text" class="input h-12" required placeholder="e.g. smtp.mailgun.org">
            </div>
            <div>
              <label class="block text-xs text-slate-500 font-medium mb-2 ml-1">SMTP Port</label>
              <input v-model="form.port" type="number" class="input h-12" required placeholder="e.g. 587">
            </div>
            <div>
              <label class="block text-xs text-slate-500 font-medium mb-2 ml-1">Username</label>
              <input v-model="form.username" type="text" class="input h-12" placeholder="SMTP Username">
            </div>
            <div>
              <label class="block text-xs text-slate-500 font-medium mb-2 ml-1">Password</label>
              <input v-model="form.password" type="password" class="input h-12" placeholder="Leave blank to keep existing password">
            </div>
            <div>
              <label class="block text-xs text-slate-500 font-medium mb-2 ml-1">Encryption</label>
              <select v-model="form.encryption" class="input h-12 font-bold">
                <option value="tls">TLS</option>
                <option value="ssl">SSL</option>
                <option value="">None</option>
              </select>
            </div>
            <div class="flex items-center mt-8">
              <label class="flex items-center gap-3 cursor-pointer">
                <input type="checkbox" v-model="form.is_enabled" class="w-5 h-5 rounded border-slate-300 text-blue-600 focus:ring-blue-500">
                <span class="text-sm font-bold text-slate-700">Enable Email Notifications</span>
              </label>
            </div>
          </div>

          <div class="flex items-center gap-4 pt-4">
            <button type="submit" class="btn btn-primary" :disabled="saving">
              <span v-if="saving">Saving...</span>
              <span v-else>Save Configuration</span>
            </button>
            <button type="button" @click="testEmail" class="btn border border-slate-200 bg-white text-slate-700 hover:bg-slate-50" :disabled="testing || !form.is_enabled">
              <span v-if="testing">Sending...</span>
              <span v-else>Send Test Email</span>
            </button>
          </div>
        </form>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted, reactive } from 'vue'
import api from '@/services/api'
import { useNotificationStore } from '@/stores/notification'

const notif = useNotificationStore()
const systemHealth = ref({})
const loading = ref(true)
const saving = ref(false)
const testing = ref(false)

const form = reactive({
  host: '',
  port: '',
  username: '',
  password: '',
  encryption: 'tls',
  is_enabled: true
})

async function fetchData() {
  try {
    const healthRes = await api.get('/system-health')
    systemHealth.value = healthRes.data || healthRes

    const configRes = await api.get('/email-config')
    const config = configRes.data || configRes
    if (config) {
      form.host = config.host
      form.port = config.port
      form.username = config.username
      form.encryption = config.encryption
      form.is_enabled = config.is_enabled
    }
  } catch (err) {
    notif.error('Failed to load email configuration')
  } finally {
    loading.value = false
  }
}

async function saveConfig() {
  saving.value = true
  try {
    await api.post('/email-config', form)
    notif.success('Email configuration saved successfully')
    // Clear password field after save
    form.password = ''
    // Refresh health
    const healthRes = await api.get('/system-health')
    systemHealth.value = healthRes.data || healthRes
  } catch (err) {
    notif.error(err.response?.data?.message || 'Failed to save configuration')
  } finally {
    saving.value = false
  }
}

async function testEmail() {
  const email = prompt('Enter an email address to send the test email to:')
  if (!email) return

  testing.value = true
  try {
    await api.post('/email-config/test', { email })
    notif.success('Test email sent successfully')
  } catch (err) {
    notif.error(err.response?.data?.message || 'Failed to send test email')
  } finally {
    testing.value = false
  }
}

onMounted(() => {
  fetchData()
})
</script>
