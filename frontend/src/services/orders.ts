import api from './api'
import type { Order, ApiResponse, PaginatedResponse } from '../types'

export interface CheckoutItemPayload {
  product_id: number
  quantity: number
  size: string
}

export interface CheckoutPayload {
  phone: string
  city: string
  zip_code: string
  address: string
  items: CheckoutItemPayload[]
}

export const ordersService = {
  async checkout(payload: CheckoutPayload) {
    const { data } = await api.post<ApiResponse<Order>>('/orders/checkout', payload)
    return data.data
  },
  async getMyOrders() {
    const { data } = await api.get<PaginatedResponse<Order>>('/orders/me')
    return data
  },
  async getMyOrder(id: number) {
    const { data } = await api.get<ApiResponse<Order>>(`/orders/me/${id}`)
    return data.data
  },
}
