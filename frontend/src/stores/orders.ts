import { defineStore } from 'pinia'
import { ref } from 'vue'
import type { Order } from '../types'
import { ordersService } from '../services/orders'

export const useOrdersStore = defineStore('orders', () => {
  const myOrders = ref<Order[]>([])
  const loading = ref(false)

  async function fetchMyOrders() {
    loading.value = true
    try {
      const res = await ordersService.getMyOrders()
      myOrders.value = res.data
    } finally {
      loading.value = false
    }
  }

  async function checkout(payload: any) {
    loading.value = true
    try {
      return await ordersService.checkout(payload)
    } finally {
      loading.value = false
    }
  }

  return { myOrders, loading, fetchMyOrders, checkout }
})
