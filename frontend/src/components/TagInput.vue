<template>
  <div>
    <label v-if="label" class="block text-xs font-bold text-slate-400 capitalize tracking-widest mb-2 ml-1">
      {{ label }}
      <span v-if="required" class="text-rose-500">*</span>
    </label>
    
    <div 
      class="w-full relative bg-white border rounded-xl overflow-hidden transition-colors focus-within:ring-2 focus-within:ring-brand focus-within:border-brand"
      :class="[
        error ? 'border-rose-400 focus-within:ring-rose-500 focus-within:border-rose-500' : 'border-slate-200 hover:border-slate-300',
        disabled ? 'opacity-60 cursor-not-allowed' : ''
      ]"
      @click="focusInput"
    >
      <div class="p-2 flex flex-wrap gap-2">
        <span 
          v-for="(tag, index) in modelValue" 
          :key="tag"
          class="inline-flex items-center gap-1.5 px-3 py-1 bg-white border border-slate-200 text-slate-700 text-xs font-bold rounded-lg shadow-sm"
        >
          {{ tag }}
          <button 
            v-if="!disabled"
            type="button" 
            @click.stop="removeTag(index)"
            class="text-slate-400 hover:text-rose-500 transition-colors focus:outline-none"
          >
            &times;
          </button>
        </span>
        
        <input 
          ref="inputRef"
          type="text" 
          v-model="inputValue"
          @keydown.enter.prevent="addTag"
          @keydown.,.prevent="addTag"
          @keydown.backspace="handleBackspace"
          :placeholder="modelValue.length === 0 ? placeholder : ''"
          :disabled="disabled"
          class="flex-1 min-w-[100px] bg-transparent outline-none px-2 py-1 text-sm font-semibold text-slate-800 placeholder-slate-400"
        />
      </div>
    </div>
    
    <div class="flex justify-between items-center mt-1.5 px-1">
      <p v-if="error" class="text-[10px] font-bold capitalize tracking-widest text-rose-500">{{ error }}</p>
      <p v-else-if="helpText" class="text-[10px] font-bold capitalize tracking-widest text-slate-400">{{ helpText }}</p>
      
      <p v-if="min > 0" class="text-[10px] font-bold tracking-widest capitalize transition-colors" 
         :class="modelValue.length < min ? 'text-amber-500' : 'text-emerald-500'">
        {{ modelValue.length }} / {{ min }} min
      </p>
    </div>
  </div>
</template>

<script setup>
import { ref } from 'vue'

const props = defineProps({
  modelValue: { type: Array, default: () => [] },
  label: { type: String, default: '' },
  placeholder: { type: String, default: 'Type and press Enter' },
  helpText: { type: String, default: '' },
  required: { type: Boolean, default: false },
  disabled: { type: Boolean, default: false },
  error: { type: String, default: '' },
  min: { type: Number, default: 0 }
})

const emit = defineEmits(['update:modelValue'])
const inputRef = ref(null)
const inputValue = ref('')

function focusInput() {
  if (!props.disabled && inputRef.value) {
    inputRef.value.focus()
  }
}

function addTag() {
  const tag = inputValue.value.trim().replace(/^,+|,+$/g, '')
  if (tag && !props.modelValue.includes(tag)) {
    emit('update:modelValue', [...props.modelValue, tag])
  }
  inputValue.value = ''
}

function removeTag(index) {
  const newTags = [...props.modelValue]
  newTags.splice(index, 1)
  emit('update:modelValue', newTags)
}

function handleBackspace(e) {
  if (inputValue.value === '' && props.modelValue.length > 0) {
    removeTag(props.modelValue.length - 1)
  }
}
</script>
