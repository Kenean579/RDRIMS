<template>
  <div class="relative inline-block text-left" ref="menuRef">
    <!-- Trigger Button -->
    <button
      @click.stop="toggleMenu"
      type="button"
      class="inline-flex items-center justify-center rounded-full hover:bg-slate-100 text-slate-400 hover:text-slate-600 focus:outline-none focus:ring-2 focus:ring-brand focus:ring-offset-1 transition-colors"
      :class="[
        size === 'sm' ? 'w-8 h-8' : 'w-10 h-10',
        isOpen ? 'bg-slate-100 text-slate-700' : ''
      ]"
      aria-haspopup="true"
      :aria-expanded="isOpen"
    >
      <span class="sr-only">Open options</span>
      <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 5v.01M12 12v.01M12 19v.01M12 6a1 1 0 110-2 1 1 0 010 2zm0 7a1 1 0 110-2 1 1 0 010 2zm0 7a1 1 0 110-2 1 1 0 010 2z" />
      </svg>
    </button>

    <!-- Dropdown Menu -->
    <transition
      enter-active-class="transition ease-out duration-150"
      enter-from-class="transform opacity-0 scale-95 translate-y-[-10px]"
      enter-to-class="transform opacity-100 scale-100 translate-y-0"
      leave-active-class="transition ease-in duration-100"
      leave-from-class="transform opacity-100 scale-100 translate-y-0"
      leave-to-class="transform opacity-0 scale-95 translate-y-[-10px]"
    >
      <div
        v-if="isOpen"
        class="absolute z-50 mt-1 w-[220px] rounded-xl bg-white border border-slate-200 shadow-xl py-1.5"
        :class="[
          align === 'right' ? 'right-0 origin-top-right' : 'left-0 origin-top-left'
        ]"
        role="menu"
        aria-orientation="vertical"
      >
        <template v-for="(action, index) in visibleActions" :key="index">
          <!-- Separator -->
          <div v-if="action.separator" class="my-1.5 border-t border-slate-100"></div>

          <!-- Menu Item -->
          <button
            v-else
            @click.stop="handleAction(action)"
            :disabled="action.disabled"
            class="w-full text-left flex items-center gap-2.5 px-3.5 py-2.5 text-sm font-medium transition-colors duration-200"
            :class="[
              action.disabled ? 'opacity-50 cursor-not-allowed text-slate-400' : getHoverClass(action.key || action.label),
              !action.disabled ? 'text-slate-600' : ''
            ]"
            role="menuitem"
          >
            <!-- Custom Icon or Built-in SVG -->
            <component v-if="action.iconComponent" :is="action.iconComponent" class="w-4 h-4 shrink-0" />
            <span v-else-if="action.icon" v-html="getIconSvg(action.icon)" class="w-4 h-4 shrink-0 flex items-center justify-center"></span>
            <span v-else v-html="getIconSvg(action.key || action.label)" class="w-4 h-4 shrink-0 flex items-center justify-center"></span>
            
            <span class="truncate">{{ action.label }}</span>
          </button>
        </template>
        <div v-if="visibleActions.length === 0" class="px-3.5 py-2.5 text-xs text-slate-400 italic">
          No actions available
        </div>
      </div>
    </transition>
  </div>
</template>

<script setup>
import { ref, computed, onMounted, onUnmounted, watch } from 'vue'

const props = defineProps({
  actions: {
    type: Array,
    required: true,
    // Expected format: { label: 'Edit', key: 'edit', handler: () => {}, show: true/false, disabled: true/false, separator: true/false }
  },
  size: {
    type: String,
    default: 'sm', // 'sm' | 'md'
  },
  align: {
    type: String,
    default: 'right', // 'right' | 'left'
  }
})

const isOpen = ref(false)
const menuRef = ref(null)

const visibleActions = computed(() => {
  return props.actions.filter(action => action.show !== false)
})

const toggleMenu = () => {
  isOpen.value = !isOpen.value
}

const closeMenu = () => {
  isOpen.value = false
}

const handleAction = (action) => {
  if (action.disabled) return
  closeMenu()
  if (typeof action.handler === 'function') {
    action.handler()
  }
}

// Click outside detection
const handleClickOutside = (event) => {
  if (isOpen.value && menuRef.value && !menuRef.value.contains(event.target)) {
    closeMenu()
  }
}

// Escape key to close
const handleKeydown = (event) => {
  if (isOpen.value && event.key === 'Escape') {
    closeMenu()
  }
}

onMounted(() => {
  document.addEventListener('pointerdown', handleClickOutside)
  document.addEventListener('keydown', handleKeydown)
})

onUnmounted(() => {
  document.removeEventListener('pointerdown', handleClickOutside)
  document.removeEventListener('keydown', handleKeydown)
})

// Auto-close if another menu opens (optional global event, simulating simple approach here by relying on click outside)

const getHoverClass = (key) => {
  const k = (key || '').toLowerCase()
  if (k.includes('edit')) return 'hover:bg-blue-50 hover:text-blue-700'
  if (k.includes('view')) return 'hover:bg-slate-50 hover:text-slate-700'
  if (k.includes('permissions') || k.includes('shield')) return 'hover:bg-green-50 hover:text-green-700'
  if (k.includes('approve') || k.includes('accept')) return 'hover:bg-green-50 hover:text-green-700'
  if (k.includes('reject')) return 'hover:bg-amber-50 hover:text-amber-700'
  if (k.includes('delete') || k.includes('remove') || k.includes('archive')) return 'hover:bg-red-50 hover:text-red-600'
  if (k.includes('download')) return 'hover:bg-blue-50 hover:text-blue-700'
  return 'hover:bg-slate-50 hover:text-slate-700'
}

const getIconSvg = (key) => {
  const k = (key || '').toLowerCase()
  if (k.includes('view')) return '<svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>'
  if (k.includes('edit')) return '<svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>'
  if (k.includes('delete') || k.includes('remove')) return '<svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>'
  if (k.includes('approve') || k.includes('accept')) return '<svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>'
  if (k.includes('reject')) return '<svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>'
  if (k.includes('assign') || k.includes('user')) return '<svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z"/></svg>'
  if (k.includes('download')) return '<svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a2 2 0 002 2h2m4-4V4m0 0l4 4m-4-4l-4 4m5 6h2a2 2 0 002-2v-5a2 2 0 00-2-2H9"/></svg>'
  if (k.includes('permissions') || k.includes('shield')) return '<svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/></svg>'
  if (k.includes('versions') || k.includes('history')) return '<svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>'
  if (k.includes('link')) return '<svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/></svg>'
  // Default icon
  return '<svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/></svg>'
}
</script>
