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
  'approved':     { class: 'badge-green',   color: '#1b7a42' },
  'active':       { class: 'badge-green',   color: '#1b7a42' },
  'accepted':     { class: 'badge-green',   color: '#1b7a42' },
  'open':         { class: 'badge-green',   color: '#1b7a42' },
  'completed':    { class: 'badge-blue',    color: '#1d4ed8' },
  'finished':     { class: 'badge-blue',    color: '#1d4ed8' },
  'pending':      { class: 'badge-yellow',  color: '#8a6914' },
  'draft':        { class: 'badge-gray',    color: '#52596b' },
  'review':       { class: 'badge-yellow',  color: '#8a6914' },
  'submitted':    { class: 'badge-blue',    color: '#1d4ed8' },
  'under_review': { class: 'badge-yellow',  color: '#8a6914' },
  'processing':   { class: 'badge-yellow',  color: '#8a6914' },
  'rejected':     { class: 'badge-red',     color: '#a12424' },
  'failed':       { class: 'badge-red',     color: '#a12424' },
  'inactive':     { class: 'badge-gray',    color: '#52596b' },
  'closed':       { class: 'badge-gray',    color: '#52596b' },
  'suspended':    { class: 'badge-red',     color: '#a12424' },
  'published':    { class: 'badge-green',   color: '#1b7a42' },
}

const statusStr = computed(() => {
  if (!props.status) return 'pending'
  if (typeof props.status === 'string') return props.status
  if (typeof props.status === 'object' && props.status.name) return String(props.status.name)
  return String(props.status)
})

const config = computed(() => {
  const s = statusStr.value.toLowerCase()
  for (const key in statusMap) {
    if (s.includes(key)) return statusMap[key]
  }
  return { class: 'badge-gray', color: '#52596b' }
})

const badgeClass = computed(() => config.value.class)
const dotColor = computed(() => config.value.color)

const formattedStatus = computed(() => {
  const s = statusStr.value.replace(/_/g, ' ')
  return s.charAt(0).toUpperCase() + s.slice(1)
})
</script>
