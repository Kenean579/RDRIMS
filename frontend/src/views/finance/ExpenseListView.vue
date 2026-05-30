<template>
  <div class="flex flex-col gap-6 card">
    <div class="section-header">
      <div>
        <h1 class="section-title">Expense Tracking</h1>
        <p class="section-subtitle">Track project expenditures and financial disbursements</p>
      </div>
      <button @click="showCreate = true" class="btn btn-primary">
        <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 4v16m8-8H4" /></svg>
        Record Expense
      </button>
    </div>

    <div v-if="loading" class="card p-8"><LoadingSkeleton :rows="4" /></div>

    <div v-else class="card overflow-hidden">
      <div class="overflow-x-auto">
        <table class="table-auto">
          <thead>
            <tr>
              <th>Project</th>
              <th>Description</th>
              <th>Amount</th>
              <th>Date</th>
              <th>Status</th>
              <th>Actions</th>
            </tr>
          </thead>
          <tbody>
            <tr v-for="ex in expenses" :key="ex.id" class="group">
              <td class="font-bold text-slate-700 truncate max-w-[150px]">{{ ex.project?.title }}</td>
              <td class="text-sm text-slate-500">{{ ex.description }}</td>
              <td><span class="font-bold text-slate-800">{{ formatCurrency(ex.amount) }}</span></td>
              <td class="text-sm text-slate-500">{{ formatDate(ex.expense_date) }}</td>
              <td>
                <span class="badge" :class="{
                  'badge-green': ex.status === 'approved',
                  'badge-red': ex.status === 'rejected',
                  'badge-yellow': ex.status !== 'approved' && ex.status !== 'rejected'
                }">{{ ex.status }}</span>
              </td>
              <td>
                <div v-if="ex.status === 'pending' || ex.status === 'submitted'" class="flex gap-2">
                   <button @click="updateExpenseStatus(ex.id, 'approved')" class="text-xs font-bold text-emerald-600 hover:text-emerald-700 bg-emerald-50 px-2 py-1 rounded">Approve</button>
                   <button @click="updateExpenseStatus(ex.id, 'rejected')" class="text-xs font-bold text-rose-600 hover:text-rose-700 bg-rose-50 px-2 py-1 rounded">Reject</button>
                </div>
              </td>
            </tr>
          </tbody>
        </table>
      </div>
      <div class="p-4 border-t border-slate-100">
        <Pagination :current-page="pagination.current_page" :total-pages="pagination.last_page" :total="pagination.total" @page-change="fetchExpenses" />
      </div>
    </div>

    <Modal :show="showCreate" title="Record New Expense" @close="showCreate = false">
      <form @submit.prevent="saveExpense" class="space-y-5 px-1 py-1">
        <div>
          <label class="block text-[11px] text-slate-500 font-bold uppercase tracking-wider mb-1.5 ml-1">Project *</label>
          <select v-model="form.project_id" required class="input">
            <option v-for="p in projects" :key="p.id" :value="p.id">{{ p.title }}</option>
          </select>
        </div>
        <div class="grid grid-cols-2 gap-4">
          <div>
            <label class="block text-[11px] text-slate-500 font-bold uppercase tracking-wider mb-1.5 ml-1">Amount *</label>
            <input v-model.number="form.amount" type="number" step="0.01" required class="input" />
          </div>
          <div>
            <label class="block text-[11px] text-slate-500 font-bold uppercase tracking-wider mb-1.5 ml-1">Date *</label>
            <input v-model="form.expense_date" type="date" required class="input" />
          </div>
        </div>
        <div>
          <label class="block text-[11px] text-slate-500 font-bold uppercase tracking-wider mb-1.5 ml-1">Description *</label>
          <input v-model="form.description" type="text" required class="input" placeholder="Expense description..." />
        </div>
        <div class="flex justify-end gap-3 pt-4">
          <button type="button" @click="showCreate = false" class="btn btn-secondary">Cancel</button>
          <button type="submit" class="btn btn-primary px-10">Save Expense</button>
        </div>
      </form>
    </Modal>
  </div>
</template>

<script setup>
import { ref, reactive, onMounted } from 'vue'
import { useNotificationStore } from '@/stores/notification'
import api from '@/services/api'
import Pagination from '@/components/Pagination.vue'
import Modal from '@/components/Modal.vue'
import LoadingSkeleton from '@/components/LoadingSkeleton.vue'
import { formatDate, formatCurrency } from '@/utils/formatters'

const notif = useNotificationStore()
const expenses = ref([]); const projects = ref([]); const loading = ref(true)
const pagination = reactive({ current_page: 1, last_page: 1, total: 0 })
const showCreate = ref(false)
const form = reactive({ project_id: '', amount: '', expense_date: new Date().toISOString().split('T')[0], description: '', category: 'general' })

async function fetchExpenses(page = 1) {
  loading.value = true
  try {
    const { data } = await api.get('/expenses', { params: { page } })
    expenses.value = data.data; Object.assign(pagination, { current_page: data.current_page, last_page: data.last_page, total: data.total })
  } catch (e) {} finally { loading.value = false }
}

async function saveExpense() {
  try {
    await api.post('/expenses', form)
    notif.success('Expense recorded!')
    showCreate.value = false; Object.assign(form, { project_id: '', amount: '', description: '' }); fetchExpenses()
  } catch (e) { notif.error('Failed to save expense') }
}

async function updateExpenseStatus(id, newStatus) {
  try {
    await api.put(`/expenses/${id}`, { status: newStatus })
    notif.success(`Expense ${newStatus}!`)
    fetchExpenses(pagination.current_page)
  } catch (e) { notif.error('Failed to update status') }
}

onMounted(async () => {
  fetchExpenses()
  try { const { data } = await api.get('/projects'); projects.value = data.data || data } catch (e) {}
})
</script>
