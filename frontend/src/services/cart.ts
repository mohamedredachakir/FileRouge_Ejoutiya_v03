import api from './api'
import type { ApiResponse } from '../types'

export interface CartItemPayload { product_id: number; size: string; quantity?: number }
export interface UpdateItemPayload { quantity: number }

export const cartService = {
  async getCart() {
    const { data } = await api.get<ApiResponse<any>>('/cart')
    return data.data
  },
  async addItem(payload: CartItemPayload) {
    const { data } = await api.post<ApiResponse<any>>('/cart/items', payload)
    return data.data
  },
  async updateItem(itemId: number, payload: UpdateItemPayload) {
    const { data } = await api.put<ApiResponse<any>>(`/cart/items/${itemId}`, payload)
    return data.data
  },
  async removeItem(itemId: number) {
    await api.delete(`/cart/items/${itemId}`)
  },
  async removeItemByProduct(productId: number, size: string) {
    await api.delete(`/cart/items/${productId}`, { params: { size } })
  },
  async clearCart() {
    await api.delete('/cart')
  },
}
