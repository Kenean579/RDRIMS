import axios from 'axios'

const api = axios.create({
  baseURL: 'http://127.0.0.1:8000/api',
  headers: { 'Content-Type': 'application/json', 'Accept': 'application/json' },
  timeout: 30000,
})

import { useContextStore } from '@/stores/context'

api.interceptors.request.use(config => {
  const token = localStorage.getItem('rdrims_token')
  if (token) config.headers.Authorization = `Bearer ${token}`
  
  // Inject global hierarchical context into GET queries
  // Exclude hierarchy lookup endpoints to prevent circular filtering
  const CONTEXT_EXCLUDED = ['/universities', '/campuses', '/faculties', '/departments', '/lookups', '/settings']
  if (config.method === 'get' || config.method === 'GET') {
    try {
      const isExcluded = CONTEXT_EXCLUDED.some(ep => config.url?.endsWith(ep) || config.url?.match(new RegExp(`${ep}\\?`)) || config.url?.match(new RegExp(`${ep}/`)))
      if (!isExcluded) {
        const context = useContextStore()
        if (context) {
          config.params = config.params || {}
          if (context.university_id) config.params.university_id = context.university_id
          if (context.campus_id) config.params.campus_id = context.campus_id
          if (context.faculty_id) config.params.faculty_id = context.faculty_id
          if (context.department_id) config.params.department_id = context.department_id
        }
      }
    } catch (e) {
      // Pinia not yet initialized, ignore
    }
  }
  
  return config
}, error => Promise.reject(error))

// Public endpoints that must NOT trigger a logout redirect on 401
const PUBLIC_ENDPOINTS = ['/settings', '/lookups', '/universities', '/calls', '/publications', '/community-problems', '/events', '/public']

api.interceptors.response.use(
  response => response,
  error => {
    if (error.response?.status === 401) {
      const url = error.config?.url || ''
      const isPublic = PUBLIC_ENDPOINTS.some(p => url.includes(p))
      if (!isPublic) {
        localStorage.removeItem('rdrims_token')
        localStorage.removeItem('rdrims_user')
        if (window.location.pathname !== '/login') window.location.href = '/login'
      }
    }
    return Promise.reject(error)
  }
)

export default api
