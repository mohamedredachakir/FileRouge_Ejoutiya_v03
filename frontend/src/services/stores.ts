import api from './api'
import type { Store, PaginatedResponse, ApiResponse } from '../types'

export const storesService = {
  async getStores(params: { search?: string; page?: number } = {}) {
    const { data } = await api.get<PaginatedResponse<Store>>('/stores', { params })
    return data
  },
  async getStore(id: number) {
    const { data } = await api.get<ApiResponse<Store>>(`/stores/${id}`)
    return data.data
  },
}
