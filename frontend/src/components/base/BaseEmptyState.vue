<script setup lang="ts">
defineProps<{ icon?: string; title?: string; message?: string }>()
</script>
<template>
  <div class="empty-state">
    <div class="empty-icon">{{ icon || '∅' }}</div>
    <div class="empty-title">{{ title || 'NOTHING HERE' }}</div>
    <div class="empty-msg">{{ message || '' }}</div>
  </div>
</template>
