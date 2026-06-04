<template>
  <div class="flex flex-col gap-6 card">
    <div class="section-header">
      <div>
        <h1 class="section-title">Language Preference</h1>
        <p class="section-subtitle">Choose your preferred language for the system interface</p>
      </div>
    </div>

    <div v-if="loading" class="card p-8"><LoadingSkeleton :rows="2" /></div>

    <div v-else class="card p-8 max-w-xl">
      <form @submit.prevent="savePreference" class="flex flex-col gap-6">
        <div>
          <label class="block text-[11px] text-slate-500 font-medium  tracking-wider mb-3 ml-1">Interface Language</label>
          <div class="space-y-3">
            <label class="flex items-center gap-4 p-4 rounded-2xl border border-slate-100 cursor-pointer hover:border-blue-200 hover:bg-blue-50/30 transition group" :class="{ 'border-blue-300 bg-blue-50/50': locale === 'en' }">
              <input type="radio" v-model="locale" value="en" class="w-4 h-4 text-blue-600 focus:ring-blue-500" />
              <div>
                <p class="text-sm font-bold text-slate-800 group-hover:text-blue-600 transition">English</p>
                <p class="text-[11px] text-slate-400 font-medium mt-0.5">Default system language</p>
              </div>
            </label>
            <label class="flex items-center gap-4 p-4 rounded-2xl border border-slate-100 cursor-pointer hover:border-blue-200 hover:bg-blue-50/30 transition group" :class="{ 'border-blue-300 bg-blue-50/50': locale === 'am' }">
              <input type="radio" v-model="locale" value="am" class="w-4 h-4 text-blue-600 focus:ring-blue-500" />
              <div>
                <p class="text-sm font-bold text-slate-800 group-hover:text-blue-600 transition">አማርኛ (Amharic)</p>
                <p class="text-[11px] text-slate-400 font-medium mt-0.5">Amharic language interface</p>
              </div>
            </label>
          </div>
        </div>
        <div class="flex justify-end pt-2">
          <button type="submit" class="btn btn-primary px-5">Save Preference</button>
        </div>
      </form>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import { useNotificationStore } from '@/stores/notification'
import api from '@/services/api'
import LoadingSkeleton from '@/components/LoadingSkeleton.vue'

const notif = useNotificationStore()
const locale = ref('en')
const loading = ref(true)

onMounted(async () => {
  try {
    const { data } = await api.get('/language-preference')
    locale.value = data.locale || 'en'
  } catch (e) {} finally {
    loading.value = false
  }
})

async function savePreference() {
  try {
    await api.put('/language-preference', { locale: locale.value })
    notif.success('Language preference saved!')
  } catch (err) {
    notif.error('Failed to save preference')
  }
}
</script>
