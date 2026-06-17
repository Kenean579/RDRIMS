<template>
  <div class="flex flex-col gap-6 animate-fade">
    <!-- Header -->
    <div class="section-header">
      <div>
        <h1 class="section-title">Institutional Roles & Permissions</h1>
        <p class="section-subtitle">Manage custom roles and permission overrides for your {{ auth.userScope }}</p>
      </div>
      <button @click="openCreateRole" class="btn btn-primary" v-if="auth.isAdmin" aria-label="Create a new institutional role">
        <svg class="w-4 h-4 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 4v16m8-8H4" /></svg>
        Create {{ auth.userScope.replace('_', ' ') }} Role
      </button>
    </div>

    <!-- Tabs -->
    <div class="flex gap-4 border-b border-slate-200" role="tablist" aria-label="Role management tabs">
      <button 
        v-for="t in ['Roles', 'Permissions Explorer']" :key="t"
        @click="activeTab = t"
        role="tab"
        :aria-selected="activeTab === t"
        :aria-controls="t === 'Roles' ? 'panel-roles' : 'panel-permissions'"
        class="pb-3 px-1 text-sm font-semibold transition-all duration-200 relative focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-blue-500 focus-visible:ring-offset-2 rounded-t-lg"
        :class="activeTab === t ? 'text-blue-600' : 'text-slate-400 hover:text-slate-600'"
      >
        {{ t }}
        <div v-if="activeTab === t" class="absolute bottom-0 left-0 right-0 h-0.5 bg-blue-600 rounded-full transition-all duration-300"></div>
      </button>
    </div>

    <!-- Roles Tab -->
    <div v-if="activeTab === 'Roles'" id="panel-roles" role="tabpanel">
      <!-- Loading State -->
      <div v-if="loading" class="grid grid-cols-1 md:grid-cols-2 gap-5">
        <div v-for="i in 4" :key="i" class="bg-white border border-slate-200 rounded-2xl p-6 animate-pulse">
          <div class="flex items-start justify-between mb-4">
            <div class="flex items-center gap-3">
              <div class="w-11 h-11 rounded-xl bg-slate-100"></div>
              <div>
                <div class="h-4 w-32 bg-slate-100 rounded-lg mb-2"></div>
                <div class="h-3 w-20 bg-slate-50 rounded-lg"></div>
              </div>
            </div>
            <div class="w-8 h-8 bg-slate-50 rounded-lg"></div>
          </div>
          <div class="h-3 w-full bg-slate-50 rounded-lg mb-2"></div>
          <div class="h-3 w-2/3 bg-slate-50 rounded-lg"></div>
        </div>
      </div>
      
      <!-- Role Cards Grid -->
      <div v-else class="grid grid-cols-1 md:grid-cols-2 gap-5">
        <div
          v-for="role in roles"
          :key="role.id"
          class="bg-white border border-slate-200 rounded-2xl p-6 group hover:border-blue-200 hover:shadow-lg transition-all duration-300 relative"
          tabindex="0"
          :aria-label="'Role: ' + role.name.replace(/_/g, ' ')"
        >
          <!-- Card Header -->
          <div class="flex justify-between items-start mb-3">
            <div class="flex items-center gap-3">
              <div class="w-11 h-11 rounded-xl bg-blue-50 text-blue-600 flex items-center justify-center shrink-0">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/></svg>
              </div>
              <div>
                <h3 class="text-lg font-semibold text-slate-900 leading-tight">{{ role.name.replace(/_/g, ' ') }}</h3>
                <div class="flex gap-1.5 mt-1">
                  <span v-if="!role.university_id && !role.campus_id && !role.faculty_id && !role.department_id && !role.research_center_id" class="text-xs font-medium text-blue-700 bg-blue-50 px-2.5 py-0.5 rounded-full border border-blue-100">Global System Role</span>
                  <span v-else class="text-xs font-medium text-green-700 bg-green-50 px-2.5 py-0.5 rounded-full border border-green-100">
                    {{ getRoleLevelDisplay(role) }} scoped
                  </span>
                </div>
              </div>
            </div>

            <!-- Three Dot Menu -->
            <ActionMenu
              :actions="getRoleActions(role)"
              align="right"
              size="sm"
            />
          </div>

          <!-- Description -->
          <p class="text-sm text-slate-500 leading-relaxed mb-4 line-clamp-2">
            {{ role.description || 'Baseline role with hierarchical permission resolution.' }}
          </p>
        </div>
      </div>
    </div>

    <!-- Permission Explorer Tab -->
    <div v-else id="panel-permissions" role="tabpanel" class="bg-white border border-slate-200 rounded-2xl p-8">
       <div class="flex items-center gap-4 mb-8">
          <div class="w-12 h-12 rounded-xl bg-amber-50 text-amber-600 flex items-center justify-center shrink-0">
             <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
          </div>
          <div>
             <h2 class="text-xl font-semibold text-slate-900">Permissions Baseline</h2>
             <p class="text-sm text-slate-500">Explore all available system permissions that can be assigned to roles.</p>
          </div>
       </div>
       
       <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
          <div
            v-for="p in allPermissions"
            :key="p.id"
            class="p-4 rounded-xl border border-slate-200 bg-white hover:border-blue-200 hover:bg-blue-50/30 transition-all duration-200"
            tabindex="0"
            :aria-label="'Permission: ' + p.name"
          >
             <div class="font-medium text-slate-900 text-sm mb-1">{{ p.name }}</div>
             <p class="text-xs text-slate-500 leading-relaxed">{{ p.description || 'Control access to system resources.' }}</p>
          </div>
       </div>
    </div>

    <!-- Create Role Modal -->
    <Modal :show="showCreate" :title="'Create Custom ' + auth.userScope.replace('_', ' ') + ' Role'" @close="showCreate = false">
      <form @submit.prevent="createRole" class="space-y-6">
        <div class="bg-blue-50 p-4 rounded-xl border border-blue-100">
          <p class="text-xs font-medium text-slate-700 leading-relaxed">
            This role will be created at the <strong class="text-blue-700">{{ auth.userScope.toUpperCase() }}</strong> level and will be visible to all users within this branch of the hierarchy.
          </p>
        </div>
        <div class="space-y-4">
          <div class="space-y-1.5">
            <label class="block text-xs font-medium text-slate-500 ml-1" for="inst-role-name">Role Internal Name *</label>
            <input id="inst-role-name" v-model="roleForm.name" type="text" required placeholder="e.g. branch_research_viewer" class="input h-12" aria-required="true" />
            <p class="text-xs text-slate-400 ml-1 leading-tight">Use lowercase and underscores, e.g. 'custom_dept_reviewer'.</p>
          </div>
          <div class="space-y-1.5">
            <label class="block text-xs font-medium text-slate-500 ml-1" for="inst-role-desc">Description</label>
            <textarea id="inst-role-desc" v-model="roleForm.description" rows="3" placeholder="Explain what this role manages..." class="input py-3 min-h-[100px]"></textarea>
          </div>
        </div>
        <div class="flex justify-end gap-3 pt-6 border-t border-slate-200">
          <button type="button" @click="showCreate = false" class="bg-slate-100 hover:bg-slate-200 text-slate-700 px-6 py-2.5 rounded-xl text-sm font-medium transition-colors duration-200">Discard</button>
          <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white px-6 py-2.5 rounded-xl text-sm font-medium transition-colors duration-200 shadow-sm" :disabled="submitting">
            Create {{ auth.userScope.replace('_', ' ') }} Role
          </button>
        </div>
      </form>
    </Modal>

    <!-- Manage Permissions Panel -->
    <Modal :show="!!activeRole" :title="'Manage Permissions: ' + activeRole?.name.replace(/_/g, ' ')" @close="activeRole = null" size="xl">
       <div class="space-y-8 max-h-[80vh] overflow-y-auto px-1 scroll-smooth">
          <!-- Info Banner -->
          <div class="bg-amber-50 p-4 rounded-xl border border-amber-100 flex items-start gap-3">
             <svg class="w-5 h-5 text-amber-600 mt-0.5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
             <p class="text-xs font-medium text-amber-700 leading-relaxed">
               Overrides applied here are specific to your <strong>{{ auth.userScope.toUpperCase() }}</strong>. 
               Checked perms that were already "Global" will become "Hard Overrides" for this scope.
             </p>
          </div>

          <!-- Legend -->
          <div class="flex items-center justify-between">
             <h4 class="text-sm font-semibold text-slate-500 flex items-center gap-2">
                <span class="w-2 h-2 rounded-full bg-blue-600"></span>
                Effective Hierarchical Settings
             </h4>
             <div class="flex gap-2">
                <span class="px-2.5 py-1 bg-blue-50 text-blue-700 text-xs font-medium border border-blue-100 rounded-full">Global Baseline</span>
                <span class="px-2.5 py-1 bg-amber-50 text-amber-700 text-xs font-medium border border-amber-100 rounded-full">Custom Override</span>
             </div>
          </div>

          <!-- Loading -->
          <div v-if="loadingPerms" class="space-y-4">
             <div v-for="i in 5" :key="i" class="h-10 bg-slate-50 animate-pulse rounded-xl"></div>
          </div>

          <!-- Permission Groups -->
          <div v-else class="grid grid-cols-1 md:grid-cols-2 gap-x-8 gap-y-6">
             <div v-for="group in groupedPermissions" :key="group.name" class="space-y-3">
                <div class="text-xs font-semibold text-slate-900 border-b border-slate-200 pb-2 flex items-center justify-between sticky top-0 bg-white z-10 pt-1">
                   {{ group.name }}
                   <span class="text-slate-400 font-medium">{{ group.perms.length }} perms</span>
                </div>
                <div
                  v-for="p in group.perms"
                  :key="p.id"
                  class="flex items-start gap-3 group/p p-3 rounded-xl border transition-all duration-200 cursor-pointer"
                  :class="isPermGranted(p.id)
                    ? 'border-blue-500 bg-blue-50'
                    : 'border-slate-200 hover:border-blue-200 hover:bg-blue-50/30'"
                  @click="toggleOverride(p.id, !isPermGranted(p.id))"
                  tabindex="0"
                  role="checkbox"
                  :aria-checked="isPermGranted(p.id)"
                  :aria-label="'Permission: ' + formatPermName(p.name)"
                  @keydown.space.prevent="toggleOverride(p.id, !isPermGranted(p.id))"
                  @keydown.enter.prevent="toggleOverride(p.id, !isPermGranted(p.id))"
                >
                    <div class="relative flex items-center h-6">
                       <input 
                         type="checkbox" 
                         :checked="isPermGranted(p.id)"
                         @change.stop="toggleOverride(p.id, $event.target.checked)"
                         @click.stop
                         class="w-5 h-5 rounded-lg border-2 border-slate-200 text-blue-600 focus:ring-blue-500 accent-blue-600 cursor-pointer"
                       />
                       <div v-if="hasOverride(p.id)" class="absolute -top-1 -right-1 w-2.5 h-2.5 bg-amber-400 border-2 border-white rounded-full" title="Custom override"></div>
                    </div>
                    <div class="flex-1 min-w-0">
                       <div class="text-sm font-medium text-slate-900 leading-none mb-1 group-hover/p:text-blue-700 transition-colors duration-200">
                          {{ formatPermName(p.name) }}
                       </div>
                       <p class="text-xs text-slate-500 leading-tight overflow-hidden text-ellipsis">{{ p.description || 'Allows ' + p.name.replace(/_/g, ' ') + ' access.' }}</p>
                    </div>
                </div>
             </div>
          </div>

          <!-- Footer -->
          <div class="sticky bottom-0 bg-white pt-6 pb-2 border-t border-slate-200 mt-8 flex justify-end gap-3 z-10">
             <button @click="activeRole = null" class="bg-slate-100 hover:bg-slate-200 text-slate-700 px-8 py-2.5 rounded-xl text-sm font-medium transition-colors duration-200">Discard Changes</button>
             <button @click="saveOverrides" class="bg-blue-600 hover:bg-blue-700 text-white px-8 py-2.5 rounded-xl text-sm font-medium transition-colors duration-200 shadow-sm" :disabled="submitting">
                Save Hierarchy Overrides
             </button>
          </div>
       </div>
    </Modal>
  </div>
</template>

<script setup>
import { ref, reactive, computed, onMounted } from 'vue'
import api from '@/services/api'
import { useAuthStore } from '@/stores/auth'
import { useNotificationStore } from '@/stores/notification'
import Modal from '@/components/Modal.vue'
import ActionMenu from '@/components/ActionMenu.vue'

const auth = useAuthStore()
const notif = useNotificationStore()
const activeTab = ref('Roles')
const roles = ref([])
const allPermissions = ref([])
const loading = ref(true)
const loadingPerms = ref(false)
const submitting = ref(false)

const showCreate = ref(false)
const roleForm = reactive({ name: '', description: '', level: '' })

const activeRole = ref(null)
const rolePermsData = reactive({ global_permissions: [], overrides: [] })
const localOverrides = ref([]) // list of {permission_id, granted}

function getRoleActions(role) {
  return [
    { label: 'Manage Permissions', key: 'permissions', handler: () => managePermissions(role) },
    { label: 'View Details', key: 'view', handler: () => managePermissions(role) },
  ]
}

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

function getRoleLevelDisplay(role) {
  if (role.research_center_id) return 'Research Center'
  if (role.department_id) return 'Department'
  if (role.faculty_id) return 'Faculty'
  if (role.campus_id) return 'Campus'
  if (role.university_id) return 'University'
  return 'Global'
}

function openCreateRole() {
  showCreate.value = true
  roleForm.name = ''
  roleForm.description = ''
  roleForm.level = auth.userScope
}

async function createRole() {
  submitting.value = true
  try {
    await api.post('/institution/roles', roleForm)
    notif.success(`${auth.userScope.toUpperCase().replace('_', ' ')} role created`)
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
    // Filter local overrides specifically for this user's current scope level if needed, 
    // but the backend already returns those relevant to the user.
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
    if (granted === isGlobal) {
      localOverrides.value.splice(idx, 1)
    } else {
      localOverrides.value[idx].granted = granted
    }
  } else {
    if (granted !== isGlobal) {
      localOverrides.value.push({ permission_id: permId, granted })
    }
  }
}

async function saveOverrides() {
  submitting.value = true
  try {
    await api.post(`/institution/roles/${activeRole.value.id}/permissions`, {
      level: auth.userScope,
      overrides: localOverrides.value
    })
    notif.success('Hierarchy overrides saved and applied')
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
