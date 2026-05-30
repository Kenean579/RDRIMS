import axios from 'axios'

const api = axios.create({
  baseURL: 'http://127.0.0.1:8000/api',
  headers: { 'Content-Type': 'application/json', 'Accept': 'application/json' },
  timeout: 30000,
})

api.interceptors.request.use(config => {
  const token = localStorage.getItem('rdrims_token')
  if (token) config.headers.Authorization = `Bearer ${token}`
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
