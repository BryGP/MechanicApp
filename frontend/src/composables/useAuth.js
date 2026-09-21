/**
 * @fileoverview Authentication and Authorization Composable
 * @module composables/useAuth
 * @description Provides reactive role management (Administrator vs. Operator)
 * and PIN-based security verification for high-privilege operations in the workshop.
 */

import { ref } from 'vue'

/** Storage key for the customized administrator PIN */
const ADMIN_PIN_KEY = 'mechanic_admin_pin'

/** Storage key for the active admin session state */
const ADMIN_SESSION_KEY = 'mechanic_is_admin'

/** Default fallback PIN when none is configured in localStorage */
const DEFAULT_PIN = '1234'

/**
 * Shared reactive state indicating whether administrator mode is currently active.
 * Restored from localStorage on initialization to maintain session across refreshes.
 * @type {import('vue').Ref<boolean>}
 */
const isAdmin = ref(localStorage.getItem(ADMIN_SESSION_KEY) === 'true')

/**
 * Composable for managing user roles, PIN authorization, and administrator sessions.
 * 
 * @returns {Object} Authentication controls and reactive state
 * @property {import('vue').Ref<boolean>} isAdmin - Whether admin privileges are active
 * @property {function(string): boolean} verifyPin - Validates a given PIN against the stored PIN
 * @property {function(string): boolean} loginAdmin - Authenticates and activates admin mode
 * @property {function(): void} logoutAdmin - Deactivates admin mode and clears session
 * @property {function(string, string): boolean} updatePin - Changes the admin PIN after verification
 * @property {string} defaultPin - The initial system PIN (1234)
 */
export function useAuth() {
  /**
   * Retrieves the current administrator PIN from persistent storage.
   * @private
   * @returns {string} The active PIN
   */
  function getAdminPin() {
    return localStorage.getItem(ADMIN_PIN_KEY) || DEFAULT_PIN
  }

  /**
   * Validates whether an input PIN matches the stored administrator PIN.
   * 
   * @param {string|number} inputPin - PIN entered by the user
   * @returns {boolean} True if the PIN is valid and matches
   */
  function verifyPin(inputPin) {
    if (!inputPin) return false
    return String(inputPin).trim() === getAdminPin()
  }

  /**
   * Attempts to activate Administrator mode using the provided PIN.
   * Persists the session flag in localStorage upon success.
   * 
   * @param {string|number} inputPin - PIN to authenticate
   * @returns {boolean} True if login was successful, false otherwise
   */
  function loginAdmin(inputPin) {
    if (verifyPin(inputPin)) {
      isAdmin.value = true
      localStorage.setItem(ADMIN_SESSION_KEY, 'true')
      return true
    }
    return false
  }

  /**
   * Terminates the current Administrator session, returning to standard Operator mode.
   */
  function logoutAdmin() {
    isAdmin.value = false
    localStorage.removeItem(ADMIN_SESSION_KEY)
  }

  /**
   * Updates the Administrator PIN to a new value after verifying the current PIN.
   * 
   * @param {string|number} currentPin - The current active PIN for verification
   * @param {string|number} newPin - The new PIN (minimum 4 characters)
   * @returns {boolean} True if updated successfully, false if current PIN invalid or new PIN malformed
   */
  function updatePin(currentPin, newPin) {
    if (!verifyPin(currentPin)) return false
    if (!newPin || String(newPin).trim().length < 4) return false
    localStorage.setItem(ADMIN_PIN_KEY, String(newPin).trim())
    return true
  }

  return {
    isAdmin,
    verifyPin,
    loginAdmin,
    logoutAdmin,
    updatePin,
    defaultPin: DEFAULT_PIN,
  }
}
