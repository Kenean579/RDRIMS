<template>
  <div class="min-h-screen flex items-center justify-center bg-gray-50 px-4 py-8 card">
    <div class="bg-white p-8 rounded-lg shadow-sm border border-gray-100 w-full max-w-md">
      <div class="text-center mb-6">
        <div class="w-14 h-14 bg-blue-600 rounded-lg flex items-center justify-center mx-auto mb-3"><span class="text-white text-xl font-bold">R</span></div>
        <h1 class="text-xl font-bold text-gray-800">Create Account</h1>
        <p class="text-sm text-gray-500 mt-1">Join the RDRIMS research community</p>
      </div>
      <div v-if="auth.error" class="bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded-lg mb-4 text-sm">{{ auth.error }}</div>
      <form @submit.prevent="handleRegister" class="space-y-4">
        <div><label class="block text-sm font-medium text-gray-700 mb-1">Full Name *</label><input v-model="form.name" type="text" required class="w-full border border-gray-300 rounded-lg px-4 py-2.5 text-sm focus:ring-2 focus:ring-blue-500 outline-none" placeholder="Dr. Abebe Kebede" /></div>
        <div><label class="block text-sm font-medium text-gray-700 mb-1">Email *</label><input v-model="form.email" type="email" required class="w-full border border-gray-300 rounded-lg px-4 py-2.5 text-sm focus:ring-2 focus:ring-blue-500 outline-none" placeholder="abebe@wollo.edu.et" /></div>
        <div><label class="block text-sm font-medium text-gray-700 mb-1">Department</label><select v-model="form.department_id" class="w-full border border-gray-300 rounded-lg px-4 py-2.5 text-sm focus:ring-2 focus:ring-blue-500 outline-none bg-white"><option value="">Select department (optional)</option><option v-for="dept in departments" :key="dept.id" :value="dept.id">{{ dept.name }}</option></select></div>
        <div><label class="block text-sm font-medium text-gray-700 mb-1">Password *</label><input v-model="form.password" type="password" required minlength="8" class="w-full border border-gray-300 rounded-lg px-4 py-2.5 text-sm focus:ring-2 focus:ring-blue-500 outline-none" placeholder="Minimum 8 characters" /></div>
        <div><label class="block text-sm font-medium text-gray-700 mb-1">Confirm Password *</label><input v-model="form.password_confirmation" type="password" required minlength="8" class="w-full border border-gray-300 rounded-lg px-4 py-2.5 text-sm focus:ring-2 focus:ring-blue-500 outline-none" placeholder="Re-enter password" /></div>
        <button type="submit" :disabled="auth.loading" class="w-full bg-blue-600 text-white py-2.5 rounded-lg hover:bg-blue-700 text-sm font-medium disabled:opacity-60">{{ auth.loading ? 'Creating...' : 'Create Account' }}</button>
      </form>
      <p class="text-center mt-6 text-sm text-gray-500">Already have an account? <router-link to="/login" class="text-blue-600 hover:underline font-medium">Sign In</router-link></p>
    </div>
  </div>
</template>
<script setup>
import { reactive, ref, onMounted } from 'vue'
import { useRouter } from 'vue-router'
import { useAuthStore } from '@/stores/auth'
import api from '@/services/api'
const router = useRouter(); const auth = useAuthStore()
const form = reactive({ name: '', email: '', password: '', password_confirmation: '', department_id: '' })
const departments = ref([])
onMounted(async () => { try { const { data } = await api.get('/departments'); departments.value = data } catch (e) {} })
async function handleRegister() { if (form.password !== form.password_confirmation) { auth.error = 'Passwords do not match.'; return }; if (await auth.register({ ...form, department_id: form.department_id || null })) router.push('/dashboard') }
</script>
