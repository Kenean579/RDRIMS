<template>
  <Teleport to="body">
    <div v-if="show" class="fixed inset-0 z-50 flex items-center justify-center">
      <div class="fixed inset-0 bg-black/50" @click="$emit('cancel')"></div>
      <div class="relative bg-white rounded-lg shadow-xl p-6 w-full max-w-md mx-4">
        <h3 class="text-lg font-semibold text-gray-800 mb-2">{{ title }}</h3>
        <p class="text-sm text-gray-600 mb-4">{{ message }}</p>
        <slot name="extra"></slot>
        <div class="flex justify-end gap-3 mt-4">
          <button @click="$emit('cancel')" class="px-4 py-2 text-sm border border-gray-300 rounded-lg hover:bg-gray-50">Cancel</button>
          <button @click="$emit('confirm')" :class="{ 'bg-red-600 hover:bg-red-700': variant === 'danger', 'bg-blue-600 hover:bg-blue-700': variant === 'primary', 'bg-amber-600 hover:bg-amber-700': variant === 'warning' }"
            class="px-4 py-2 text-sm text-white rounded-lg">{{ confirmText }}</button>
        </div>
      </div>
    </div>
  </Teleport>
</template>
<script setup>
defineProps({ show: Boolean, title: { type: String, default: 'Confirm' }, message: String, confirmText: { type: String, default: 'Confirm' }, variant: { type: String, default: 'danger' } })
defineEmits(['confirm', 'cancel'])
</script>
