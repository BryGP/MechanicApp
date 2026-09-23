/**
 * @fileoverview Authentication and Authorization Composable
 * @module composables/useAuth
 * @description Provides reactive role management (Administrator vs. Operator)
 * and secure PIN-based verification. Strictly avoids storing plaintext credentials
 * in localStorage: uses backend verification, memory-only session tokens, and zero-leakage policies.
 */

import { ref } from 'vue'
import { apiClient, setMemoryAdminPin, getMemoryAdminPin } from '../utils/apiClient'

/** Storage key for the active admin session state in sessionStorage (never stores the PIN) */
const ADMIN_SESSION_KEY = 'mechanic_is_admin'

// Limpieza de seguridad preventiva: purgar cualquier credencial previa en localStorage
try {
  localStorage.removeItem('mechanic_admin_pin')
} catch {}

/**
 * Shared reactive state indicating whether administrator mode is currently active.
 * Restored from sessionStorage on initialization for the current browser tab only.
 * @type {import('vue').Ref<boolean>}
 */
const isAdmin = ref(
  (typeof sessionStorage !== 'undefined' && sessionStorage.getItem(ADMIN_SESSION_KEY) === 'true') ||
  (typeof localStorage !== 'undefined' && localStorage.getItem(ADMIN_SESSION_KEY) === 'true')
)

/**
 * Composable for managing user roles, PIN authorization, and administrator sessions.
 * 
 * @returns {Object} Authentication controls and reactive state
 */
export function useAuth() {
  /**
   * Validates whether an input PIN matches the server administrator hash.
   * Performs an asynchronous verification against the backend API.
   * 
   * @param {string|number} inputPin - PIN entered by the user
   * @returns {Promise<boolean>} True if valid and verified by server
   */
  async function verifyPin(inputPin) {
    if (!inputPin) return false
    try {
      const res = await apiClient.post('/admin/pin/verify', {
        pin: String(inputPin).trim()
      })
      return !!(res && res.valid)
    } catch {
      return false
    }
  }

  /**
   * Attempts to activate Administrator mode using the provided PIN.
   * On success, keeps the verified PIN exclusively in volatile memory (RAM)
   * and marks the session flag. Never writes the PIN to localStorage.
   * 
   * @param {string|number} inputPin - PIN to authenticate
   * @returns {Promise<boolean>} True if login was successful
   */
  async function loginAdmin(inputPin) {
    const cleanPin = String(inputPin || '').trim()
    const isValid = await verifyPin(cleanPin)
    if (isValid) {
      isAdmin.value = true
      setMemoryAdminPin(cleanPin)
      try {
        sessionStorage.setItem(ADMIN_SESSION_KEY, 'true')
      } catch {}
      return true
    }
    return false
  }

  /**
   * Terminates the current Administrator session, returning to standard Operator mode.
   * Immediately wipes the volatile PIN from memory.
   */
  function logoutAdmin() {
    isAdmin.value = false
    setMemoryAdminPin(null)
    try {
      sessionStorage.removeItem(ADMIN_SESSION_KEY)
      localStorage.removeItem(ADMIN_SESSION_KEY)
      localStorage.removeItem('mechanic_admin_pin')
    } catch {}
  }

  /**
   * Updates the Administrator PIN directly on the backend server.
   * Never persists the PIN in client localStorage.
   * 
   * @param {string|number} currentPin - The current active PIN for verification
   * @param {string|number} newPin - The new PIN (minimum 4 characters)
   * @returns {Promise<boolean>} True if updated successfully
   * @throws {Error} If current PIN is invalid or backend rejects update
   */
  async function updatePin(currentPin, newPin) {
    const cleanCurrent = String(currentPin || '').trim()
    const cleanNew = String(newPin || '').trim()

    if (!cleanNew || cleanNew.length < 4) {
      throw new Error('El nuevo PIN debe tener al menos 4 dígitos.')
    }

    // Synchronize credential rotation with the backend REST API
    await apiClient.post('/admin/pin/change', {
      current_pin: cleanCurrent,
      new_pin: cleanNew,
    })

    // If currently logged in as admin, update in-memory volatile session PIN
    if (isAdmin.value) {
      setMemoryAdminPin(cleanNew)
    }

    return true
  }

  return {
    isAdmin,
    verifyPin,
    loginAdmin,
    logoutAdmin,
    updatePin,
    getMemoryAdminPin,
  }
}
