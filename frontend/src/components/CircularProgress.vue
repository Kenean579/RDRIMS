<template>
  <div class="relative inline-flex items-center justify-center flex-col" :style="{ width: size + 'px', height: size + 'px' }">
    <svg class="transform -rotate-90 w-full h-full" viewBox="0 0 100 100">
      <!-- Background Circle -->
      <circle 
        class="text-slate-100 placeholder-circle transition-all duration-500 ease-in-out" 
        stroke-width="8" 
        stroke="currentColor" 
        fill="transparent" 
        r="44" 
        cx="50" 
        cy="50"
      />
      <!-- Progress Circle -->
      <circle 
        class="progress-circle transition-all duration-1000 ease-out" 
        :class="strokeColor"
        stroke-width="8" 
        :stroke-dasharray="circumference"
        :stroke-dashoffset="strokeDashoffset"
        stroke-linecap="round"
        stroke="currentColor" 
        fill="transparent" 
        r="44" 
        cx="50" 
        cy="50"
      />
    </svg>
    <div class="absolute inset-0 flex flex-col items-center justify-center text-center">
      <span class="text-xl font-black tabular-nums tracking-tighter" :class="textColor">
        {{ Math.round(percentage) }}<span class="text-xs ml-0.5">%</span>
      </span>
      <span v-if="label" class="text-[9px] capitalize tracking-widest font-bold text-slate-400 mt-1">
        {{ label }}
      </span>
    </div>
  </div>
</template>

<script setup>
import { computed } from 'vue'

const props = defineProps({
  percentage: { type: Number, default: 0 },
  size: { type: Number, default: 120 },
  label: { type: String, default: '' },
  threshold: { type: Number, default: 20 }, // Plagiarism threshold limit
  inverse: { type: Boolean, default: false } // If true, higher is better (e.g. originality instead of plagiarism)
})

const radius = 44
const circumference = radius * 2 * Math.PI

const strokeDashoffset = computed(() => {
  const p = Math.max(0, Math.min(100, props.percentage))
  return circumference - (p / 100) * circumference
})

const strokeColor = computed(() => {
  if (props.inverse) {
    if (props.percentage >= (100 - props.threshold)) return 'text-emerald-500'
    if (props.percentage >= (100 - props.threshold * 2)) return 'text-amber-500'
    return 'text-rose-500'
  } else {
    // Standard: Lower is better (similarity score)
    if (props.percentage <= props.threshold) return 'text-emerald-500'
    if (props.percentage <= props.threshold + 10) return 'text-amber-500'
    return 'text-rose-500'
  }
})

const textColor = computed(() => {
  if (props.inverse) {
    if (props.percentage >= (100 - props.threshold)) return 'text-emerald-600'
    if (props.percentage >= (100 - props.threshold * 2)) return 'text-amber-600'
    return 'text-rose-600'
  } else {
    if (props.percentage <= props.threshold) return 'text-emerald-600'
    if (props.percentage <= props.threshold + 10) return 'text-amber-600'
    return 'text-rose-600'
  }
})
</script>

<style scoped>
.progress-circle {
  transition: stroke-dashoffset 1s ease-out, stroke 0.3s ease;
}
</style>
