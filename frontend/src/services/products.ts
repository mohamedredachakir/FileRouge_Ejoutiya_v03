import api from './api'
import type { Product, PaginatedResponse, ApiResponse } from '../types'

export interface ProductFilters {
  category?: string
  sort?: string
  store_id?: number
  page?: number
  search?: string
}

export const productsService = {
  async getProducts(filters: ProductFilters = {}) {
    const { data } = await api.get<PaginatedResponse<Product>>('/products', { params: filters })
    return data
  },
  async getProduct(id: number) {
    const { data } = await api.get<ApiResponse<Product>>(`/products/${id}`)
    return data.data
  },
}
