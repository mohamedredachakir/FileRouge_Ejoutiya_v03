import { defineStore } from 'pinia'
import { ref, computed, watch } from 'vue'
import type { CartItem } from '../types'
import { useAuthStore } from './auth'

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

  async function addItem(item: Omit<CartItem, 'qty'>) {
    const existing = items.value.find(
      i => i.product_id === item.product_id && i.size === item.size
    )
    if (existing) {
      existing.qty++
    } else {
      items.value.push({ ...item, qty: 1 })
    }

    const auth = useAuthStore()
    if (auth.isAuthenticated && auth.isClient) {
      const { cartService } = await import('../services/cart')
      await cartService.addItem({ 
        product_id: item.product_id, 
        size: item.size, 
        quantity: existing ? existing.qty : 1 
      })
    }
  }

  async function updateQty(productId: number, size: string, delta: number) {
    const idx = items.value.findIndex(i => i.product_id === productId && i.size === size)
    if (idx === -1) return
    items.value[idx].qty += delta
    
    if (items.value[idx].qty <= 0) {
      items.value.splice(idx, 1)
      const auth = useAuthStore()
      if (auth.isAuthenticated && auth.isClient) {
        const { cartService } = await import('../services/cart')
        await cartService.removeItemByProduct(productId, size)
      }
    } else {
      const auth = useAuthStore()
      if (auth.isAuthenticated && auth.isClient) {
        const { cartService } = await import('../services/cart')
        await cartService.addItem({ 
          product_id: productId, 
          size, 
          quantity: items.value[idx].qty 
        })
      }
    }
  }

  async function removeItem(productId: number, size: string) {
    const idx = items.value.findIndex(i => i.product_id === productId && i.size === size)
    if (idx !== -1) {
      items.value.splice(idx, 1)
      const auth = useAuthStore()
      if (auth.isAuthenticated && auth.isClient) {
        const { cartService } = await import('../services/cart')
        await cartService.removeItemByProduct(productId, size)
      }
    }
  }

  async function clearCart() { 
    items.value = [] 
    const auth = useAuthStore()
    if (auth.isAuthenticated && auth.isClient) {
      const { cartService } = await import('../services/cart')
      await cartService.clearCart()
    }
  }

  async function syncWithBackend() {
    const auth = useAuthStore()
    if (!auth.isAuthenticated || !auth.isClient) return

    try {
      const { cartService } = await import('../services/cart')
      
      // If we have local items, push them to backend to "merge"
      // Note: This is a simple merge. Backend addItem now UPSERTS.
      if (items.value.length > 0) {
        for (const item of items.value) {
          try {
            await cartService.addItem({
              product_id: item.product_id,
              size: item.size,
              quantity: item.qty
            })
          } catch (e) {
            console.warn('Failed to push item during sync:', item.product_name)
          }
        }
      }

      const remoteItems = await cartService.getCart()
      if (remoteItems && remoteItems.items) {
        items.value = remoteItems.items.map((i: any) => ({
          product_id: i.product_id,
          product_name: i.product.name,
          product_price: i.product.price,
          product_category: i.product.category,
          main_image_url: i.product.main_image_url,
          store_name: i.product.store?.store_name || '—',
          size: i.size,
          qty: i.quantity
        }))
      }
    } catch (e) {
      console.error('Failed to sync cart:', e)
    }
  }

  // Sync on auth change
  const auth = useAuthStore()
  watch(() => auth.isAuthenticated, (isAuth) => {
    if (isAuth && auth.isClient) syncWithBackend()
    else items.value = [] // Optional: clear on logout or keep local? Standard is clear or move to guest
  })

  function confirmOrder(ref: string) {
    items.value = [] // Manual clear
    orderConfirmed.value = ref
    checkoutVisible.value = false
  }

  return {
    items, drawerOpen, checkoutVisible, orderConfirmed,
    count, total,
    openDrawer, closeDrawer, toggleDrawer,
    addItem, updateQty, removeItem, clearCart, confirmOrder, syncWithBackend
  }
})
