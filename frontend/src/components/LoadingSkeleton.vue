<template>
  <div class="space-y-4 animate-pulse">
    <div v-for="i in rows" :key="i" class="bg-gray-200 rounded h-6"></div>
  </div>
</template>
<script setup>defineProps({ rows: { type: Number, default: 5 } })</script>
