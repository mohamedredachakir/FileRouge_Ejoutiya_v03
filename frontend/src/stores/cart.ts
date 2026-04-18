import { defineStore } from 'pinia'
import { ref, computed, watch } from 'vue'
import type { CartItem } from '../types'

const STORAGE_KEY = 'ejoutiya_cart'

function loadFromStorage(): CartItem[] {
  try {
    const raw = localStorage.getItem(STORAGE_KEY)
    return raw ? JSON.parse(raw) : []
  } catch { return [] }
}

export const useCartStore = defineStore('cart', () => {
  const items = ref<CartItem[]>(loadFromStorage())
  const drawerOpen = ref(false)
  const checkoutVisible = ref(false)
  const orderConfirmed = ref<string | null>(null)

  const count = computed(() => items.value.reduce((s, i) => s + i.qty, 0))
  const total = computed(() => items.value.reduce((s, i) => s + i.product_price * i.qty, 0))

  watch(items, (v) => {
    localStorage.setItem(STORAGE_KEY, JSON.stringify(v))
  }, { deep: true })

  function openDrawer() { drawerOpen.value = true }
  function closeDrawer() {
    drawerOpen.value = false
    checkoutVisible.value = false
    orderConfirmed.value = null
  }
  function toggleDrawer() {
    if (drawerOpen.value) closeDrawer()
    else openDrawer()
  }

  function addItem(item: Omit<CartItem, 'qty'>) {
    const existing = items.value.find(
      i => i.product_id === item.product_id && i.size === item.size
    )
    if (existing) {
      existing.qty++
    } else {
      items.value.push({ ...item, qty: 1 })
    }
  }

  function updateQty(productId: number, size: string, delta: number) {
    const idx = items.value.findIndex(i => i.product_id === productId && i.size === size)
    if (idx === -1) return
    items.value[idx].qty += delta
    if (items.value[idx].qty <= 0) items.value.splice(idx, 1)
  }

  function removeItem(productId: number, size: string) {
    const idx = items.value.findIndex(i => i.product_id === productId && i.size === size)
    if (idx !== -1) items.value.splice(idx, 1)
  }

  function clearCart() { items.value = [] }

  function confirmOrder(ref: string) {
    clearCart()
    orderConfirmed.value = ref
    checkoutVisible.value = false
  }

  return {
    items, drawerOpen, checkoutVisible, orderConfirmed,
    count, total,
    openDrawer, closeDrawer, toggleDrawer,
    addItem, updateQty, removeItem, clearCart, confirmOrder,
  }
})
