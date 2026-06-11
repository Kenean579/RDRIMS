<template>
  <div class="flex flex-col gap-6 animate-fade">
    <div class="section-header">
      <div>
        <h1 class="section-title">Institutional Roles & Permissions</h1>
        <p class="section-subtitle">Manage custom roles and permission overrides for your university</p>
      </div>
      <button @click="openCreateRole" class="btn btn-primary">
        <svg class="w-4 h-4 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 4v16m8-8H4" /></svg>
        Create Custom Role
      </button>
    </div>

    <!-- Tabs -->
    <div class="flex gap-4 border-b border-slate-200">
      <button 
        v-for="t in ['Roles', 'Permissions Explorer']" :key="t"
        @click="activeTab = t"
        class="pb-3 px-1 text-sm font-bold transition-all relative"
        :class="activeTab === t ? 'text-brand' : 'text-slate-400 hover:text-slate-600'"
      >
        {{ t }}
        <div v-if="activeTab === t" class="absolute bottom-0 left-0 right-0 h-0.5 bg-brand rounded-full"></div>
      </button>
    </div>

    <div v-if="activeTab === 'Roles'">
      <div v-if="loading" class="grid grid-cols-1 md:grid-cols-2 gap-4">
        <div v-for="i in 4" :key="i" class="h-32 bg-slate-50 animate-pulse rounded-3xl border border-slate-100"></div>
      </div>
      
      <div v-else class="grid grid-cols-1 md:grid-cols-2 gap-5">
        <div v-for="role in roles" :key="role.id" 
          class="card p-6 flex flex-col justify-between group hover:border-brand/30 transition-all cursor-pointer"
          @click="managePermissions(role)"
        >
          <div>
            <div class="flex justify-between items-start mb-3">
              <div class="flex items-center gap-2.5">
                 <div class="w-10 h-10 rounded-xl bg-slate-100 flex items-center justify-center text-slate-500">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/></svg>
                 </div>
                 <div>
                    <h3 class="font-bold text-slate-800 tracking-tight">{{ role.name.replace(/_/g, ' ').toUpperCase() }}</h3>
                    <span v-if="!role.university_id" class="text-[10px] uppercase font-black tracking-widest text-brand bg-brand/5 px-2 py-0.5 rounded-md border border-brand/10">Global Role</span>
                    <span v-else class="text-[10px] uppercase font-black tracking-widest text-emerald-600 bg-emerald-50 px-2 py-0.5 rounded-md border border-emerald-100">Institutional</span>
                 </div>
              </div>
              <button class="text-brand opacity-0 group-hover:opacity-100 transition-all text-xs font-bold py-1 px-3 bg-brand/5 rounded-lg border border-brand/10">Manage →</button>
            </div>
            <p class="text-xs font-medium text-slate-500 leading-relaxed mb-4 line-clamp-2">
              {{ role.description || 'Baseline role defined by system platform.' }}
            </p>
          </div>
        </div>
      </div>
    </div>

    <!-- Permission Explorer -->
    <div v-else class="card p-8">
       <div class="flex items-center gap-4 mb-8">
          <div class="w-12 h-12 rounded-2xl bg-amber-50 text-amber-600 flex items-center justify-center">
             <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
          </div>
          <div>
             <h2 class="text-xl font-bold text-slate-800">Permissions Baseline</h2>
             <p class="text-xs font-medium text-slate-500">Explore all available system permissions that can be assigned to roles.</p>
          </div>
       </div>
       
       <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
          <div v-for="p in allPermissions" :key="p.id" class="p-4 rounded-2xl border border-slate-100 bg-slate-50/50">
             <div class="font-bold text-slate-700 text-xs mb-1">{{ p.name }}</div>
             <p class="text-[10px] text-slate-400 font-medium leading-normal">{{ p.description || 'Control access to system resources.' }}</p>
          </div>
       </div>
    </div>

    <!-- Create Role Modal -->
    <Modal :show="showCreate" title="Create Custom Institutional Role" @close="showCreate = false">
      <form @submit.prevent="createRole" class="space-y-6">
        <p class="text-xs font-medium text-slate-500 bg-slate-50 p-4 rounded-2xl border border-slate-100 italic">
          Institutional roles are only visible within your university. They inherit no permissions by default.
        </p>
        <div class="space-y-4">
          <div class="space-y-1.5">
            <label class="block text-xs font-bold text-slate-500 ml-1">Role Internal Name *</label>
            <input v-model="roleForm.name" type="text" required placeholder="e.g. dept_finance_auditor" class="input h-12" />
            <p class="text-[10px] text-slate-400 ml-1 italic leading-tight">Use lowercase and underscores for internal name.</p>
          </div>
          <div class="space-y-1.5">
            <label class="block text-xs font-bold text-slate-500 ml-1">Description</label>
            <textarea v-model="roleForm.description" rows="3" placeholder="Explain what this role manages..." class="input py-3 min-h-[100px]"></textarea>
          </div>
        </div>
        <div class="flex justify-end gap-3 pt-6 border-t border-slate-100">
          <button type="button" @click="showCreate = false" class="btn btn-secondary font-bold text-xs px-6">Discard</button>
          <button type="submit" class="btn btn-primary font-bold text-xs px-6" :disabled="submitting">
            Create Role
          </button>
        </div>
      </form>
    </Modal>

    <!-- Manage Permissions Panel (Slide-over / Modal) -->
    <Modal :show="!!activeRole" :title="'Manage Permissions: ' + activeRole?.name.replace(/_/g, ' ').toUpperCase()" @close="activeRole = null" size="xl">
       <div class="space-y-8 max-h-[80vh] overflow-y-auto px-1 scroll-smooth">
          <div class="flex items-center justify-between">
             <div>
                <h4 class="text-xs font-black text-slate-400 uppercase tracking-widest flex items-center gap-2">
                   <span class="w-2 h-2 rounded-full bg-brand"></span>
                   Effective Permission Set
                </h4>
             </div>
             <div class="flex gap-2">
                <span class="px-2 py-1 bg-brand/5 text-brand text-[9px] font-black border border-brand/10 rounded uppercase">Baseline</span>
                <span class="px-2 py-1 bg-amber-50 text-amber-600 text-[9px] font-black border border-amber-100 rounded uppercase">Override</span>
             </div>
          </div>

          <div v-if="loadingPerms" class="space-y-4">
             <div v-for="i in 5" :key="i" class="h-10 bg-slate-50 animate-pulse rounded-xl"></div>
          </div>

          <div v-else class="grid grid-cols-1 md:grid-cols-2 gap-x-8 gap-y-4">
             <div v-for="group in groupedPermissions" :key="group.name" class="space-y-3">
                <div class="text-[10px] font-black text-slate-800 uppercase tracking-widest border-b border-slate-100 pb-2 flex items-center justify-between">
                   {{ group.name }}
                   <span class="text-slate-300">{{ group.perms.length }} perms</span>
                </div>
                <div v-for="p in group.perms" :key="p.id" class="flex items-start gap-3 group/p">
                    <div class="relative flex items-center h-6">
                       <input 
                         type="checkbox" 
                         :checked="isPermGranted(p.id)"
                         @change="toggleOverride(p.id, $event.target.checked)"
                         class="w-5 h-5 rounded-lg border-2 border-slate-200 text-brand focus:ring-brand accent-brand cursor-pointer"
                       />
                       <div v-if="hasOverride(p.id)" class="absolute -top-1 -right-1 w-2.5 h-2.5 bg-amber-400 border-2 border-white rounded-full"></div>
                    </div>
                    <div>
                       <div class="text-xs font-bold text-slate-700 leading-none mb-1 group-hover/p:text-brand transition-colors cursor-pointer" @click="toggleOverride(p.id, !isPermGranted(p.id))">
                          {{ formatPermName(p.name) }}
                       </div>
                       <p class="text-[10px] text-slate-400 font-medium leading-tight line-clamp-1">{{ p.description || 'Allows ' + p.name + ' in the system.' }}</p>
                    </div>
                </div>
             </div>
          </div>

          <div class="sticky bottom-0 bg-white pt-6 pb-2 border-t border-slate-100 mt-8 flex justify-end gap-3 z-10">
             <button @click="activeRole = null" class="btn btn-secondary font-bold text-xs px-8">Discard</button>
             <button @click="saveOverrides" class="btn btn-primary font-bold text-xs px-8 shadow-lg shadow-brand/20" :disabled="submitting">
                Save & Apply Overrides
             </button>
          </div>
       </div>
    </Modal>
  </div>
</template>

<script setup>
import { ref, reactive, computed, onMounted } from 'vue'
import api from '@/services/api'
import { useNotificationStore } from '@/stores/notification'
import Modal from '@/components/Modal.vue'

const notif = useNotificationStore()
const activeTab = ref('Roles')
const roles = ref([])
const allPermissions = ref([])
const loading = ref(true)
const loadingPerms = ref(false)
const submitting = ref(false)

const showCreate = ref(false)
const roleForm = reactive({ name: '', description: '' })

const activeRole = ref(null)
const rolePermsData = reactive({ global_permissions: [], overrides: [] })
const localOverrides = ref([]) // list of {permission_id, granted}

async function fetchData() {
  loading.value = true
  try {
    const rolesRes = await api.get('/institution/roles')
    roles.value = rolesRes.data
    const permsRes = await api.get('/institution/permissions')
    allPermissions.value = permsRes.data
  } catch (err) {
    notif.error('Failed to load role data')
  } finally {
    loading.value = false
  }
}

function openCreateRole() {
  showCreate.value = true
  roleForm.name = ''
  roleForm.description = ''
}

async function createRole() {
  submitting.value = true
  try {
    await api.post('/institution/roles', roleForm)
    notif.success('Custom role created')
    showCreate.value = false
    fetchData()
  } catch (err) {
    notif.error(err.response?.data?.message || 'Failed to create role')
  } finally {
    submitting.value = false
  }
}

async function managePermissions(role) {
  activeRole.value = role
  loadingPerms.value = true
  try {
    const { data } = await api.get(`/institution/roles/${role.id}/permissions`)
    rolePermsData.global_permissions = data.global_permissions
    rolePermsData.overrides = data.overrides
    localOverrides.value = JSON.parse(JSON.stringify(data.overrides))
  } catch (err) {
    notif.error('Failed to load permissions')
  } finally {
    loadingPerms.value = false
  }
}

const groupedPermissions = computed(() => {
  const groups = {}
  allPermissions.value.forEach(p => {
    const category = p.name.split('_')[0].toUpperCase()
    if (!groups[category]) groups[category] = []
    groups[category].push(p)
  })
  return Object.keys(groups).sort().map(name => ({ name, perms: groups[name] }))
})

function isPermGranted(permId) {
  const override = localOverrides.value.find(o => o.permission_id === permId)
  if (override) return override.granted
  return rolePermsData.global_permissions.includes(permId)
}

function hasOverride(permId) {
  return localOverrides.value.some(o => o.permission_id === permId)
}

function toggleOverride(permId, granted) {
  const isGlobal = rolePermsData.global_permissions.includes(permId)
  const idx = localOverrides.value.findIndex(o => o.permission_id === permId)
  
  if (idx > -1) {
    // If we're toggling back to its global state, remove the override
    if (granted === isGlobal) {
      localOverrides.value.splice(idx, 1)
    } else {
      localOverrides.value[idx].granted = granted
    }
  } else {
    // If toggling AWAY from its global state, add an override
    if (granted !== isGlobal) {
      localOverrides.value.push({ permission_id: permId, granted })
    }
  }
}

async function saveOverrides() {
  submitting.value = true
  try {
    await api.post(`/institution/roles/${activeRole.value.id}/permissions`, {
      overrides: localOverrides.value
    })
    notif.success('Permissions updated successfully')
    activeRole.value = null
  } catch (err) {
    notif.error('Failed to save overrides')
  } finally {
    submitting.value = false
  }
}

function formatPermName(name) {
  return name.replace(/_/g, ' ').replace(/\b\w/g, l => l.toUpperCase())
}

onMounted(fetchData)
</script>
