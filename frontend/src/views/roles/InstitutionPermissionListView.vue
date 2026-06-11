<template>
  <div class="flex flex-col gap-5 pb-6 animate-fade card">
    <div class="section-header">
      <div>
        <h1 class="section-title">Institution Permissions</h1>
        <p class="section-subtitle">Read-only reference of all available permissions in the system</p>
      </div>
    </div>

    <div v-if="loading" class="card p-8 flex justify-center"><div class="animate-spin rounded-full h-10 w-10 border-b-2 border-brand"></div></div>

    <div v-else-if="error" class="card p-8 text-center">
      <p class="text-rose-500 font-bold text-xs mb-4">{{ error }}</p>
      <button @click="fetchPermissions" class="btn btn-ghost text-xs font-bold border border-slate-100 px-6">Retry</button>
    </div>

    <div v-else class="card overflow-hidden">
      <div class="overflow-x-auto">
        <table class="table-auto">
          <thead>
            <tr>
              <th class="pl-8 py-4 text-xs font-medium text-slate-400">Permission</th>
              <th class="py-4 text-xs font-medium text-slate-400">Group</th>
              <th class="py-4 text-xs font-medium text-slate-400">Description</th>
            </tr>
          </thead>
          <tbody class="divide-y divide-slate-50">
            <tr v-for="perm in permissions" :key="perm.id" class="group hover:bg-slate-50/50 transition-colors">
              <td class="pl-8 py-4">
                <span class="text-sm font-bold text-slate-800">{{ perm.name }}</span>
              </td>
              <td class="py-4">
                <span class="px-2.5 py-1 bg-brand/5 text-brand text-xs font-bold rounded-lg border border-brand/10">{{ perm.group || 'General' }}</span>
              </td>
              <td class="py-4 text-sm text-slate-500 font-medium">{{ perm.description || '—' }}</td>
            </tr>
          </tbody>
        </table>
      </div>
      <div v-if="permissions.length === 0" class="p-5 text-center">
        <p class="text-sm font-medium text-slate-400 italic">No permissions found.</p>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import api from '@/services/api'

const permissions = ref([])
const loading = ref(true)
const error = ref(null)

async function fetchPermissions() {
  loading.value = true; error.value = null
  try {
    const { data } = await api.get('/lookups/permissions')
    permissions.value = Array.isArray(data) ? data : (data.data || [])
  } catch (e) {
    error.value = 'Failed to load permissions'
  } finally { loading.value = false }
}

onMounted(fetchPermissions)
</script>
