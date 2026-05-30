<template>
  <div class="relative">
    <label v-if="label" class="block text-xs font-black text-slate-400 uppercase tracking-widest mb-2 ml-1">
      {{ label }}
      <span v-if="required" class="text-rose-500">*</span>
    </label>
    <div class="relative">
      <select 
        :value="modelValue"
        @change="$emit('update:modelValue', $event.target.value)"
        :disabled="disabled || loading"
        class="appearance-none w-full px-4 py-3 bg-slate-50 border border-slate-200 text-slate-900 rounded-xl focus:outline-none focus:ring-2 focus:ring-brand focus:border-brand font-bold text-sm hover:bg-slate-100 transition-colors disabled:opacity-60 disabled:cursor-not-allowed"
        :class="error ? 'border-rose-500 ring-rose-500' : ''"
      >
        <option value="" disabled>{{ placeholder || 'Select an option' }}</option>
        <option v-if="loading" value="" disabled>Loading...</option>
        <option v-else v-for="item in options" :key="item.id" :value="item.id">
          {{ item.name || item.title || item.label }}
        </option>
      </select>
      <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center px-4 text-slate-400">
        <svg v-if="loading" class="animate-spin h-4 w-4" fill="none" viewBox="0 0 24 24">
          <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
          <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
        </svg>
        <svg v-else class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M19 9l-7 7-7-7" />
        </svg>
      </div>
    </div>
    <p v-if="error" class="mt-1.5 text-[10px] font-black uppercase tracking-widest text-rose-500 ml-1">{{ error }}</p>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import { useLookupStore } from '@/stores/lookup'

const props = defineProps({
  modelValue: { type: [String, Number], default: '' },
  lookupKey: { type: String, required: true }, // e.g. 'proposal_types', 'call_statuses'
  label: { type: String, default: '' },
  placeholder: { type: String, default: '' },
  required: { type: Boolean, default: false },
  disabled: { type: Boolean, default: false },
  error: { type: String, default: '' }
})

defineEmits(['update:modelValue'])

const lookupStore = useLookupStore()
const options = ref([])
const loading = ref(true)

onMounted(async () => {
  loading.value = true
  try {
    options.value = await lookupStore.fetchLookup(props.lookupKey)
  } catch (err) {
    console.error(`Failed to load options for ${props.lookupKey}`, err)
  } finally {
    loading.value = false
  }
})
</script>
