<script setup lang="ts">
defineProps<{
  variant?: 'cta' | 'outline' | 'ghost' | 'danger'
  size?: 'sm' | 'md' | 'lg'
  disabled?: boolean
  loading?: boolean
  type?: string
}>()
const sizeMap = { sm: 'font-size:9px;padding:8px 16px', md: 'font-size:10px;padding:12px 24px', lg: 'font-size:11px;padding:14px 32px' }
</script>
<template>
  <button
    :type="(type as any) || 'button'"
    :disabled="disabled || loading"
    :class="['btn-' + (variant || 'cta')]"
    :style="sizeMap[size || 'md']"
  >
    <span v-if="loading">...</span>
    <slot v-else />
  </button>
</template>
