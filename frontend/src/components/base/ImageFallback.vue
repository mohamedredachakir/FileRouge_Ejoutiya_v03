<script setup lang="ts">
import { computed, ref, watch } from 'vue'

const props = defineProps<{
  src?: string | null
  alt?: string
  fallbackText?: string
  imgClass?: string
}>()

const apiBase = (import.meta.env.VITE_API_URL || '').replace('/api', '')

function resolveUrl(url?: string | null): string | undefined {
  if (!url) return undefined
  if (url.startsWith('http://') || url.startsWith('https://')) return url
  if (url.startsWith('storage/') || url.startsWith('/storage/')) {
    return `${apiBase}/${url.replace(/^\//, '')}`
  }
  return url
}

const imgSrc = computed(() => resolveUrl(props.src))
const error = ref(!imgSrc.value)

watch(
  () => props.src,
  () => {
    error.value = !imgSrc.value
  }
)

function onError() { error.value = true }
</script>

<template>
  <div v-if="error || !imgSrc" class="pp" style="font-size:clamp(10px,2vw,20px)">
    {{ fallbackText || alt || '—' }}
  </div>
  <img
    v-else
    :src="imgSrc"
    :alt="alt"
    :class="imgClass"
    @error="onError"
    style="width:100%;height:100%;object-fit:cover;display:block"
  />
</template>
