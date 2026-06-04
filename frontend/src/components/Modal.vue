<template>
  <Teleport to="body">
    <div v-if="show" class="fixed inset-0 z-50 flex items-center justify-center">
      <div class="fixed inset-0 bg-black/50" @click="$emit('close')"></div>
      <div class="relative bg-white rounded-2xl shadow-xl w-full mx-4" :class="sizeClass">
        <div class="flex items-center justify-between p-8 border-b border-slate-100">
          <h3 class="text-xl font-bold text-gray-800 tracking-tight">{{ title }}</h3>
          <button @click="$emit('close')" class="text-gray-400 hover:text-gray-600 text-2xl">&times;</button>
        </div>
        <div class="p-8 max-h-[75vh] overflow-y-auto font-medium text-slate-600"><slot /></div>
        <div v-if="$slots.footer" class="p-8 border-t border-slate-100 flex justify-end gap-3 bg-slate-50/50 rounded-b-2xl"><slot name="footer" /></div>
      </div>
    </div>
  </Teleport>
</template>
<script setup>
import { computed } from 'vue'
const props = defineProps({ show: Boolean, title: String, size: { type: String, default: 'md' } })
defineEmits(['close'])
const sizeClass = computed(() => ({ sm: 'max-w-sm', md: 'max-w-md', lg: 'max-w-lg', xl: 'max-w-xl', '2xl': 'max-w-2xl' }[props.size] || 'max-w-md'))
</script>
