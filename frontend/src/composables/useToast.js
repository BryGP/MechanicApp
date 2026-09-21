/**
 * @fileoverview Toast Notification Management Composable
 * @module composables/useToast
 * @description Provides a reactive notification queue system with automatic timeout dismissal,
 * supporting multiple toast types (success, error, info) rendered in the foreground.
 */

import { ref } from 'vue'

/**
 * Shared reactive list of active toast notifications.
 * @type {import('vue').Ref<Array<{id: number, message: string, type: string}>>}
 */
const toasts = ref([])

/** Auto-incrementing identifier counter for toast instances */
let nextId = 0

/**
 * Composable for dispatching and managing temporary screen notifications.
 * 
 * @returns {Object} Toast state and notification trigger functions
 * @property {import('vue').Ref<Array>} toasts - Active toast array
 * @property {function(number): void} remove - Removes a specific toast by its ID
 * @property {function(string): void} success - Displays a success notification (green)
 * @property {function(string): void} error - Displays an error/alert notification (red)
 * @property {function(string): void} info - Displays an informational notification (blue)
 */
export function useToast() {
  /**
   * Removes a toast notification from the queue by its unique ID.
   * 
   * @param {number} id - Unique identifier of the toast to dismiss
   */
  function remove(id) {
    toasts.value = toasts.value.filter(t => t.id !== id)
  }

  /**
   * Appends a new notification to the active queue and schedules its automatic dismissal.
   * 
   * @private
   * @param {string} message - Text content to display to the user
   * @param {'success'|'error'|'info'} [type='success'] - Visual severity category
   */
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