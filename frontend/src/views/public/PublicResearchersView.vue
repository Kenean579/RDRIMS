<template>
  <div class="flex flex-col gap-12 pb-16">
    <section class="bg-white border-b border-slate-100 pt-12 pb-16">
      <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <h1 class="text-4xl md:text-5xl font-black text-slate-900 tracking-tight mb-3">Our Researchers</h1>
        <p class="text-lg text-slate-500 font-medium max-w-2xl">Meet the academic professionals driving innovation and discovery across our university network.</p>
      </div>
    </section>

    <section class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 w-full">
      <div class="flex flex-col sm:flex-row gap-4 mb-8">
        <div class="flex-1 relative">
          <svg class="absolute left-4 top-1/2 -translate-y-1/2 w-5 h-5 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
          <input v-model="search" type="text" placeholder="Search researchers by name, department, or expertise..."
            class="w-full pl-12 pr-4 py-3.5 bg-white border border-slate-200 rounded-2xl text-sm font-medium focus:ring-2 focus:ring-brand/30 focus:border-brand outline-none shadow-sm transition-all" />
        </div>
      </div>

      <div v-if="loading" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-6">
        <div v-for="i in 8" :key="i" class="bg-white rounded-2xl border border-slate-200 p-6 animate-pulse text-center">
          <div class="w-20 h-20 bg-slate-200 rounded-full mx-auto mb-4"></div>
          <div class="h-5 w-2/3 bg-slate-100 rounded mx-auto mb-2"></div>
          <div class="h-3 w-1/2 bg-slate-50 rounded mx-auto"></div>
        </div>
      </div>

      <div v-else-if="filteredUsers.length === 0" class="text-center py-20">
        <div class="h-20 w-20 bg-slate-100 rounded-full flex items-center justify-center mx-auto mb-6 text-3xl">👩‍🔬</div>
        <h3 class="text-xl font-black text-slate-700 mb-2">No researchers found</h3>
        <p class="text-sm text-slate-500 font-medium">Try adjusting your search terms.</p>
      </div>

      <div v-else class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-6">
        <div v-for="user in filteredUsers" :key="user.id"
          class="bg-white rounded-2xl border border-slate-200 p-6 shadow-sm hover:shadow-lg hover:-translate-y-1 transition-all group text-center">
          <!-- Avatar -->
          <div class="w-20 h-20 rounded-full mx-auto mb-4 flex items-center justify-center text-2xl font-black text-white shadow-lg"
            :class="avatarColors[user.id % avatarColors.length]">
            {{ getInitials(user.name) }}
          </div>
          <h3 class="text-base font-black text-slate-800 mb-1 group-hover:text-brand transition-colors">{{ user.name }}</h3>
          <p v-if="user.department?.name" class="text-xs font-bold text-slate-400 mb-3">{{ user.department.name }}</p>
          <p v-if="user.title" class="text-xs font-medium text-slate-500 mb-4">{{ user.title }}</p>

          <!-- Expertise Tags -->
          <div v-if="user.expertise?.length" class="flex flex-wrap justify-center gap-1.5 mt-2">
            <span v-for="exp in user.expertise.slice(0, 3)" :key="exp.id"
              class="px-2 py-0.5 bg-brand/5 text-brand text-[9px] font-black uppercase tracking-widest rounded-lg border border-brand/10">
              {{ exp.name }}
            </span>
            <span v-if="user.expertise.length > 3" class="px-2 py-0.5 bg-slate-50 text-slate-400 text-[9px] font-black rounded-lg">
              +{{ user.expertise.length - 3 }}
            </span>
          </div>
        </div>
      </div>
    </section>
  </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue'
import api from '@/services/api'

const users = ref([])
const loading = ref(true)
const search = ref('')

const avatarColors = [
  'bg-gradient-to-br from-indigo-500 to-purple-600',
  'bg-gradient-to-br from-emerald-500 to-teal-600',
  'bg-gradient-to-br from-amber-500 to-orange-600',
  'bg-gradient-to-br from-rose-500 to-pink-600',
  'bg-gradient-to-br from-cyan-500 to-blue-600',
  'bg-gradient-to-br from-violet-500 to-fuchsia-600',
]

const filteredUsers = computed(() => {
  if (!search.value) return users.value
  const q = search.value.toLowerCase()
  return users.value.filter(u =>
    u.name?.toLowerCase().includes(q) ||
    u.department?.name?.toLowerCase().includes(q) ||
    u.expertise?.some(e => e.name?.toLowerCase().includes(q))
  )
})

function getInitials(name) {
  if (!name) return '?'
  return name.split(' ').map(w => w[0]).join('').substring(0, 2).toUpperCase()
}

onMounted(async () => {
  try {
    const { data } = await api.get('/users', { params: { per_page: 50 } })
    users.value = data.data || data
  } catch (e) {}
  loading.value = false
})
</script>
