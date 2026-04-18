import api from './api'
import type { Store, Product, Order, ApiResponse, PaginatedResponse } from '../types'

export interface StoreUpdatePayload { name?: string; bio?: string; logo_url?: string; hero_image_url?: string }
export interface ProductCreatePayload {
  name: string; description?: string; price: number; stock: number
  category: string; status: string; sizes: string[]; image_urls?: string[]
}
export type ProductUpdatePayload = Partial<ProductCreatePayload>

export const storeDashboardService = {
  async getMyStore() {
    const { data } = await api.get<ApiResponse<Store>>('/store/me')
    return data.data
  },
  async updateMyStore(payload: StoreUpdatePayload) {
    const { data } = await api.put<ApiResponse<Store>>('/store/me', payload)
    return data.data
  },
  async getMyProducts() {
    const { data } = await api.get<PaginatedResponse<Product>>('/store/products')
    return data
  },
  async createProduct(payload: ProductCreatePayload) {
    const { data } = await api.post<ApiResponse<Product>>('/store/products', payload)
    return data.data
  },
  async updateProduct(id: number, payload: ProductUpdatePayload) {
    const { data } = await api.put<ApiResponse<Product>>(`/store/products/${id}`, payload)
    return data.data
  },
  async deleteProduct(id: number) {
    await api.delete(`/store/products/${id}`)
  },
  async getStoreOrders(status?: string) {
    const params = status && status !== 'ALL' ? { status } : {}
    const { data } = await api.get<PaginatedResponse<Order>>('/store/orders', { params })
    return data
  },
  async updateOrderStatus(orderId: number, status: string) {
    const { data } = await api.patch<ApiResponse<Order>>(`/store/orders/${orderId}/status`, { status })
    return data.data
  },
}
