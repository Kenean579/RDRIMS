import { ref } from 'vue'
import api from '@/services/api'

export function useGet(endpoint, params = {}) {
  const data = ref(null)
  const loading = ref(true)
  const error = ref(null)

  async function fetch() {
    loading.value = true; error.value = null
    try { const res = await api.get(endpoint, { params }); data.value = res.data }
    catch (err) { error.value = err.response?.data?.message || 'Request failed' }
    finally { loading.value = false }
  }

  return { data, loading, error, fetch }
}
