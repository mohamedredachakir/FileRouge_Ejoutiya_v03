<script setup lang="ts">
import { ref } from 'vue'
import { useCartStore } from '../../stores/cart'
import { useOrdersStore } from '../../stores/orders'
import { useAuthStore } from '../../stores/auth'
import { useUiStore } from '../../stores/ui'
import ImageFallback from '../../components/base/ImageFallback.vue'

const cart = useCartStore()
const orders = useOrdersStore()
const auth = useAuthStore()
const ui = useUiStore()

const checkoutLoading = ref(false)

async function showCheckout() {
  if (!auth.isAuthenticated) {
    ui.openModal('login')
    cart.closeDrawer()
    return
  }
  
  if (!auth.user?.phone || !auth.user?.city || !auth.user?.address) {
    ui.showToast('PLEASE COMPLETE YOUR PROFILE DELIVERY INFO FIRST.', 'error')
    return
  }

  if (cart.items.length === 0) {
    ui.showToast('YOUR CART IS EMPTY.', 'error')
    return
  }
  
  await confirmOrder()
}

async function confirmOrder() {
  checkoutLoading.value = true
  try {
    const result = await orders.checkout({
      phone: auth.user!.phone!,
      city: auth.user!.city!,
      zip_code: auth.user!.zip_code || '',
      address: auth.user!.address!,
      items: cart.items.map((item) => ({
        product_id: item.product_id,
        quantity: item.qty,
        size: item.size,
      })),
    })
    
    if (result.user) {
      auth.user = result.user
    }

    const ref = result.reference || 'EJT-' + String(Math.floor(Math.random() * 90000) + 10000)
    cart.confirmOrder(ref)
    ui.showToast('ORDER CONFIRMED.')
  } catch (e: any) {
    const msg = e?.response?.data?.message || 'CHECKOUT FAILED'
    ui.showToast(msg.toUpperCase(), 'error')
  } finally {
    checkoutLoading.value = false
  }
}
</script>

<template>
  <Teleport to="body">
    <!-- Overlay -->
    <div class="cart-overlay" :class="{ on: cart.drawerOpen }" @click="cart.closeDrawer()" />

    <!-- Drawer -->
    <div class="cart-drawer" :class="{ on: cart.drawerOpen }">
      <!-- Header -->
      <div class="cd-hdr">
        <span class="cd-title">
          {{ cart.orderConfirmed ? 'ORDER PLACED' : 'CART' }}
        </span>
        <button class="cd-close" @click="cart.closeDrawer()">×</button>
      </div>

      <!-- Order Confirmed -->
      <template v-if="cart.orderConfirmed">
        <div class="cd-body" style="overflow-y:auto">
          <div class="conf-screen">
            <div class="conf-check">✓</div>
            <div class="conf-h">ORDER<br>CONFIRMED</div>
            <div class="conf-id">{{ cart.orderConfirmed }}</div>
            <p style="font-size:12px;color:var(--color-text-dim);line-height:1.8;max-width:280px;text-align:center">
              Your order has been received. Our team will confirm it shortly. Cash on delivery — pay when it arrives.
            </p>
            <button class="btn-cta" style="font-size:9px;padding:12px 24px;margin-top:24px" @click="cart.closeDrawer()">CONTINUE SHOPPING →</button>
          </div>
        </div>
      </template>

      <!-- Cart items -->
      <template v-else>
        <div class="cd-body" style="overflow-y:auto;flex:1">
          <div v-if="cart.items.length === 0" class="cd-empty">
            <div class="cd-empty-icon">∅</div>
            <div class="cd-empty-txt">Your cart is empty.<br>Start shopping.</div>
          </div>
          <div v-else>
            <div
              v-for="item in cart.items"
              :key="item.product_id + '-' + item.size"
              class="cart-item"
            >
              <div class="ci-thumb">
                <ImageFallback :src="item.main_image_url" :fallback-text="item.product_category" :alt="item.product_name" />
              </div>
              <div>
                <div class="ci-name">{{ item.product_name }}</div>
                <div class="ci-meta">{{ item.store_name }} / {{ item.size }}</div>
                <div class="ci-qty-ctrl">
                  <button class="qty-btn" @click="cart.updateQty(item.product_id, item.size, -1)">−</button>
                  <span class="qty-num">{{ item.qty }}</span>
                  <button class="qty-btn" @click="cart.updateQty(item.product_id, item.size, 1)">+</button>
                </div>
              </div>
              <div style="display:flex;flex-direction:column;align-items:flex-end;gap:6px">
                <span class="ci-price">{{ item.product_price * item.qty }} MAD</span>
                <button class="ci-remove" @click="cart.removeItem(item.product_id, item.size)">×</button>
              </div>
            </div>
          </div>
        </div>

        <div v-if="cart.items.length > 0" class="cd-foot">
          <div class="cd-tot">
            <span>TOTAL</span>
            <span class="cd-tot-v">{{ cart.total }} MAD</span>
          </div>
          <button class="btn-checkout" :disabled="checkoutLoading" @click="showCheckout">
            {{ checkoutLoading ? 'PROCESSING...' : 'PLACE ORDER →' }}
          </button>
        </div>
      </template>
    </div>
  </Teleport>
</template>
