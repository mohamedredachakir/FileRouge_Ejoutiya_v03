import api from './api'
import type { Store, PaginatedResponse, ApiResponse } from '../types'

export const storesService = {
  async getStores() {
    const { data } = await api.get<PaginatedResponse<Store>>('/stores')
    return data
  },
  async getStore(id: number) {
    const { data } = await api.get<ApiResponse<Store>>(`/stores/${id}`)
    return data.data
  },
}
