/**
 * @fileoverview Centralized REST API Client
 * @module utils/apiClient
 * @description Standardized HTTP communication wrapper around the browser Fetch API.
 * Provides automatic JSON serialization, headers injection (including Content-Type,
 * Accept, and administrative security tokens), and unified HTTP error parsing.
 */

/** Base API URL configured via Vite environment variables */
const BASE_URL = import.meta.env.VITE_API_URL

/** Storage key for the customized administrator PIN */
const ADMIN_PIN_KEY = 'mechanic_admin_pin'

/** Default fallback PIN when none is configured in localStorage */
const DEFAULT_PIN = '1234'

/**
 * Retrieves the currently configured administrator PIN from local storage.
 * @returns {string} The active administrator PIN
 */
function getActiveAdminPin() {
  return localStorage.getItem(ADMIN_PIN_KEY) || DEFAULT_PIN
}

/**
 * Executes an HTTP request against the backend REST API with standard headers and error handling.
 * 
 * @async
 * @param {string} endpoint - API path relative to base (e.g. '/products' or '/orders/5')
 * @param {RequestInit} [options={}] - Standard Fetch options (method, headers, body, etc.)
 * @returns {Promise<any>} Parsed JSON response payload or null
 * @throws {Error} Descriptive error message extracted from API validation or network failures
 */
async function request(endpoint, options = {}) {
  const url = `${BASE_URL}${endpoint.startsWith('/') ? endpoint : `/${endpoint}`}`

  const headers = {
    'Content-Type': 'application/json',
    'Accept': 'application/json',
    'X-Admin-Pin': options.adminPin || getActiveAdminPin(),
    ...(options.headers || {}),
  }

  const config = {
    ...options,
    headers,
  }

  if (config.body && typeof config.body === 'object' && !(config.body instanceof FormData)) {
    config.body = JSON.stringify(config.body)
  }

  let response
  try {
    response = await fetch(url, config)
  } catch (networkError) {
    throw new Error('Error de conexión: No se pudo comunicar con el servidor backend.')
  }

  if (!response.ok) {
    const errorData = await response.json().catch(() => ({}))

    let errorMessage = 'Error en el servidor al procesar la solicitud.'

    if (errorData.errors && typeof errorData.errors === 'object') {
      errorMessage = Object.values(errorData.errors).flat().join('. ')
    } else if (errorData.message) {
      errorMessage = errorData.message
    }

    throw new Error(errorMessage)
  }

  // Handle 204 No Content
  if (response.status === 204) {
    return null
  }

  return await response.json().catch(() => ({}))
}

/**
 * Centralized API Client Export
 */
export const apiClient = {
  /**
   * HTTP GET request helper.
   * @param {string} endpoint - Target path
   * @param {Object} [options] - Additional options
   * @returns {Promise<any>}
   */
  get(endpoint, options = {}) {
    return request(endpoint, { ...options, method: 'GET' })
  },

  /**
   * HTTP POST request helper.
   * @param {string} endpoint - Target path
   * @param {any} data - Payload data
   * @param {Object} [options] - Additional options
   * @returns {Promise<any>}
   */
  post(endpoint, data, options = {}) {
    return request(endpoint, { ...options, method: 'POST', body: data })
  },

  /**
   * HTTP PUT request helper.
   * @param {string} endpoint - Target path
   * @param {any} data - Updated payload data
   * @param {Object} [options] - Additional options
   * @returns {Promise<any>}
   */
  put(endpoint, data, options = {}) {
    return request(endpoint, { ...options, method: 'PUT', body: data })
  },

  /**
   * HTTP DELETE request helper.
   * @param {string} endpoint - Target path
   * @param {Object} [options] - Options (may include custom adminPin)
   * @returns {Promise<any>}
   */
  delete(endpoint, options = {}) {
    return request(endpoint, { ...options, method: 'DELETE' })
  },
}

export default apiClient
