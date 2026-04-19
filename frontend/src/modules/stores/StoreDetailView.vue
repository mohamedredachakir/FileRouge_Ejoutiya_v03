<script setup lang="ts">
import { onMounted, computed } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import { useStoresCatalogStore } from '../../stores/storesCatalog'
import { useCatalogStore } from '../../stores/catalog'
import { useCartStore } from '../../stores/cart'
import { useUiStore } from '../../stores/ui'
import ImageFallback from '../../components/base/ImageFallback.vue'
import BaseBadge from '../../components/base/BaseBadge.vue'
import AppFooter from '../../components/layout/AppFooter.vue'
import { CATEGORY_LABELS } from '../../types'
import type { Product } from '../../types'

const route = useRoute()
const router = useRouter()
const storesCatalog = useStoresCatalogStore()
const catalog = useCatalogStore()
const cart = useCartStore()
const ui = useUiStore()

const storeId = computed(() => Number(route.params.id))

onMounted(async () => {
  await Promise.all([
    storesCatalog.fetchStore(storeId.value),
    catalog.fetchProducts({ store_id: storeId.value }),
  ])
})

const store = computed(() => storesCatalog.selectedStore)

function getBadge(p: Product) {
  if (p.is_new) return 'new'
  if (p.stock === 0 || p.status === 'out_of_stock') return 'oos'
  if (p.original_price) return 'sale'
  return ''
}

async function quickAdd(p: Product, sz: string, e: Event) {
  e.stopPropagation()
  ui.showToast('ADDED TO CART.')
  await cart.addItem({
    product_id: p.id,
    product_name: p.name,
    product_price: p.price,
    product_category: p.category,
    main_image_url: p.main_image_url,
    store_name: store.value?.store_name || '—',
    size: sz,
  })
}
</script>

<template>
  <div>
    <div v-if="storesCatalog.loadingStore" style="padding:80px;text-align:center">
      <div class="skeleton" style="height:50vh;margin-bottom:1px" />
    </div>
    <template v-else-if="store">
      <!-- Hero -->
      <div class="store-hero texture">
        <div class="store-hero-bg">
          <ImageFallback :src="store.hero_image_url" :fallback-text="store.store_name" :alt="store.store_name" />
        </div>
        <div class="store-hero-ghost">{{ store.store_name }}</div>
        <div class="store-hero-grad" />
        <div class="store-hero-body">
          <button
            style="position:absolute;top:20px;left:40px;z-index:5;color:var(--color-text-dim);font-family:'Space Mono',monospace;font-size:9px;letter-spacing:.12em;text-transform:uppercase;background:none;border:none;cursor:pointer;transition:color .15s"
            @click="router.push('/stores')"
            @mouseenter="($event.target as HTMLElement).style.color='var(--color-text)'"
            @mouseleave="($event.target as HTMLElement).style.color='var(--color-text-dim)'"
          >← ALL BRANDS</button>
          <div class="store-logo-lg">
            <ImageFallback :src="store.logo_url" :fallback-text="store.store_name.slice(0,2)" :alt="store.store_name" />
          </div>
          <div class="store-hero-info">
            <div class="store-name-lg">{{ store.store_name }}</div>
            <div class="store-bio-lg">{{ store.bio }}</div>
          </div>
          <BaseBadge :variant="store.status" :text="store.status.replace('_', ' ').toUpperCase()" />
        </div>
      </div>

      <!-- Marquee -->
      <div class="mq">
        <div class="mq-inner">
          <span class="mq-seg">{{ store.store_name }}</span><span class="mq-dot">///</span>
          <span class="mq-seg">OFFICIAL STORE</span><span class="mq-dot">///</span>
          <span class="mq-seg">{{ catalog.total }} PRODUCTS</span><span class="mq-dot">///</span>
          <span class="mq-seg">CASH ON DELIVERY</span><span class="mq-dot">///</span>
          <span class="mq-seg">{{ store.store_name }}</span><span class="mq-dot">///</span>
          <span class="mq-seg">OFFICIAL STORE</span><span class="mq-dot">///</span>
          <span class="mq-seg">{{ catalog.total }} PRODUCTS</span><span class="mq-dot">///</span>
          <span class="mq-seg">CASH ON DELIVERY</span><span class="mq-dot">///</span>
        </div>
      </div>

      <!-- Products -->
      <section class="sec">
        <div class="sec-hdr">
          <div>
            <div class="sec-label">Collection</div>
            <div class="sec-title">ALL PRODUCTS</div>
          </div>
          <span style="font-family:'Space Mono',monospace;font-size:10px;letter-spacing:.1em;color:var(--color-text-soft)">{{ catalog.total }} ITEMS</span>
        </div>

        <div class="pg-grid-auto">
          <template v-if="catalog.loading">
            <div v-for="i in 6" :key="i" class="pc">
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
                    :disabled="p.stock === 0"
                    @click.stop="quickAdd(p, sz, $event)"
                  >{{ sz }}</button>
                </div>
              </div>
              <div class="pc-info">
                <div>
                  <div class="pc-name">{{ p.name }}</div>
                  <div class="pc-sub">{{ store.store_name }}</div>
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
              <div class="empty-title">NO PRODUCTS YET</div>
            </div>
          </div>
        </div>
      </section>

      <AppFooter />
    </template>
    <div v-else class="empty-state">
      <div class="empty-icon">∅</div>
      <div class="empty-title">STORE NOT FOUND</div>
      <button class="btn-out" style="margin-top:20px;padding:12px 24px;font-size:10px" @click="router.push('/stores')">← ALL BRANDS</button>
    </div>
  </div>
</template>
