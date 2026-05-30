<template>
  <div class="flex flex-col gap-8 pb-12 animate-fade card">
    <!-- Header -->
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
      <div>
        <h1 class="text-3xl font-black text-slate-900 tracking-tight">Settings</h1>
        <p class="text-slate-500 font-medium mt-1">Control how the system works.</p>
      </div>
      <button @click="fetchSettings" class="btn btn-secondary h-11 px-6 shadow-sm group">
        <svg class="w-4 h-4 mr-1.5 group-hover:rotate-180 transition-transform duration-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" /></svg>
        Refresh
      </button>
    </div>

    <!-- Content -->
    <div v-if="loading" class="card p-12 space-y-4">
      <div v-for="i in 5" :key="i" class="h-10 bg-slate-50/50 rounded-xl animate-pulse"></div>
    </div>
    
    <div v-else-if="error" class="card border-rose-100 bg-rose-50/30 p-12 text-center max-w-2xl mx-auto shadow-xl shadow-rose-500/5">
       <div class="w-16 h-16 bg-rose-100 text-rose-600 rounded-2xl flex items-center justify-center mx-auto mb-5 text-2xl shadow-inner">⚠️</div>
       <h3 class="text-lg font-black text-slate-800 mb-2 uppercase tracking-tight">Access Error</h3>
       <p class="text-sm text-rose-600 font-bold mb-6 uppercase tracking-widest text-[11px]">{{ error }}</p>
       <button @click="fetchSettings" class="btn bg-rose-600 hover:bg-rose-700 text-white px-8 h-11 text-[11px] font-black uppercase tracking-widest border-0">Retry Connection</button>
    </div>

    <div v-else-if="settings.length === 0" class="card">
      <EmptyState icon="⚙️" title="No settings found" description="System controls will appear here once ready." />
    </div>

    <div v-else class="card overflow-hidden">
      <div class="overflow-x-auto">
        <table class="table-auto">
          <thead>
            <tr>
              <th class="pl-8 py-4 text-[10px] font-black uppercase tracking-widest text-slate-400 text-left">Internal Name</th>
              <th class="py-4 text-[10px] font-black uppercase tracking-widest text-slate-400 text-left">Current Value</th>
              <th class="py-4 text-[10px] font-black uppercase tracking-widest text-slate-400 text-left">What it does</th>
              <th class="pr-8 py-4 text-[10px] font-black uppercase tracking-widest text-slate-400 text-right">Actions</th>
            </tr>
          </thead>
          <tbody class="divide-y divide-slate-50 font-bold">
            <tr v-for="s in settings" :key="s.id" class="group hover:bg-slate-50/50 transition-colors">
              <td class="pl-8 py-5">
                <span class="text-[10px] font-black bg-slate-100 text-slate-500 px-2 py-1 rounded-lg uppercase tracking-widest border border-slate-200 shadow-sm">{{ s.key }}</span>
              </td>
              <td class="min-w-[280px] py-5">
                <div v-if="editingId === s.id" class="flex gap-2 pr-4">
                  <input v-model="editValue" type="text" class="input h-10 px-4 font-black text-slate-700" @keyup.enter="saveSetting(s)" auto-focus />
                </div>
                <span v-else class="text-sm font-black text-slate-800">{{ s.value }}</span>
              </td>
              <td class="py-5">
                <p class="text-xs text-slate-500 font-medium leading-relaxed max-w-xs">{{ s.description }}</p>
              </td>
              <td class="pr-8 py-5 text-right">
                <div class="flex justify-end gap-2">
                  <template v-if="editingId === s.id">
                    <button @click="saveSetting(s)" class="btn btn-primary h-9 px-4 text-[10px] font-black uppercase tracking-widest shadow-lg shadow-blue-500/20">Save</button>
                    <button @click="editingId = null" class="btn btn-secondary h-9 px-4 text-[10px] font-black uppercase tracking-widest">Cancel</button>
                  </template>
                  <button v-else @click="startEdit(s)" class="btn btn-ghost hover:bg-white text-brand text-[10px] font-black uppercase tracking-widest h-9 px-4">Change</button>
                </div>
              </td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import { useNotificationStore } from '@/stores/notification'
import api from '@/services/api'
import EmptyState from '@/components/EmptyState.vue'

const notif = useNotificationStore()
const settings = ref([]); const loading = ref(true); const error = ref(null)
const editingId = ref(null); const editValue = ref('')

async function fetchSettings() {
  loading.value = true; error.value = null
  try { const { data } = await api.get('/settings'); settings.value = data }
  catch (err) { error.value = err.response?.data?.message || 'Failed' } finally { loading.value = false }
}

function startEdit(s) { editingId.value = s.id; editValue.value = s.value }

async function saveSetting(s) {
  try { await api.put(`/settings/${s.id}`, { value: editValue.value }); notif.success('Updated!'); editingId.value = null; fetchSettings() }
  catch (err) { notif.error(err.response?.data?.message || 'Failed') }
}

onMounted(fetchSettings)
</script>
