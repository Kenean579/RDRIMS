import { defineStore } from 'pinia'
import { ref } from 'vue'

export const useNotificationStore = defineStore('notification', () => {
  const message = ref('')
  const type = ref('success')
  const show = ref(false)
  let timer = null

  function notify(msg, msgType = 'success', duration = 5000) {
    if (timer) clearTimeout(timer)
    message.value = msg; type.value = msgType; show.value = true
    timer = setTimeout(() => { show.value = false; message.value = '' }, duration)
  }
  function success(msg) { notify(msg, 'success') }
  function error(msg) { notify(msg, 'error', 8000) }
  function warning(msg) { notify(msg, 'warning') }
  function info(msg) { notify(msg, 'info') }

  return { message, type, show, notify, success, error, warning, info }
})
