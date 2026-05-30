<template>
  <div card>
    <div class="mb-6"><router-link to="/proposals" class="text-sm text-blue-600 hover:underline mb-2 inline-block">← Back to Proposals</router-link><h1 class="text-xl font-bold text-gray-800">Create New Proposal</h1><p class="text-sm text-gray-500 mt-1">Fill in the details to submit your research proposal</p></div>
    <form @submit.prevent="handleSubmit" class="flex flex-col gap-6">
      <div class="bg-white rounded-lg shadow-sm border border-gray-100 p-6"><h2 class="text-base font-semibold text-gray-800 mb-4">1. Quick Summary</h2>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
          <div class="md:col-span-2"><label class="block text-sm font-medium text-gray-700 mb-1">Proposal Document (PDF) *</label><input type="file" @change="e => form.proposal_file = e.target.files[0]" required accept=".pdf" class="w-full border border-gray-300 rounded-lg px-4 py-2 text-sm focus:ring-2 focus:ring-blue-500 outline-none bg-white" /></div>
          <div class="md:col-span-2"><label class="block text-sm font-medium text-gray-700 mb-1">Title *</label><input v-model="form.title" type="text" required maxlength="255" class="w-full border border-gray-300 rounded-lg px-4 py-2.5 text-sm focus:ring-2 focus:ring-blue-500 outline-none" placeholder="Enter the title of your research proposal" /></div>
          <div><label class="block text-sm font-medium text-gray-700 mb-1">Call for Proposal</label><select v-model="form.call_id" class="w-full border border-gray-300 rounded-lg px-4 py-2.5 text-sm focus:ring-2 focus:ring-blue-500 outline-none bg-white"><option value="">Open Call</option><option v-for="call in openCalls" :key="call.id" :value="call.id">{{ call.title }}</option></select></div>
          <div><label class="block text-sm font-medium text-gray-700 mb-1">Proposal Type *</label><select v-model="form.type_id" required class="w-full border border-gray-300 rounded-lg px-4 py-2.5 text-sm focus:ring-2 focus:ring-blue-500 outline-none bg-white"><option value="">Select type</option><option v-for="t in proposalTypes" :key="t.id" :value="t.id">{{ t.name.toUpperCase() }}</option></select></div>
          <div><label class="block text-sm font-medium text-gray-700 mb-1">Academic Year</label><select v-model="form.academic_year_id" class="w-full border border-gray-300 rounded-lg px-4 py-2.5 text-sm focus:ring-2 focus:ring-blue-500 outline-none bg-white"><option value="">Select year</option><option v-for="y in academicYears" :key="y.id" :value="y.id">{{ y.name }} {{ y.is_current ? '(Current)' : '' }}</option></select></div>
          <div><label class="block text-sm font-medium text-gray-700 mb-1">Budget (ETB) *</label><input v-model.number="form.budget" type="number" required min="0" step="0.01" class="w-full border border-gray-300 rounded-lg px-4 py-2.5 text-sm focus:ring-2 focus:ring-blue-500 outline-none" placeholder="500000.00" /></div>
        </div>
      </div>
      <div class="bg-white rounded-lg shadow-sm border border-gray-100 p-6"><h2 class="text-base font-semibold text-gray-800 mb-4">2. Research Details</h2>
        <div class="space-y-4">
          <div><label class="block text-sm font-medium text-gray-700 mb-1">Keywords *</label><input v-model="form.keywords" type="text" required class="w-full border border-gray-300 rounded-lg px-4 py-2.5 text-sm focus:ring-2 focus:ring-blue-500 outline-none" placeholder="AI, Machine Learning, Agriculture" /></div>
          <div><label class="block text-sm font-medium text-gray-700 mb-1">Abstract *</label><textarea v-model="form.abstract" required rows="4" class="w-full border border-gray-300 rounded-lg px-4 py-2.5 text-sm focus:ring-2 focus:ring-blue-500 outline-none resize-none" placeholder="Brief summary..."></textarea></div>
          <div><label class="block text-sm font-medium text-gray-700 mb-1">Objectives *</label><textarea v-model="form.objectives" required rows="3" class="w-full border border-gray-300 rounded-lg px-4 py-2.5 text-sm focus:ring-2 focus:ring-blue-500 outline-none resize-none" placeholder="1. First objective&#10;2. Second objective"></textarea></div>
          <div><label class="block text-sm font-medium text-gray-700 mb-1">Methodology *</label><textarea v-model="form.methodology" required rows="4" class="w-full border border-gray-300 rounded-lg px-4 py-2.5 text-sm focus:ring-2 focus:ring-blue-500 outline-none resize-none" placeholder="Describe your methodology..."></textarea></div>
        </div>
      </div>
      <div class="bg-white rounded-lg shadow-sm border border-gray-100 p-6">
        <div class="flex items-center justify-between mb-4"><h2 class="text-base font-semibold text-gray-800">3. Co-Team Members</h2><button type="button" @click="addInvestigator" class="text-sm text-blue-600 hover:text-blue-800 font-medium">+ Add Investigator</button></div>
        <div v-if="form.investigators.length === 0" class="text-center py-8 text-gray-400 text-sm"><p>No co-investigators added.</p><p class="text-xs mt-1">You will be the Principal Investigator by default.</p></div>
        <div v-else class="space-y-3"><div v-for="(inv, index) in form.investigators" :key="index" class="flex flex-col sm:flex-row gap-3 p-4 border border-gray-200 rounded-lg bg-gray-50">
          <div class="flex-1"><label class="block text-xs text-gray-500 mb-1">User</label><select v-model="inv.user_id" @change="onUserSelected(index)" class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500 outline-none bg-white"><option value="">External person</option><option v-for="u in availableUsers" :key="u.id" :value="u.id">{{ u.name }} ({{ u.email }})</option></select></div>
          <div v-if="!inv.user_id" class="flex-1"><label class="block text-xs text-gray-500 mb-1">Full Name *</label><input v-model="inv.name" type="text" required class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500 outline-none" placeholder="External name" /></div>
          <div v-if="!inv.user_id" class="flex-1"><label class="block text-xs text-gray-500 mb-1">Email *</label><input v-model="inv.email" type="email" required class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500 outline-none" placeholder="email@example.com" /></div>
          <div class="w-40"><label class="block text-xs text-gray-500 mb-1">Role *</label><select v-model="inv.role_id" required class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500 outline-none bg-white"><option value="">Select role</option><option v-for="r in investigatorRoles" :key="r.id" :value="r.id">{{ r.name }}</option></select></div>
          <div class="flex items-end"><button type="button" @click="removeInvestigator(index)" class="text-red-500 hover:text-red-700 text-sm font-medium p-2">✕</button></div>
        </div></div>
      </div>
      <div class="flex items-center gap-3 justify-end"><router-link to="/proposals" class="px-6 py-2.5 text-sm font-medium text-gray-700 border border-gray-300 rounded-lg hover:bg-gray-50">Cancel</router-link><button type="submit" :disabled="submitting" class="px-6 py-2.5 text-sm font-medium text-white bg-blue-600 rounded-lg hover:bg-blue-700 disabled:opacity-60">{{ submitting ? 'Saving...' : 'Save as Draft' }}</button></div>
    </form>
  </div>
</template>
<script setup>
import { ref, reactive, onMounted } from 'vue'
import { useRouter } from 'vue-router'
import { useAuthStore } from '@/stores/auth'
import { useNotificationStore } from '@/stores/notification'
import api from '@/services/api'
const router = useRouter(); const auth = useAuthStore(); const notif = useNotificationStore()
const form = reactive({ call_id: '', type_id: '', academic_year_id: '', title: '', keywords: '', abstract: '', objectives: '', methodology: '', budget: null, proposal_file: null, investigators: [] })
const submitting = ref(false)
const openCalls = ref([]); const proposalTypes = ref([]); const academicYears = ref([]); const investigatorRoles = ref([]); const availableUsers = ref([])
function addInvestigator() { form.investigators.push({ user_id: '', name: '', email: '', role_id: '' }) }
function removeInvestigator(i) { form.investigators.splice(i, 1) }
function onUserSelected(i) { const inv = form.investigators[i]; if (inv.user_id) { inv.name = ''; inv.email = '' } }
async function handleSubmit() { if (form.investigators.length === 0) { notif.warning('Add at least one co-investigator.'); return }; submitting.value = true; try { const payload = new FormData();
        if(form.call_id) payload.append('call_id', form.call_id);
        payload.append('type_id', form.type_id);
        if(form.academic_year_id) payload.append('academic_year_id', form.academic_year_id);
        payload.append('title', form.title);
        payload.append('keywords', form.keywords);
        payload.append('abstract', form.abstract);
        payload.append('objectives', form.objectives);
        payload.append('methodology', form.methodology);
        payload.append('budget', form.budget);
        if (form.proposal_file) payload.append('proposal_file', form.proposal_file);
        payload.append('investigators', JSON.stringify(form.investigators.map(inv => ({ user_id: inv.user_id || null, name: inv.name || null, email: inv.email || null, role_id: inv.role_id })))); const { data } = await api.post('/proposals', payload); notif.success('Proposal created!'); router.push(`/proposals/${data.id}`) } catch (err) { notif.error(err.response?.data?.message || 'Failed') } finally { submitting.value = false } }
onMounted(async () => { try { const [cr, tr, yr, rr, ur] = await Promise.all([api.get('/calls', { params: { status: 'open' } }), api.get('/lookups/proposal_types'), api.get('/academic-years'), api.get('/lookups/investigator_roles'), api.get('/users', { params: { per_page: 100 } })]); openCalls.value = cr.data.data || cr.data; proposalTypes.value = tr.data; academicYears.value = yr.data; investigatorRoles.value = rr.data; availableUsers.value = ur.data.data || ur.data } catch (err) { notif.error('Failed to load form data.') } })
</script>
