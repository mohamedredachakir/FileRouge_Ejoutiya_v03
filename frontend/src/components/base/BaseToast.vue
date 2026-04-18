<script setup lang="ts">
import { useUiStore } from '../../stores/ui'
const ui = useUiStore()
</script>
<template>
  <Teleport to="body">
    <div class="toast-container">
      <TransitionGroup name="toast-list">
        <div
          v-for="t in ui.toasts"
          :key="t.id"
          class="toast-item"
          :class="t.type === 'error' ? 'error' : t.type === 'warn' ? 'warn' : ''"
          @click="ui.dismissToast(t.id)"
        >
          {{ t.message }}
        </div>
      </TransitionGroup>
    </div>
  </Teleport>
</template>
<style scoped>
.toast-list-enter-active, .toast-list-leave-active { transition: all .3s ease; }
.toast-list-enter-from { opacity: 0; transform: translateY(20px); }
.toast-list-leave-to { opacity: 0; transform: translateY(20px); }
</style>
