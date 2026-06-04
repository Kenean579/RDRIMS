<template>
  <div class="flex flex-col gap-6 pb-6">
    <!-- Header -->
    <div class="mb-2">
      <router-link :to="`/proposals/${proposal.id}`" class="inline-flex items-center gap-1.5 text-sm font-medium text-slate-500 hover:text-brand transition-colors mb-4">
        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
        Back to Proposal
      </router-link>
      <h1 class="text-xl font-bold text-slate-900 tracking-tight">Initialize Research Project</h1>
      <p class="text-slate-500 font-medium mt-1">Convert an approved research proposal into an active project.</p>
    </div>

    <div v-if="loading" class="bg-white rounded-2xl border border-slate-100 p-5">
      <LoadingSkeleton :rows="6" />
    </div>

    <form v-else @submit.prevent="submit" class="flex flex-col gap-5 max-w-4xl">
      <!-- Original Details Card -->
      <div class="bg-white rounded-2xl shadow-sm border border-slate-100 overflow-hidden">
        <div class="bg-slate-50/50 p-6 border-b border-slate-100">
          <h2 class="text-xs flex items-center gap-2 font-bold text-slate-500">
            <span class="w-1.5 h-4 bg-brand rounded-full"></span>
            Original Proposal Details
          </h2>
        </div>
        <div class="p-6">
          <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <div>
              <label class="block text-[10px] text-slate-400  font-medium tracking-widest mb-1.5 ml-1">Research Title</label>
              <div class="text-sm font-bold text-slate-800 bg-slate-50 border border-slate-100 p-3.5 rounded-2xl">{{ proposal.title }}</div>
            </div>
            <div>
              <label class="block text-[10px] text-slate-400  font-medium tracking-widest mb-1.5 ml-1">Principal Investigator</label>
              <div class="text-sm font-bold text-slate-800 bg-slate-50 border border-slate-100 p-3.5 rounded-2xl">{{ proposal.submitted_by?.name }}</div>
            </div>
            <div>
              <label class="block text-[10px] text-slate-400  font-medium tracking-widest mb-1.5 ml-1">Academic Year</label>
              <div class="text-sm font-bold text-slate-800 bg-slate-50 border border-slate-100 p-3.5 rounded-2xl">{{ proposal.academic_year?.name || 'N/A' }}</div>
            </div>
            <div>
              <label class="block text-[10px] text-slate-400  font-medium tracking-widest mb-1.5 ml-1">Requested Budget</label>
              <div class="text-sm font-bold text-emerald-600 bg-emerald-50 border border-emerald-100 p-3.5 rounded-2xl">{{ formatCurrency(proposal.budget) }}</div>
            </div>
          </div>
        </div>
      </div>

      <!-- Project Parameters Card -->
      <div class="bg-white rounded-2xl shadow-sm border border-slate-100 overflow-hidden">
        <div class="bg-brand/5 p-6 border-b border-brand/10">
          <h2 class="text-xs flex items-center gap-2 font-bold text-brand">
            <span class="w-1.5 h-4 bg-brand rounded-full"></span>
            Set Project Parameters
          </h2>
        </div>
        <div class="p-6">
          <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <div>
              <label class="block text-xs font-medium text-slate-400 mb-2 ml-1">Start Date <span class="text-rose-500">*</span></label>
              <input v-model="form.start_date" type="date" required 
                class="w-full border border-slate-300 rounded-2xl px-4 py-3 text-sm font-bold text-slate-800 focus:ring-2 focus:ring-brand/30 focus:border-brand outline-none transition-all" />
            </div>
            <div>
              <label class="block text-xs font-medium text-slate-400 mb-2 ml-1">Expected End Date <span class="text-rose-500">*</span></label>
              <input v-model="form.end_date" type="date" required 
                class="w-full border border-slate-300 rounded-2xl px-4 py-3 text-sm font-bold text-slate-800 focus:ring-2 focus:ring-brand/30 focus:border-brand outline-none transition-all" />
            </div>
            <div class="md:col-span-2">
              <label class="block text-xs font-bold text-emerald-600 mb-2 ml-1">Approved Total Budget (ETB) <span class="text-rose-500">*</span></label>
              <input v-model.number="form.total_budget" type="number" step="0.01" required 
                class="w-full border-2 border-emerald-200 bg-emerald-50 rounded-2xl px-4 py-4 text-emerald-800 font-bold text-lg focus:ring-2 focus:ring-emerald-500/30 focus:border-emerald-500 outline-none transition-all" 
                placeholder="0.00" />
              <p class="text-[10px] font-medium text-emerald-600 mt-2 ml-1">Initial proposal requested: {{ formatCurrency(proposal.budget) }}</p>
            </div>
          </div>
        </div>
      </div>

      <!-- Actions -->
      <div class="flex items-center justify-end gap-3 pt-4">
        <router-link :to="`/proposals/${proposal.id}`" 
          class="px-6 py-3 text-sm font-medium text-slate-500 hover:text-slate-700 transition-colors">
          Cancel
        </router-link>
        <button type="submit" :disabled="submitting" 
          class="inline-flex items-center gap-2 px-5 py-3 text-sm font-bold text-white bg-brand rounded-2xl hover:shadow-brand/50 hover:-translate-y-0.5 transition-all disabled:opacity-60 disabled:pointer-events-none">
          <svg v-if="submitting" class="w-4 h-4 animate-spin" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"/></svg>
          {{ submitting ? 'Initializing...' : 'Create Project' }}
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
import LoadingSkeleton from '@/components/LoadingSkeleton.vue'
import { formatCurrency } from '@/utils/formatters'

const route = useRoute(); const router = useRouter(); const notif = useNotificationStore()
const proposal = ref({}); const loading = ref(true); const submitting = ref(false)

const form = reactive({
  start_date: new Date().toISOString().split('T')[0],
  end_date: new Date(new Date().setFullYear(new Date().getFullYear() + 1)).toISOString().split('T')[0],
  total_budget: 0
})

onMounted(async () => {
  try {
    const { data } = await api.get(`/proposals/${route.params.id}`);
    proposal.value = data;
    form.total_budget = parseFloat(data.budget) || 0;
  } catch(e) {
    notif.error('Failed to load proposal')
    router.back()
  } finally { loading.value = false }
})

async function submit() {
  submitting.value = true
  try {
    const { data } = await api.post(`/proposals/${proposal.value.id}/create-project`, form);
    notif.success('Project successfully created from proposal!');
    router.push(`/app/projects/${data.id}`);
  } catch(e) {
    notif.error(e.response?.data?.message || 'Failed to create project')
  } finally { submitting.value = false }
}
</script>
