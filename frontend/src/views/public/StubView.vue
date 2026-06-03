<template>
  <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-5 py-24 text-center">
    <div class="bg-white rounded-3xl p-6 border border-slate-100 shadow-xl shadow-brand/5 max-w-2xl mx-auto">
      <div class="w-24 h-24 bg-brand/10 text-brand rounded-full items-center justify-center flex mx-auto mb-5">
        <svg class="w-12 h-12" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10" /></svg>
      </div>
      <h1 class="text-2xl font-bold text-slate-800 tracking-tight mb-4">{{ title }}</h1>
      <p class="text-lg text-slate-500 font-medium leading-relaxed mb-5">
        This public section is currently under construction as part of Phase 20 rollout. 
        It will be launched shortly to provide transparent access to our research ecosystem.
      </p>
      <router-link to="/" class="inline-flex items-center gap-2 px-5 py-3.5 bg-brand text-white font-bold rounded-xl shadow-lg shadow-brand/30 hover:-translate-y-0.5 transition-transform">
        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" /></svg>
        Back to Home
      </router-link>
    </div>
  </div>
</template>

<script setup>
import { computed } from 'vue'
import { useRoute } from 'vue-router'

const route = useRoute()
const title = computed(() => {
  if (route.path.includes('calls')) return 'Research Call for Proposals'
  if (route.path.includes('publications')) return 'Research Publications'
  if (route.path.includes('events')) return 'News & Events'
  if (route.path.includes('researchers')) return 'Our Researchers'
  if (route.path.includes('community')) return 'Community Problems'
  return 'Under Construction'
})
</script>
