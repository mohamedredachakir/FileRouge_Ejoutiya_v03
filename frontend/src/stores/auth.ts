import { defineStore } from 'pinia'
import { ref, computed } from 'vue'
import type { User } from '../types'
import { authService } from '../services/auth'
import type { LoginPayload, RegisterPayload, RegisterStorePayload } from '../services/auth'

export const useAuthStore = defineStore('auth', () => {
  const token = ref<string | null>(localStorage.getItem('ejoutiya_token'))
  const user = ref<User | null>(null)
  const loading = ref(false)

  const isAuthenticated = computed(() => !!token.value)
  const isClient = computed(() => user.value?.role === 'client')
  const isStoreOwner = computed(() => user.value?.role === 'store_owner')
  const isAdmin = computed(() => user.value?.role === 'admin')

  function setToken(t: string) {
    token.value = t
    localStorage.setItem('ejoutiya_token', t)
  }

  function clearAuth() {
    token.value = null
    user.value = null
    localStorage.removeItem('ejoutiya_token')
  }

  async function login(payload: LoginPayload) {
    loading.value = true
    try {
      const res = await authService.login(payload)
      setToken(res.token)
      user.value = res.user
      return res
    } finally {
      loading.value = false
    }
  }

  async function register(payload: RegisterPayload) {
    loading.value = true
    try {
      const res = await authService.register(payload)
      setToken(res.token)
      user.value = res.user
      return res
    } finally {
      loading.value = false
    }
  }

  async function registerStore(payload: RegisterStorePayload) {
    loading.value = true
    try {
      const res = await authService.registerStore(payload)
      setToken(res.token)
      user.value = res.user
      return res
    } finally {
      loading.value = false
    }
  }

  async function logout() {
    try {
      if (token.value) await authService.logout()
    } catch {}
    clearAuth()
  }

  async function fetchMe() {
    if (!token.value) return
    try {
      user.value = await authService.getMe()
    } catch {
      clearAuth()
    }
  }

  async function updateMe(payload: any) {
    loading.value = true
    try {
      user.value = await authService.updateMe(payload)
    } finally {
      loading.value = false
    }
  }

  return {
    token, user, loading,
    isAuthenticated, isClient, isStoreOwner, isAdmin,
    login, register, registerStore, logout, fetchMe, updateMe, clearAuth,
  }
})
