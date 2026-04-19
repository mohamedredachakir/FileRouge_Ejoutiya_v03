<script setup lang="ts">
import { onMounted, onUnmounted, ref } from 'vue'
import { useRouter } from 'vue-router'
import { useCatalogStore } from '../../stores/catalog'
import { useStoresCatalogStore } from '../../stores/storesCatalog'
import { useCartStore } from '../../stores/cart'
import { useUiStore } from '../../stores/ui'
import ImageFallback from '../../components/base/ImageFallback.vue'

// Import all background images from assets/bg folder
const heroImages = Object.values(import.meta.glob('../../assets/bg/*.jpg', { eager: true, import: 'default' })) as string[]

import AppFooter from '../../components/layout/AppFooter.vue'
import { CATEGORY_LABELS } from '../../types'
import type { Product, Store } from '../../types'

const router = useRouter()
const catalog = useCatalogStore()
const stores = useStoresCatalogStore()
const cart = useCartStore()
const ui = useUiStore()

const featuredProducts = ref<Product[]>([])
const featuredStores = ref<Store[]>([])
const loading = ref(true)
const currentImgIdx = ref(0)
let timer: any = null

function nextImg() {
  currentImgIdx.value = (currentImgIdx.value + 1) % heroImages.length
}

const categories = [
  { key: 'hoodie', label: 'HOODIES', sub: 'The backbone of any fit.' },
  { key: 't_shirt', label: 'T-SHIRTS', sub: 'Made to last.' },
  { key: 'pants', label: 'PANTS', sub: 'Oversized. Always.' },
  { key: 'sneakers', label: 'SNEAKERS', sub: 'The foundation.' },
  { key: 'accessories', label: 'ACCESSORIES', sub: 'Finish the look.' },
]

onMounted(async () => {
  timer = setInterval(nextImg, 8000)
  try {
    await Promise.all([
      catalog.fetchProducts({ sort: 'new' }),
      stores.fetchStores(),
    ])
    featuredProducts.value = catalog.products.slice(0, 4)
    featuredStores.value = stores.stores.filter(s => s.status === 'active').slice(0, 3)
  } catch {
    // fallback to empty arrays
  } finally {
    loading.value = false
  }
})

onUnmounted(() => {
  if (timer) clearInterval(timer)
})

function goToProduct(id: number) { router.push({ name: 'product-detail', params: { id } }) }
function goToStore(id: number) { router.push({ name: 'store-detail', params: { id } }) }
function goToCategory(cat: string) { router.push({ name: 'products', query: { category: cat } }) }

async function quickAdd(p: Product, size: string) {
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
    <!-- HERO SLIDER -->
    <section class="hero-wrap texture">
      <!-- Slider Layers -->
      <div class="hero-slider">
        <Transition name="hero-slide">
          <div 
            :key="currentImgIdx" 
            class="hero-slide-item" 
            :style="{ backgroundImage: `linear-gradient(rgba(5,5,5,0.4), rgba(5,5,5,0.6)), url(${heroImages[currentImgIdx]})` }"
          />
        </Transition>
      </div>

      <div class="hero-grid-lines">
        <div v-for="i in 6" :key="i" class="hero-grid-line" />
      </div>
      <div class="hero-ghost">EJOUTIYA</div>
      <div class="hero-content">
        <div class="hero-eyebrow">EJOUTIYA — MOROCCO'S STREETWEAR MARKETPLACE</div>
        <h1 class="hero-h1">THE STREETS<br>BELONG TO US.</h1>
        <div class="hero-actions">
          <button class="btn-cta" @click="router.push('/products')">SHOP NOW →</button>
          <button class="btn-out" @click="router.push('/stores')">ALL BRANDS</button>
        </div>
      </div>
    </section>

    <!-- MARQUEE -->
    <div class="mq">
      <div class="mq-inner">
        <span class="mq-seg">EJOUTIYA</span><span class="mq-dot">///</span>
        <span class="mq-seg">MOROCCAN STREETWEAR</span><span class="mq-dot">///</span>
        <span class="mq-seg">MULTI-BRAND MARKETPLACE</span><span class="mq-dot">///</span>
        <span class="mq-seg">CASH ON DELIVERY</span><span class="mq-dot">///</span>
        <span class="mq-seg">NEW DROPS FRIDAY</span><span class="mq-dot">///</span>
        <span class="mq-seg">REBELLION IN EVERY THREAD</span><span class="mq-dot">///</span>
        <span class="mq-seg">EJOUTIYA</span><span class="mq-dot">///</span>
        <span class="mq-seg">MOROCCAN STREETWEAR</span><span class="mq-dot">///</span>
        <span class="mq-seg">MULTI-BRAND MARKETPLACE</span><span class="mq-dot">///</span>
        <span class="mq-seg">CASH ON DELIVERY</span><span class="mq-dot">///</span>
        <span class="mq-seg">NEW DROPS FRIDAY</span><span class="mq-dot">///</span>
        <span class="mq-seg">REBELLION IN EVERY THREAD</span><span class="mq-dot">///</span>
      </div>
    </div>

    <!-- FEATURED PRODUCTS -->
    <section class="sec">
      <div class="sec-hdr">
        <div>
          <div class="sec-label">EJOUTIYA — NEW ARRIVALS</div>
          <div class="sec-title">FEATURED DROPS</div>
        </div>
        <button class="lnk-all" @click="router.push('/products')">VIEW ALL →</button>
      </div>
      <div class="pg-grid-4">
        <template v-if="loading">
          <div v-for="i in 4" :key="i" class="pc">
            <div class="skeleton" style="aspect-ratio:3/4" />
            <div style="padding:12px">
              <div class="skeleton" style="height:12px;margin-bottom:8px" />
              <div class="skeleton" style="height:10px;width:60%" />
            </div>
          </div>
        </template>
        <template v-else-if="featuredProducts.length">
          <div
            v-for="p in featuredProducts"
            :key="p.id"
            class="pc"
            @click="goToProduct(p.id)"
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
                  @click.stop="quickAdd(p, sz)"
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
        <div v-else style="grid-column:1/-1;padding:40px;text-align:center;color:var(--color-text-dim);font-family:'Space Mono',monospace;font-size:11px;letter-spacing:.1em">
          NO PRODUCTS YET
        </div>
      </div>
    </section>

    <!-- CATEGORY BENTO -->
    <section class="sec">
      <div class="sec-hdr">
        <div>
          <div class="sec-label">SHOP BY CATEGORY</div>
          <div class="sec-title">THE COLLECTION</div>
        </div>
      </div>
      <div class="bento-grid">
        <div
          v-for="(cat, idx) in categories.slice(0, 4)"
          :key="cat.key"
          class="bento-cell"
          :style="{ minHeight: '200px' }"
          @click="goToCategory(cat.key)"
        >
          <div class="bento-label">{{ String(idx + 1).padStart(2, '0') }}</div>
          <div class="bento-title">{{ cat.label }}</div>
          <div style="font-size:12px;color:var(--color-text-dim);margin-top:10px">{{ cat.sub }}</div>
        </div>
        <div
          v-if="categories[4]"
          class="bento-cell bento-wide"
          style="min-height:160px"
          @click="goToCategory(categories[4].key)"
        >
          <div class="bento-label">05</div>
          <div class="bento-title">{{ categories[4].label }}</div>
          <div style="font-size:12px;color:var(--color-text-dim);margin-top:10px">{{ categories[4].sub }}</div>
        </div>
      </div>
    </section>

    <!-- MANIFESTO -->
    <section class="manifesto-sec texture">
      <div style="max-width:900px;margin:0 auto">
        <div class="sec-label" style="margin-bottom:20px">MANIFESTO</div>
        <p class="manifesto-q">"We don't sell clothes.<br>We sell the uniform of the ones who stayed real."</p>
        <p class="manifesto-body">
          EJOUTIYA is Morocco's first multi-brand streetwear marketplace. Born from the underground, built for the streets. 
          Every brand on this platform is vetted. Every piece is authentic. 
          Cash on delivery — no excuses, no barriers. The movement is yours.
        </p>
      </div>
    </section>

    <!-- FEATURED BRANDS -->
    <section class="sec">
      <div class="sec-hdr">
        <div>
          <div class="sec-label">EJOUTIYA — BRANDS</div>
          <div class="sec-title">FEATURED BRANDS</div>
        </div>
        <button class="lnk-all" @click="router.push('/stores')">ALL BRANDS →</button>
      </div>
      <div class="pg-grid-3">
        <template v-if="loading">
          <div v-for="i in 3" :key="i" class="sc">
            <div class="skeleton" style="aspect-ratio:16/7" />
            <div style="padding:16px">
              <div class="skeleton" style="height:14px;margin-bottom:8px" />
              <div class="skeleton" style="height:12px;width:80%" />
            </div>
          </div>
        </template>
        <template v-else-if="featuredStores.length">
          <div
            v-for="s in featuredStores"
            :key="s.id"
            class="sc"
            @click="goToStore(s.id)"
          >
            <div class="sc-cover">
              <ImageFallback :src="s.hero_image_url" :fallback-text="s.store_name" :alt="s.store_name" />
            </div>
            <div class="sc-info">
              <div class="sc-head">
                <div class="sc-logo">
                  <ImageFallback :src="s.logo_url" :fallback-text="s.store_name.slice(0,2)" :alt="s.store_name" />
                </div>
                <div>
                  <div class="sc-name">{{ s.store_name }}</div>
                </div>
              </div>
              <div class="sc-bio">{{ s.bio }}</div>
              <div class="sc-footer">
                <span class="sc-count">{{ s.products_count || 0 }} PRODUCTS</span>
                <button class="lnk-all">ENTER STORE →</button>
              </div>
            </div>
          </div>
        </template>
        <div v-else style="grid-column:1/-1;padding:40px;text-align:center;color:var(--color-text-dim);font-family:'Space Mono',monospace;font-size:11px;letter-spacing:.1em">
          NO BRANDS YET
        </div>
      </div>
    </section>

    <AppFooter />
  </div>
</template>
