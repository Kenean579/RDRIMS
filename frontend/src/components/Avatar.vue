<template>
  <div 
    class="relative inline-flex items-center justify-center overflow-hidden bg-slate-100 shrink-0 select-none"
    :class="[
      sizeClasses,
      rounded ? 'rounded-full' : 'rounded-xl',
      border ? 'border-2 border-white shadow-sm ring-1 ring-slate-900/5' : ''
    ]"
  >
    <img 
      v-if="src && !imgError" 
      :src="src" 
      :alt="name"
      @error="imgError = true"
      class="w-full h-full object-cover"
    />
    <span 
      v-else 
      class="font-bold tracking-widest capitalize text-slate-500"
      :class="textClasses"
    >
      {{ initials }}
    </span>
    
    <!-- Status indicator (active/inactive green/gray dot) -->
    <span 
      v-if="showStatus"
      class="absolute bottom-0 right-0 block rounded-full ring-2 ring-white"
      :class="[
        statusSizeClasses,
        isActive ? 'bg-emerald-500' : 'bg-slate-400'
      ]"
    ></span>
  </div>
</template>

<script setup>
import { computed, ref, watch } from 'vue'

const props = defineProps({
  src: { type: String, default: null },
  name: { type: String, default: 'User' },
  size: { type: String, default: 'md', validator: (v) => ['xs', 'sm', 'md', 'lg', 'xl', '2xl'].includes(v) },
  rounded: { type: Boolean, default: true },
  border: { type: Boolean, default: false },
  showStatus: { type: Boolean, default: false },
  isActive: { type: Boolean, default: true }
})

const imgError = ref(false)

watch(() => props.src, () => {
  imgError.value = false
})

const initials = computed(() => {
  if (!props.name) return '?'
  const parts = props.name.trim().split(' ')
  if (parts.length === 1) return parts[0].substring(0, 2).toUpperCase()
  return (parts[0][0] + parts[parts.length - 1][0]).toUpperCase()
})

const sizeClasses = computed(() => {
  switch (props.size) {
    case 'xs': return 'h-6 w-6'
    case 'sm': return 'h-8 w-8'
    case 'md': return 'h-10 w-10'
    case 'lg': return 'h-12 w-12'
    case 'xl': return 'h-16 w-16'
    case '2xl': return 'h-24 w-24'
    default: return 'h-10 w-10'
  }
})

const textClasses = computed(() => {
  switch (props.size) {
    case 'xs': return 'text-[10px]'
    case 'sm': return 'text-xs'
    case 'md': return 'text-sm'
    case 'lg': return 'text-base'
    case 'xl': return 'text-xl'
    case '2xl': return 'text-xl'
    default: return 'text-sm'
  }
})

const statusSizeClasses = computed(() => {
  switch (props.size) {
    case 'xs': return 'h-1.5 w-1.5'
    case 'sm': return 'h-2 w-2'
    case 'md': return 'h-2.5 w-2.5'
    case 'lg': return 'h-3 w-3'
    case 'xl': return 'h-4 w-4'
    case '2xl': return 'h-5 w-5'
    default: return 'h-2.5 w-2.5'
  }
})
</script>
