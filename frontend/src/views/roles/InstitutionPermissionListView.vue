<template>
  <div class="flex flex-col gap-6 animate-fade">
    <!-- Header -->
    <div class="section-header">
      <div>
        <h1 class="section-title">Institution Permissions</h1>
        <p class="section-subtitle">Read-only reference of all available permissions in the system</p>
      </div>
    </div>

    <!-- Loading State -->
    <div v-if="loading" class="bg-white border border-slate-200 rounded-2xl p-8 flex justify-center">
      <div class="flex flex-col items-center gap-3">
        <div class="animate-spin rounded-full h-10 w-10 border-b-2 border-blue-600"></div>
        <p class="text-sm text-slate-400 font-medium">Loading permissions…</p>
      </div>
    </div>

    <!-- Error State -->
    <div v-else-if="error" class="bg-white border border-slate-200 rounded-2xl p-8 text-center">
      <div class="w-12 h-12 rounded-xl bg-red-50 text-red-600 flex items-center justify-center mx-auto mb-4">
        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
      </div>
      <p class="text-sm font-medium text-red-600 mb-4">{{ error }}</p>
      <button @click="fetchPermissions" class="bg-slate-100 hover:bg-slate-200 text-slate-700 px-6 py-2.5 rounded-xl text-sm font-medium transition-colors duration-200" aria-label="Retry loading permissions">Retry</button>
    </div>

    <!-- Permissions Table -->
    <div v-else class="bg-white border border-slate-200 rounded-2xl overflow-hidden">
      <div class="overflow-x-auto">
        <table class="table-auto" role="table" aria-label="System permissions table">
          <thead>
            <tr>
              <th class="pl-8 py-4 text-xs font-medium text-slate-500">Permission</th>
              <th class="py-4 text-xs font-medium text-slate-500">Group</th>
              <th class="py-4 text-xs font-medium text-slate-500">Description</th>
            </tr>
          </thead>
          <tbody class="divide-y divide-slate-100">
            <tr
              v-for="perm in permissions"
              :key="perm.id"
              class="group hover:bg-blue-50/30 transition-colors duration-200"
            >
              <td class="pl-8 py-4">
                <span class="text-sm font-semibold text-slate-900">{{ perm.name }}</span>
              </td>
              <td class="py-4">
                <span class="px-2.5 py-1 bg-blue-50 text-blue-700 text-xs font-medium rounded-full border border-blue-100">{{ perm.group || 'General' }}</span>
              </td>
              <td class="py-4 text-sm text-slate-500">{{ perm.description || '—' }}</td>
            </tr>
          </tbody>
        </table>
      </div>
      <div v-if="permissions.length === 0" class="p-12 text-center">
        <div class="w-12 h-12 rounded-xl bg-slate-100 text-slate-400 flex items-center justify-center mx-auto mb-4">
          <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4"/></svg>
        </div>
        <p class="text-sm font-medium text-slate-500">No permissions found.</p>
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
