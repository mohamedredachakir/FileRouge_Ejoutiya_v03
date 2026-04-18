<script setup lang="ts">
defineProps<{
  show: boolean
  title?: string
  message?: string
  confirmText?: string
  cancelText?: string
  danger?: boolean
}>()
const emit = defineEmits(['confirm', 'cancel'])
</script>
<template>
  <Teleport to="body">
    <Transition name="modal">
      <div v-if="show" class="confirm-overlay" @click.self="emit('cancel')">
        <div class="confirm-box">
          <div class="confirm-title">{{ title || 'CONFIRM' }}</div>
          <div class="confirm-msg">{{ message }}</div>
          <div class="confirm-actions">
            <button :class="danger ? 'btn-danger' : 'btn-cta'" style="flex:1;padding:12px;font-size:10px" @click="emit('confirm')">
              {{ confirmText || 'CONFIRM' }}
            </button>
            <button class="btn-out" style="padding:12px 20px;font-size:10px" @click="emit('cancel')">
              {{ cancelText || 'CANCEL' }}
            </button>
          </div>
        </div>
      </div>
    </Transition>
  </Teleport>
</template>
