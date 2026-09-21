/**
 * @fileoverview Centralized Confirmation Modal Composable
 * @module composables/useConfirm
 * @description Manages a Promise-based modal confirmation workflow, replacing standard
 * browser alert/confirm dialogues with a rich, asynchronous UI component.
 */

import { ref } from 'vue'

/**
 * Shared visibility state for the centralized confirmation modal.
 * @type {import('vue').Ref<boolean>}
 */
const isOpen = ref(false)

/**
 * State container for the active confirmation dialog parameters.
 * @type {import('vue').Ref<Object>}
 */
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

/**
 * Composable for triggering asynchronous confirmation dialogues.
 * 
 * @example
 * const { askConfirm } = useConfirm()
 * const confirmed = await askConfirm({
 *   title: 'Delete Work Order',
 *   itemName: 'Order #42',
 *   requiresAdmin: true
 * })
 * if (confirmed) { ... }
 * 
 * @returns {Object} Confirmation state and triggering functions
 */
export function useConfirm() {
  /**
   * Opens the confirmation modal with custom configuration and returns a Promise
   * that resolves to true (confirmed) or false (cancelled).
   * 
   * @param {Object} options - Configuration parameters
   * @param {string} [options.title='Confirmar Eliminación'] - Modal heading
   * @param {string} [options.message] - Descriptive inquiry message
   * @param {string} [options.itemName] - Name or title of the item being deleted/altered
   * @param {string} [options.itemType='general'] - Category: 'product' | 'service' | 'order' | 'expense'
   * @param {boolean} [options.requiresAdmin=false] - Whether an Admin PIN is enforced
   * @param {string} [options.warningText] - High-visibility security warning or caveat
   * @param {string} [options.confirmText='Sí, eliminar'] - Confirmation button label
   * @param {string} [options.cancelText='Cancelar'] - Dismissal button label
   * @returns {Promise<boolean>} Resolves to true if user confirms, false if dismissed
   */
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

  /**
   * Resolves the current confirmation promise with true and hides the modal.
   */
  function handleConfirm() {
    isOpen.value = false
    if (modalState.value.resolve) {
      modalState.value.resolve(true)
      modalState.value.resolve = null
    }
  }

  /**
   * Resolves the current confirmation promise with false and hides the modal.
   */
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
