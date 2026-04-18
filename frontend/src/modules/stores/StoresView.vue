<script setup lang="ts">
import { onMounted } from 'vue'
import { useRouter } from 'vue-router'
import { useStoresCatalogStore } from '../../stores/storesCatalog'
import ImageFallback from '../../components/base/ImageFallback.vue'
import AppFooter from '../../components/layout/AppFooter.vue'

const router = useRouter()
const stores = useStoresCatalogStore()

onMounted(() => stores.fetchStores())
</script>

<template>
  <div>
    <div class="shops-top">
      <div>
        <div style="font-family:'Space Mono',monospace;font-size:9px;letter-spacing:.2em;text-transform:uppercase;color:var(--color-text-soft);margin-bottom:8px">EJOUTIYA — MARKETPLACE</div>
        <div class="shop-h">ALL<br>BRANDS</div>
      </div>
      <div style="font-family:'Space Mono',monospace;font-size:28px;letter-spacing:.04em;color:var(--color-text-dim)">
        {{ stores.loading ? '—' : stores.stores.filter(s => s.status === 'active').length }} <span style="font-size:14px">BRANDS</span>
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
      <template v-else-if="stores.stores.filter(s => s.status === 'active').length">
        <div
          v-for="s in stores.stores.filter(s => s.status === 'active')"
          :key="s.id"
          class="sc"
          @click="router.push({ name: 'store-detail', params: { id: s.id } })"
        >
          <div class="sc-cover">
            <ImageFallback :src="s.hero_image_url" :fallback-text="s.name" :alt="s.name" />
          </div>
          <div class="sc-info">
            <div class="sc-head">
              <div class="sc-logo">
                <ImageFallback :src="s.logo_url" :fallback-text="s.name.slice(0,2)" :alt="s.name" />
              </div>
              <div>
                <div class="sc-name">{{ s.name }}</div>
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
          <div class="empty-title">NO BRANDS YET</div>
        </div>
      </div>
    </div>

    <AppFooter />
  </div>
</template>
