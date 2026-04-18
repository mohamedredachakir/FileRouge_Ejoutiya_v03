import api from './api'
import type { User, Store, ApiResponse, PaginatedResponse } from '../types'

export const adminService = {
  async getUsers() {
    const { data } = await api.get<PaginatedResponse<User>>('/admin/users')
    return data
  },
  async banUser(id: number) {
    const { data } = await api.patch<ApiResponse<User>>(`/admin/users/${id}/ban`)
    return data.data
  },
  async unbanUser(id: number) {
    const { data } = await api.patch<ApiResponse<User>>(`/admin/users/${id}/unban`)
    return data.data
  },
  async getAdminStores() {
    const { data } = await api.get<PaginatedResponse<Store>>('/admin/stores')
    return data
  },
  async approveStore(id: number) {
    const { data } = await api.patch<ApiResponse<Store>>(`/admin/stores/${id}/approve`)
    return data.data
  },
  async suspendStore(id: number) {
    const { data } = await api.patch<ApiResponse<Store>>(`/admin/stores/${id}/suspend`)
    return data.data
  },
  async rejectStore(id: number) {
    const { data } = await api.patch<ApiResponse<Store>>(`/admin/stores/${id}/reject`)
    return data.data
  },
}
