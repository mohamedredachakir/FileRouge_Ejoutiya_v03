<script setup lang="ts">
import { ref, onMounted, computed } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import { useCatalogStore } from '../../stores/catalog'
import { useCartStore } from '../../stores/cart'
import { useUiStore } from '../../stores/ui'
import ImageFallback from '../../components/base/ImageFallback.vue'
import { CATEGORY_LABELS } from '../../types'

const route = useRoute()
const router = useRouter()
const catalog = useCatalogStore()
const cart = useCartStore()
const ui = useUiStore()

const selectedSize = ref<string | null>(null)
const sizeError = ref(false)
const selectedThumb = ref(0)

onMounted(async () => {
  await catalog.fetchProduct(Number(route.params.id))
  selectedSize.value = null
  selectedThumb.value = 0
})

const product = computed(() => catalog.selectedProduct)
const isOos = computed(() => !product.value || product.value.stock === 0 || product.value.status === 'out_of_stock')

const allImages = computed(() => {
  if (!product.value) return []
  const imgs: string[] = []
  if (product.value.main_image_url) imgs.push(product.value.main_image_url)
  if (product.value.images) {
    product.value.images.forEach(i => { if (!imgs.includes(i.url)) imgs.push(i.url) })
  }
  if (imgs.length === 0) imgs.push('')
  return imgs
})

const currentImage = computed(() => allImages.value[selectedThumb.value] || '')

function pickSize(sz: string) {
  selectedSize.value = sz
  sizeError.value = false
}

function addToCart() {
  if (!selectedSize.value) { sizeError.value = true; return }
  if (!product.value) return
  cart.addItem({
    product_id: product.value.id,
    product_name: product.value.name,
    product_price: product.value.price,
    product_category: product.value.category,
    main_image_url: product.value.main_image_url,
    store_name: product.value.store?.name || '—',
    size: selectedSize.value,
  })
  ui.showToast('ADDED TO CART.')
  cart.openDrawer()
}

function goBack() { router.back() }
function goToStore() {
  if (product.value?.store?.id) router.push({ name: 'store-detail', params: { id: product.value.store.id } })
}
</script>

<template>
  <div>
    <!-- Loading -->
    <div v-if="catalog.loadingProduct" class="det-wrap">
      <div class="det-gal">
        <div class="skeleton" style="flex:1" />
      </div>
      <div class="det-panel">
        <div class="skeleton" style="height:10px;width:80px;margin-bottom:28px" />
        <div class="skeleton" style="height:14px;width:120px;margin-bottom:10px" />
        <div class="skeleton" style="height:24px;margin-bottom:14px" />
        <div class="skeleton" style="height:22px;width:100px;margin-bottom:24px" />
      </div>
    </div>

    <!-- Product -->
    <div v-else-if="product" class="det-wrap">
      <!-- Gallery -->
      <div class="det-gal texture">
        <div class="det-main">
          <ImageFallback :src="currentImage" :fallback-text="CATEGORY_LABELS[product.category] || product.category" :alt="product.name" />
        </div>
        <div class="det-thumbs">
          <div
            v-for="(img, idx) in allImages.slice(0, 4)"
            :key="idx"
            class="det-thumb"
            :class="{ on: selectedThumb === idx }"
            @click="selectedThumb = idx"
          >
            <ImageFallback :src="img" :fallback-text="String(idx + 1)" :alt="String(idx + 1)" />
          </div>
        </div>
      </div>

      <!-- Info Panel -->
      <div class="det-panel">
        <button class="det-back" @click="goBack">← BACK</button>
        <div class="det-coll">{{ CATEGORY_LABELS[product.category] || product.category }}</div>
        <h1 class="det-name">{{ product.name }}</h1>
        <div class="det-price">
          <span v-if="product.original_price" class="det-oldprice">{{ product.original_price }} MAD</span>
          {{ product.price }} MAD
        </div>
        <div class="det-div" />

        <!-- Store row -->
        <div v-if="product.store" class="det-store-row" @click="goToStore">
          <div class="sc-logo">
            <ImageFallback :src="product.store.logo_url" :fallback-text="product.store.name.slice(0,2)" :alt="product.store.name" />
          </div>
          <div>
            <div style="font-family:'Space Mono',monospace;font-size:10px;letter-spacing:.08em;text-transform:uppercase">{{ product.store.name }}</div>
            <div style="font-size:10px;color:var(--color-text-soft);margin-top:2px">{{ product.store.products_count || '' }} PRODUCTS</div>
          </div>
          <div style="margin-left:auto;font-family:'Space Mono',monospace;font-size:9px;letter-spacing:.12em;color:var(--color-text-dim)">VISIT STORE →</div>
        </div>
        <div class="det-div" />

        <!-- Sizes -->
        <div>
          <div class="sz-hdr">
            <span class="det-lbl">SIZE</span>
            <span v-if="sizeError" class="sz-err">SELECT A SIZE</span>
          </div>
          <div class="sz-grid">
            <button
              v-for="sz in (product.sizes || ['S','M','L','XL'])"
              :key="sz"
              class="sz-btn"
              :class="{ on: selectedSize === sz, oos: isOos }"
              :disabled="isOos"
              @click="pickSize(sz)"
            >{{ sz }}</button>
          </div>
        </div>

        <button
          class="btn-atc"
          :disabled="isOos"
          @click="addToCart"
        >
          {{ isOos ? 'OUT OF STOCK' : 'ADD TO CART →' }}
        </button>

        <p class="det-desc">{{ product.description }}</p>

        <!-- COD info -->
        <div class="cod-box">
          <div class="cod-label">Cash on Delivery &nbsp;·&nbsp; All Morocco</div>
          <div class="cod-text">Paiement à la livraison uniquement. Commandez maintenant, payez à la réception.</div>
        </div>
      </div>
    </div>

    <!-- Not found -->
    <div v-else class="empty-state">
      <div class="empty-icon">∅</div>
      <div class="empty-title">PRODUCT NOT FOUND</div>
      <button class="btn-out" style="margin-top:20px;padding:12px 24px;font-size:10px" @click="router.push('/products')">← BACK TO SHOP</button>
    </div>
  </div>
</template>
