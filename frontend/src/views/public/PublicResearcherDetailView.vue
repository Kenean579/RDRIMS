<template>
  <div class="max-w-4xl mx-auto animate-fade pb-8">
    <div v-if="loading" class="card p-8 flex justify-center"><div class="animate-spin rounded-full h-10 w-10 border-b-2 border-brand"></div></div>
    <div v-else-if="error" class="card p-8 text-center">
      <p class="text-rose-500 font-bold text-xs mb-4">{{ error }}</p>
      <button @click="fetchResearcher" class="btn btn-ghost text-xs font-bold border border-slate-100 px-6">Retry</button>
    </div>
    <div v-else-if="researcher" class="card p-8">
      <div class="flex items-start gap-6 mb-8">
        <div class="w-20 h-20 rounded-2xl overflow-hidden bg-brand-light border border-brand/20 shrink-0 shadow-sm">
          <img v-if="imageUrl(researcher.profile_image)" :src="imageUrl(researcher.profile_image)" :alt="researcher.name" class="w-full h-full object-cover" />
          <div v-else class="w-full h-full flex items-center justify-center text-brand font-bold text-2xl">{{ researcher.name.charAt(0) }}</div>
        </div>
        <div>
          <h1 class="text-2xl font-black text-slate-900 mb-2">{{ researcher.name }}</h1>
          <p class="text-sm text-slate-500 font-medium mb-3">{{ researcher.department?.name }} · {{ researcher.department?.faculty?.name }} · {{ researcher.department?.faculty?.campus?.name }}</p>
          <p class="text-xs text-slate-400 italic mb-4">{{ researcher.bio || 'No bio provided.' }}</p>
          <div class="flex flex-wrap gap-2">
            <span v-for="ex in researcher.expertise" :key="ex.id" class="px-3 py-1.5 bg-slate-50 text-slate-600 text-xs font-bold rounded-xl border border-slate-100">{{ ex.name }}</span>
          </div>
        </div>
      </div>
      <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
        <a v-if="researcher.orcid_id" :href="`https://orcid.org/${researcher.orcid_id}`" target="_blank" class="card p-4 text-center hover:border-brand/30 transition-all">
          <p class="text-xs font-bold text-slate-400 mb-1">ORCID</p>
          <p class="text-sm font-bold text-brand">View Profile</p>
        </a>
        <a v-if="researcher.scholar_url" :href="researcher.scholar_url" target="_blank" class="card p-4 text-center hover:border-brand/30 transition-all">
          <p class="text-xs font-bold text-slate-400 mb-1">Google Scholar</p>
          <p class="text-sm font-bold text-brand">View Profile</p>
        </a>
        <a v-if="researcher.linkedin_url" :href="researcher.linkedin_url" target="_blank" class="card p-4 text-center hover:border-brand/30 transition-all">
          <p class="text-xs font-bold text-slate-400 mb-1">LinkedIn</p>
          <p class="text-sm font-bold text-brand">View Profile</p>
        </a>
      </div>
    </div>
    <div v-else class="card p-8 text-center">
      <p class="text-slate-500 font-medium">Researcher not found.</p>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import { useRoute } from 'vue-router'
import api from '@/services/api'
import { imageUrl } from '@/utils/formatters'

const route = useRoute()
const researcher = ref(null)
const loading = ref(true)
const error = ref(null)

async function fetchResearcher() {
  loading.value = true; error.value = null
  try {
    const { data } = await api.get(`/public/researchers/${route.params.id}`)
    researcher.value = data
  } catch (e) {
    error.value = 'Failed to load researcher profile'
  } finally { loading.value = false }
}

onMounted(fetchResearcher)
</script>
