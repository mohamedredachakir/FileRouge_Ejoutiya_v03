<script setup lang="ts">
import { ref, onMounted } from 'vue'
import { useRouter } from 'vue-router'
import { useAuthStore } from '../../stores/auth'
import { useAdminStore } from '../../stores/admin'
import { useUiStore } from '../../stores/ui'
import BaseBadge from '../../components/base/BaseBadge.vue'
import ConfirmDialog from '../../components/base/ConfirmDialog.vue'

const router = useRouter()
const auth = useAuthStore()
const admin = useAdminStore()
const ui = useUiStore()

const activeTab = ref('users')
const confirmAction = ref<{ type: string; id: number; label: string } | null>(null)

onMounted(async () => {
  if (!auth.isAdmin) { router.push('/'); return }
  await Promise.all([admin.fetchUsers(), admin.fetchStores()])
})

async function setTab(tab: string) { activeTab.value = tab }

function openStorePreview(id: number) {
  router.push({ name: 'store-detail', params: { id } })
}

function askAction(type: string, id: number, label: string) {
  confirmAction.value = { type, id, label }
}

async function doAction() {
  if (!confirmAction.value) return
  const { type, id } = confirmAction.value
  try {
    if (type === 'ban') await admin.banUser(id)
    else if (type === 'unban') await admin.unbanUser(id)
    else if (type === 'approve') await admin.approveStore(id)
    else if (type === 'suspend') await admin.suspendStore(id)
    else if (type === 'reject') await admin.rejectStore(id)
    ui.showToast('ACTION COMPLETED.')
  } catch {
    ui.showToast('ACTION FAILED.', 'error')
  } finally {
    confirmAction.value = null
  }
}

function formatDate(d: string) {
  return new Date(d).toLocaleDateString('en-GB', { day: '2-digit', month: 'short', year: 'numeric' })
}
function statusLabel(s: string) { return s.replace(/_/g, ' ').toUpperCase() }
</script>

<template>
  <div>
    <div class="admin-wrap">
      <!-- Sidebar -->
      <div class="admin-sidebar">
        <div style="padding:20px 24px;border-bottom:1px solid var(--color-border-1);font-family:'Space Mono',monospace;font-size:11px;letter-spacing:.14em;text-transform:uppercase;color:var(--color-text-dim)">
          ADMIN PANEL
        </div>
        <button class="admin-nav-item" :class="{ on: activeTab === 'users' }" @click="setTab('users')">
          <span style="margin-right:8px">◎</span>USERS
        </button>
        <button class="admin-nav-item" :class="{ on: activeTab === 'stores' }" @click="setTab('stores')">
          <span style="margin-right:8px">▣</span>STORES
        </button>
      </div>

      <!-- Content -->
      <div class="admin-content">

        <!-- USERS -->
        <div v-if="activeTab === 'users'">
          <div class="dash-hdr">
            <div class="dash-h">USER MANAGEMENT</div>
          </div>
          <div v-if="admin.loading">
            <div class="skeleton" style="height:200px" />
          </div>
          <div v-else-if="admin.users.length">
            <table class="prod-table">
              <thead>
                <tr><th>NAME</th><th>EMAIL</th><th>PHONE</th><th>CITY</th><th>ROLE</th><th>STATUS</th><th>JOINED</th><th></th></tr>
              </thead>
              <tbody>
                <tr v-for="u in admin.users" :key="u.id">
                  <td style="font-family:'Space Mono',monospace;font-size:10px;text-transform:uppercase;color:var(--color-text)">{{ u.name }}</td>
                  <td style="font-size:11px">{{ u.email }}</td>
                  <td style="font-size:11px">{{ u.phone || '—' }}</td>
                  <td style="font-size:11px;text-transform:uppercase">{{ u.city || '—' }}</td>
                  <td>
                    <BaseBadge :variant="u.role === 'admin' ? 'confirmed' : u.role === 'store_owner' ? 'active' : 'hidden'" :text="u.role.replace('_', ' ').toUpperCase()" />
                  </td>
                  <td>
                    <BaseBadge :variant="u.is_banned ? 'rejected' : 'active'" :text="u.is_banned ? 'BANNED' : 'ACTIVE'" />
                  </td>
                  <td style="font-size:11px;color:var(--color-text-dim)">{{ formatDate(u.created_at) }}</td>
                  <td>
                    <button
                      v-if="!u.is_banned"
                      class="tbl-btn del"
                      @click="askAction('ban', u.id, 'BAN ' + u.name)"
                    >BAN</button>
                    <button
                      v-else
                      class="tbl-btn"
                      @click="askAction('unban', u.id, 'UNBAN ' + u.name)"
                    >UNBAN</button>
                  </td>
                </tr>
              </tbody>
            </table>
          </div>
          <div v-else class="empty-state">
            <div class="empty-icon">◎</div>
            <div class="empty-title">NO USERS</div>
          </div>
        </div>

        <!-- STORES -->
        <div v-if="activeTab === 'stores'">
          <div class="dash-hdr">
            <div class="dash-h">STORE MANAGEMENT</div>
          </div>
          <div v-if="admin.loading">
            <div class="skeleton" style="height:200px" />
          </div>
          <div v-else-if="admin.stores.length">
            <table class="prod-table">
              <thead>
                <tr><th>STORE</th><th>OWNER</th><th>STATUS</th><th>PRODUCTS</th><th></th></tr>
              </thead>
              <tbody>
                <tr v-for="s in admin.stores" :key="s.id">
                  <td style="font-family:'Space Mono',monospace;font-size:10px;text-transform:uppercase;color:var(--color-text)">{{ s.store_name }}</td>
                  <td style="font-size:11px">{{ s.owner?.name || '—' }}</td>
                  <td><BaseBadge :variant="s.status" :text="statusLabel(s.status)" /></td>
                  <td style="font-family:'Space Mono',monospace;font-size:11px">{{ s.products_count || 0 }}</td>
                  <td style="white-space:nowrap">
                    <button
                      class="tbl-btn"
                      @click="openStorePreview(s.id)"
                    >PREVIEW</button>
                    <button
                      v-if="s.status === 'pending_approval'"
                      class="tbl-btn"
                      @click="askAction('approve', s.id, 'APPROVE ' + s.store_name)"
                    >APPROVE</button>
                    <button
                      v-if="s.status === 'active'"
                      class="tbl-btn del"
                      @click="askAction('suspend', s.id, 'SUSPEND ' + s.store_name)"
                    >SUSPEND</button>
                    <button
                      v-if="s.status !== 'rejected'"
                      class="tbl-btn del"
                      @click="askAction('reject', s.id, 'REJECT ' + s.store_name)"
                    >REJECT</button>
                  </td>
                </tr>
              </tbody>
            </table>
          </div>
          <div v-else class="empty-state">
            <div class="empty-icon">▣</div>
            <div class="empty-title">NO STORES</div>
          </div>
        </div>

      </div>
    </div>

    <!-- Confirm Action -->
    <ConfirmDialog
      :show="!!confirmAction"
      title="CONFIRM ACTION"
      :message="'Are you sure you want to ' + (confirmAction?.label || '') + '?'"
      confirm-text="CONFIRM"
      :danger="true"
      @confirm="doAction"
      @cancel="confirmAction = null"
    />
  </div>
</template>
