<script setup lang="ts">
defineProps<{ show: boolean; width?: string }>()
const emit = defineEmits(['close'])
</script>
<template>
  <Teleport to="body">
    <div class="cart-overlay" :class="{ on: show }" @click="emit('close')" />
    <div class="cart-drawer" :class="{ on: show }" :style="{ width: width || '390px' }">
      <slot />
    </div>
  </Teleport>
</template>
