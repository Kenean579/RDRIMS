<script setup>
import { ref } from 'vue';
import api from '../api/axios';
import TheWelcome from '../components/TheWelcome.vue'

const backendStatus = ref('Click to check');

const checkBackend = async () => {
  backendStatus.value = 'Checking...';
  try {
    // Try to reach the CSRF endpoint first (standard Sanctum flow)
    await api.get('http://localhost:8000/sanctum/csrf-cookie');
    backendStatus.value = 'Connected to Backend!';
  } catch (err) {
    backendStatus.value = 'Connection Failed: ' + err.message;
  }
};
</script>

<template>
  <main class="p-8">
    <div class="mb-8 p-4 bg-gray-100 rounded-lg shadow-sm border border-gray-200">
      <h2 class="text-xl font-bold mb-4">Backend Connection Test</h2>
      <div class="flex items-center gap-4">
        <button 
          @click="checkBackend" 
          class="bg-blue-600 hover:bg-blue-700 text-white font-bold py-2 px-6 rounded-lg transition-colors duration-200"
        >
          Check Backend
        </button>
        <span :class="{'text-green-600': backendStatus.includes('Connected'), 'text-red-600': backendStatus.includes('Failed'), 'text-gray-600': !backendStatus.includes('Connected') && !backendStatus.includes('Failed')}" class="font-medium">
          Status: {{ backendStatus }}
        </span>
      </div>
    </div>
    <TheWelcome />
  </main>
</template>
