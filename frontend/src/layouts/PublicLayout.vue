<template>
  <div class="min-h-screen flex flex-col bg-slate-50 relative font-inter text-slate-900">
    <PublicNavbar />
    
    <main class="grow w-full">
      <!-- Route content rendering -->
      <router-view v-slot="{ Component }">
        <transition name="fade" mode="out-in">
          <component :is="Component" />
        </transition>
      </router-view>
    </main>
    
    <PublicFooter />
  </div>
</template>

<script setup>
import PublicNavbar from './PublicNavbar.vue'
import PublicFooter from './PublicFooter.vue'
</script>

<style scoped>
.fade-enter-active,
.fade-leave-active {
  transition: opacity 0.2s ease;
}

.fade-enter-from,
.fade-leave-to {
  opacity: 0;
}
</style>
