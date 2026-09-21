import { ref } from 'vue'

const isOpen = ref(false)
const modalState = ref({
  title: 'Confirmar Eliminación',
  message: '',
  itemName: '',
  itemType: 'general', // 'product' | 'service' | 'order' | 'expense'
  requiresAdmin: false,
  warningText: '',
  confirmText: 'Sí, eliminar',
  cancelText: 'Cancelar',
  resolve: null,
})

export function useConfirm() {
  function askConfirm(options = {}) {
    return new Promise((resolve) => {
      modalState.value = {
        title: options.title || 'Confirmar Eliminación',
        message: options.message || '¿Estás seguro de que deseas eliminar este elemento?',
        itemName: options.itemName || '',
        itemType: options.itemType || 'general',
        requiresAdmin: !!options.requiresAdmin,
        warningText: options.warningText || '',
        confirmText: options.confirmText || 'Sí, eliminar',
        cancelText: options.cancelText || 'Cancelar',
        resolve,
      }
      isOpen.value = true
    })
  }

  function handleConfirm() {
    isOpen.value = false
    if (modalState.value.resolve) {
      modalState.value.resolve(true)
      modalState.value.resolve = null
    }
  }

  function handleCancel() {
    isOpen.value = false
    if (modalState.value.resolve) {
      modalState.value.resolve(false)
      modalState.value.resolve = null
    }
  }

  return {
    isOpen,
    modalState,
    askConfirm,
    handleConfirm,
    handleCancel,
  }
}
