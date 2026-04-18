import { defineStore } from 'pinia'
import { ref } from 'vue'
import type { User, Store } from '../types'
import { adminService } from '../services/admin'

export const useAdminStore = defineStore('admin', () => {
  const users = ref<User[]>([])
  const stores = ref<Store[]>([])
  const loading = ref(false)

  async function fetchUsers() {
    loading.value = true
    try {
      const res = await adminService.getUsers()
      users.value = res.data
    } finally {
      loading.value = false
    }
  }

  async function banUser(id: number) {
    const updated = await adminService.banUser(id)
    const idx = users.value.findIndex(u => u.id === id)
    if (idx !== -1) users.value[idx] = updated
  }

  async function unbanUser(id: number) {
    const updated = await adminService.unbanUser(id)
    const idx = users.value.findIndex(u => u.id === id)
    if (idx !== -1) users.value[idx] = updated
  }

  async function fetchStores() {
    loading.value = true
    try {
      const res = await adminService.getAdminStores()
      stores.value = res.data
    } finally {
      loading.value = false
    }
  }

  async function approveStore(id: number) {
    const updated = await adminService.approveStore(id)
    const idx = stores.value.findIndex(s => s.id === id)
    if (idx !== -1) stores.value[idx] = updated
  }

  async function suspendStore(id: number) {
    const updated = await adminService.suspendStore(id)
    const idx = stores.value.findIndex(s => s.id === id)
    if (idx !== -1) stores.value[idx] = updated
  }

  async function rejectStore(id: number) {
    const updated = await adminService.rejectStore(id)
    const idx = stores.value.findIndex(s => s.id === id)
    if (idx !== -1) stores.value[idx] = updated
  }

  return {
    users, stores, loading,
    fetchUsers, banUser, unbanUser,
    fetchStores, approveStore, suspendStore, rejectStore,
  }
})
