<script setup lang="ts">
defineProps<{
  label?: string
  modelValue?: string | number
  type?: string
  placeholder?: string
  disabled?: boolean
  error?: string
}>()
defineEmits(['update:modelValue'])
</script>
<template>
  <div class="form-field">
    <label v-if="label" class="form-label">{{ label }}</label>
    <input
      :type="type || 'text'"
      :value="modelValue"
      :placeholder="placeholder"
      :disabled="disabled"
      class="form-input"
      @input="$emit('update:modelValue', ($event.target as HTMLInputElement).value)"
    />
    <div v-if="error" style="font-size:10px;color:var(--color-accent);margin-top:4px;font-family:'Space Mono',monospace;letter-spacing:.06em;text-transform:uppercase">{{ error }}</div>
  </div>
</template>
