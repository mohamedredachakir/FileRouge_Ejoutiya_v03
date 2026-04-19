import { defineStore } from 'pinia'
import { ref } from 'vue'
import type { Store } from '../types'
import { storesService } from '../services/stores'

export const useStoresCatalogStore = defineStore('storesCatalog', () => {
  const stores = ref<Store[]>([])
  const selectedStore = ref<Store | null>(null)
  const loading = ref(false)
  const loadingStore = ref(false)
  const total = ref(0)
  const currentPage = ref(1)
  const lastPage = ref(1)

  async function fetchStores(params: { search?: string; page?: number } = {}) {
    loading.value = true
    try {
      const res = await storesService.getStores(params)
      stores.value = res.data
      total.value = res.meta.total
      currentPage.value = res.meta.current_page
      lastPage.value = res.meta.last_page
    } finally {
      loading.value = false
    }
  }

  async function fetchStore(id: number) {
    loadingStore.value = true
    try {
      selectedStore.value = await storesService.getStore(id)
    } finally {
      loadingStore.value = false
    }
  }

  return {
    stores, selectedStore, loading, loadingStore,
    total, currentPage, lastPage,
    fetchStores, fetchStore,
  }
})
