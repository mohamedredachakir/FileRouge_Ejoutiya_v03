<script setup lang="ts">
defineProps<{ width?: string; height?: string; class?: string }>()
</script>
<template>
  <div class="skeleton" :style="{ width: width || '100%', height: height || '16px' }" />
</template>
