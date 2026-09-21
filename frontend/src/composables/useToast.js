import { ref } from 'vue'

const toasts = ref([])
let nextId = 0

export function useToast() {
  function remove(id) {
    toasts.value = toasts.value.filter(t => t.id !== id)
  }

  function add(message, type = 'success') {
    const id = ++nextId
    toasts.value.push({ id, message, type })
    setTimeout(() => {
      remove(id)
    }, 4500)
  }

  return {
    toasts,
    remove,
    success: (msg) => add(msg, 'success'),
    error: (msg) => add(msg, 'error'),
    info: (msg) => add(msg, 'info'),
  }
}