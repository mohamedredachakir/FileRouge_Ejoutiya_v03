<script setup lang="ts">
import { ref, onMounted, onUnmounted, watch } from 'vue'
import { useRouter, useRoute } from 'vue-router'
import { useUiStore } from '../../stores/ui'
import { productsService } from '../../services/products'
import { storesService } from '../../services/stores'
import type { Product, Store } from '../../types'

const router = useRouter()
const route = useRoute()
const ui = useUiStore()
const query = ref('')
const productResults = ref<Product[]>([])
const storeResults = ref<Store[]>([])
const loading = ref(false)
const inputRef = ref<HTMLInputElement | null>(null)

let searchTimeout: any = null

watch(query, (newVal) => {
  clearTimeout(searchTimeout)
  if (!newVal.trim()) {
    productResults.value = []
    storeResults.value = []
    loading.value = false
    return
  }
  
  loading.value = true
  searchTimeout = setTimeout(async () => {
    try {
      const [prodRes, storeRes] = await Promise.all([
        productsService.getProducts({ search: newVal.trim(), page: 1 }),
        storesService.getStores({ search: newVal.trim(), page: 1 })
      ])
      productResults.value = prodRes.data.slice(0, 4)
      storeResults.value = storeRes.data.slice(0, 3)
    } finally {
      loading.value = false
    }
  }, 300)
})

function handleSearch(specificTitle?: string) {
  const finalQuery = specificTitle || query.value.trim()
  if (!finalQuery) return
  ui.closeModal()
  
  // If we are on the brands page, search brands. Otherwise products.
  const targetRoute = route.name === 'stores' ? 'stores' : 'products'
  
  router.push({
    name: targetRoute,
    query: { search: finalQuery, page: 1 }
  })
}

function selectResult(type: 'product' | 'store', id: number) {
  ui.closeModal()
  if (type === 'product') {
    router.push({ name: 'product-detail', params: { id } })
  } else {
    router.push({ name: 'store-detail', params: { id } })
  }
}

function close() {
  ui.closeModal()
}

onMounted(() => {
  setTimeout(() => inputRef.value?.focus(), 100)
  
  const escListener = (e: KeyboardEvent) => {
    if (e.key === 'Escape') close()
  }
  window.addEventListener('keydown', escListener)
  onUnmounted(() => window.removeEventListener('keydown', escListener))
})
</script>

<template>
  <Teleport to="body">
    <Transition name="fade">
      <div v-if="ui.modal === 'search'" class="search-modal-overlay" @click.self="close">
        <div class="search-modal-container">
          <div class="sm-top">
            <span class="sm-label">SEARCH MARKETPLACE</span>
            <button class="sm-close" @click="close">CLOSE (ESC)</button>
          </div>
          
          <div class="sm-input-wrap">
            <input 
              ref="inputRef"
              v-model="query" 
              type="text" 
              placeholder="QUICK SEARCH..." 
              class="sm-input"
              @keydown.enter="handleSearch()"
            />
            <button class="sm-btn" @click="handleSearch()">
              FIND →
            </button>
          </div>

          <!-- Live Results Dual Columns -->
          <div v-if="productResults.length || storeResults.length || loading" class="sm-results-wrap">
            <template v-if="loading">
              <div class="sm-results-grid">
                <div class="sm-col">
                  <div class="sm-res-section">BRANDS</div>
                  <div v-for="i in 2" :key="i" class="sm-res-item skeleton" style="height:48px;margin-bottom:8px" />
                </div>
                <div class="sm-col">
                  <div class="sm-res-section">PRODUCTS</div>
                  <div v-for="i in 3" :key="i" class="sm-res-item skeleton" style="height:48px;margin-bottom:8px" />
                </div>
              </div>
            </template>
            <template v-else>
              <div class="sm-results-grid">
                <!-- Brands Column -->
                <div class="sm-col">
                  <div class="sm-res-section">BRANDS ({{ storeResults.length }})</div>
                  <div v-if="!storeResults.length" class="sm-no-res">No brands found</div>
                  <div 
                    v-for="s in storeResults" 
                    :key="'s'+s.id" 
                    class="sm-res-item"
                    @click="selectResult('store', s.id)"
                  >
                    <div class="sm-res-img is-store">
                      <img :src="s.logo_url" :alt="s.store_name" />
                    </div>
                    <div class="sm-res-info">
                      <div class="sm-res-name">{{ s.store_name }}</div>
                      <div class="sm-res-store">OFFICIAL</div>
                    </div>
                  </div>
                </div>

                <!-- Products Column -->
                <div class="sm-col">
                  <div class="sm-res-section">PRODUCTS ({{ productResults.length }})</div>
                  <div v-if="!productResults.length" class="sm-no-res">No products found</div>
                  <div 
                    v-for="p in productResults" 
                    :key="'p'+p.id" 
                    class="sm-res-item"
                    @click="selectResult('product', p.id)"
                  >
                    <div class="sm-res-img">
                      <img :src="p.main_image_url" :alt="p.name" />
                    </div>
                    <div class="sm-res-info">
                      <div class="sm-res-name">{{ p.name }}</div>
                      <div class="sm-res-store">{{ p.store?.store_name }}</div>
                    </div>
                  </div>
                </div>
              </div>
            </template>
          </div>

          <div class="sm-hints">
            <span>TRY:</span>
            <button @click="handleSearch('Nike')">NIKE</button>
            <button @click="handleSearch('Loiter')">LOITER</button>
            <button @click="handleSearch('Vintage')">VINTAGE</button>
            <button @click="handleSearch('Denim')">DENIM</button>
          </div>
        </div>
      </div>
    </Transition>
  </Teleport>
</template>

<style scoped>
.search-modal-overlay {
  position: fixed;
  inset: 0;
  background: rgba(5,5,5,.85);
  backdrop-filter: blur(4px);
  z-index: 1000;
  display: flex;
  align-items: flex-start;
  justify-content: center;
  padding-top: 12svh;
}

.search-modal-container {
  width: 100%;
  max-width: 640px;
  background: var(--color-surface-1);
  border: 1px solid var(--color-border-2);
  padding: 24px;
  animation: slideDown .4s cubic-bezier(0.16, 1, 0.3, 1);
}

.sm-top {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 16px;
}

.sm-label {
  font-family: 'Space Mono', monospace;
  font-size: 9px;
  letter-spacing: .2em;
  color: var(--color-text-dim);
}

.sm-close {
  background: none;
  border: none;
  color: var(--color-text-soft);
  font-family: 'Space Mono', monospace;
  font-size: 9px;
  letter-spacing: .1em;
  cursor: pointer;
}

.sm-close:hover {
  color: var(--color-text-muted);
}

.sm-input-wrap {
  display: flex;
  align-items: center;
  background: var(--color-surface-2);
  border: 1px solid var(--color-border-2);
  padding: 0 12px;
  height: 44px;
  transition: border-color .3s;
}

.sm-input-wrap:focus-within {
  border-color: var(--color-text-dim);
}

.sm-input {
  flex: 1;
  background: transparent;
  border: none;
  color: var(--color-text);
  font-family: 'Space Mono', monospace;
  font-size: 13px;
  letter-spacing: .02em;
  outline: none;
  text-transform: uppercase;
  width: 100%;
}

.sm-input::placeholder {
  color: var(--color-text-soft);
}

.sm-btn {
  background: transparent;
  border: none;
  color: var(--color-text-muted);
  font-family: 'Space Mono', monospace;
  font-size: 11px;
  cursor: pointer;
  padding-left: 12px;
}

.sm-btn:hover {
  color: var(--color-text);
}

.sm-results-wrap {
  margin-top: 20px;
  border: 1px solid var(--color-border-1);
  background: var(--color-surface-1);
}

.sm-results-grid {
  display: grid;
  grid-template-columns: 1fr 1.4fr;
  min-height: 100px;
}

.sm-col {
  border-right: 1px solid var(--color-border-1);
  max-height: 400px;
  overflow-y: auto;
  scrollbar-width: thin;
  scrollbar-color: var(--color-border-3) transparent;
}

.sm-col::-webkit-scrollbar {
  width: 4px;
}

.sm-col::-webkit-scrollbar-track {
  background: transparent;
}

.sm-col::-webkit-scrollbar-thumb {
  background: var(--color-border-3);
  border-radius: 10px;
}

.sm-col::-webkit-scrollbar-thumb:hover {
  background: var(--color-text-soft);
}

.sm-col:last-child {
  border-right: none;
}

.sm-no-res {
  padding: 24px;
  font-family: 'Space Mono', monospace;
  font-size: 8px;
  color: var(--color-text-soft);
  text-align: center;
  text-transform: uppercase;
}

.sm-res-item {
  display: flex;
  align-items: center;
  gap: 12px;
  padding: 8px 12px;
  border-bottom: 1px solid var(--color-border-1);
  cursor: pointer;
  transition: background .15s;
}

.sm-res-item:last-child {
  border-bottom: none;
}

.sm-res-item:hover {
  background: var(--color-surface-2);
}

.sm-res-section {
  padding: 8px 12px;
  background: var(--color-surface-2);
  font-family: 'Space Mono', monospace;
  font-size: 8px;
  letter-spacing: .2em;
  color: var(--color-text-soft);
  border-bottom: 1px solid var(--color-border-1);
}

.sm-res-img {
  width: 40px;
  height: 40px;
  background: var(--color-surface-2);
  flex-shrink: 0;
}

.sm-res-img.is-store {
  border-radius: 50%;
  overflow: hidden;
  border: 1px solid var(--color-border-1);
}

.sm-res-img img {
  width: 100%;
  height: 100%;
  object-fit: cover;
}

.sm-res-info {
  flex: 1;
  min-width: 0;
}

.sm-res-name {
  font-family: 'Space Mono', monospace;
  font-size: 10px;
  letter-spacing: .02em;
  text-transform: uppercase;
  color: var(--color-text);
  white-space: nowrap;
  overflow: hidden;
  text-overflow: ellipsis;
}

.sm-res-store {
  font-family: 'Space Mono', monospace;
  font-size: 9px;
  color: var(--color-text-soft);
  text-transform: uppercase;
}

.sm-res-arrow {
  color: var(--color-text-soft);
  font-size: 12px;
}

.sm-hints {
  margin-top: 16px;
  display: flex;
  gap: 12px;
  font-family: 'Space Mono', monospace;
  font-size: 9px;
  letter-spacing: .05em;
  color: var(--color-text-soft);
  align-items: center;
  flex-wrap: wrap;
}

.sm-hints button {
  background: none;
  border: none;
  color: var(--color-text-dim);
  text-decoration: underline;
  cursor: pointer;
  text-transform: uppercase;
}

.sm-hints button:hover {
  color: var(--color-text-muted);
}

@keyframes slideDown {
  from { opacity: 0; transform: translateY(-20px); }
  to { opacity: 1; transform: translateY(0); }
}

.fade-enter-active, .fade-leave-active { transition: opacity .25s ease; }
.fade-enter-from, .fade-leave-to { opacity: 0; }
</style>
