import { createRouter, createWebHistory } from 'vue-router'
import { useAuthStore } from '../stores/auth'

declare module 'vue-router' {
  interface RouteMeta {
    requiresAuth?: boolean
    role?: 'client' | 'store_owner' | 'admin'
    transition?: string
  }
}

const router = createRouter({
  history: createWebHistory(import.meta.env.BASE_URL),
  scrollBehavior(to, _from, savedPosition) {
    if (savedPosition) return savedPosition
    return { top: 0 }
  },
  routes: [
    {
      path: '/',
      name: 'landing',
      component: () => import('../modules/landing/LandingView.vue'),
      meta: { transition: 'page-fade' },
    },
    {
      path: '/products',
      name: 'products',
      component: () => import('../modules/catalog/ProductsView.vue'),
      meta: { transition: 'page-fade' },
    },
    {
      path: '/products/:id',
      name: 'product-detail',
      component: () => import('../modules/catalog/ProductDetailView.vue'),
      meta: { transition: 'page-slide-left' },
    },
    {
      path: '/stores',
      name: 'stores',
      component: () => import('../modules/stores/StoresView.vue'),
      meta: { transition: 'page-fade' },
    },
    {
      path: '/stores/:id',
      name: 'store-detail',
      component: () => import('../modules/stores/StoreDetailView.vue'),
      meta: { transition: 'page-fade' },
    },
    {
      path: '/profile',
      name: 'profile',
      component: () => import('../modules/profile/ProfileView.vue'),
      meta: { requiresAuth: true, role: 'client', transition: 'page-fade' },
    },
    {
      path: '/dashboard',
      name: 'dashboard',
      component: () => import('../modules/store-dashboard/StoreDashboardView.vue'),
      meta: { requiresAuth: true, role: 'store_owner', transition: 'page-fade' },
    },
    {
      path: '/admin',
      name: 'admin',
      component: () => import('../modules/admin/AdminView.vue'),
      meta: { requiresAuth: true, role: 'admin', transition: 'page-fade' },
    },
  ],
})

router.beforeEach(async (to, _from, next) => {
  const auth = useAuthStore()

  if (auth.token && !auth.user) {
    await auth.fetchMe()
  }

  if (to.meta.requiresAuth) {
    if (!auth.isAuthenticated) {
      const { useUiStore } = await import('../stores/ui')
      const ui = useUiStore()
      ui.openModal('login')
      return next({ name: 'landing' })
    }
    if (to.meta.role && auth.user?.role !== to.meta.role) {
      return next({ name: 'landing' })
    }
  }

  next()
})

export default router
