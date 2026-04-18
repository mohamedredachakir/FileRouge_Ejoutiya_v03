<script setup lang="ts">
defineProps<{
  label?: string
  modelValue?: string
  placeholder?: string
  rows?: number
  disabled?: boolean
}>()
defineEmits(['update:modelValue'])
</script>
<template>
  <div class="form-field">
    <label v-if="label" class="form-label">{{ label }}</label>
    <textarea
      :value="modelValue"
      :placeholder="placeholder"
      :rows="rows || 4"
      :disabled="disabled"
      class="form-input"
      style="resize:vertical"
      @input="$emit('update:modelValue', ($event.target as HTMLTextAreaElement).value)"
    />
  </div>
</template>
