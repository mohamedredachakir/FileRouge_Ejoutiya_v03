<script setup lang="ts">
defineProps<{
  label?: string
  modelValue?: string
  options: { value: string; label: string }[]
  disabled?: boolean
}>()
defineEmits(['update:modelValue'])
</script>
<template>
  <div class="form-field">
    <label v-if="label" class="form-label">{{ label }}</label>
    <select
      :value="modelValue"
      :disabled="disabled"
      class="form-select"
      @change="$emit('update:modelValue', ($event.target as HTMLSelectElement).value)"
    >
      <option v-for="opt in options" :key="opt.value" :value="opt.value">{{ opt.label }}</option>
    </select>
  </div>
</template>
