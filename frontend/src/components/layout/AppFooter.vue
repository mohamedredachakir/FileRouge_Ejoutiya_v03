<script setup lang="ts">
import { useRouter } from 'vue-router'
const router = useRouter()
</script>
<template>
  <footer class="foot">
    <div>
      <div class="foot-logo">EJOUTIYA</div>
      <p class="foot-tag">Morocco's multi-brand streetwear platform. Rebellion in every thread.</p>
    </div>
    <div>
      <div class="foot-h">Navigate</div>
      <button class="foot-lnk" @click="router.push('/')">Home</button>
      <button class="foot-lnk" @click="router.push('/products')">Shop All</button>
      <button class="foot-lnk" @click="router.push('/stores')">All Brands</button>
    </div>
    <div>
      <div class="foot-h">Info</div>
      <button class="foot-lnk">Size Guide</button>
      <button class="foot-lnk">Shipping Info</button>
      <button class="foot-lnk">FAQ</button>
    </div>
    <div>
      <div class="foot-h">Follow</div>
      <button class="foot-lnk">Instagram</button>
      <button class="foot-lnk">TikTok</button>
      <button class="foot-lnk">Snapchat</button>
    </div>
  </footer>
  <div class="foot-bot">
    <span class="foot-cp">© 2026 EJOUTIYA™ — All rights reserved</span>
    <span class="foot-cp">Casablanca, Morocco</span>
  </div>
</template>
