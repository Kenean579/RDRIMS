<template>
  <div class="w-full">
    <label v-if="label" class="block text-xs font-medium text-slate-400 mb-2 ml-1">
      {{ label }}
      <span v-if="required" class="text-rose-500">*</span>
    </label>

    <div 
      @dragover.prevent="dragover = true"
      @dragleave.prevent="dragover = false"
      @drop.prevent="handleDrop"
      class="border-2 border-dashed rounded-2xl p-5 flex flex-col items-center justify-center transition-all bg-slate-50 relative overflow-hidden"
      :class="[ dragover ? 'border-brand bg-brand/5' : 'border-slate-300 hover:border-slate-400', error ? 'border-rose-400 bg-rose-50' : '' ]"
    >
      <input 
        type="file" 
        :accept="allowedExtensions" 
        class="absolute inset-0 w-full h-full opacity-0 cursor-pointer" 
        @change="handleFileSelect"
        :disabled="disabled"
      />
      
      <div v-if="!selectedFile" class="text-center pointer-events-none">
        <div class="h-14 w-14 rounded-full bg-white shadow-sm flex items-center justify-center mx-auto mb-4 text-slate-400 border border-slate-100">
          <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12" />
          </svg>
        </div>
        <p class="text-sm font-bold text-slate-700">Click to upload or drag and drop</p>
        <p class="text-xs text-slate-400 font-medium mt-2">
          {{ allowedExtensions.replace(/\./g, '') }} (MAX. {{ maxSizeMb }}MB)
        </p>
      </div>

      <div v-else class="w-full relative z-10 bg-white border border-slate-100 rounded-2xl p-4 flex items-center gap-4 shadow-sm">
        <div class="h-10 w-10 shrink-0 rounded-2xl bg-indigo-50 border border-indigo-100 flex items-center justify-center text-indigo-500">
          <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
          </svg>
        </div>
        <div class="flex-1 min-w-0">
          <p class="text-sm font-bold text-slate-800 truncate">{{ selectedFile.name }}</p>
          <p class="text-xs font-medium tracking-widest text-slate-400 mt-1">{{ formatBytes(selectedFile.size) }}</p>
        </div>
        <button type="button" @click.prevent="clearFile" class="shrink-0 h-8 w-8 rounded-full bg-slate-100 hover:bg-rose-100 text-slate-500 hover:text-rose-600 flex items-center justify-center transition-colors">
          <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
          </svg>
        </button>
      </div>
    </div>
    <p v-if="error || internalError" class="mt-2 text-xs font-medium text-rose-500 ml-1">
      {{ error || internalError }}
    </p>
  </div>
</template>

<script setup>
import { ref, computed, watch } from 'vue'
import { useLookupStore } from '@/stores/lookup'

const props = defineProps({
  modelValue: { type: File, default: null },
  label: { type: String, default: '' },
  required: { type: Boolean, default: false },
  disabled: { type: Boolean, default: false },
  error: { type: String, default: '' }
})

const emit = defineEmits(['update:modelValue'])
const lookupStore = useLookupStore()

const dragover = ref(false)
const selectedFile = ref(props.modelValue)
const internalError = ref('')

const maxSizeMb = computed(() => Number(lookupStore.getSetting('max_file_upload_size_mb', 10)))
const allowedExtensions = computed(() => lookupStore.getSetting('allowed_file_types', '.pdf,.doc,.docx'))

watch(() => props.modelValue, (newVal) => {
  if (newVal !== selectedFile.value) {
    selectedFile.value = newVal
  }
})

function handleFileSelect(event) {
  const file = event.target.files[0]
  processFile(file)
  event.target.value = '' // Reset input
}

function handleDrop(event) {
  dragover.value = false
  const file = event.dataTransfer.files[0]
  processFile(file)
}

function processFile(file) {
  internalError.value = ''
  if (!file) return

  // Validate size
  if (file.size > maxSizeMb.value * 1024 * 1024) {
    internalError.value = `File size exceeds the maximum limit of ${maxSizeMb.value}MB.`
    return
  }

  // Validate extension (dot-agnostic check)
  const ext = file.name.split('.').pop().toLowerCase()
  const allowed = allowedExtensions.value.toLowerCase().replace(/\./g, '').split(',')
  
  if (!allowed.includes(ext)) {
    internalError.value = `Invalid file type. Allowed types: ${allowedExtensions.value}`
    return
  }

  selectedFile.value = file
  emit('update:modelValue', file)
}

function clearFile() {
  selectedFile.value = null
  internalError.value = ''
  emit('update:modelValue', null)
}

function formatBytes(bytes, decimals = 2) {
  if (!+bytes) return '0 Bytes'
  const k = 1024
  const dm = decimals < 0 ? 0 : decimals
  const sizes = ['Bytes', 'KB', 'MB', 'GB']
  const i = Math.floor(Math.log(bytes) / Math.log(k))
  return `${parseFloat((bytes / Math.pow(k, i)).toFixed(dm))} ${sizes[i]}`
}
</script>
