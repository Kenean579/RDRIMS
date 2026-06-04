<template>
  <div class="w-full">
    <div v-if="showLabels" class="flex justify-between items-end mb-1.5 px-0.5">
      <span class="text-xs font-medium text-slate-500">{{ label || 'Progress' }}</span>
      <span class="text-xs font-bold text-slate-700">{{ formattedValue }} {{ unit }}</span>
    </div>
    
    <div 
      class="w-full bg-slate-100 rounded-full overflow-hidden" 
      :style="{ height: height + 'px' }"
      :class="bgClass"
    >
      <div 
        class="h-full rounded-full transition-all duration-700 ease-out flex items-center justify-end px-2"
        :class="barColor"
        :style="{ width: clampedPercentage + '%' }"
      >
        <span v-if="showInnerLabel && height >= 16" class="text-[10px] font-medium text-white tracking-widest  truncate mix-blend-overlay">
          {{ Math.round(clampedPercentage) }}%
        </span>
      </div>
    </div>
    
    <div v-if="helpText" class="mt-1 px-0.5 space-x-1">
      <span class="text-[10px] font-medium text-slate-400">{{ helpText }}</span>
    </div>
  </div>
</template>

<script setup>
import { computed } from 'vue'

const props = defineProps({
  value: { type: Number, default: 0 },
  max: { type: Number, default: 100 },
  height: { type: [Number, String], default: 8 },
  label: { type: String, default: '' },
  showLabels: { type: Boolean, default: false },
  showInnerLabel: { type: Boolean, default: false },
  unit: { type: String, default: '%' },
  helpText: { type: String, default: '' },
  bgClass: { type: String, default: '' },
  colorClass: { type: String, default: '' },
  // If true, automatically colors green/yellow/red based on completion/utilization
  dynamicColor: { type: Boolean, default: false },
  // If inverse is true, red means close to 100% (e.g. Budget utilized), 
  // green means close to 0%
  inverseDynamic: { type: Boolean, default: false }
})

const clampedPercentage = computed(() => {
  if (props.max === 0) return 0
  const pct = (props.value / props.max) * 100
  return Math.max(0, Math.min(100, pct))
})

const formattedValue = computed(() => {
  if (props.unit === '%') {
    return Math.round(clampedPercentage.value)
  }
  // format with commas for ETB / counts
  return props.value.toLocaleString()
})

const barColor = computed(() => {
  if (props.colorClass) return props.colorClass
  
  if (props.dynamicColor) {
    if (props.inverseDynamic) {
      if (clampedPercentage.value > 90) return 'bg-rose-500'
      if (clampedPercentage.value > 75) return 'bg-amber-400'
      return 'bg-emerald-500'
    } else {
      if (clampedPercentage.value > 90) return 'bg-emerald-500'
      if (clampedPercentage.value > 50) return 'bg-amber-400'
      return 'bg-brand'
    }
  }
  
  return 'bg-brand' // default primary color map
})
</script>
