<script setup lang="ts">
defineProps<{ variant?: string; text?: string }>()

const variantMap: Record<string, string> = {
  active: 'badge-active',
  hidden: 'badge-hidden',
  out_of_stock: 'badge-oos',
  oos: 'badge-oos',
  pending: 'badge-pending',
  pending_approval: 'badge-pending',
  confirmed: 'badge-confirmed',
  rejected: 'badge-rejected',
  delivery: 'badge-delivery',
  suspended: 'badge-rejected',
  new: 'badge-new',
  sale: 'badge-sale',
}
</script>
<template>
  <span class="badge" :class="variantMap[variant || ''] || 'badge-hidden'">
    <slot>{{ text || variant }}</slot>
  </span>
</template>
