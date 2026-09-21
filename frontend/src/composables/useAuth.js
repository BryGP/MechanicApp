import { ref } from 'vue'

const ADMIN_PIN_KEY = 'mechanic_admin_pin'
const ADMIN_SESSION_KEY = 'mechanic_is_admin'
const DEFAULT_PIN = '1234'

const isAdmin = ref(localStorage.getItem(ADMIN_SESSION_KEY) === 'true')

export function useAuth() {
  function getAdminPin() {
    return localStorage.getItem(ADMIN_PIN_KEY) || DEFAULT_PIN
  }

  function verifyPin(inputPin) {
    if (!inputPin) return false
    return String(inputPin).trim() === getAdminPin()
  }

  function loginAdmin(inputPin) {
    if (verifyPin(inputPin)) {
      isAdmin.value = true
      localStorage.setItem(ADMIN_SESSION_KEY, 'true')
      return true
    }
    return false
  }

  function logoutAdmin() {
    isAdmin.value = false
    localStorage.removeItem(ADMIN_SESSION_KEY)
  }

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
