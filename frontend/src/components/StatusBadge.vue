<template>
  <span :class="['badge', badgeClass]">
    <span class="badge-dot" :style="{ background: dotColor }"></span>
    {{ formattedStatus }}
  </span>
</template>

<script setup>
import { computed } from 'vue'

const props = defineProps({
  status: { type: String, default: 'pending' }
})

const statusMap = {
  // Success / Active
  'approved':   { class: 'badge-green', color: '#10b981' },
  'active':     { class: 'badge-green', color: '#10b981' },
  'accepted':   { class: 'badge-green', color: '#10b981' },
  'completed':  { class: 'badge-indigo', color: '#6366f1' },
  'finished':   { class: 'badge-indigo', color: '#6366f1' },
  
  // Pending / Review
  'pending':    { class: 'badge-yellow', color: '#f59e0b' },
  'draft':      { class: 'badge-gray',   color: '#64748b' },
  'review':     { class: 'badge-blue',   color: '#3b82f6' },
  'processing': { class: 'badge-blue',   color: '#3b82f6' },
  
  // Danger / Negative
  'rejected':   { class: 'badge-red',    color: '#ef4444' },
  'failed':     { class: 'badge-red',    color: '#ef4444' },
  'inactive':   { class: 'badge-gray',   color: '#94a3b8' },
  'closed':     { class: 'badge-gray',   color: '#475569' },
}

const config = computed(() => {
  const s = props.status.toLowerCase()
  for (const key in statusMap) {
    if (s.includes(key)) return statusMap[key]
  }
  return { class: 'badge-gray', color: '#94a3b8' }
})

const badgeClass = computed(() => config.value.class)
const dotColor = computed(() => config.value.color)

const formattedStatus = computed(() => {
  return props.status.replace(/_/g, ' ').replace(/\b\w/g, l => l.toUpperCase())
})
</script>

<style scoped>
.badge {
  display: inline-flex;
  align-items: center;
  gap: 6px;
  padding: 3px 10px;
}
.badge-dot {
  width: 6px;
  height: 6px;
  border-radius: 50%;
}
</style>
