import { ref } from 'vue'
import { useRouter } from 'vue-router'

const isRefreshing = ref(false)

export function usePageRefresh() {
  const router = useRouter()

  async function refreshPage() {
    if (isRefreshing.value) return

    isRefreshing.value = true

    const currentPath = router.currentRoute.value.fullPath

    // temporarily remove and return to reload component
    await router.replace('/')

    setTimeout(async () => {
      await router.replace(currentPath)
      isRefreshing.value = false
    }, 100)
  }

  return {
    refreshPage,
    isRefreshing
  }
}