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

  // Hierarchical level calculation based on highest role
  const hierarchicalLevel = computed(() => {
    const roleHierarchy = {
      'super_admin': 6,
      'research_admin': 5,
      'campus_admin': 4,
      'faculty_admin': 3,
      'department_head': 2,
      'director': 2, // Same as department head for center management
      'researcher': 1,
      'reviewer': 1,
      'student': 1,
      'guest': 0,
      'finance_officer': 5, // Functional but university-level
      'ethics_officer': 5  // Functional but university-level
    }
    
    if (!userRoles.value.length) return 0
    return Math.max(...userRoles.value.map(role => roleHierarchy[role] || 0))
  })

  // Check if user can manage a specific hierarchical level
  function canManageLevel(targetLevel) {
    return hierarchicalLevel.value >= targetLevel
  }

  // Check if user has any of the specified roles (union of all roles for multi-role users)
  function hasRole(...names) {
    if (userRoles.value.includes('super_admin')) return true
    return names.length === 0 || names.some(r => userRoles.value.includes(r))
  }

  // Check if user has a specific permission (union of all role permissions)
  function hasPermission(name) {
    if (userRoles.value.includes('super_admin')) return true
    return !name || userPermissions.value.includes(name)
  }

  // Get the highest-level role for display purposes
  const primaryRole = computed(() => {
    if (!userRoles.value.length) return 'Guest'
    
    const roleDisplayOrder = [
      'super_admin',
      'research_admin', 
      'campus_admin',
      'faculty_admin',
      'department_head',
      'director',
      'finance_officer',
      'ethics_officer',
      'researcher',
      'reviewer',
      'student'
    ]
    
    // Return the highest-level role for display
    for (const role of roleDisplayOrder) {
      if (userRoles.value.includes(role)) {
        return role.replace(/_/g, ' ').replace(/\b\w/g, l => l.toUpperCase())
      }
    }
    
    return userRoles.value[0].replace(/_/g, ' ').replace(/\b\w/g, l => l.toUpperCase())
  })

  // Get all role names for multi-role support
  const allRoles = computed(() => {
    return userRoles.value.map(role => role.replace(/_/g, ' ').replace(/\b\w/g, l => l.toUpperCase()))
  })

  // Check if user is an admin (any admin level)
  const isAdmin = computed(() => {
    return userRoles.value.some(role => 
      ['super_admin', 'research_admin', 'campus_admin', 'faculty_admin', 'department_head', 'director', 'finance_officer', 'ethics_officer'].includes(role)
    )
  })

  // Check if user is a functional admin (finance or ethics)
  const isFunctionalAdmin = computed(() => {
    return userRoles.value.some(role => ['finance_officer', 'ethics_officer'].includes(role))
  })

  // Get user's scope level for UI context
  const userScope = computed(() => {
    if (userRoles.value.includes('super_admin')) return 'system'
    if (userRoles.value.includes('research_admin') || userRoles.value.includes('finance_officer') || userRoles.value.includes('ethics_officer')) return 'university'
    if (userRoles.value.includes('campus_admin')) return 'campus'
    if (userRoles.value.includes('faculty_admin')) return 'faculty'
    if (userRoles.value.includes('department_head')) return 'department'
    if (userRoles.value.includes('director')) return 'research_center'
    // Fallback: check direct institutional assignment from payload
    if (user.value?.research_center) return 'research_center'
    if (user.value?.department_id) return 'department'
    if (user.value?.university_id || user.value?.university) return 'university'
    return 'individual'
  })

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
      console.error('Failed to fetch user data:', err)
      if (err.response?.status === 401) {
        // Silently clear stale credentials – do NOT redirect.
        // The router guard will handle redirect only if the user
        // tries to access a requiresAuth route.
        token.value = ''
        user.value = null
        localStorage.removeItem('rdrims_token')
        localStorage.removeItem('rdrims_user')
      } else if (err.response?.status === 500) {
        // Handle server errors gracefully - use cached user data if available
        console.warn('Server error fetching user data, using cached data')
        error.value = 'Server error - using cached data'
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

  return { 
    user, token, loading, error, 
    isAuthenticated, 
    userRoles, userPermissions, 
    hierarchicalLevel, canManageLevel,
    hasRole, hasPermission, 
    primaryRole, allRoles,
    isAdmin, isFunctionalAdmin,
    userScope,
    login, register, fetchUser, updateProfile, clearError, logout 
  }
})