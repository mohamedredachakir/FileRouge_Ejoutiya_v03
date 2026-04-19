<script setup lang="ts">
import { onMounted, ref, watch } from 'vue'
import { useRouter, useRoute } from 'vue-router'
import { useStoresCatalogStore } from '../../stores/storesCatalog'
import ImageFallback from '../../components/base/ImageFallback.vue'
import AppFooter from '../../components/layout/AppFooter.vue'

const router = useRouter()
const route = useRoute()
const stores = useStoresCatalogStore()

const searchQuery = ref((route.query.search as string) || '')
const currentPage = ref(parseInt(route.query.page as string) || 1)

async function load() {
  await stores.fetchStores({
    search: searchQuery.value,
    page: currentPage.value
  })
}

onMounted(load)

watch(() => route.query.search, (newVal) => {
  searchQuery.value = (newVal as string) || ''
  currentPage.value = 1
  load()
})

watch(() => route.query.page, (newVal) => {
  currentPage.value = parseInt(newVal as string) || 1
  load()
})

function clearSearch() {
  searchQuery.value = ''
  currentPage.value = 1
  router.replace({
    query: { ...route.query, search: undefined, page: 1 }
  })
  load()
}

function goToPage(page: number) {
  currentPage.value = page
  router.push({
    query: { ...route.query, page }
  })
  window.scrollTo({ top: 0, behavior: 'smooth' })
}
</script>

<template>
  <div>
    <div class="shops-top">
      <div>
        <div style="font-family:'Space Mono',monospace;font-size:9px;letter-spacing:.2em;text-transform:uppercase;color:var(--color-text-soft);margin-bottom:8px">EJOUTIYA — MARKETPLACE</div>
        <div class="shop-h">ALL<br>BRANDS</div>
      </div>
      <div v-if="searchQuery" class="search-indicator" style="margin-left:40px;margin-bottom:20px">
        <span class="si-label">SEARCHED BRANDS:</span>
        <span class="si-val">{{ searchQuery }}</span>
        <button class="si-clear" @click="clearSearch">×</button>
      </div>
      <div style="margin-left:auto;font-family:'Space Mono',monospace;font-size:28px;letter-spacing:.04em;color:var(--color-text-dim)">
        {{ stores.loading ? '—' : stores.total }} <span style="font-size:14px">BRANDS</span>
      </div>
    </div>

    <div class="pg-grid-3" style="margin-top:1px">
      <template v-if="stores.loading">
        <div v-for="i in 6" :key="i" class="sc">
          <div class="skeleton" style="aspect-ratio:16/7" />
          <div style="padding:16px">
            <div class="skeleton" style="height:14px;margin-bottom:8px" />
            <div class="skeleton" style="height:12px;width:80%" />
          </div>
        </div>
      </template>
      <template v-else-if="stores.stores.length">
        <div
          v-for="s in stores.stores"
          :key="s.id"
          class="sc"
          @click="router.push({ name: 'store-detail', params: { id: s.id } })"
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
      <div v-else style="grid-column:1/-1">
        <div class="empty-state">
          <div class="empty-icon">∅</div>
          <div class="empty-title">NO BRANDS FOUND</div>
          <div class="empty-msg" v-if="searchQuery">Try a different search term.</div>
        </div>
      </div>
    </div>

    <!-- Pagination -->
    <div v-if="stores.lastPage > 1" class="pagination-wrap">
      <div class="pagination">
        <button 
          class="pg-btn" 
          :disabled="stores.currentPage === 1" 
          @click="goToPage(stores.currentPage - 1)"
        >
          <span class="pg-arrow">←</span> PREV
        </button>
        
        <div class="pg-pages">
          <button 
            v-for="p in stores.lastPage" 
            :key="p" 
            class="pg-num" 
            :class="{ active: p === stores.currentPage }"
            @click="goToPage(p)"
          >{{ p }}</button>
        </div>

        <button 
          class="pg-btn" 
          :disabled="stores.currentPage === stores.lastPage" 
          @click="goToPage(stores.currentPage + 1)"
        >
          NEXT <span class="pg-arrow">→</span>
        </button>
      </div>
      <div class="pg-info">PAGE {{ stores.currentPage }} OF {{ stores.lastPage }}</div>
    </div>

    <AppFooter />
  </div>
</template>
