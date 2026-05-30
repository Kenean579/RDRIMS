export function getStatusColor(statusName) {
  if (!statusName) return 'bg-gray-100 text-gray-600'
  const name = statusName.toLowerCase()
  if (/approved|active|completed|done|granted|accepted|success/.test(name)) return 'bg-green-100 text-green-700'
  if (/rejected|suspended|expired|declined|failed|cancelled/.test(name)) return 'bg-red-100 text-red-700'
  if (/submitted|in_progress|processing|ongoing/.test(name)) return 'bg-blue-100 text-blue-700'
  if (/under_review|claimed|minor|revision/.test(name)) return 'bg-amber-100 text-amber-700'
  if (/pending|open|finance_check|major/.test(name)) return 'bg-orange-100 text-orange-700'
  if (/draft|not_started/.test(name)) return 'bg-gray-100 text-gray-600'
  return 'bg-gray-100 text-gray-600'
}

export function formatStatusName(statusName) {
  if (!statusName) return ''
  return statusName.replace(/_/g, ' ').replace(/\b\w/g, c => c.toUpperCase())
}
