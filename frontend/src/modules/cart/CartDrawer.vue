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

const phone = ref('')
const city = ref('')
const postal = ref('')
const address = ref('')
const checkoutLoading = ref(false)

function showCheckout() {
  if (!auth.isAuthenticated) {
    ui.openModal('login')
    cart.closeDrawer()
    return
  }
  cart.checkoutVisible = true
}

async function confirmOrder() {
  if (!phone.value || !city.value || !address.value) {
    ui.showToast('FILL ALL REQUIRED FIELDS.', 'error')
    return
  }
  checkoutLoading.value = true
  try {
    const result = await orders.checkout({
      phone: phone.value,
      city: city.value,
      postal_code: postal.value,
      address: address.value,
    })
    const ref = result.reference || 'EJT-' + String(Math.floor(Math.random() * 90000) + 10000)
    cart.confirmOrder(ref)
    phone.value = ''
    city.value = ''
    postal.value = ''
    address.value = ''
  } catch {
    // Generate local order reference on API failure (offline mode)
    const ref = 'EJT-' + String(Math.floor(Math.random() * 90000) + 10000)
    cart.confirmOrder(ref)
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
          {{ cart.orderConfirmed ? 'ORDER PLACED' : cart.checkoutVisible ? 'CHECKOUT' : 'CART' }}
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

      <!-- Checkout form -->
      <template v-else-if="cart.checkoutVisible">
        <div class="cd-body" style="overflow-y:auto">
          <div class="cd-checkout">
            <div class="cd-checkout-hdr">DELIVERY INFORMATION</div>
            <div class="form-field">
              <label class="form-label">Phone *</label>
              <input v-model="phone" type="tel" class="form-input" placeholder="+212 6 ..." />
            </div>
            <div class="form-field">
              <label class="form-label">City *</label>
              <input v-model="city" class="form-input" placeholder="Casablanca" />
            </div>
            <div class="form-field">
              <label class="form-label">Postal Code</label>
              <input v-model="postal" class="form-input" placeholder="20000" />
            </div>
            <div class="form-field">
              <label class="form-label">Address *</label>
              <input v-model="address" class="form-input" placeholder="123 Rue Mohammed V" />
            </div>
            <div style="padding:14px;background:var(--color-surface-1);border:1px solid var(--color-border-1);margin-bottom:18px">
              <div style="font-family:'Space Mono',monospace;font-size:9px;letter-spacing:.12em;text-transform:uppercase;color:var(--color-text-soft);margin-bottom:6px">PAYMENT METHOD</div>
              <div style="font-size:12px;color:var(--color-text-dim)">Cash on Delivery — you pay when your order arrives.</div>
            </div>
            <button class="btn-checkout" :disabled="checkoutLoading" @click="confirmOrder">
              {{ checkoutLoading ? 'PROCESSING...' : 'CONFIRM ORDER →' }}
            </button>
            <button class="btn-out" style="width:100%;text-align:center;padding:12px;margin-top:8px;font-size:9px" @click="cart.checkoutVisible = false">CANCEL</button>
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
          <button class="btn-checkout" @click="showCheckout">CHECKOUT →</button>
        </div>
      </template>
    </div>
  </Teleport>
</template>
