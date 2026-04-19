import api from './api'
import type { User, ApiResponse } from '../types'

export interface LoginPayload { email: string; password: string }
export interface RegisterPayload { 
  name: string; 
  email: string; 
  password: string; 
  password_confirmation: string;
  phone: string;
  city: string;
  zip_code: string;
  address: string;
}
export interface RegisterStorePayload {
  name: string; bio: string; email: string; password: string
  password_confirmation: string; 
  phone: string;
  city: string;
  zip_code: string;
  address: string;
  logo_url?: string; hero_image_url?: string
}
export interface UpdateMePayload { name?: string; email?: string; phone?: string; city?: string; zip_code?: string; address?: string }

export const authService = {
  async login(payload: LoginPayload) {
    const { data } = await api.post<{ token: string; user: User }>('/auth/login', payload)
    return data
  },
  async register(payload: RegisterPayload) {
    const { data } = await api.post<{ token: string; user: User }>('/auth/register', payload)
    return data
  },
  async registerStore(payload: RegisterStorePayload | FormData) {
    const isFormData = payload instanceof FormData
    const { data } = await api.post<{ token: string; user: User }>('/auth/register-store', payload, {
      headers: isFormData ? { 'Content-Type': 'multipart/form-data' } : {},
    })
    return data
  },
  async logout() {
    await api.post('/auth/logout')
  },
  async getMe() {
    const { data } = await api.get<ApiResponse<User>>('/me')
    return data.data
  },
  async updateMe(payload: UpdateMePayload) {
    const { data } = await api.put<ApiResponse<User>>('/me', payload)
    return data.data
  },
}
