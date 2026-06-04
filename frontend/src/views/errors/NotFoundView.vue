<template>
  <div class="min-h-screen flex items-center justify-center bg-gray-50 px-4 card">
    <div class="text-center">
      <p class="text-8xl font-bold text-gray-300 mb-4">404</p>
      <h1 class="text-2xl font-bold text-gray-800 mb-2">Page Not Found</h1>
      <p class="text-gray-500 mb-6">The page you're looking for doesn't exist or has been moved.</p>
      <router-link to="/"
        class="bg-blue-600 text-white px-6 py-2.5 rounded-2xl hover:bg-blue-700 transition text-sm font-medium">
        Go to Dashboard
      </router-link>
    </div>
  </div>
</template>
