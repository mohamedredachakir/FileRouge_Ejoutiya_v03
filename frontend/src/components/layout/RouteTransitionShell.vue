<script setup lang="ts">
import { useRoute } from 'vue-router'
import { computed } from 'vue'
const route = useRoute()
const transition = computed(() => route.meta.transition || 'page-fade')
</script>
<template>
  <RouterView v-slot="{ Component }">
    <Transition :name="transition" mode="out-in">
      <component :is="Component" :key="route.fullPath" />
    </Transition>
  </RouterView>
</template>
