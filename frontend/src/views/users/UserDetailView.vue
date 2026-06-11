<template>
  <div class="flex flex-col gap-5 pb-6 animate-fade card">
    <!-- Header & Navigation -->
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-6">
      <div class="flex-1">
        <router-link to="/app/users" class="flex items-center gap-2 text-brand font-bold text-xs mb-3 hover:translate-x-1 transition-transform">
          <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M10 19l-7-7m0 0l7-7m-7 7h18" /></svg>
          Back to Users
        </router-link>
        <h1 class="text-xl font-bold text-slate-900 tracking-tight leading-tight">{{ user.name || 'User Profile' }}</h1>
        <p class="text-slate-500 font-bold mt-1">{{ user.email }}</p>
      </div>
      <div class="flex items-center gap-3">
         <span :class="user.is_active ? 'text-emerald-600 border-emerald-200' : 'text-slate-400 border-slate-100'" class="px-4 py-1 rounded-full text-xs font-medium border">
           {{ user.is_active ? 'Active Status' : 'Inactive Account' }}
         </span>
      </div>
    </div>

    <div v-if="loading" class="grid grid-cols-1 lg:grid-cols-3 gap-5">
      <div class="card h-96 animate-pulse bg-slate-50/50"></div>
      <div class="lg:col-span-2 space-y-8">
        <div class="card h-48 animate-pulse bg-slate-50/50"></div>
        <div class="card h-48 animate-pulse bg-slate-50/50"></div>
      </div>
    </div>

    <template v-else>
      <div class="grid grid-cols-1 lg:grid-cols-3 gap-5 font-bold">
        <!-- Profile Widget -->
        <div class="space-y-8">
          <div class="card p-8 flex flex-col items-center text-center relative overflow-hidden">
            <div class="absolute top-0 left-0 w-full h-24 bg-slate-100 opacity-10"></div>
            <div class="w-24 h-24 rounded-[2.5rem] bg-slate-100 flex items-center justify-center text-xl font-bold text-white shadow-xl shadow-brand/20 mb-6 relative z-10 border-4 border-white overflow-hidden shrink-0">
              <img v-if="imageUrl(user.profile_image)" :src="imageUrl(user.profile_image)" class="w-full h-full object-cover"/>
              <span v-else>{{ getInitials(user.name) }}</span>
            </div>
            <h3 class="text-xl font-bold text-slate-900 mb-1">{{ user.name }}</h3>
            <p class="text-xs text-slate-400 font-bold mb-6">{{ user.department?.name || 'Academic Staff' }}</p>
            
            <div class="w-full space-y-4 text-left">
              <div class="p-4 rounded-2xl border border-slate-100">
                <p class="text-xs font-medium text-slate-400 mb-1.5 flex items-center gap-1.5">
                   <i class="far fa-envelope text-brand"></i>
                   Contact Email
                </p>
                <p class="text-xs text-slate-700 truncate">{{ user.email }}</p>
              </div>
              <div v-if="user.orcid_id" class="p-4 rounded-2xl border border-slate-100">
                <p class="text-xs font-medium text-slate-400 mb-1.5 flex items-center gap-1.5">
                   <i class="fab fa-orcid text-[#A6CE39]"></i>
                   ORCID Identifier
                </p>
                <p class="text-xs text-slate-700 font-bold">{{ user.orcid_id }}</p>
              </div>
            </div>

            <div v-if="user.bio" class="mt-5 text-left">
              <p class="text-xs font-medium text-slate-400 mb-2 ml-1">biography</p>
              <p class="text-xs text-slate-600 leading-relaxed font-medium italic p-4 bg-indigo-50/30 rounded-2xl border border-indigo-100/30">{{ user.bio }}</p>
            </div>
          </div>
        </div>

        <!-- Details Widget -->
        <div class="lg:col-span-2 space-y-8">
           <!-- Roles & Permissions -->
           <div class="card p-8">
             <div class="flex items-center justify-between mb-5">
               <h2 class="text-xs font-medium text-slate-400 flex items-center gap-2">
                 <span class="w-1 h-3 bg-brand rounded-full"></span>
                 Access Roles
               </h2>
               <button @click="showAssignRole = true" class="text-xs font-medium text-brand hover:underline">+ Assign Role</button>
             </div>
             
             <div v-if="user.roles?.length" class="flex flex-wrap gap-3">
               <div v-for="role in user.roles" :key="role.id" class="flex items-center gap-3 px-4 py-2 text-indigo-700 rounded-2xl border border-indigo-300 group">
                  <span class="text-xs font-bold">{{ role.name }}</span>
                  <button @click="revokeRole(role)" class="text-indigo-300 hover:text-rose-500 transition-colors">
                    <i class="fas fa-times-circle"></i>
                  </button>
               </div>
             </div>
             <div v-else class="p-6 text-center bg-slate-50 rounded-3xl border border-dashed border-slate-100">
               <p class="text-xs font-bold text-slate-300">No access roles assigned</p>
             </div>
           </div>

           <!-- Multi-Column Center & Expertise -->
           <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
             <!-- Centers -->
             <div class="card p-8">
               <div class="flex items-center justify-between mb-6">
                 <h2 class="text-xs font-medium text-slate-400">Research Hubs</h2>
                 <button @click="showAssignCenter = true" class="text-xs font-medium text-brand hover:underline">+ Add Hub</button>
               </div>
               
               <div v-if="user.research_centers?.length" class="space-y-3">
                 <div v-for="rc in user.research_centers" :key="rc.id" class="flex items-center justify-between p-4 bg-white rounded-2xl border border-slate-100 shadow-sm group">
                   <div class="min-w-0">
                      <p class="text-xs font-bold text-slate-800 truncate mb-1">{{ rc.name }}</p>
                      <p class="text-xs text-slate-400">{{ rc.code || 'HUB' }} Center</p>
                   </div>
                   <button @click="detachCenter(rc)" class="text-slate-300 hover:text-rose-500 transition-colors">
                      <i class="fas fa-unlink text-xs"></i>
                   </button>
                 </div>
               </div>
               <div v-else class="p-5 text-center bg-white rounded-2xl border border-dashed border-slate-100">
                  <p class="text-xs font-medium text-slate-300">Not assigned to any hub</p>
               </div>
             </div>

             <!-- Expertise -->
             <div class="card p-8">
               <div class="flex items-center justify-between mb-6">
                 <h2 class="text-xs font-medium text-slate-400">Domain Expertise</h2>
                 <button @click="showAssignExpertise = true" class="text-xs font-medium text-brand hover:underline">+ Add Tag</button>
               </div>
               
               <div v-if="user.expertise?.length" class="flex flex-wrap gap-2">
                 <span v-for="exp in user.expertise" :key="exp.id" class="inline-flex items-center gap-2.5 px-3 py-1.5 bg-white border border-slate-100 rounded-2xl text-xs font-medium text-slate-600  tracking-tighter">
                   {{ exp.name }}
                   <button @click="detachExpertise(exp)" class="text-slate-300 hover:text-rose-500 transition-colors">&times;</button>
                 </span>
               </div>
               <div v-else class="p-5 text-center bg-white rounded-2xl border border-dashed border-slate-100">
                  <p class="text-xs font-medium text-slate-300">No expertise tags</p>
               </div>
             </div>
           </div>
        </div>
      </div>
    </template>

    <!-- Assign Role Modal -->
    <Modal :show="showAssignRole" title="Assign Role" @close="showAssignRole = false">
      <form @submit.prevent="assignRole" class="space-y-4">
        <div>
          <label class="block text-sm font-medium text-gray-700 mb-1">Role</label>
          <select v-model="selectedRoleId" required class="w-full border border-gray-300 rounded-2xl px-4 py-2.5 text-sm focus:ring-2 focus:ring-blue-500 outline-none bg-white">
            <option value="">Select role</option>
            <option v-for="r in allRoles" :key="r.id" :value="r.id">{{ r.name }}</option>
          </select>
        </div>
        <div class="flex justify-end gap-3">
          <button type="button" @click="showAssignRole = false" class="px-4 py-2 text-sm border border-gray-300 rounded-2xl">Cancel</button>
          <button type="submit" class="px-4 py-2 text-sm bg-blue-600 text-white rounded-2xl">Assign</button>
        </div>
      </form>
    </Modal>

    <!-- Assign Center Modal -->
    <Modal :show="showAssignCenter" title="Assign Research Center" @close="showAssignCenter = false">
      <form @submit.prevent="assignCenter" class="space-y-4">
        <div>
          <label class="block text-sm font-medium text-gray-700 mb-1">Research Center</label>
          <select v-model="selectedCenterId" required class="w-full border border-gray-300 rounded-2xl px-4 py-2.5 text-sm focus:ring-2 focus:ring-blue-500 outline-none bg-white">
            <option value="">Select center</option>
            <option v-for="rc in allCenters" :key="rc.id" :value="rc.id">{{ rc.name }}</option>
          </select>
        </div>
        <div class="flex justify-end gap-3">
          <button type="button" @click="showAssignCenter = false" class="px-4 py-2 text-sm border border-gray-300 rounded-2xl">Cancel</button>
          <button type="submit" class="px-4 py-2 text-sm bg-blue-600 text-white rounded-2xl">Assign</button>
        </div>
      </form>
    </Modal>

    <!-- Assign Expertise Modal -->
    <Modal :show="showAssignExpertise" title="Assign Expertise" @close="showAssignExpertise = false">
      <form @submit.prevent="assignExpertise" class="space-y-4">
        <div>
          <label class="block text-sm font-medium text-gray-700 mb-1">Expertise</label>
          <select v-model="selectedExpertiseId" required class="w-full border border-gray-300 rounded-2xl px-4 py-2.5 text-sm focus:ring-2 focus:ring-blue-500 outline-none bg-white">
            <option value="">Select expertise</option>
            <option v-for="exp in allExpertise" :key="exp.id" :value="exp.id">{{ exp.name }}</option>
          </select>
        </div>
        <div class="flex justify-end gap-3">
          <button type="button" @click="showAssignExpertise = false" class="px-4 py-2 text-sm border border-gray-300 rounded-2xl">Cancel</button>
          <button type="submit" class="px-4 py-2 text-sm bg-blue-600 text-white rounded-2xl">Assign</button>
        </div>
      </form>
    </Modal>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import { useRoute } from 'vue-router'
import { useNotificationStore } from '@/stores/notification'
import api from '@/services/api'
import Modal from '@/components/Modal.vue'
import { getInitials, imageUrl } from '@/utils/formatters'

const route = useRoute(); const notif = useNotificationStore()
const user = ref({}); const loading = ref(true); const error = ref(null)
const allRoles = ref([]); const allCenters = ref([]); const allExpertise = ref([])
const showAssignRole = ref(false); const selectedRoleId = ref('')
const showAssignCenter = ref(false); const selectedCenterId = ref('')
const showAssignExpertise = ref(false); const selectedExpertiseId = ref('')

async function fetchUser() {
  loading.value = true; error.value = null
  try { const { data } = await api.get(`/users/${route.params.id}`); user.value = data }
  catch (err) { error.value = err.response?.data?.message || 'Failed' }
  finally { loading.value = false }
}

async function assignRole() {
  try { await api.post(`/users/${user.value.id}/roles`, { role_id: selectedRoleId.value }); notif.success('Role assigned!'); showAssignRole.value = false; fetchUser() }
  catch (err) { notif.error(err.response?.data?.message || 'Failed') }
}

async function revokeRole(role) {
  try { await api.delete(`/users/${user.value.id}/roles/${role.id}`); notif.success('Role revoked!'); fetchUser() }
  catch (err) { notif.error('Failed') }
}

async function assignCenter() {
  try { await api.post(`/users/${user.value.id}/research-centers`, { research_center_id: selectedCenterId.value }); notif.success('Center assigned!'); showAssignCenter.value = false; fetchUser() }
  catch (err) { notif.error('Failed') }
}

async function detachCenter(rc) {
  try { await api.delete(`/users/${user.value.id}/research-centers/${rc.id}`); notif.success('Removed!'); fetchUser() }
  catch (err) { notif.error('Failed') }
}

async function assignExpertise() {
  try { await api.post(`/users/${user.value.id}/expertise`, { expertise_id: selectedExpertiseId.value }); notif.success('Expertise assigned!'); showAssignExpertise.value = false; fetchUser() }
  catch (err) { notif.error('Failed') }
}

async function detachExpertise(exp) {
  try { await api.delete(`/users/${user.value.id}/expertise/${exp.id}`); notif.success('Removed!'); fetchUser() }
  catch (err) { notif.error('Failed') }
}

onMounted(async () => {
  await fetchUser()
  try {
    const rRes = await api.get('/roles')
    const cRes = await api.get('/research-centers')
    const eRes = await api.get('/expertise')
    allRoles.value = rRes.data; allCenters.value = cRes.data; allExpertise.value = eRes.data
  } catch (e) {}
})
</script>
