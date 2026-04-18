import { defineStore } from 'pinia'
import { ref } from 'vue'

export type ToastType = 'default' | 'error' | 'warn'
export interface Toast { id: number; message: string; type: ToastType }
export type ModalType = 'login' | 'register' | 'register-store' | null

let toastId = 0

export const useUiStore = defineStore('ui', () => {
  const toasts = ref<Toast[]>([])
  const loadingOverlay = ref(false)
  const modal = ref<ModalType>(null)
  const registerStoreStep = ref(1)

  function showToast(message: string, type: ToastType = 'default', duration = 3000) {
    const id = ++toastId
    toasts.value.push({ id, message, type })
    setTimeout(() => {
      const idx = toasts.value.findIndex(t => t.id === id)
      if (idx !== -1) toasts.value.splice(idx, 1)
    }, duration)
  }

  function dismissToast(id: number) {
    const idx = toasts.value.findIndex(t => t.id === id)
    if (idx !== -1) toasts.value.splice(idx, 1)
  }

  function openModal(type: ModalType) { modal.value = type; registerStoreStep.value = 1 }
  function closeModal() { modal.value = null }

  function showLoading() { loadingOverlay.value = true }
  function hideLoading() { loadingOverlay.value = false }

  return {
    toasts, loadingOverlay, modal, registerStoreStep,
    showToast, dismissToast, openModal, closeModal, showLoading, hideLoading,
  }
})
