<template>
  <Teleport to="body">
    <div v-if="show" class="fixed inset-0 z-50 flex items-center justify-center">
      <div class="fixed inset-0 bg-black/50" @click="$emit('close')"></div>
      <div class="relative bg-white rounded-lg shadow-xl w-full mx-4" :class="sizeClass">
        <div class="flex items-center justify-between p-5 border-b border-gray-200">
          <h3 class="text-lg font-semibold text-gray-800">{{ title }}</h3>
          <button @click="$emit('close')" class="text-gray-400 hover:text-gray-600 text-xl">&times;</button>
        </div>
        <div class="p-5 max-h-[70vh] overflow-y-auto"><slot /></div>
        <div v-if="$slots.footer" class="p-5 border-t border-gray-200 flex justify-end gap-3"><slot name="footer" /></div>
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
