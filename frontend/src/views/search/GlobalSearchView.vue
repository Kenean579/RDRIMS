<template>
  <div class="card p-8">
    <div class="mb-6">
      <h1 class="text-xl font-bold text-gray-800">Search Results</h1>
      <p v-if="query" class="text-sm text-gray-500 mt-1">
        Results for "{{ query }}"
      </p>
    </div>

    <!-- Search Input -->
    <div class="bg-white rounded-2xl shadow-sm border border-slate-100 p-4 mb-6">
      <div class="relative">
        <span class="absolute left-3 top-1/2 -translate-y-1/2 text-gray-400">🔍</span>
        <input
          v-model="query"
          type="text"
          placeholder="Search across proposals, projects, publications, and more..."
          class="w-full border border-gray-300 rounded-2xl pl-10 pr-4 py-2.5 text-sm focus:ring-2 focus:ring-blue-500 outline-none"
          @keyup.enter="performSearch"
        />
      </div>
    </div>

    <!-- Loading -->
    <div v-if="loading" class="card p-8">
      <LoadingSkeleton :rows="6" />
    </div>

    <!-- Results -->
    <div v-else-if="searched && results.length === 0" class="bg-white rounded-2xl shadow-sm border border-slate-100 p-5">
      <EmptyState icon="🔍" title="No results found" description="Try adjusting your search terms or checking different keywords." />
    </div>

    <div v-else-if="searched && results.length > 0" class="space-y-4">
      <div v-for="(result, index) in results" :key="index"
        class="bg-white rounded-2xl shadow-sm border border-slate-100 p-5 hover:shadow-md transition cursor-pointer"
        @click="navigateToResult(result)">
        <div class="flex items-center gap-3">
          <span class="text-lg">{{ getIcon(result.type) }}</span>
          <div class="flex-1">
            <div class="flex items-center gap-2">
              <span class="text-xs font-medium bg-blue-100 text-blue-700 px-2 py-0.5 rounded-full ">
                {{ result.type }}
              </span>
              <p class="text-sm font-semibold text-gray-800">{{ result.title }}</p>
            </div>
            <p class="text-xs text-gray-500 mt-1" v-if="result.description">
              {{ result.description?.substring(0, 150) }}
            </p>
          </div>
        </div>
      </div>
    </div>

    <!-- Initial State -->
    <div v-else class="bg-white rounded-2xl shadow-sm border border-slate-100 p-5 text-center text-gray-400">
      <EmptyState icon="🔎" title="Search RDRIMS" description="Type a keyword and press Enter to search across all records." />
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import { useRouter, useRoute } from 'vue-router'
import api from '@/services/api'
import LoadingSkeleton from '@/components/LoadingSkeleton.vue'
import EmptyState from '@/components/EmptyState.vue'

const router = useRouter()
const route = useRoute()
const query = ref('')
const results = ref([])
const loading = ref(false)
const searched = ref(false)

onMounted(() => {
  if (route.query.q) {
    query.value = route.query.q
    performSearch()
  }
})

function getIcon(type) {
  const icons = {
    proposal: '📝', project: '📁', publication: '📄', output: '📤',
    patent: '💡', event: '📅', partner: '🤝', community: '🏘️', user: '👤'
  }
  return icons[type] || '📌'
}

async function performSearch() {
  if (!query.value.trim()) return
  loading.value = true
  searched.value = true
  try {
    const { data } = await api.get('/search', { params: { query: query.value } })
    results.value = data.data || data
  } catch (e) {
    results.value = []
  } finally {
    loading.value = false
  }
}

function navigateToResult(result) {
  const routes = {
    proposal: `/proposals/${result.id}`,
    project: `/projects/${result.id}`,
    publication: `/publications/${result.id}`,
    output: `/outputs/${result.id}`,
    patent: `/patents/${result.id}`,
    event: `/events/${result.id}`,
    partner: `/partners/${result.id}`,
    community: `/community`,
    user: `/users/${result.id}`,
  }
  const path = routes[result.type]
  if (path) router.push(path)
}
</script>
