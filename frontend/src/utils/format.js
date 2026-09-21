/**
 * @fileoverview Formatting Utilities
 * @module utils/format
 * @description Standardized formatting helper functions for currencies, numbers,
 * and human-readable date-time representations across the application.
 */

/**
 * Formats a numeric value as a localized currency string in Mexican Pesos (MXN).
 * Handles negative signs, decimals, and empty/null inputs gracefully.
 * 
 * @example
 * formatCurrency(1250.5) // returns "$1,250.50"
 * formatCurrency(-450)   // returns "-$450.00"
 * 
 * @param {number|string} val - Numeric input value
 * @returns {string} Formatted currency string with two decimal places
 */
export function formatCurrency(val) {
  const num = parseFloat(val || 0)
  const sign = num < 0 ? '-$' : '$'
  return sign + Math.abs(num).toLocaleString('en-US', {
    minimumFractionDigits: 2,
    maximumFractionDigits: 2,
  })
}

/**
 * Formats an integer value with localized thousands separators.
 * 
 * @example
 * formatNumber(12500) // returns "12,500"
 * 
 * @param {number|string} val - Input number
 * @returns {string} Formatted integer string
 */
export function formatNumber(val) {
  const num = parseInt(val || 0, 10)
  return num.toLocaleString('en-US')
}

/**
 * Formats an ISO date string or timestamp into a localized, human-friendly date and time.
 * Uses Mexican Spanish convention (e.g., '21 sep 2026, 14:30').
 * 
 * @example
 * formatDateTime('2026-09-21T18:30:00Z') // returns "21 sep 2026, 12:30"
 * 
 * @param {string|Date} val - ISO timestamp or Date object
 * @returns {string} Formatted localized date and time, or fallback string
 */
export function formatDateTime(val) {
  if (!val) return '—'
  const d = new Date(val)
  if (isNaN(d.getTime())) return String(val)
  return d.toLocaleDateString('es-MX', {
    day: '2-digit',
    month: 'short',
    year: 'numeric',
    hour: '2-digit',
    minute: '2-digit',
  })
}
