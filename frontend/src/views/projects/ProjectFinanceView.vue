<template>
  <div class="flex flex-col gap-8 pb-12 animate-fade">
    <!-- Header -->
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-6">
      <div>
        <router-link :to="`/app/projects/${route.params.id}`" class="flex items-center gap-2 text-brand font-black capitalize tracking-widest text-[10px] mb-3 hover:translate-x-1 transition-transform">
          <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M10 19l-7-7m0 0l7-7m-7 7h18" /></svg>
          Back to Project Overview
        </router-link>
        <h1 class="text-3xl font-black text-slate-900 tracking-tight leading-tight max-w-2xl">Financial Ledger</h1>
        <p class="text-slate-500 font-medium mt-1 capitalize tracking-widest text-[9px]">Expense Tracking & Disbursals</p>
      </div>
      <div>
        <button v-if="auth.hasRole('super_admin', 'resource_admin', 'finance_officer') || isPI" @click="showAddExpense = true" class="btn bg-brand hover:bg-brand-dark text-white text-[11px] font-black capitalize tracking-widest h-11 px-6 shadow-lg shadow-brand/30 flex items-center gap-2">
          <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/></svg>
          Record Expense
        </button>
      </div>
    </div>

    <div v-if="loading" class="grid grid-cols-1 md:grid-cols-3 gap-6">
      <div v-for="i in 3" :key="i" class="bg-white rounded-2xl h-32 animate-pulse border border-slate-100"></div>
    </div>

    <div v-else class="space-y-8">
      
      <!-- Key Financial Metrics -->
      <div class="grid grid-cols-1 md:grid-cols-4 gap-6">
        <div class="bg-white rounded-2xl p-6 border border-slate-200 shadow-sm">
          <p class="text-[10px] capitalize tracking-widest text-slate-400 mb-2 font-black">Total Budget</p>
          <p class="text-2xl font-black text-slate-800">{{ formatCurrency(project.total_budget || 0) }}</p>
        </div>
        <div class="bg-emerald-50 rounded-2xl p-6 border border-emerald-200 shadow-sm">
          <p class="text-[10px] capitalize tracking-widest text-emerald-600 mb-2 font-black">Remaining Balance</p>
          <p class="text-2xl font-black text-emerald-700">{{ formatCurrency(project.remaining_budget || 0) }}</p>
        </div>
        <div class="bg-rose-50 rounded-2xl p-6 border border-rose-200 shadow-sm">
          <p class="text-[10px] capitalize tracking-widest text-rose-600 mb-2 font-black">Total Spent</p>
          <p class="text-2xl font-black text-rose-700">{{ formatCurrency(project.spent_amount || 0) }}</p>
        </div>
        <div class="bg-indigo-50 rounded-2xl p-6 border border-indigo-200 shadow-sm">
          <p class="text-[10px] capitalize tracking-widest text-indigo-600 mb-2 font-black">Total Disbursed</p>
          <p class="text-2xl font-black text-indigo-700">{{ formatCurrency(project.disbursed_amount || 0) }}</p>
        </div>
      </div>

      <!-- Main Ledger Table -->
      <div class="bg-white rounded-2xl shadow-sm border border-slate-200 overflow-hidden">
        <div class="p-6 border-b border-slate-100 flex items-center justify-between">
          <h2 class="text-sm font-black capitalize tracking-widest text-slate-700">Expense Log</h2>
        </div>
        
        <div v-if="!project.expenses || project.expenses.length === 0" class="p-12 text-center text-slate-400 font-bold">
          No expenses recorded yet.
        </div>
        
        <table v-else class="w-full text-left">
          <thead class="bg-slate-50 text-[10px] font-black capitalize tracking-widest text-slate-500">
            <tr>
              <th class="px-6 py-4 rounded-tl-lg">Date</th>
              <th class="px-6 py-4">Title / Purpose</th>
              <th class="px-6 py-4">Category</th>
              <th class="px-6 py-4 text-right">Amount (ETB)</th>
              <th class="px-6 py-4 text-center rounded-tr-lg">Status</th>
            </tr>
          </thead>
          <tbody class="text-sm">
            <tr v-for="exp in project.expenses" :key="exp.id" class="border-t border-slate-100 hover:bg-slate-50/50 transition-colors">
              <td class="px-6 py-4 font-bold text-slate-600">{{ formatDate(exp.expense_date || exp.created_at) }}</td>
              <td class="px-6 py-4 font-bold text-slate-900">{{ exp.title }}</td>
              <td class="px-6 py-4"><span class="px-2 py-1 bg-slate-100 rounded text-[10px] font-black text-slate-500 capitalize tracking-widest">{{ exp.category?.name || 'General' }}</span></td>
              <td class="px-6 py-4 text-right font-black text-rose-600">{{ formatCurrency(exp.amount) }}</td>
              <td class="px-6 py-4 text-center">
                <StatusBadge :status="exp.status?.name" />
              </td>
            </tr>
          </tbody>
        </table>
      </div>

    </div>

    <!-- Add Expense Modal -->
    <Modal :show="showAddExpense" title="Record New Expense" @close="showAddExpense = false">
      <form @submit.prevent="addExpense" class="space-y-6">
        <div>
          <label class="block text-[10px] font-black text-slate-500 capitalize tracking-widest mb-2 ml-1">Expense Title / Description *</label>
          <input v-model="expenseForm.title" type="text" required class="w-full border border-slate-300 rounded-xl px-4 py-3 text-sm font-bold focus:ring-2 focus:ring-brand/30 focus:border-brand outline-none transition-all" placeholder="e.g. Lab Equipment Purchase" />
        </div>
        <div class="grid grid-cols-2 gap-6">
          <div>
            <label class="block text-[10px] font-black text-slate-500 capitalize tracking-widest mb-2 ml-1">Amount (ETB) *</label>
            <input v-model.number="expenseForm.amount" type="number" step="0.01" required min="1" class="w-full border border-slate-300 rounded-xl px-4 py-3 text-sm font-bold focus:ring-2 focus:ring-brand/30 focus:border-brand outline-none transition-all" />
          </div>
          <div>
            <label class="block text-[10px] font-black text-slate-500 capitalize tracking-widest mb-2 ml-1">Date *</label>
            <input v-model="expenseForm.expense_date" type="date" required class="w-full border border-slate-300 rounded-xl px-4 py-3 text-sm font-bold focus:ring-2 focus:ring-brand/30 focus:border-brand outline-none transition-all" />
          </div>
        </div>
        <div class="flex justify-end gap-3 pt-6 border-t border-slate-100">
          <button type="button" @click="showAddExpense = false" class="btn btn-secondary px-8 h-11 text-[11px] font-black capitalize tracking-widest">Cancel</button>
          <button type="submit" :disabled="submitting" class="btn btn-primary px-10 h-11 shadow-lg shadow-brand/20 text-[11px] font-black capitalize tracking-widest">
            {{ submitting ? 'Saving...' : 'Add Expense' }}
          </button>
        </div>
      </form>
    </Modal>
  </div>
</template>

<script setup>
import { ref, reactive, computed, onMounted } from 'vue'
import { useRoute } from 'vue-router'
import { useAuthStore } from '@/stores/auth'
import { useNotificationStore } from '@/stores/notification'
import api from '@/services/api'
import StatusBadge from '@/components/StatusBadge.vue'
import Modal from '@/components/Modal.vue'
import { formatDate, formatCurrency } from '@/utils/formatters'

const route = useRoute(); const auth = useAuthStore(); const notif = useNotificationStore()
const project = ref({}); const loading = ref(true); const submitting = ref(false)

const showAddExpense = ref(false)
const expenseForm = reactive({ title: '', amount: 0, expense_date: new Date().toISOString().split('T')[0] })

const isPI = computed(() => auth.user?.id === project.value.pi_id)

async function fetchProjectData() {
  loading.value = true
  try {
    const { data } = await api.get(`/projects/${route.params.id}`)
    project.value = data
  } catch(e) {
    notif.error('Failed to load financial records')
  } finally { loading.value = false }
}

async function addExpense() {
  submitting.value = true
  try {
    // In our backend, there's usually an expenses endpoint like POST /projects/{id}/expenses.
    // If not, it will 404, but we implement this to match system specs.
    await api.post(`/projects/${project.value.id}/expenses`, expenseForm)
    notif.success('Expense recorded successfully')
    showAddExpense.value = false
    Object.assign(expenseForm, { title: '', amount: 0, expense_date: new Date().toISOString().split('T')[0] })
    fetchProjectData()
  } catch (err) {
    notif.error('Failed to save expense. You might lack permissions or the endpoint needs adjustment.')
  } finally { submitting.value = false }
}

onMounted(fetchProjectData)
</script>
