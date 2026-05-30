<template>
  <div class="flex flex-col gap-6 animate-fade card">
    <div class="mb-6">
      <router-link :to="`/proposals/${proposal.id}`" class="text-sm text-blue-600 hover:underline mb-2 inline-block">← Back to Proposal</router-link>
      <h1 class="text-3xl font-black text-slate-900 tracking-tight">Convert to Project</h1>
      <p class="text-slate-500 font-medium mt-1">Initialize project parameters from approved proposal.</p>
    </div>

    <div v-if="loading" class="card p-8"><LoadingSkeleton :rows="6" /></div>

    <form v-else @submit.prevent="submit" class="flex flex-col gap-6">
      <div class="card p-6 bg-slate-50">
        <h2 class="text-[11px] font-black uppercase tracking-widest text-slate-400 mb-4">Original Proposal Details</h2>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
          <div><label class="block text-[10px] text-slate-400 uppercase font-black tracking-widest">Title</label><div class="text-sm font-bold mt-1 text-slate-800">{{ proposal.title }}</div></div>
          <div><label class="block text-[10px] text-slate-400 uppercase font-black tracking-widest">Principal Investigator</label><div class="text-sm font-bold mt-1 text-slate-800">{{ proposal.submitted_by?.name }}</div></div>
          <div><label class="block text-[10px] text-slate-400 uppercase font-black tracking-widest">Academic Year</label><div class="text-sm font-bold mt-1 text-slate-800">{{ proposal.academic_year?.name || 'N/A' }}</div></div>
          <div><label class="block text-[10px] text-slate-400 uppercase font-black tracking-widest">Requested Budget</label><div class="text-sm font-bold mt-1 text-slate-800">{{ formatCurrency(proposal.budget) }}</div></div>
        </div>
      </div>

      <div class="card p-6">
         <h2 class="text-[11px] font-black uppercase tracking-widest text-brand mb-4">Set Project Parameters</h2>
         <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <div>
              <label class="block text-[11px] text-slate-500 font-black uppercase tracking-widest mb-2 ml-1">Start Date *</label>
              <input v-model="form.start_date" type="date" required class="input h-12 font-bold" />
            </div>
            <div>
              <label class="block text-[11px] text-slate-500 font-black uppercase tracking-widest mb-2 ml-1">End Date *</label>
              <input v-model="form.end_date" type="date" required class="input h-12 font-bold" />
            </div>
            <div>
              <label class="block text-[11px] text-slate-500 font-black uppercase tracking-widest mb-2 ml-1">Approved Total Budget *</label>
              <input v-model.number="form.total_budget" type="number" step="0.01" required class="input h-12 font-bold" />
            </div>
         </div>
      </div>

      <div class="flex justify-end gap-4 mt-2">
         <router-link :to="`/proposals/${proposal.id}`" class="btn btn-secondary px-8 font-black uppercase tracking-widest text-[11px]">Cancel</router-link>
         <button type="submit" :disabled="submitting" class="btn btn-primary px-10 shadow-lg shadow-blue-500/20 font-black uppercase tracking-widest text-[11px]">
            {{ submitting ? 'Creating...' : 'Initialize Project' }}
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
    notif.success('Project successfully created!');
    router.push(`/projects/${data.id}`);
  } catch(e) {
    notif.error(e.response?.data?.message || 'Failed to create project')
  } finally { submitting.value = false }
}
</script>
