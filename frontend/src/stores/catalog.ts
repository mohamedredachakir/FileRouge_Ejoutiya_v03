import { defineStore } from 'pinia'
import { ref } from 'vue'
import type { Product } from '../types'
import { productsService } from '../services/products'

export const useCatalogStore = defineStore('catalog', () => {
  const products = ref<Product[]>([])
  const selectedProduct = ref<Product | null>(null)
  const loading = ref(false)
  const loadingProduct = ref(false)
  const category = ref('')
  const sort = ref('default')
  const total = ref(0)
  const currentPage = ref(1)
  const lastPage = ref(1)

  async function fetchProducts(params: { category?: string; sort?: string; store_id?: number; page?: number; search?: string } = {}) {
    loading.value = true
    try {
      const res = await productsService.getProducts(params)
      products.value = res.data
      total.value = res.meta.total
      currentPage.value = res.meta.current_page
      lastPage.value = res.meta.last_page
    } finally {
      loading.value = false
    }
  }

  async function fetchProduct(id: number) {
    loadingProduct.value = true
    try {
      selectedProduct.value = await productsService.getProduct(id)
    } finally {
      loadingProduct.value = false
    }
  }

  return {
    products, selectedProduct, loading, loadingProduct,
    category, sort, total, currentPage, lastPage,
    fetchProducts, fetchProduct,
  }
})
