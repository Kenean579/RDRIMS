import { defineStore } from 'pinia'
import { ref, computed } from 'vue'
import api from '@/services/api'

export const useAuthStore = defineStore('auth', () => {
  const user = ref(JSON.parse(localStorage.getItem('rdrims_user') || 'null'))
  const token = ref(localStorage.getItem('rdrims_token') || '')
  const loading = ref(false)
  const error = ref(null)

  const isAuthenticated = computed(() => !!token.value)

  const userRoles = computed(() => {
    if (!user.value?.roles) return []
    return user.value.roles.map(r => r.name)
  })

  const userPermissions = computed(() => {
    if (!user.value?.roles) return []
    const perms = []
    user.value.roles.forEach(role => {
      if (role.permissions) role.permissions.forEach(p => { if (!perms.includes(p.name)) perms.push(p.name) })
    })
    return perms
  })

  const hasRole = computed(() => (...names) => names.length === 0 || names.some(r => userRoles.value.includes(r)))
  const hasPermission = computed(() => name => !name || userPermissions.value.includes(name))
  const primaryRole = computed(() => userRoles.value.length ? userRoles.value[0].replace(/_/g, ' ') : 'Guest')

  async function login(email, password) {
    loading.value = true; error.value = null
    try {
      const { data } = await api.post('/login', { email, password })
      token.value = data.access_token; user.value = data.user
      localStorage.setItem('rdrims_token', data.access_token)
      localStorage.setItem('rdrims_user', JSON.stringify(data.user))
      return true
    } catch (err) { error.value = err.response?.data?.message || 'Login failed'; return false }
    finally { loading.value = false }
  }

  async function register(formData) {
    loading.value = true; error.value = null
    try {
      const { data } = await api.post('/register', formData)
      token.value = data.access_token; user.value = data.user
      localStorage.setItem('rdrims_token', data.access_token)
      localStorage.setItem('rdrims_user', JSON.stringify(data.user))
      return true
    } catch (err) {
      if (err.response?.data?.errors) {
        const msgs = []; Object.values(err.response.data.errors).forEach(a => a.forEach(m => msgs.push(m)))
        error.value = msgs.join('. ')
      } else error.value = err.response?.data?.message || 'Registration failed'
      return false
    } finally { loading.value = false }
  }

  async function fetchUser() {
    try {
      const { data } = await api.get('/user')
      user.value = data
      localStorage.setItem('rdrims_user', JSON.stringify(data))
    } catch (err) {
      if (err.response?.status === 401) {
        // Silently clear stale credentials – do NOT redirect.
        // The router guard will handle redirect only if the user
        // tries to access a requiresAuth route.
        token.value = ''
        user.value = null
        localStorage.removeItem('rdrims_token')
        localStorage.removeItem('rdrims_user')
      }
    }
  }

  function clearError() { error.value = null }

  async function logout() {
    try { await api.post('/logout') } catch (e) {}
    token.value = ''; user.value = null
    localStorage.removeItem('rdrims_token'); localStorage.removeItem('rdrims_user')
    window.location.href = '/login'
  }

  async function updateProfile(formData) {
    loading.value = true; error.value = null
    try {
      const { data } = await api.put('/profile', formData)
      user.value = data; localStorage.setItem('rdrims_user', JSON.stringify(data))
      return true
    } catch (err) { error.value = err.response?.data?.message || 'Update failed'; return false }
    finally { loading.value = false }
  }

  return { user, token, loading, error, isAuthenticated, userRoles, userPermissions, hasRole, hasPermission, primaryRole, login, register, fetchUser, updateProfile, clearError, logout }
})
