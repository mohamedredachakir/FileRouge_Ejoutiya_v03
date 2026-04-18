import { defineStore } from 'pinia'
import { ref } from 'vue'
import type { Store } from '../types'
import { storesService } from '../services/stores'

export const useStoresCatalogStore = defineStore('storesCatalog', () => {
  const stores = ref<Store[]>([])
  const selectedStore = ref<Store | null>(null)
  const loading = ref(false)
  const loadingStore = ref(false)

  async function fetchStores() {
    loading.value = true
    try {
      const res = await storesService.getStores()
      stores.value = res.data
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
    fetchStores, fetchStore,
  }
})
