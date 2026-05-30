<template>
  <div card>
    <div class="mb-6">
      <router-link to="/users" class="text-sm text-blue-600 hover:underline mb-2 inline-block">← Back to Users</router-link>
      <h1 class="text-xl font-bold text-gray-800">{{ user.name || 'User Detail' }}</h1>
    </div>

    <div v-if="loading" class="bg-white rounded-lg shadow-sm p-6">
      <div class="space-y-4 animate-pulse"><div v-for="i in 6" :key="i" class="h-5 bg-gray-200 rounded"></div></div>
    </div>

    <div v-else-if="error" class="bg-red-50 border border-red-200 rounded-lg p-6 text-center">
      <p class="text-red-700 text-sm">{{ error }}</p>
    </div>

    <template v-else>
      <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Profile Card -->
        <div class="bg-white rounded-lg shadow-sm border border-gray-100 p-6 text-center">
          <div class="w-20 h-20 bg-blue-600 rounded-full flex items-center justify-center text-3xl font-bold text-white mx-auto mb-4">
            {{ getInitials(user.name) }}
          </div>
          <h2 class="text-lg font-semibold text-gray-800">{{ user.name }}</h2>
          <p class="text-sm text-gray-500">{{ user.email }}</p>
          <span :class="user.is_active ? 'bg-green-100 text-green-700' : 'bg-red-100 text-red-700'"
            class="inline-block mt-2 px-3 py-0.5 rounded-full text-xs font-medium">
            {{ user.is_active ? 'Active' : 'Inactive' }}
          </span>
          <div class="mt-3 text-sm text-gray-600">
            <p v-if="user.department"><strong>Department:</strong> {{ user.department.name }}</p>
            <p v-if="user.orcid_id">ORCID: {{ user.orcid_id }}</p>
            <p v-if="user.bio" class="mt-2 text-xs">{{ user.bio }}</p>
          </div>
        </div>

        <!-- Roles & Permissions -->
        <div class="lg:col-span-2 space-y-6">
          <!-- Roles -->
          <div class="bg-white rounded-lg shadow-sm border border-gray-100 p-6">
            <div class="flex items-center justify-between mb-4">
              <h2 class="text-base font-semibold text-gray-800">Roles</h2>
              <button @click="showAssignRole = true" class="text-sm text-blue-600 hover:text-blue-800 font-medium">+ Assign Role</button>
            </div>
            <div v-if="user.roles?.length" class="flex flex-wrap gap-2">
              <span v-for="role in user.roles" :key="role.id"
                class="bg-blue-100 text-blue-700 px-3 py-1 rounded-full text-sm font-medium flex items-center gap-2">
                {{ role.name }}
                <button @click="revokeRole(role)" class="text-blue-400 hover:text-red-500 text-lg leading-none">&times;</button>
              </span>
            </div>
            <p v-else class="text-sm text-gray-400">No roles assigned.</p>
          </div>

          <!-- Research Centers -->
          <div class="bg-white rounded-lg shadow-sm border border-gray-100 p-6">
            <div class="flex items-center justify-between mb-4">
              <h2 class="text-base font-semibold text-gray-800">Research Centers</h2>
              <button @click="showAssignCenter = true" class="text-sm text-blue-600 hover:text-blue-800 font-medium">+ Assign Center</button>
            </div>
            <div v-if="user.research_centers?.length" class="space-y-2">
              <div v-for="rc in user.research_centers" :key="rc.id" class="flex items-center justify-between p-2 rounded bg-gray-50">
                <span class="text-sm text-gray-800">{{ rc.name }}</span>
                <button @click="detachCenter(rc)" class="text-red-500 text-sm">Remove</button>
              </div>
            </div>
            <p v-else class="text-sm text-gray-400">No research centers assigned.</p>
          </div>

          <!-- Expertise -->
          <div class="bg-white rounded-lg shadow-sm border border-gray-100 p-6">
            <div class="flex items-center justify-between mb-4">
              <h2 class="text-base font-semibold text-gray-800">Expertise</h2>
              <button @click="showAssignExpertise = true" class="text-sm text-blue-600 hover:text-blue-800 font-medium">+ Assign Expertise</button>
            </div>
            <div v-if="user.expertise?.length" class="flex flex-wrap gap-2">
              <span v-for="exp in user.expertise" :key="exp.id"
                class="bg-green-100 text-green-700 px-3 py-1 rounded-full text-sm font-medium flex items-center gap-2">
                {{ exp.name }}
                <button @click="detachExpertise(exp)" class="text-green-400 hover:text-red-500 text-lg leading-none">&times;</button>
              </span>
            </div>
            <p v-else class="text-sm text-gray-400">No expertise assigned.</p>
          </div>
        </div>
      </div>
    </template>

    <!-- Assign Role Modal -->
    <Modal :show="showAssignRole" title="Assign Role" @close="showAssignRole = false">
      <form @submit.prevent="assignRole" class="space-y-4">
        <div>
          <label class="block text-sm font-medium text-gray-700 mb-1">Role</label>
          <select v-model="selectedRoleId" required class="w-full border border-gray-300 rounded-lg px-4 py-2.5 text-sm focus:ring-2 focus:ring-blue-500 outline-none bg-white">
            <option value="">Select role</option>
            <option v-for="r in allRoles" :key="r.id" :value="r.id">{{ r.name }}</option>
          </select>
        </div>
        <div class="flex justify-end gap-3">
          <button type="button" @click="showAssignRole = false" class="px-4 py-2 text-sm border border-gray-300 rounded-lg">Cancel</button>
          <button type="submit" class="px-4 py-2 text-sm bg-blue-600 text-white rounded-lg">Assign</button>
        </div>
      </form>
    </Modal>

    <!-- Assign Center Modal -->
    <Modal :show="showAssignCenter" title="Assign Research Center" @close="showAssignCenter = false">
      <form @submit.prevent="assignCenter" class="space-y-4">
        <div>
          <label class="block text-sm font-medium text-gray-700 mb-1">Research Center</label>
          <select v-model="selectedCenterId" required class="w-full border border-gray-300 rounded-lg px-4 py-2.5 text-sm focus:ring-2 focus:ring-blue-500 outline-none bg-white">
            <option value="">Select center</option>
            <option v-for="rc in allCenters" :key="rc.id" :value="rc.id">{{ rc.name }}</option>
          </select>
        </div>
        <div class="flex justify-end gap-3">
          <button type="button" @click="showAssignCenter = false" class="px-4 py-2 text-sm border border-gray-300 rounded-lg">Cancel</button>
          <button type="submit" class="px-4 py-2 text-sm bg-blue-600 text-white rounded-lg">Assign</button>
        </div>
      </form>
    </Modal>

    <!-- Assign Expertise Modal -->
    <Modal :show="showAssignExpertise" title="Assign Expertise" @close="showAssignExpertise = false">
      <form @submit.prevent="assignExpertise" class="space-y-4">
        <div>
          <label class="block text-sm font-medium text-gray-700 mb-1">Expertise</label>
          <select v-model="selectedExpertiseId" required class="w-full border border-gray-300 rounded-lg px-4 py-2.5 text-sm focus:ring-2 focus:ring-blue-500 outline-none bg-white">
            <option value="">Select expertise</option>
            <option v-for="exp in allExpertise" :key="exp.id" :value="exp.id">{{ exp.name }}</option>
          </select>
        </div>
        <div class="flex justify-end gap-3">
          <button type="button" @click="showAssignExpertise = false" class="px-4 py-2 text-sm border border-gray-300 rounded-lg">Cancel</button>
          <button type="submit" class="px-4 py-2 text-sm bg-blue-600 text-white rounded-lg">Assign</button>
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
import { getInitials } from '@/utils/formatters'

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
    const [rRes, cRes, eRes] = await Promise.all([api.get('/roles'), api.get('/research-centers'), api.get('/expertise')])
    allRoles.value = rRes.data; allCenters.value = cRes.data; allExpertise.value = eRes.data
  } catch (e) {}
})
</script>
