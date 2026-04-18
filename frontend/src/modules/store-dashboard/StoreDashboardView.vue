<script setup lang="ts">
import { ref, onMounted } from 'vue'
import { useRouter } from 'vue-router'
import { useAuthStore } from '../../stores/auth'
import { useUiStore } from '../../stores/ui'
import { storeDashboardService } from '../../services/storeDashboard'
import BaseBadge from '../../components/base/BaseBadge.vue'
import ConfirmDialog from '../../components/base/ConfirmDialog.vue'
import ImageFallback from '../../components/base/ImageFallback.vue'
import type { Store, Product, Order } from '../../types'
import { CATEGORY_LABELS } from '../../types'

const router = useRouter()
const auth = useAuthStore()
const ui = useUiStore()

const activeTab = ref('store')
const myStore = ref<Store | null>(null)
const products = ref<Product[]>([])
const storeOrders = ref<Order[]>([])
const orderFilter = ref('ALL')
const loadingStore = ref(true)
const loadingProducts = ref(false)
const loadingOrders = ref(false)

// Store form
const storeFormName = ref('')
const storeFormBio = ref('')
const storeFormLogo = ref('')
const storeFormCover = ref('')
const savingStore = ref(false)

// Product drawer
const drawerOpen = ref(false)
const drawerOverlay = ref(false)
const editingProduct = ref<Product | null>(null)
const pdId = ref<number | null>(null)
const pdName = ref('')
const pdDesc = ref('')
const pdPrice = ref<number | null>(null)
const pdStock = ref<number | null>(null)
const pdCategory = ref('hoodie')
const pdStatus = ref('active')
const pdImages = ref('')
const pdSizes = ref('S,M,L,XL')
const savingProduct = ref(false)

// Confirm delete
const confirmDelete = ref(false)
const deletingProductId = ref<number | null>(null)

const ORDER_STATUSES = ['ALL', 'PENDING', 'CONFIRMED', 'DELIVERY', 'REJECTED']
const CATEGORIES = [
  { value: 'hoodie', label: 'HOODIE' },
  { value: 't_shirt', label: 'T-SHIRT' },
  { value: 'pants', label: 'PANTS' },
  { value: 'sneakers', label: 'SNEAKERS' },
  { value: 'accessories', label: 'ACCESSORIES' },
]

onMounted(async () => {
  if (!auth.isStoreOwner) { router.push('/'); return }
  await loadStore()
})

async function loadStore() {
  loadingStore.value = true
  try {
    myStore.value = await storeDashboardService.getMyStore()
    storeFormName.value = myStore.value.name
    storeFormBio.value = myStore.value.bio || ''
    storeFormLogo.value = myStore.value.logo_url || ''
    storeFormCover.value = myStore.value.hero_image_url || ''
  } catch {
    ui.showToast('FAILED TO LOAD STORE.', 'error')
  } finally {
    loadingStore.value = false
  }
}

async function loadProducts() {
  loadingProducts.value = true
  try {
    const res = await storeDashboardService.getMyProducts()
    products.value = res.data
  } catch {
    ui.showToast('FAILED TO LOAD PRODUCTS.', 'error')
  } finally {
    loadingProducts.value = false
  }
}

async function loadOrders() {
  loadingOrders.value = true
  try {
    const res = await storeDashboardService.getStoreOrders(orderFilter.value !== 'ALL' ? orderFilter.value.toLowerCase() : undefined)
    storeOrders.value = res.data
  } catch {
    ui.showToast('FAILED TO LOAD ORDERS.', 'error')
  } finally {
    loadingOrders.value = false
  }
}

async function setTab(tab: string) {
  activeTab.value = tab
  if (tab === 'products') await loadProducts()
  if (tab === 'orders') await loadOrders()
}

async function saveStore() {
  savingStore.value = true
  try {
    myStore.value = await storeDashboardService.updateMyStore({
      name: storeFormName.value,
      bio: storeFormBio.value,
      logo_url: storeFormLogo.value,
      hero_image_url: storeFormCover.value,
    })
    ui.showToast('STORE SAVED.')
  } catch {
    ui.showToast('FAILED TO SAVE.', 'error')
  } finally {
    savingStore.value = false
  }
}

function openProdDrawer(p: Product | null) {
  editingProduct.value = p
  if (p) {
    pdId.value = p.id
    pdName.value = p.name
    pdDesc.value = p.description || ''
    pdPrice.value = p.price
    pdStock.value = p.stock
    pdCategory.value = p.category
    pdStatus.value = p.status
    pdImages.value = p.main_image_url || ''
    pdSizes.value = (p.sizes || ['S', 'M', 'L', 'XL']).join(',')
  } else {
    pdId.value = null; pdName.value = ''; pdDesc.value = ''
    pdPrice.value = null; pdStock.value = null
    pdCategory.value = 'hoodie'; pdStatus.value = 'active'
    pdImages.value = ''; pdSizes.value = 'S,M,L,XL'
  }
  drawerOpen.value = true
  setTimeout(() => { drawerOverlay.value = true }, 10)
}

function closeProdDrawer() {
  drawerOpen.value = false
  drawerOverlay.value = false
}

async function saveProd() {
  if (!pdName.value || !pdPrice.value || !pdStock.value) { ui.showToast('FILL REQUIRED FIELDS.', 'error'); return }
  savingProduct.value = true
  try {
    const payload = {
      name: pdName.value,
      description: pdDesc.value,
      price: Number(pdPrice.value),
      stock: Number(pdStock.value),
      category: pdCategory.value,
      status: pdStatus.value,
      sizes: pdSizes.value.split(',').map((s: string) => s.trim()).filter(Boolean),
      image_urls: pdImages.value.split('\n').map((s: string) => s.trim()).filter(Boolean),
    }
    if (pdId.value) {
      const updated = await storeDashboardService.updateProduct(pdId.value, payload)
      const idx = products.value.findIndex((p: Product) => p.id === pdId.value)
      if (idx !== -1) products.value[idx] = updated
      ui.showToast('PRODUCT UPDATED.')
    } else {
      const created = await storeDashboardService.createProduct(payload)
      products.value.unshift(created)
      ui.showToast('PRODUCT CREATED.')
    }
    closeProdDrawer()
  } catch {
    ui.showToast('FAILED TO SAVE PRODUCT.', 'error')
  } finally {
    savingProduct.value = false
  }
}

function askDelete(id: number) { deletingProductId.value = id; confirmDelete.value = true }

async function deleteProduct() {
  if (!deletingProductId.value) return
  try {
    await storeDashboardService.deleteProduct(deletingProductId.value)
    products.value = products.value.filter((p: Product) => p.id !== deletingProductId.value)
    ui.showToast('PRODUCT DELETED.')
  } catch {
    ui.showToast('DELETE FAILED.', 'error')
  } finally {
    confirmDelete.value = false
    deletingProductId.value = null
  }
}

async function updateOrderStatus(orderId: number, status: string) {
  try {
    const updated = await storeDashboardService.updateOrderStatus(orderId, status)
    const idx = storeOrders.value.findIndex((o: Order) => o.id === orderId)
    if (idx !== -1) storeOrders.value[idx] = updated
    ui.showToast('ORDER STATUS UPDATED.')
  } catch {
    ui.showToast('UPDATE FAILED.', 'error')
  }
}

async function filterOrders(status: string) {
  orderFilter.value = status
  await loadOrders()
}

async function logout() {
  await auth.logout()
  router.push('/')
}

function statusLabel(s: string) { return s.replace(/_/g, ' ').toUpperCase() }

function formatPrice(n: number) {
  return n.toLocaleString('fr-DZ', { minimumFractionDigits: 0 }) + ' DA'
}
</script>

<template>
  <div style="display:flex;min-height:100vh;background:#0a0a0a;color:#e0e0e0;font-family:'Space Grotesk',sans-serif;">

    <!-- SIDEBAR -->
    <aside style="width:220px;min-height:100vh;background:#111;border-right:1px solid #222;display:flex;flex-direction:column;padding:0;flex-shrink:0;">
      <div style="padding:28px 20px 20px;">
        <div style="font-size:11px;letter-spacing:3px;color:#555;margin-bottom:8px;">STORE DASHBOARD</div>
        <div style="font-size:15px;font-weight:700;letter-spacing:1px;color:#fff;margin-bottom:6px;">
          {{ myStore?.name || '...' }}
        </div>
        <BaseBadge v-if="myStore" :variant="myStore.status" :text="statusLabel(myStore.status)" />
      </div>

      <nav style="flex:1;padding:8px 0;">
        <button
          v-for="tab in ['store', 'products', 'orders']"
          :key="tab"
          @click="setTab(tab)"
          style="width:100%;display:block;padding:13px 22px;text-align:left;background:none;border:none;cursor:pointer;font-family:inherit;font-size:10px;letter-spacing:2px;font-weight:700;transition:all .2s;"
          :style="activeTab === tab
            ? 'color:#fff;background:#1a1a1a;border-left:3px solid #fff;'
            : 'color:#555;border-left:3px solid transparent;'"
        >
          {{ tab === 'store' ? 'MY STORE' : tab === 'products' ? 'PRODUCTS' : 'ORDERS' }}
        </button>
      </nav>

      <div style="padding:20px;">
        <button
          @click="logout"
          style="width:100%;padding:11px;background:none;border:1px solid #333;color:#555;font-family:inherit;font-size:10px;letter-spacing:2px;cursor:pointer;transition:all .2s;"
          onmouseenter="this.style.borderColor='#fff';this.style.color='#fff'"
          onmouseleave="this.style.borderColor='#333';this.style.color='#555'"
        >
          LOGOUT
        </button>
      </div>
    </aside>

    <!-- MAIN CONTENT -->
    <main style="flex:1;padding:40px;overflow-x:auto;">

      <!-- MY STORE TAB -->
      <div v-if="activeTab === 'store'">
        <div style="font-size:11px;letter-spacing:3px;color:#555;margin-bottom:28px;">MY STORE</div>

        <div v-if="loadingStore" style="color:#555;font-size:12px;letter-spacing:2px;">LOADING...</div>

        <div v-else style="max-width:560px;">
          <div style="margin-bottom:20px;">
            <label style="display:block;font-size:10px;letter-spacing:2px;color:#555;margin-bottom:8px;">STORE NAME</label>
            <input
              v-model="storeFormName"
              type="text"
              style="width:100%;background:#111;border:1px solid #333;color:#fff;padding:12px 14px;font-family:inherit;font-size:13px;outline:none;box-sizing:border-box;"
              placeholder="Your store name"
            />
          </div>

          <div style="margin-bottom:20px;">
            <label style="display:block;font-size:10px;letter-spacing:2px;color:#555;margin-bottom:8px;">BIO</label>
            <textarea
              v-model="storeFormBio"
              rows="4"
              style="width:100%;background:#111;border:1px solid #333;color:#fff;padding:12px 14px;font-family:inherit;font-size:13px;outline:none;resize:vertical;box-sizing:border-box;"
              placeholder="Describe your store..."
            ></textarea>
          </div>

          <div style="margin-bottom:20px;">
            <label style="display:block;font-size:10px;letter-spacing:2px;color:#555;margin-bottom:8px;">LOGO URL</label>
            <input
              v-model="storeFormLogo"
              type="text"
              style="width:100%;background:#111;border:1px solid #333;color:#fff;padding:12px 14px;font-family:inherit;font-size:13px;outline:none;box-sizing:border-box;"
              placeholder="https://..."
            />
          </div>

          <div style="margin-bottom:28px;">
            <label style="display:block;font-size:10px;letter-spacing:2px;color:#555;margin-bottom:8px;">COVER IMAGE URL</label>
            <input
              v-model="storeFormCover"
              type="text"
              style="width:100%;background:#111;border:1px solid #333;color:#fff;padding:12px 14px;font-family:inherit;font-size:13px;outline:none;box-sizing:border-box;"
              placeholder="https://..."
            />
          </div>

          <button
            @click="saveStore"
            :disabled="savingStore"
            style="padding:14px 36px;background:#fff;color:#000;border:none;font-family:inherit;font-size:10px;letter-spacing:3px;font-weight:700;cursor:pointer;transition:opacity .2s;"
            :style="savingStore ? 'opacity:.5;cursor:not-allowed;' : ''"
          >
            {{ savingStore ? 'SAVING...' : 'SAVE' }}
          </button>
        </div>
      </div>

      <!-- PRODUCTS TAB -->
      <div v-else-if="activeTab === 'products'">
        <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:28px;">
          <div style="font-size:11px;letter-spacing:3px;color:#555;">PRODUCTS</div>
          <button
            @click="openProdDrawer(null)"
            style="padding:11px 22px;background:#fff;color:#000;border:none;font-family:inherit;font-size:10px;letter-spacing:2px;font-weight:700;cursor:pointer;"
          >
            + ADD PRODUCT
          </button>
        </div>

        <div v-if="loadingProducts" style="color:#555;font-size:12px;letter-spacing:2px;">LOADING...</div>

        <div v-else-if="products.length === 0" style="color:#555;font-size:12px;letter-spacing:2px;padding:40px 0;">
          NO PRODUCTS YET.
        </div>

        <div v-else style="overflow-x:auto;">
          <table style="width:100%;border-collapse:collapse;font-size:12px;">
            <thead>
              <tr style="border-bottom:1px solid #222;">
                <th style="text-align:left;padding:10px 12px;font-size:9px;letter-spacing:2px;color:#555;font-weight:400;">THUMBNAIL</th>
                <th style="text-align:left;padding:10px 12px;font-size:9px;letter-spacing:2px;color:#555;font-weight:400;">NAME</th>
                <th style="text-align:left;padding:10px 12px;font-size:9px;letter-spacing:2px;color:#555;font-weight:400;">CATEGORY</th>
                <th style="text-align:left;padding:10px 12px;font-size:9px;letter-spacing:2px;color:#555;font-weight:400;">PRICE</th>
                <th style="text-align:left;padding:10px 12px;font-size:9px;letter-spacing:2px;color:#555;font-weight:400;">STOCK</th>
                <th style="text-align:left;padding:10px 12px;font-size:9px;letter-spacing:2px;color:#555;font-weight:400;">STATUS</th>
                <th style="text-align:left;padding:10px 12px;font-size:9px;letter-spacing:2px;color:#555;font-weight:400;">ACTIONS</th>
              </tr>
            </thead>
            <tbody>
              <tr
                v-for="product in products"
                :key="product.id"
                style="border-bottom:1px solid #1a1a1a;transition:background .15s;"
              >
                <td style="padding:10px 12px;">
                  <div style="width:48px;height:48px;background:#1a1a1a;overflow:hidden;flex-shrink:0;">
                    <ImageFallback :src="product.main_image_url" :alt="product.name" fallback-text="—" />
                  </div>
                </td>
                <td style="padding:10px 12px;font-size:12px;color:#ddd;letter-spacing:.5px;">{{ product.name }}</td>
                <td style="padding:10px 12px;font-size:10px;color:#777;letter-spacing:1px;">{{ CATEGORY_LABELS[product.category] || product.category }}</td>
                <td style="padding:10px 12px;font-size:12px;color:#ddd;">{{ formatPrice(product.price) }}</td>
                <td style="padding:10px 12px;font-size:12px;color:#ddd;">{{ product.stock }}</td>
                <td style="padding:10px 12px;">
                  <BaseBadge :variant="product.status" :text="statusLabel(product.status)" />
                </td>
                <td style="padding:10px 12px;">
                  <div style="display:flex;gap:8px;">
                    <button
                      @click="openProdDrawer(product)"
                      style="padding:6px 14px;background:none;border:1px solid #333;color:#aaa;font-family:inherit;font-size:9px;letter-spacing:1px;cursor:pointer;"
                    >EDIT</button>
                    <button
                      @click="askDelete(product.id)"
                      style="padding:6px 14px;background:none;border:1px solid #333;color:#e55;font-family:inherit;font-size:9px;letter-spacing:1px;cursor:pointer;"
                    >DELETE</button>
                  </div>
                </td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>

      <!-- ORDERS TAB -->
      <div v-else-if="activeTab === 'orders'">
        <div style="font-size:11px;letter-spacing:3px;color:#555;margin-bottom:20px;">ORDERS</div>

        <!-- Filter buttons -->
        <div style="display:flex;gap:8px;margin-bottom:28px;flex-wrap:wrap;">
          <button
            v-for="status in ORDER_STATUSES"
            :key="status"
            @click="filterOrders(status)"
            style="padding:9px 18px;font-family:inherit;font-size:9px;letter-spacing:2px;font-weight:700;cursor:pointer;border:1px solid #333;transition:all .2s;"
            :style="orderFilter === status
              ? 'background:#fff;color:#000;border-color:#fff;'
              : 'background:none;color:#555;'"
          >
            {{ status }}
          </button>
        </div>

        <div v-if="loadingOrders" style="color:#555;font-size:12px;letter-spacing:2px;">LOADING...</div>

        <div v-else-if="storeOrders.length === 0" style="color:#555;font-size:12px;letter-spacing:2px;padding:40px 0;">
          NO ORDERS FOUND.
        </div>

        <div v-else style="display:flex;flex-direction:column;gap:14px;">
          <div
            v-for="order in storeOrders"
            :key="order.id"
            style="background:#111;border:1px solid #1e1e1e;padding:20px 24px;"
          >
            <div style="display:flex;align-items:flex-start;justify-content:space-between;flex-wrap:wrap;gap:12px;">
              <div>
                <div style="font-size:13px;font-weight:700;letter-spacing:1px;color:#fff;margin-bottom:4px;">
                  #{{ order.reference }}
                </div>
                <div style="font-size:10px;color:#555;letter-spacing:1px;margin-bottom:8px;">
                  {{ order.city }} · {{ order.phone }}
                </div>
                <div style="display:flex;align-items:center;gap:12px;">
                  <BaseBadge :variant="order.status" :text="statusLabel(order.status)" />
                  <span style="font-size:12px;color:#aaa;">{{ formatPrice(order.total) }}</span>
                </div>
              </div>

              <div style="display:flex;gap:8px;flex-wrap:wrap;">
                <button
                  v-if="order.status === 'pending'"
                  @click="updateOrderStatus(order.id, 'confirmed')"
                  style="padding:8px 16px;background:#fff;color:#000;border:none;font-family:inherit;font-size:9px;letter-spacing:2px;font-weight:700;cursor:pointer;"
                >CONFIRM</button>
                <button
                  v-if="order.status === 'confirmed'"
                  @click="updateOrderStatus(order.id, 'delivery')"
                  style="padding:8px 16px;background:none;border:1px solid #3a7;color:#3a7;font-family:inherit;font-size:9px;letter-spacing:2px;font-weight:700;cursor:pointer;"
                >MARK DELIVERY</button>
                <button
                  v-if="order.status === 'pending' || order.status === 'confirmed'"
                  @click="updateOrderStatus(order.id, 'rejected')"
                  style="padding:8px 16px;background:none;border:1px solid #e55;color:#e55;font-family:inherit;font-size:9px;letter-spacing:2px;font-weight:700;cursor:pointer;"
                >REJECT</button>
              </div>
            </div>

            <!-- Order items -->
            <div v-if="order.items && order.items.length" style="margin-top:14px;padding-top:14px;border-top:1px solid #1e1e1e;">
              <div
                v-for="item in order.items"
                :key="item.id"
                style="display:flex;align-items:center;gap:12px;padding:6px 0;font-size:12px;color:#aaa;"
              >
                <span style="color:#777;">{{ item.quantity }}×</span>
                <span>{{ item.product?.name || '—' }}</span>
                <span style="color:#555;">{{ item.size }}</span>
                <span style="margin-left:auto;color:#ddd;">{{ formatPrice(item.unit_price * item.quantity) }}</span>
              </div>
            </div>
          </div>
        </div>
      </div>

    </main>

    <!-- PRODUCT DRAWER OVERLAY -->
    <Teleport to="body">
      <div
        v-if="drawerOpen"
        @click.self="closeProdDrawer"
        style="position:fixed;inset:0;background:rgba(0,0,0,.7);z-index:200;display:flex;justify-content:flex-end;transition:opacity .3s;"
        :style="drawerOverlay ? 'opacity:1;' : 'opacity:0;'"
      >
        <div
          style="width:420px;max-width:100vw;height:100vh;background:#0f0f0f;border-left:1px solid #222;overflow-y:auto;padding:36px 28px;box-sizing:border-box;display:flex;flex-direction:column;gap:18px;transition:transform .3s;"
          :style="drawerOverlay ? 'transform:translateX(0);' : 'transform:translateX(100%);'"
        >
          <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:8px;">
            <div style="font-size:11px;letter-spacing:3px;color:#aaa;">
              {{ pdId ? 'EDIT PRODUCT' : 'ADD PRODUCT' }}
            </div>
            <button
              @click="closeProdDrawer"
              style="background:none;border:none;color:#555;font-size:20px;cursor:pointer;line-height:1;"
            >✕</button>
          </div>

          <!-- Name -->
          <div>
            <label style="display:block;font-size:9px;letter-spacing:2px;color:#555;margin-bottom:7px;">NAME *</label>
            <input
              v-model="pdName"
              type="text"
              style="width:100%;background:#111;border:1px solid #333;color:#fff;padding:11px 13px;font-family:inherit;font-size:13px;outline:none;box-sizing:border-box;"
              placeholder="Product name"
            />
          </div>

          <!-- Description -->
          <div>
            <label style="display:block;font-size:9px;letter-spacing:2px;color:#555;margin-bottom:7px;">DESCRIPTION</label>
            <textarea
              v-model="pdDesc"
              rows="3"
              style="width:100%;background:#111;border:1px solid #333;color:#fff;padding:11px 13px;font-family:inherit;font-size:13px;outline:none;resize:vertical;box-sizing:border-box;"
              placeholder="Product description"
            ></textarea>
          </div>

          <!-- Price & Stock -->
          <div style="display:flex;gap:12px;">
            <div style="flex:1;">
              <label style="display:block;font-size:9px;letter-spacing:2px;color:#555;margin-bottom:7px;">PRICE (DA) *</label>
              <input
                v-model.number="pdPrice"
                type="number"
                min="0"
                style="width:100%;background:#111;border:1px solid #333;color:#fff;padding:11px 13px;font-family:inherit;font-size:13px;outline:none;box-sizing:border-box;"
                placeholder="0"
              />
            </div>
            <div style="flex:1;">
              <label style="display:block;font-size:9px;letter-spacing:2px;color:#555;margin-bottom:7px;">STOCK *</label>
              <input
                v-model.number="pdStock"
                type="number"
                min="0"
                style="width:100%;background:#111;border:1px solid #333;color:#fff;padding:11px 13px;font-family:inherit;font-size:13px;outline:none;box-sizing:border-box;"
                placeholder="0"
              />
            </div>
          </div>

          <!-- Category -->
          <div>
            <label style="display:block;font-size:9px;letter-spacing:2px;color:#555;margin-bottom:7px;">CATEGORY</label>
            <select
              v-model="pdCategory"
              style="width:100%;background:#111;border:1px solid #333;color:#fff;padding:11px 13px;font-family:inherit;font-size:13px;outline:none;box-sizing:border-box;cursor:pointer;"
            >
              <option v-for="cat in CATEGORIES" :key="cat.value" :value="cat.value">{{ cat.label }}</option>
            </select>
          </div>

          <!-- Status -->
          <div>
            <label style="display:block;font-size:9px;letter-spacing:2px;color:#555;margin-bottom:7px;">STATUS</label>
            <select
              v-model="pdStatus"
              style="width:100%;background:#111;border:1px solid #333;color:#fff;padding:11px 13px;font-family:inherit;font-size:13px;outline:none;box-sizing:border-box;cursor:pointer;"
            >
              <option value="active">ACTIVE</option>
              <option value="hidden">HIDDEN</option>
              <option value="out_of_stock">OUT OF STOCK</option>
            </select>
          </div>

          <!-- Sizes -->
          <div>
            <label style="display:block;font-size:9px;letter-spacing:2px;color:#555;margin-bottom:7px;">SIZES (comma separated)</label>
            <input
              v-model="pdSizes"
              type="text"
              style="width:100%;background:#111;border:1px solid #333;color:#fff;padding:11px 13px;font-family:inherit;font-size:13px;outline:none;box-sizing:border-box;"
              placeholder="S,M,L,XL"
            />
          </div>

          <!-- Image URLs -->
          <div>
            <label style="display:block;font-size:9px;letter-spacing:2px;color:#555;margin-bottom:7px;">IMAGE URLS (one per line)</label>
            <textarea
              v-model="pdImages"
              rows="4"
              style="width:100%;background:#111;border:1px solid #333;color:#fff;padding:11px 13px;font-family:inherit;font-size:13px;outline:none;resize:vertical;box-sizing:border-box;"
              placeholder="https://..."
            ></textarea>
          </div>

          <!-- Actions -->
          <div style="display:flex;gap:10px;padding-top:8px;">
            <button
              @click="saveProd"
              :disabled="savingProduct"
              style="flex:1;padding:13px;background:#fff;color:#000;border:none;font-family:inherit;font-size:10px;letter-spacing:2px;font-weight:700;cursor:pointer;transition:opacity .2s;"
              :style="savingProduct ? 'opacity:.5;cursor:not-allowed;' : ''"
            >
              {{ savingProduct ? 'SAVING...' : (pdId ? 'UPDATE' : 'CREATE') }}
            </button>
            <button
              @click="closeProdDrawer"
              style="padding:13px 20px;background:none;border:1px solid #333;color:#777;font-family:inherit;font-size:10px;letter-spacing:2px;cursor:pointer;"
            >CANCEL</button>
          </div>
        </div>
      </div>
    </Teleport>

    <!-- CONFIRM DELETE DIALOG -->
    <ConfirmDialog
      :show="confirmDelete"
      title="DELETE PRODUCT"
      message="Are you sure you want to delete this product? This cannot be undone."
      confirm-text="DELETE"
      cancel-text="CANCEL"
      :danger="true"
      @confirm="deleteProduct"
      @cancel="confirmDelete = false"
    />

  </div>
</template>
