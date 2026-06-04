<template>
  <div class="flex flex-col gap-8 animate-fade pb-8">
    <!-- Header -->
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-6 px-1">
      <div>
        <h1 class="text-2xl font-bold text-slate-900 tracking-tight leading-tight">Partners & Collaborators</h1>
        <p class="text-slate-500 font-medium mt-2 text-xs flex items-center gap-2  tracking-widest">
          <span class="w-2 h-2 rounded-full bg-brand"></span>
          External organizations contributing to research
        </p>
      </div>
      <div class="flex items-center gap-3">
        <button v-if="auth.hasRole('super_admin', 'research_admin')" @click="showCreate = true" class="btn btn-primary h-11 px-6 text-[11px] font-bold gap-2">
          <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M12 4v16m8-8H4"/></svg>
          Add Partner
        </button>
        <button @click="fetchPartners" class="btn btn-secondary h-11 px-4 group">
          <svg class="w-4 h-4 group-hover:rotate-180 transition-transform duration-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/></svg>
        </button>
      </div>
    </div>

    <!-- Loading -->
    <div v-if="loading" class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-5 gap-5">
      <div v-for="i in 10" :key="i" class="bg-white rounded-3xl h-44 animate-pulse border border-slate-100"></div>
    </div>

    <!-- Empty -->
    <div v-else-if="partners.length === 0" class="card p-16 text-center">
      <div class="w-20 h-20 bg-slate-50 rounded-3xl flex items-center justify-center mx-auto mb-6 text-4xl border border-slate-100">🤝</div>
      <h3 class="text-lg font-bold text-slate-800 mb-2">No partners found</h3>
      <p class="text-sm text-slate-500 font-medium mb-8">Connect with external organizations and industry leaders to expand your research scope.</p>
      <button v-if="auth.hasRole('super_admin', 'research_admin')" @click="showCreate = true" class="btn btn-primary px-8 h-11 text-[11px] font-bold shadow-lg shadow-brand/20">Add First Partner</button>
    </div>

    <!-- Grid -->
    <div v-else class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-5 gap-5">
      <div v-for="p in partners" :key="p.id"
        class="bg-white rounded-3xl border border-slate-100 hover:border-brand/20 hover:shadow-xl hover:shadow-brand/5 transition-all overflow-hidden group flex flex-col"
      >
        <router-link :to="`/app/partners/${p.id}`" class="p-6 flex flex-col items-center text-center flex-1">
          <div class="w-14 h-14 rounded-2xl flex items-center justify-center text-2xl group-hover:scale-110 transition-transform mb-4 border border-slate-100 bg-slate-50">
            {{ getLogo(p) }}
          </div>
          <h3 class="text-sm font-bold text-slate-800 group-hover:text-brand transition-colors line-clamp-2 leading-snug mb-2">{{ p.name }}</h3>
          <p class="text-[10px] font-semibold text-slate-400  tracking-widest">{{ p.type?.name || 'Partner' }}</p>
          <div class="mt-3 pt-3 border-t border-slate-50 w-full">
            <a v-if="p.website_url" :href="p.website_url" target="_blank" @click.stop class="text-[10px] font-bold text-brand hover:underline">
              Visit Website ↗
            </a>
            <span v-else class="text-[10px] text-slate-300 italic">No website</span>
          </div>
        </router-link>
        <!-- Admin actions -->
        <div v-if="auth.hasRole('super_admin', 'research_admin')" class="px-4 pb-4 flex gap-2">
          <button @click="editPartner(p)" class="flex-1 btn btn-secondary text-[10px] font-bold h-8 px-0 justify-center">Edit</button>
          <button @click="confirmDelete(p)" class="w-8 h-8 flex items-center justify-center rounded-lg text-slate-300 hover:text-rose-500 hover:bg-rose-50 transition-colors">
            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
          </button>
        </div>
      </div>
    </div>

    <!-- Create/Edit Modal -->
    <Modal :show="showCreate || !!editingPartner" :title="editingPartner ? 'Edit Partner' : 'Add New Partner'" size="md" @close="closeModal">
      <form @submit.prevent="savePartner" class="space-y-5">
        <div>
          <label class="block text-[11px] text-slate-500 font-bold mb-2 ml-1">Organization Name *</label>
          <input v-model="form.name" type="text" required class="input h-12 font-bold" placeholder="e.g. World Bank Group" />
        </div>
        <div class="grid grid-cols-2 gap-4">
          <div>
            <label class="block text-[11px] text-slate-500 font-bold mb-2 ml-1">Type</label>
            <select v-model="form.type_id" class="input h-12 font-bold">
              <option value="">Select type...</option>
              <option v-for="t in partnerTypes" :key="t.id" :value="t.id">{{ t.name }}</option>
            </select>
          </div>
          <div>
            <label class="block text-[11px] text-slate-500 font-bold mb-2 ml-1">Sector</label>
            <input v-model="form.sector" type="text" class="input h-12 font-bold" placeholder="e.g. Health, Agriculture" />
          </div>
        </div>
        <div>
          <label class="block text-[11px] text-slate-500 font-bold mb-2 ml-1">Website URL</label>
          <input v-model="form.website_url" type="url" class="input h-12 font-bold" placeholder="https://..." />
        </div>
        <div class="grid grid-cols-2 gap-4">
          <div>
            <label class="block text-[11px] text-slate-500 font-bold mb-2 ml-1">Contact Email</label>
            <input v-model="form.contact_email" type="email" class="input h-12 font-bold" placeholder="contact@org.com" />
          </div>
          <div>
            <label class="block text-[11px] text-slate-500 font-bold mb-2 ml-1">Country</label>
            <input v-model="form.country" type="text" class="input h-12 font-bold" placeholder="e.g. Ethiopia" />
          </div>
        </div>
        <div>
          <label class="block text-[11px] text-slate-500 font-bold mb-2 ml-1">Description</label>
          <textarea v-model="form.description" rows="3" class="input resize-none pt-3 font-medium" placeholder="Brief description of this partner organization..."></textarea>
        </div>
        <div class="flex justify-end gap-3 pt-4 border-t border-slate-100">
          <button type="button" @click="closeModal" class="btn btn-secondary px-6 h-11 text-[11px] font-bold">Cancel</button>
          <button type="submit" class="btn btn-primary px-8 h-11 text-[11px] font-bold shadow-lg shadow-brand/20">
            {{ editingPartner ? 'Save Changes' : 'Add Partner' }}
          </button>
        </div>
      </form>
    </Modal>

    <!-- Delete Confirm -->
    <ConfirmDialog
      :show="showDelete"
      title="Remove Partner"
      :message="`Are you sure you want to remove '${deletingPartner?.name}'? This will also remove all associated MoUs.`"
      confirmText="Remove"
      variant="danger"
      @confirm="deletePartner"
      @cancel="showDelete = false"
    />
  </div>
</template>

<script setup>
import { ref, reactive, onMounted } from 'vue'
import { useAuthStore } from '@/stores/auth'
import { useNotificationStore } from '@/stores/notification'
import api from '@/services/api'
import Modal from '@/components/Modal.vue'
import ConfirmDialog from '@/components/ConfirmDialog.vue'
import EmptyState from '@/components/EmptyState.vue'

const auth = useAuthStore()
const notif = useNotificationStore()
const loading = ref(true)
const partners = ref([])
const partnerTypes = ref([])

const showCreate = ref(false)
const editingPartner = ref(null)
const showDelete = ref(false)
const deletingPartner = ref(null)

const form = reactive({
  name: '',
  type_id: '',
  sector: '',
  website_url: '',
  contact_email: '',
  country: '',
  description: '',
})

async function fetchPartners() {
  loading.value = true
  try {
    const { data } = await api.get('/partners')
    partners.value = data.data || data
  } catch (e) {
    notif.error('Failed to load partners')
  } finally {
    loading.value = false
  }
}

function getLogo(p) {
  const type = p.type?.name?.toLowerCase() || ''
  if (type === 'university') return '🎓'
  if (type === 'industry' || type === 'company') return '🏭'
  if (type === 'ngo' || type === 'nonprofit') return '🌐'
  if (type === 'government') return '🏛️'
  if (type === 'hospital' || type === 'health') return '🏥'
  return '🤝'
}

function editPartner(p) {
  editingPartner.value = p
  Object.assign(form, {
    name: p.name || '',
    type_id: p.type_id || '',
    sector: p.sector || '',
    website_url: p.website_url || '',
    contact_email: p.contact_email || '',
    country: p.country || '',
    description: p.description || '',
  })
}

function closeModal() {
  showCreate.value = false
  editingPartner.value = null
  Object.assign(form, { name: '', type_id: '', sector: '', website_url: '', contact_email: '', country: '', description: '' })
}

async function savePartner() {
  try {
    if (editingPartner.value) {
      await api.put(`/partners/${editingPartner.value.id}`, form)
      notif.success('Partner updated!')
    } else {
      await api.post('/partners', form)
      notif.success('Partner added!')
    }
    closeModal()
    fetchPartners()
  } catch (err) {
    notif.error(err.response?.data?.message || 'Failed to save partner')
  }
}

function confirmDelete(p) {
  deletingPartner.value = p
  showDelete.value = true
}

async function deletePartner() {
  try {
    await api.delete(`/partners/${deletingPartner.value.id}`)
    notif.success('Partner removed')
    showDelete.value = false
    fetchPartners()
  } catch (err) {
    notif.error(err.response?.data?.message || 'Failed to remove partner')
  }
}

onMounted(async () => {
  fetchPartners()
  try {
    const { data } = await api.get('/lookups/agreement_types')
    partnerTypes.value = data
  } catch (e) {}
})
</script>
