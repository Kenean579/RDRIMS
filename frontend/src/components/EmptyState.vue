<template>
  <div class="empty-state">
    <div class="empty-icon-shell">
      <span class="empty-icon text-3xl">{{ icon }}</span>
    </div>
    <h3 class="empty-title">{{ title }}</h3>
    <p class="empty-description">{{ description }}</p>
    <div v-if="actionLabel" class="empty-action">
      <button @click="$emit('action')" class="btn btn-primary">
        <svg v-if="actionIcon === 'add'" class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 4v16m8-8H4" /></svg>
        {{ actionLabel }}
      </button>
    </div>
  </div>
</template>

<script setup>
defineProps({
  icon: { type: String, default: '📂' },
  title: { type: String, default: 'No data found' },
  description: { type: String, default: 'There are no items to display yet.' },
  actionLabel: { type: String, default: '' },
  actionIcon: { type: String, default: '' }
})
defineEmits(['action'])
</script>

<style scoped>
.empty-state {
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
  padding: 64px 24px;
  text-align: center;
}

.empty-icon-shell {
  width: 80px;
  height: 80px;
  background: var(--surface-2);
  border-radius: 24px;
  display: flex;
  align-items: center;
  justify-content: center;
  margin-bottom: 20px;
  border: 1px solid var(--border-2);
}

.empty-title {
  font-size: 16px;
  font-weight: 700;
  color: var(--text-primary);
  margin-bottom: 8px;
}

.empty-description {
  font-size: 14px;
  color: var(--text-secondary);
  max-width: 320px;
  line-height: 1.5;
  margin-bottom: 24px;
}

.empty-action {
  display: flex;
}
</style>
