<script setup lang="ts">
defineProps<{ tabs: { key: string; label: string }[]; modelValue: string }>()
defineEmits(['update:modelValue'])
</script>
<template>
  <div style="display:flex;gap:6px;flex-wrap:wrap;margin-bottom:24px">
    <button
      v-for="tab in tabs"
      :key="tab.key"
      class="fb"
      :class="{ on: modelValue === tab.key }"
      @click="$emit('update:modelValue', tab.key)"
    >
      {{ tab.label }}
    </button>
  </div>
</template>
