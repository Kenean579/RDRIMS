<template>
  <div card>
    <div class="mb-6">
      <router-link :to="`/proposals/${proposal.id}`" class="text-sm text-blue-600 hover:underline mb-2 inline-block">← Back to Proposal</router-link>
      <h1 class="text-xl font-bold text-gray-800">Edit Proposal</h1>
    </div>

    <div v-if="loading" class="bg-white rounded-lg shadow-sm p-6">
      <div class="space-y-4 animate-pulse"><div v-for="i in 6" :key="i" class="h-6 bg-gray-200 rounded"></div></div>
    </div>

    <div v-else-if="error" class="bg-red-50 border border-red-200 rounded-lg p-6 text-center">
      <p class="text-red-700 text-sm">{{ error }}</p>
    </div>

    <form v-else @submit.prevent="handleSubmit" class="flex flex-col gap-6">
      <div class="bg-white rounded-lg shadow-sm border border-gray-100 p-6">
        <h2 class="text-base font-semibold text-gray-800 mb-4">Quick Summary</h2>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
          <div class="md:col-span-2">
            <label class="block text-sm font-medium text-gray-700 mb-1">Title *</label>
            <input v-model="form.title" type="text" required class="w-full border border-gray-300 rounded-lg px-4 py-2.5 text-sm focus:ring-2 focus:ring-blue-500 outline-none" />
          </div>
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Type</label>
            <select v-model="form.type_id" class="w-full border border-gray-300 rounded-lg px-4 py-2.5 text-sm focus:ring-2 focus:ring-blue-500 outline-none bg-white">
              <option v-for="t in proposalTypes" :key="t.id" :value="t.id">{{ t.name.toUpperCase() }}</option>
            </select>
          </div>
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Budget (ETB) *</label>
            <input v-model.number="form.budget" type="number" required min="0" class="w-full border border-gray-300 rounded-lg px-4 py-2.5 text-sm focus:ring-2 focus:ring-blue-500 outline-none" />
          </div>
        </div>
      </div>

      <div class="bg-white rounded-lg shadow-sm border border-gray-100 p-6">
        <h2 class="text-base font-semibold text-gray-800 mb-4">Research Details</h2>
        <div class="space-y-4">
          <div><label class="block text-sm font-medium text-gray-700 mb-1">Keywords *</label><input v-model="form.keywords" type="text" required class="w-full border border-gray-300 rounded-lg px-4 py-2.5 text-sm focus:ring-2 focus:ring-blue-500 outline-none" /></div>
          <div><label class="block text-sm font-medium text-gray-700 mb-1">Abstract *</label><textarea v-model="form.abstract" required rows="4" class="w-full border border-gray-300 rounded-lg px-4 py-2.5 text-sm focus:ring-2 focus:ring-blue-500 outline-none resize-none"></textarea></div>
          <div><label class="block text-sm font-medium text-gray-700 mb-1">Objectives *</label><textarea v-model="form.objectives" required rows="3" class="w-full border border-gray-300 rounded-lg px-4 py-2.5 text-sm focus:ring-2 focus:ring-blue-500 outline-none resize-none"></textarea></div>
          <div><label class="block text-sm font-medium text-gray-700 mb-1">Methodology *</label><textarea v-model="form.methodology" required rows="4" class="w-full border border-gray-300 rounded-lg px-4 py-2.5 text-sm focus:ring-2 focus:ring-blue-500 outline-none resize-none"></textarea></div>
        </div>
      </div>

      <div class="flex items-center gap-3 justify-end">
        <router-link :to="`/proposals/${proposal.id}`" class="px-6 py-2.5 text-sm font-medium text-gray-700 border border-gray-300 rounded-lg hover:bg-gray-50">Cancel</router-link>
        <button type="submit" :disabled="submitting" class="px-6 py-2.5 text-sm font-medium text-white bg-blue-600 rounded-lg hover:bg-blue-700 disabled:opacity-60">{{ submitting ? 'Saving...' : 'Save Changes' }}</button>
      </div>
    </form>
  </div>
</template>

<script setup>
import { ref, reactive, onMounted } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import { useNotificationStore } from '@/stores/notification'
import api from '@/services/api'

const borderStyles = 'border border-gray-300 rounded-lg px-4 py-2.5 text-sm focus:ring-2 focus:ring-blue-500 outline-none'
const route = useRoute(); const router = useRouter(); const notif = useNotificationStore()
const proposal = ref({}); const loading = ref(true); const error = ref(null); const submitting = ref(false)
const proposalTypes = ref([])
const form = reactive({ title: '', type_id: '', budget: null, keywords: '', abstract: '', objectives: '', methodology: '' })

async function fetchProposal() {
  loading.value = true; error.value = null
  try {
    const { data } = await api.get(`/proposals/${route.params.id}`)
    proposal.value = data
    Object.assign(form, { title: data.title, type_id: data.type_id, budget: data.budget, keywords: data.keywords, abstract: data.abstract, objectives: data.objectives, methodology: data.methodology })
  } catch (err) { error.value = err.response?.data?.message || 'Failed to load' }
  finally { loading.value = false }
}

async function handleSubmit() {
  submitting.value = true
  try {
    await api.put(`/proposals/${proposal.value.id}`, form)
    notif.success('Proposal updated!')
    router.push(`/proposals/${proposal.value.id}`)
  } catch (err) { notif.error(err.response?.data?.message || 'Failed to update') }
  finally { submitting.value = false }
}

onMounted(async () => {
  await fetchProposal()
  try { const { data } = await api.get('/lookups/proposal_types'); proposalTypes.value = data } catch (e) {}
})
</script>
