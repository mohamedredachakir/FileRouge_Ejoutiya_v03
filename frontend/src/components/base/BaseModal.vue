<script setup lang="ts">
defineProps<{ show: boolean }>()
const emit = defineEmits(['close'])
function onOverlayClick(e: MouseEvent) {
  if ((e.target as HTMLElement).classList.contains('auth-overlay')) emit('close')
}
</script>
<template>
  <Teleport to="body">
    <Transition name="modal">
      <div v-if="show" class="auth-overlay" @click="onOverlayClick">
        <div class="auth-box">
          <slot />
        </div>
      </div>
    </Transition>
  </Teleport>
</template>
