<template>
  <div class="flex flex-col gap-8 pb-12 animate-fade card">
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-6">
      <div>
        <router-link :to="`/app/proposals/${proposal.id}`" class="flex items-center gap-2 text-brand font-black capitalize tracking-widest text-[10px] mb-3 hover:translate-x-1 transition-transform">
          <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M10 19l-7-7m0 0l7-7m-7 7h18" /></svg>
          Back to Proposal
        </router-link>
        <h1 class="text-3xl font-black text-slate-900 tracking-tight leading-tight">Edit Proposal</h1>
      </div>
    </div>

    <div v-if="loading" class="space-y-8">
      <div class="card h-40 animate-pulse bg-slate-50"></div>
      <div class="card h-96 animate-pulse bg-slate-50"></div>
    </div>

    <form v-else @submit.prevent="handleSubmit" class="space-y-8 font-bold">
      <!-- Section: Summary -->
      <div class="card p-8">
        <h2 class="text-xs font-black text-slate-400 capitalize tracking-widest mb-8 flex items-center gap-2">
          <span class="w-1 h-3 bg-brand rounded-full"></span>
          Quick Summary
        </h2>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
          <div class="md:col-span-2">
            <label class="block text-[11px] text-slate-500 capitalize tracking-widest mb-2 ml-1">Title *</label>
            <input v-model="form.title" type="text" required class="input h-12 font-black" />
          </div>
          <div>
            <label class="block text-[11px] text-slate-500 capitalize tracking-widest mb-2 ml-1">Proposal Type</label>
            <select v-model="form.type_id" class="input h-12 font-black">
              <option v-for="t in proposalTypes" :key="t.id" :value="t.id">{{ t.name.toUpperCase() }}</option>
            </select>
          </div>
          <div>
            <label class="block text-[11px] text-slate-500 capitalize tracking-widest mb-2 ml-1">Budget (ETB) *</label>
            <input v-model.number="form.budget" type="number" required class="input h-12 font-black" />
          </div>
        </div>
      </div>

      <!-- Section: Details -->
      <div class="card p-8">
        <h2 class="text-xs font-black text-slate-400 capitalize tracking-widest mb-8 flex items-center gap-2">
          <span class="w-1 h-3 bg-brand rounded-full"></span>
          Research Details
        </h2>
        <div class="space-y-6">
          <div>
            <label class="block text-[11px] text-slate-500 capitalize tracking-widest mb-2 ml-1">Keywords *</label>
            <input v-model="form.keywords" type="text" required class="input h-12 font-bold" placeholder="AI, Biology..." />
          </div>
          <div>
            <label class="block text-[11px] text-slate-500 capitalize tracking-widest mb-2 ml-1">Abstract *</label>
            <textarea v-model="form.abstract" required rows="4" class="input pt-3 font-medium resize-none"></textarea>
          </div>
          <div>
            <label class="block text-[11px] text-slate-500 capitalize tracking-widest mb-2 ml-1">Objectives *</label>
            <textarea v-model="form.objectives" required rows="4" class="input pt-3 font-medium resize-none"></textarea>
          </div>
          <div>
            <label class="block text-[11px] text-slate-500 capitalize tracking-widest mb-2 ml-1">Methodology *</label>
            <textarea v-model="form.methodology" required rows="5" class="input pt-3 font-medium resize-none"></textarea>
          </div>
        </div>
      </div>

      <!-- Section: Team -->
      <div class="card p-8">
        <div class="flex items-center justify-between mb-8">
          <h2 class="text-xs font-black text-slate-400 capitalize tracking-widest flex items-center gap-2">
            <span class="w-1 h-3 bg-brand rounded-full"></span>
            Research Team
          </h2>
          <button type="button" @click="addInvestigator" class="btn btn-secondary h-10 px-6 text-[10px] font-black capitalize tracking-widest border border-slate-200">
            Add Member
          </button>
        </div>

        <div v-if="form.investigators.length === 0" class="p-12 text-center bg-slate-50 rounded-3xl border border-dashed border-slate-200">
          <p class="text-[10px] font-black text-slate-400 capitalize tracking-widest italic">No co-investigators added.</p>
        </div>

        <div v-else class="space-y-4">
          <div v-for="(inv, index) in form.investigators" :key="index" class="p-6 bg-slate-50/50 rounded-2xl border border-slate-100 relative group">
            <button type="button" @click="removeInvestigator(index)" class="absolute -top-2 -right-2 w-8 h-8 bg-white text-rose-500 rounded-full shadow-md flex items-center justify-center border border-slate-100 hover:bg-rose-50 transition-colors opacity-0 group-hover:opacity-100">
              <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M6 18L18 6M6 6l12 12"/></svg>
            </button>
            
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
              <div class="md:col-span-2">
                <label class="block text-[10px] font-black text-slate-400 capitalize tracking-widest mb-2 ml-1">User Affiliation</label>
                <select v-model="inv.user_id" @change="onUserSelected(index)" class="input h-11 font-bold">
                  <option value="">External Person</option>
                  <option v-for="u in availableUsers" :key="u.id" :value="u.id">{{ u.name }} ({{ u.email }})</option>
                </select>
              </div>
              <div>
                <label class="block text-[10px] font-black text-slate-400 capitalize tracking-widest mb-2 ml-1">Role</label>
                <select v-model="inv.role_id" required class="input h-11 font-bold">
                  <option v-for="r in investigatorRoles" :key="r.id" :value="r.id">{{ r.name }}</option>
                </select>
              </div>
              <div v-if="!inv.user_id">
                <label class="block text-[10px] font-black text-slate-400 capitalize tracking-widest mb-2 ml-1">Full Name</label>
                <input v-model="inv.name" type="text" required class="input h-11 font-bold" />
              </div>
              <div v-if="!inv.user_id">
                <label class="block text-[10px] font-black text-slate-400 capitalize tracking-widest mb-2 ml-1">Email</label>
                <input v-model="inv.email" type="email" required class="input h-11 font-bold" />
              </div>
              <div v-if="!inv.user_id">
                <label class="block text-[10px] font-black text-slate-400 capitalize tracking-widest mb-2 ml-1">Institution</label>
                <input v-model="inv.institution" type="text" class="input h-11 font-bold" placeholder="University of..." />
              </div>
            </div>
          </div>
        </div>
      </div>

      <div class="flex items-center gap-3 justify-end pt-6 border-t border-slate-100">
        <router-link :to="`/app/proposals/${proposal.id}`" class="btn btn-secondary px-8 h-12">Discard</router-link>
        <button type="submit" :disabled="submitting" class="btn btn-primary px-12 h-12 shadow-lg shadow-blue-500/20">
          {{ submitting ? 'Processing...' : 'Save Changes' }}
        </button>
      </div>
    </form>
  </div>
</template>

<script setup>
import { ref, reactive, onMounted } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import { useNotificationStore } from '@/stores/notification'
import api from '@/services/api'

const route = useRoute(); const router = useRouter(); const notif = useNotificationStore()
const proposal = ref({}); const loading = ref(true); const error = ref(null); const submitting = ref(false)
const proposalTypes = ref([]); const availableUsers = ref([]); const investigatorRoles = ref([])
const form = reactive({ title: '', type_id: '', budget: null, keywords: '', abstract: '', objectives: '', methodology: '', investigators: [] })

async function fetchProposal() {
  loading.value = true; error.value = null
  try {
    const { data } = await api.get(`/proposals/${route.params.id}`)
    proposal.value = data
    Object.assign(form, { 
      title: data.title, 
      type_id: data.type_id, 
      budget: data.budget, 
      keywords: data.keywords, 
      abstract: data.abstract, 
      objectives: data.objectives, 
      methodology: data.methodology,
      investigators: (data.investigators || []).map(inv => ({
        user_id: inv.user_id || '',
        name: inv.name || '',
        email: inv.email || '',
        institution: inv.institution || '',
        role_id: inv.role_id
      }))
    })
  } catch (err) { error.value = err.response?.data?.message || 'Failed to load' }
  finally { loading.value = false }
}

function addInvestigator() {
  form.investigators.push({ user_id: '', name: '', email: '', institution: '', role_id: investigatorRoles.value[0]?.id || '' })
}

function removeInvestigator(i) {
  form.investigators.splice(i, 1)
}

function onUserSelected(i) {
  const inv = form.investigators[i]
  if (inv.user_id) { inv.name = ''; inv.email = ''; inv.institution = '' }
}

async function handleSubmit() {
  submitting.value = true
  try {
    await api.put(`/proposals/${proposal.value.id}`, form)
    notif.success('Proposal updated!')
    router.push(`/app/proposals/${proposal.value.id}`)
  } catch (err) { notif.error(err.response?.data?.message || 'Failed to update') }
  finally { submitting.value = false }
}

onMounted(async () => {
  await fetchProposal()
  try {
    const [typesRes, usersRes, rolesRes] = await Promise.all([
      api.get('/lookups/proposal_types'),
      api.get('/users', { params: { per_page: 200 } }),
      api.get('/lookups/investigator_roles')
    ])
    proposalTypes.value = typesRes.data
    availableUsers.value = usersRes.data.data || usersRes.data
    investigatorRoles.value = rolesRes.data
  } catch (e) {}
})
</script>
