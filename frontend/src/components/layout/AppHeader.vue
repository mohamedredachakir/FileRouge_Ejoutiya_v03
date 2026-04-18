<script setup lang="ts">
import { computed } from 'vue'
import { useRouter } from 'vue-router'
import { useAuthStore } from '../../stores/auth'
import { useCartStore } from '../../stores/cart'
import { useUiStore } from '../../stores/ui'

const router = useRouter()
const auth = useAuthStore()
const cart = useCartStore()
const ui = useUiStore()

const isGuest = computed(() => !auth.isAuthenticated)

function goTo(name: string) { router.push({ name }) }

async function handleLogout() {
  await auth.logout()
  router.push({ name: 'landing' })
  ui.showToast('LOGGED OUT.')
}
</script>

<template>
  <!-- Announcement bar -->
  <div class="ann-bar">
    <div class="ann-inner">
      <span class="ann-seg">FREE SHIPPING OVER 500 MAD</span>
      <span class="ann-seg">///</span>
      <span class="ann-seg">CASH ON DELIVERY — ALL MOROCCO</span>
      <span class="ann-seg">///</span>
      <span class="ann-seg">NEW DROPS EVERY FRIDAY</span>
      <span class="ann-seg">///</span>
      <span class="ann-seg">FREE SHIPPING OVER 500 MAD</span>
      <span class="ann-seg">///</span>
      <span class="ann-seg">CASH ON DELIVERY — ALL MOROCCO</span>
      <span class="ann-seg">///</span>
      <span class="ann-seg">NEW DROPS EVERY FRIDAY</span>
      <span class="ann-seg">///</span>
    </div>
  </div>

  <!-- Nav -->
  <nav class="nav-sticky">
    <div
      class="nav-logo"
      style="font-family:'Space Mono',monospace;font-size:17px;letter-spacing:.22em;cursor:pointer;transition:opacity .15s;text-transform:uppercase"
      @click="goTo('landing')"
      @mouseenter="($event.target as HTMLElement).style.opacity='.7'"
      @mouseleave="($event.target as HTMLElement).style.opacity='1'"
    >
      EJOUTIYA
    </div>

    <div class="nav-links" style="display:flex;gap:28px">
      <RouterLink
        to="/"
        class="nav-lnk"
        :class="{ on: $route.name === 'landing' }"
        style="font-size:11px;font-weight:600;text-transform:uppercase;letter-spacing:.1em;color:var(--color-text-dim);transition:color .15s;text-decoration:none"
        active-class=""
      >HOME</RouterLink>
      <RouterLink
        to="/products"
        class="nav-lnk"
        :class="{ on: $route.name === 'products' || $route.name === 'product-detail' }"
        style="font-size:11px;font-weight:600;text-transform:uppercase;letter-spacing:.1em;color:var(--color-text-dim);transition:color .15s;text-decoration:none"
        active-class=""
      >SHOP</RouterLink>
      <RouterLink
        to="/stores"
        class="nav-lnk"
        :class="{ on: $route.name === 'stores' || $route.name === 'store-detail' }"
        style="font-size:11px;font-weight:600;text-transform:uppercase;letter-spacing:.1em;color:var(--color-text-dim);transition:color .15s;text-decoration:none"
        active-class=""
      >BRANDS</RouterLink>
      <RouterLink
        v-if="auth.isStoreOwner"
        to="/dashboard"
        class="nav-lnk"
        style="font-size:11px;font-weight:600;text-transform:uppercase;letter-spacing:.1em;color:var(--color-text-dim);transition:color .15s;text-decoration:none"
        active-class=""
      >DASHBOARD</RouterLink>
      <RouterLink
        v-if="auth.isAdmin"
        to="/admin"
        class="nav-lnk"
        style="font-size:11px;font-weight:600;text-transform:uppercase;letter-spacing:.1em;color:var(--color-text-dim);transition:color .15s;text-decoration:none"
        active-class=""
      >ADMIN</RouterLink>
    </div>

    <div class="nav-r" style="display:flex;align-items:center;gap:16px">
      <button
        v-if="isGuest"
        class="nav-icon"
        style="color:var(--color-text-dim);font-size:15px;padding:4px;transition:color .15s;cursor:pointer;background:none;border:none;font-family:'Space Mono',monospace;font-size:10px;letter-spacing:.1em;text-transform:uppercase"
        @click="ui.openModal('login')"
        @mouseenter="($event.target as HTMLElement).style.color='var(--color-text)'"
        @mouseleave="($event.target as HTMLElement).style.color='var(--color-text-dim)'"
      >
        SIGN IN
      </button>
      <button
        v-if="!isGuest && auth.isClient"
        class="nav-icon"
        style="color:var(--color-text-dim);padding:4px;transition:color .15s;cursor:pointer;background:none;border:none;font-family:'Space Mono',monospace;font-size:10px;letter-spacing:.1em;text-transform:uppercase"
        :title="auth.user?.name"
        @click="goTo('profile')"
        @mouseenter="($event.target as HTMLElement).style.color='var(--color-text)'"
        @mouseleave="($event.target as HTMLElement).style.color='var(--color-text-dim)'"
      >
        {{ auth.user?.name?.split(' ')[0]?.toUpperCase() || 'ACCOUNT' }}
      </button>
      <button
        v-if="!isGuest && !auth.isClient"
        class="nav-icon"
        style="color:var(--color-text-dim);padding:4px;transition:color .15s;cursor:pointer;background:none;border:none;font-family:'Space Mono',monospace;font-size:10px;letter-spacing:.1em;text-transform:uppercase"
        @click="handleLogout"
      >
        LOGOUT
      </button>

      <button class="cart-trig" @click="cart.toggleDrawer()">
        <span style="font-family:'Space Mono',monospace;font-size:9px;letter-spacing:.12em;text-transform:uppercase">CART</span>
        <span class="c-pill">{{ cart.count }}</span>
      </button>
    </div>
  </nav>
</template>

<style scoped>
.nav-lnk { color: var(--color-text-dim) !important; }
.nav-lnk:hover, .nav-lnk.on { color: var(--color-text) !important; }
.cart-trig {
  border: 1px solid var(--color-border-2);
  padding: 6px 14px;
  font-family: 'Space Mono', monospace;
  font-size: 9px;
  letter-spacing: .12em;
  text-transform: uppercase;
  display: flex;
  align-items: center;
  gap: 8px;
  transition: border-color .15s;
  cursor: pointer;
  background: transparent;
  color: var(--color-text);
}
.cart-trig:hover { border-color: var(--color-text); }
.c-pill {
  background: var(--color-text);
  color: var(--color-bg);
  font-size: 9px;
  font-weight: 700;
  padding: 1px 5px;
  min-width: 16px;
  text-align: center;
  font-family: 'Space Mono', monospace;
}
</style>
