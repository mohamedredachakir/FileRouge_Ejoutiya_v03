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

async function load() {
  const params: any = {}
  if (activeFilter.value !== 'ALL') params.category = activeFilter.value
  if (activeSort.value !== 'default') params.sort = activeSort.value
  await catalog.fetchProducts(params)
}

onMounted(load)
watch([activeFilter, activeSort], load)

function setFilter(key: string) {
  activeFilter.value = key
  router.replace({ query: key !== 'ALL' ? { category: key } : {} })
}

function quickAdd(p: Product, size: string, e: Event) {
  e.stopPropagation()
  cart.addItem({
    product_id: p.id,
    product_name: p.name,
    product_price: p.price,
    product_category: p.category,
    main_image_url: p.main_image_url,
    store_name: p.store?.name || '—',
    size,
  })
  ui.showToast('ADDED TO CART.')
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

    <!-- Filter bar -->
    <div class="filter-bar">
      <button
        v-for="f in filters"
        :key="f.key"
        class="fb"
        :class="{ on: activeFilter === f.key }"
        @click="setFilter(f.key)"
      >{{ f.label }}</button>
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
                @click.stop="quickAdd(p, sz, $event)"
              >{{ sz }}</button>
            </div>
          </div>
          <div class="pc-info">
            <div>
              <div class="pc-name">{{ p.name }}</div>
              <div class="pc-sub">{{ p.store?.name || '—' }}</div>
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

    <AppFooter />
  </div>
</template>
