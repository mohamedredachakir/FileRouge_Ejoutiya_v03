<script setup lang="ts">
import { ref, onMounted, watch } from 'vue'
import { useRouter, useRoute } from 'vue-router'
import { useCatalogStore } from '../../stores/catalog'
import { useCartStore } from '../../stores/cart'
import { useUiStore } from '../../stores/ui'
import ImageFallback from '../../components/base/ImageFallback.vue'
import AppFooter from '../../components/layout/AppFooter.vue'
import { CATEGORY_LABELS } from '../../types'
import type { Product } from '../../types'

const router = useRouter()
const route = useRoute()
const catalog = useCatalogStore()
const cart = useCartStore()
const ui = useUiStore()

const activeFilter = ref((route.query.category as string) || 'ALL')
const activeSort = ref('default')
const currentPage = ref(parseInt(route.query.page as string) || 1)
const searchQuery = ref((route.query.search as string) || '')

watch(() => route.query.search, (newVal) => {
  searchQuery.value = (newVal as string) || ''
  currentPage.value = 1
  load()
})

const filters = [
  { key: 'ALL', label: 'ALL' },
  { key: 't_shirt', label: 'T-SHIRTS' },
  { key: 'hoodie', label: 'HOODIES' },
  { key: 'pants', label: 'PANTS' },
  { key: 'sneakers', label: 'SNEAKERS' },
  { key: 'accessories', label: 'ACCESSORIES' },
]

const sortOptions = [
  { value: 'default', label: 'DEFAULT' },
  { value: 'price-asc', label: 'PRICE: LOW → HIGH' },
  { value: 'price-desc', label: 'PRICE: HIGH → LOW' },
  { value: 'name', label: 'A → Z' },
]

let searchTimeout: any = null
async function load() {
  const params: any = {}
  if (activeFilter.value !== 'ALL') params.category = activeFilter.value
  if (activeSort.value !== 'default') params.sort = activeSort.value
  if (searchQuery.value) params.search = searchQuery.value
  params.page = currentPage.value
  await catalog.fetchProducts(params)
}

function handleSearch() {
  clearTimeout(searchTimeout)
  searchTimeout = setTimeout(() => {
    currentPage.value = 1
    router.replace({
      query: {
        ...route.query,
        search: searchQuery.value || undefined,
        page: 1
      }
    })
    load()
  }, 500)
}

onMounted(load)
watch([activeFilter, activeSort, currentPage], load)

function setFilter(key: string) {
  activeFilter.value = key
  currentPage.value = 1
  router.replace({ 
    query: { 
      ...(key !== 'ALL' ? { category: key } : {}),
      search: searchQuery.value || undefined,
      page: 1 
    } 
  })
}

function clearSearch() {
  searchQuery.value = ''
  currentPage.value = 1
  router.replace({
    query: {
      ...route.query,
      search: undefined,
      page: 1
    }
  })
  load()
}

function goToPage(page: number) {
  currentPage.value = page
  router.push({
    query: {
      ...route.query,
      page
    }
  })
  window.scrollTo({ top: 0, behavior: 'smooth' })
}

async function quickAdd(p: Product, size: string, e: Event) {
  e.stopPropagation()
  ui.showToast('ADDED TO CART.')
  await cart.addItem({
    product_id: p.id,
    product_name: p.name,
    product_price: p.price,
    product_category: p.category,
    main_image_url: p.main_image_url,
    store_name: p.store?.store_name || '—',
    size,
  })
}

function getBadge(p: Product) {
  if (p.is_new) return 'new'
  if (p.stock === 0 || p.status === 'out_of_stock') return 'oos'
  if (p.original_price) return 'sale'
  return ''
}
</script>

<template>
  <div>
    <!-- Header -->
    <div class="shops-top">
      <div>
        <div style="font-family:'Space Mono',monospace;font-size:9px;letter-spacing:.2em;text-transform:uppercase;color:var(--color-text-soft);margin-bottom:8px">EJOUTIYA — MARKETPLACE</div>
        <div class="shop-h">ALL<br>PRODUCTS</div>
      </div>
      <div style="text-align:right">
        <div style="font-family:'Space Mono',monospace;font-size:28px;letter-spacing:.04em;color:var(--color-text-dim)">
          {{ catalog.loading ? '—' : catalog.total }} <span style="font-size:14px">ITEMS</span>
        </div>
      </div>
    </div>

    <!-- Search & Filter bar -->
    <div class="filter-bar">
      <div v-if="searchQuery" class="search-indicator">
        <span class="si-label">RESULTS FOR:</span>
        <span class="si-val">{{ searchQuery }}</span>
        <button class="si-clear" @click="clearSearch">×</button>
      </div>
      
      <div class="fb-group">
        <button
          v-for="f in filters"
          :key="f.key"
          class="fb"
          :class="{ on: activeFilter === f.key }"
          @click="setFilter(f.key)"
        >{{ f.label }}</button>
      </div>

      <div style="margin-left:auto">
        <select
          v-model="activeSort"
          class="form-select"
          style="background:var(--color-surface-1);border:1px solid var(--color-border-2);color:var(--color-text);font-family:'Space Mono',monospace;font-size:9px;letter-spacing:.08em;text-transform:uppercase;padding:7px 12px;outline:none;cursor:pointer"
        >
          <option v-for="opt in sortOptions" :key="opt.value" :value="opt.value">{{ opt.label }}</option>
        </select>
      </div>
    </div>

    <!-- Grid -->
    <div class="pg-grid-auto" style="min-height:400px">
      <template v-if="catalog.loading">
        <div v-for="i in 8" :key="i" class="pc">
          <div class="skeleton" style="aspect-ratio:3/4" />
          <div style="padding:12px">
            <div class="skeleton" style="height:12px;margin-bottom:8px" />
            <div class="skeleton" style="height:10px;width:60%" />
          </div>
        </div>
      </template>
      <template v-else-if="catalog.products.length">
        <div
          v-for="p in catalog.products"
          :key="p.id"
          class="pc"
          @click="router.push({ name: 'product-detail', params: { id: p.id } })"
        >
          <div class="pc-img">
            <div v-if="getBadge(p)" class="pc-badge" :class="'badge-' + getBadge(p)">
              {{ getBadge(p) === 'new' ? 'NEW' : getBadge(p) === 'oos' ? 'OOS' : 'SALE' }}
            </div>
            <div class="pc-img-inner">
              <ImageFallback :src="p.main_image_url" :fallback-text="CATEGORY_LABELS[p.category] || p.category" :alt="p.name" />
            </div>
            <div class="pc-overlay">
              <button
                v-for="sz in (p.sizes || ['S','M','L','XL'])"
                :key="sz"
                class="sz-btn"
                :class="{ oos: p.stock === 0 }"
                :disabled="p.stock === 0"
                @click.stop="quickAdd(p, sz, $event)"
              >{{ sz }}</button>
            </div>
          </div>
          <div class="pc-info">
            <div>
              <div class="pc-name">{{ p.name }}</div>
              <div class="pc-sub">{{ p.store?.store_name || '—' }}</div>
            </div>
            <div class="pc-price">
              <span v-if="p.original_price" class="pc-price-old">{{ p.original_price }} MAD</span>
              {{ p.price }} MAD
            </div>
          </div>
        </div>
      </template>
      <div v-else style="grid-column:1/-1">
        <div class="empty-state">
          <div class="empty-icon">∅</div>
          <div class="empty-title">NO PRODUCTS FOUND</div>
          <div class="empty-msg">Try a different category or check back later.</div>
        </div>
      </div>
    </div>

    <!-- Pagination -->
    <div v-if="catalog.lastPage > 1" class="pagination-wrap">
      <div class="pagination">
        <button 
          class="pg-btn" 
          :disabled="catalog.currentPage === 1" 
          @click="goToPage(catalog.currentPage - 1)"
        >
          <span class="pg-arrow">←</span> PREV
        </button>
        
        <div class="pg-pages">
          <button 
            v-for="p in catalog.lastPage" 
            :key="p" 
            class="pg-num" 
            :class="{ active: p === catalog.currentPage }"
            @click="goToPage(p)"
          >{{ p }}</button>
        </div>

        <button 
          class="pg-btn" 
          :disabled="catalog.currentPage === catalog.lastPage" 
          @click="goToPage(catalog.currentPage + 1)"
        >
          NEXT <span class="pg-arrow">→</span>
        </button>
      </div>
      <div class="pg-info">PAGE {{ catalog.currentPage }} OF {{ catalog.lastPage }}</div>
    </div>

    <AppFooter />
  </div>
</template>
