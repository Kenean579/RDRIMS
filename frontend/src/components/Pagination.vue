<template>
  <div v-if="totalPages > 1" class="flex items-center justify-between mt-6">
    <p class="text-sm text-gray-600">Showing {{ from }} to {{ to }} of {{ total }} results</p>
    <div class="flex gap-1">
      <button @click="$emit('page-change', currentPage - 1)" :disabled="currentPage === 1"
        class="px-3 py-1.5 text-sm rounded border border-gray-300 bg-white hover:bg-gray-50 disabled:opacity-50 disabled:cursor-not-allowed">Previous</button>
      <button v-for="page in visiblePages" :key="page" @click="$emit('page-change', page)"
        :class="page === currentPage ? 'bg-blue-600 text-white border-blue-600' : 'bg-white text-gray-700 border-gray-300 hover:bg-gray-50'"
        class="px-3 py-1.5 text-sm rounded border">{{ page }}</button>
      <button @click="$emit('page-change', currentPage + 1)" :disabled="currentPage === totalPages"
        class="px-3 py-1.5 text-sm rounded border border-gray-300 bg-white hover:bg-gray-50 disabled:opacity-50 disabled:cursor-not-allowed">Next</button>
    </div>
  </div>
</template>

<script setup>
import { computed } from 'vue'
const props = defineProps({ currentPage: Number, totalPages: Number, total: Number, perPage: { type: Number, default: 20 } })
defineEmits(['page-change'])
const from = computed(() => (props.currentPage - 1) * props.perPage + 1)
const to = computed(() => Math.min(props.currentPage * props.perPage, props.total))
const visiblePages = computed(() => { const p = []; const s = Math.max(1, props.currentPage - 2); const e = Math.min(props.totalPages, props.currentPage + 2); for (let i = s; i <= e; i++) p.push(i); return p })
</script>
