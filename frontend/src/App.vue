<script setup lang="ts">
import { onMounted } from 'vue'
import { useRouter } from 'vue-router'
import AppHeader from './components/layout/AppHeader.vue'
import RouteTransitionShell from './components/layout/RouteTransitionShell.vue'
import CartDrawer from './modules/cart/CartDrawer.vue'
import AuthModal from './modules/auth/AuthModal.vue'
import BaseToast from './components/base/BaseToast.vue'
import { useAuthStore } from './stores/auth'
import { useUiStore } from './stores/ui'

const auth = useAuthStore()
const ui = useUiStore()
const router = useRouter()

onMounted(async () => {
  if (auth.token) {
    await auth.fetchMe()
  }
  window.addEventListener('ejoutiya:unauthorized', () => {
    auth.clearAuth()
    ui.openModal('login')
    ui.showToast('SESSION EXPIRED. PLEASE SIGN IN.', 'warn')
  })
  window.addEventListener('ejoutiya:forbidden', () => {
    ui.showToast('ACCESS DENIED.', 'error')
    router.push('/')
  })
})
</script>

<template>
  <AppHeader />
  <RouteTransitionShell />
  <CartDrawer />
  <AuthModal />
  <BaseToast />
</template>
