const STORAGE_BASE = (import.meta.env.VITE_API_BASE_URL || 'http://127.0.0.1:8000/api').replace('/api', '/storage') + '/'

/**
 * Build a full URL to a backend-stored image.
 * @param {object|null} file  - The relationship object e.g. event.banner or user.profile_image
 * @param {string} fallback   - Fallback URL when no image is present
 */
export function imageUrl(file, fallback = '/fallback_profile_image.png') {
  if (file?.url) return file.url
  if (!file?.file_path) return fallback
  // If it's already an absolute URL pass it through
  if (file.file_path.startsWith('http')) return file.file_path
  return STORAGE_BASE + file.file_path
}

export function formatCurrency(amount) {
  if (amount === null || amount === undefined) return 'ETB 0.00'
  return 'ETB ' + Number(amount).toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 })
}

export function formatDate(dateStr, options = {}) {
  if (!dateStr) return 'N/A'
  const date = new Date(dateStr)
  if (isNaN(date.getTime())) return 'Invalid Date'
  return date.toLocaleDateString('en-US', { year: 'numeric', month: 'short', day: 'numeric', ...options })
}

export function formatDateTime(dateStr) {
  if (!dateStr) return 'N/A'
  const date = new Date(dateStr)
  if (isNaN(date.getTime())) return 'Invalid Date'
  return date.toLocaleDateString('en-US', { year: 'numeric', month: 'short', day: 'numeric', hour: '2-digit', minute: '2-digit' })
}

export function getInitials(name) {
  if (!name) return '?'
  const parts = name.trim().split(/\s+/)
  if (parts.length === 1) return parts[0].charAt(0).toUpperCase()
  return (parts[0].charAt(0) + parts[parts.length - 1].charAt(0)).toUpperCase()
}

export function truncateText(text, maxLength = 100) {
  if (!text || text.length <= maxLength) return text || ''
  return text.substring(0, maxLength).trimEnd() + '...'
}
