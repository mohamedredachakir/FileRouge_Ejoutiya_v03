<script setup lang="ts">
import { ref, onMounted } from 'vue'
import { useRouter } from 'vue-router'
import { useAuthStore } from '../../stores/auth'
import { useOrdersStore } from '../../stores/orders'
import { useUiStore } from '../../stores/ui'
import BaseBadge from '../../components/base/BaseBadge.vue'
import AppFooter from '../../components/layout/AppFooter.vue'

const router = useRouter()
const auth = useAuthStore()
const orders = useOrdersStore()
const ui = useUiStore()

const activeTab = ref('orders')
const settingsName = ref(auth.user?.name || '')
const settingsEmail = ref(auth.user?.email || '')
const settingsPhone = ref(auth.user?.phone || '')
const settingsCity = ref(auth.user?.city || '')
const settingsZip = ref(auth.user?.zip_code || '')
const settingsAddress = ref(auth.user?.address || '')
const settingsPassword = ref('')
const settingsPasswordConfirmation = ref('')
const savingSettings = ref(false)

onMounted(async () => {
  if (!auth.isAuthenticated) { router.push('/'); return }
  await orders.fetchMyOrders()
  settingsName.value = auth.user?.name || ''
  settingsEmail.value = auth.user?.email || ''
  settingsPhone.value = auth.user?.phone || ''
  settingsCity.value = auth.user?.city || ''
  settingsZip.value = auth.user?.zip_code || ''
  settingsAddress.value = auth.user?.address || ''
})

async function saveSettings() {
  if (settingsPassword.value && settingsPassword.value !== settingsPasswordConfirmation.value) {
    ui.showToast('PASSWORDS DO NOT MATCH.', 'error')
    return
  }

  savingSettings.value = true
  try {
    const payload: any = { 
      name: settingsName.value, 
      email: settingsEmail.value, 
      phone: settingsPhone.value, 
      city: settingsCity.value,
      zip_code: settingsZip.value,
      address: settingsAddress.value 
    }
    
    if (settingsPassword.value) {
      payload.password = settingsPassword.value
      payload.password_confirmation = settingsPasswordConfirmation.value
    }

    await auth.updateMe(payload)
    ui.showToast('SETTINGS SAVED.')
    settingsPassword.value = ''
    settingsPasswordConfirmation.value = ''
  } catch {
    ui.showToast('FAILED TO SAVE.', 'error')
  } finally {
    savingSettings.value = false
  }
}

async function logout() {
  await auth.logout()
  router.push('/')
  ui.showToast('LOGGED OUT.')
}

function statusLabel(s: string) {
  return s.replace(/_/g, ' ').toUpperCase()
}

function formatDate(d: string) {
  return new Date(d).toLocaleDateString('en-GB', { day: '2-digit', month: 'short', year: 'numeric' })
}
</script>

<template>
  <div>
    <div class="profile-wrap">
      <div class="profile-hdr">
        <div>
          <div style="font-family:'Space Mono',monospace;font-size:9px;letter-spacing:.2em;text-transform:uppercase;color:var(--color-text-soft);margin-bottom:8px">CLIENT AREA</div>
          <div class="profile-h">{{ auth.user?.name?.toUpperCase() || 'MY ACCOUNT' }}</div>
        </div>
      </div>

      <div class="profile-grid">
        <!-- Sidebar -->
        <div class="profile-sidebar">
          <button class="profile-nav-btn" :class="{ on: activeTab === 'orders' }" @click="activeTab = 'orders'">MY ORDERS</button>
          <button class="profile-nav-btn" :class="{ on: activeTab === 'settings' }" @click="activeTab = 'settings'">SETTINGS</button>
          <button class="profile-nav-btn" :class="{ on: activeTab === 'sizeguide' }" @click="activeTab = 'sizeguide'">SIZE GUIDE</button>
          <button class="profile-nav-btn" style="margin-top:40px;color:var(--color-accent)" @click="logout">LOGOUT →</button>
        </div>

        <!-- Content -->
        <div class="profile-content">
          <!-- ORDERS -->
          <div v-if="activeTab === 'orders'">
            <div class="profile-sec-h">ORDER HISTORY</div>
            <div v-if="orders.loading">
              <div v-for="i in 3" :key="i" style="margin-bottom:12px">
                <div class="skeleton" style="height:52px" />
              </div>
            </div>
            <div v-else-if="orders.myOrders.length">
              <div
                v-for="o in orders.myOrders"
                :key="o.id"
                class="order-row"
              >
                <div class="order-id">{{ o.reference }}</div>
                <div>
                  <div class="order-items" style="margin-bottom:4px">
                    {{ o.items?.map(i => i.product?.name + ' / ' + i.size).join(', ') || '—' }}
                  </div>
                  <div style="font-size:10px;color:var(--color-text-soft)">{{ formatDate(o.created_at) }}</div>
                </div>
                <div class="order-total">{{ o.total }} MAD</div>
                <BaseBadge :variant="o.status" :text="statusLabel(o.status)" />
              </div>
            </div>
            <div v-else class="empty-state">
              <div class="empty-icon">∅</div>
              <div class="empty-title">NO ORDERS YET</div>
              <div class="empty-msg">Start shopping to see your orders here.</div>
            </div>
          </div>

          <!-- SETTINGS -->
          <div v-if="activeTab === 'settings'">
            <div class="profile-sec-h">ACCOUNT SETTINGS</div>
            <div class="form-field">
              <label class="form-label">Full Name</label>
              <input v-model="settingsName" class="form-input" placeholder="Your name" />
            </div>
            <div class="form-field">
              <label class="form-label">Email</label>
              <input v-model="settingsEmail" type="email" class="form-input" placeholder="email@example.com" />
            </div>
            <div class="form-field">
              <label class="form-label">Phone</label>
              <input v-model="settingsPhone" class="form-input" placeholder="+212 ..." />
            </div>
            <div class="form-field">
              <label class="form-label">City</label>
              <input v-model="settingsCity" class="form-input" placeholder="Casablanca" />
            </div>
            <div class="form-field">
              <label class="form-label">Zip Code</label>
              <input v-model="settingsZip" class="form-input" placeholder="Zip code" />
            </div>
            <div class="form-field">
              <label class="form-label">Address</label>
              <input v-model="settingsAddress" class="form-input" placeholder="Your Address" />
            </div>
            <div style="margin-top:24px;padding-top:24px;border-top:1px solid var(--color-border-1)">
              <div class="profile-sec-h" style="font-size:9px">SECURITY — CHANGE PASSWORD (LEAVE BLANK IF NO CHANGE)</div>
              <div class="form-field">
                <label class="form-label">New Password</label>
                <input v-model="settingsPassword" type="password" class="form-input" placeholder="••••••••" />
              </div>
              <div class="form-field">
                <label class="form-label">Confirm New Password</label>
                <input v-model="settingsPasswordConfirmation" type="password" class="form-input" placeholder="••••••••" />
              </div>
            </div>
            <button class="btn-cta" style="font-size:10px;padding:12px 24px" :disabled="savingSettings" @click="saveSettings">
              {{ savingSettings ? '...' : 'SAVE CHANGES →' }}
            </button>
          </div>

          <!-- SIZE GUIDE -->
          <div v-if="activeTab === 'sizeguide'">
            <div class="profile-sec-h">SIZE GUIDE — HOODIES &amp; T-SHIRTS</div>
            <table class="sg-table">
              <thead>
                <tr><th>SIZE</th><th>CHEST (CM)</th><th>LENGTH (CM)</th><th>SLEEVE (CM)</th></tr>
              </thead>
              <tbody>
                <tr><td>XS</td><td>88 – 92</td><td>68</td><td>58</td></tr>
                <tr><td>S</td><td>92 – 96</td><td>70</td><td>60</td></tr>
                <tr><td>M</td><td>96 – 100</td><td>72</td><td>62</td></tr>
                <tr><td>L</td><td>100 – 104</td><td>74</td><td>64</td></tr>
                <tr><td>XL</td><td>104 – 108</td><td>76</td><td>66</td></tr>
                <tr><td>XXL</td><td>108 – 116</td><td>78</td><td>68</td></tr>
              </tbody>
            </table>
            <div class="profile-sec-h" style="margin-top:32px">SIZE GUIDE — PANTS</div>
            <table class="sg-table">
              <thead>
                <tr><th>SIZE</th><th>WAIST (CM)</th><th>HIP (CM)</th><th>INSEAM (CM)</th></tr>
              </thead>
              <tbody>
                <tr><td>XS</td><td>70 – 74</td><td>90 – 94</td><td>76</td></tr>
                <tr><td>S</td><td>74 – 78</td><td>94 – 98</td><td>78</td></tr>
                <tr><td>M</td><td>78 – 82</td><td>98 – 102</td><td>80</td></tr>
                <tr><td>L</td><td>82 – 86</td><td>102 – 106</td><td>82</td></tr>
                <tr><td>XL</td><td>86 – 92</td><td>106 – 112</td><td>84</td></tr>
              </tbody>
            </table>
          </div>
        </div>
      </div>
    </div>

    <AppFooter />
  </div>
</template>
