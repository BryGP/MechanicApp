/**
 * Currency and numeric formatting helpers.
 */

export function formatCurrency(val) {
  const num = parseFloat(val || 0)
  const sign = num < 0 ? '-$' : '$'
  return sign + Math.abs(num).toLocaleString('en-US', {
    minimumFractionDigits: 2,
    maximumFractionDigits: 2,
  })
}

export function formatNumber(val) {
  const num = parseInt(val || 0, 10)
  return num.toLocaleString('en-US')
}

export function formatDateTime(val) {
  if (!val) return '—'
  const d = new Date(val)
  if (isNaN(d.getTime())) return String(val)
  return d.toLocaleDateString('es-MX', {
    day: '2-digit',
    month: 'short',
    year: 'numeric',
    hour: '2-digit',
    minute: '2-digit'
  })
}
