export interface User {
  id: number
  name: string
  email: string
  phone?: string
  city?: string
  role: 'client' | 'store_owner' | 'admin'
  is_banned: boolean
  created_at: string
}

export interface ProductImage {
  id: number
  url: string
  sort_order: number
}

export interface Store {
  id: number
  name: string
  bio: string
  logo_url?: string
  hero_image_url?: string
  status: 'active' | 'pending_approval' | 'suspended' | 'rejected'
  products_count?: number
  owner?: User
  owner_id?: number
}

export interface Product {
  id: number
  name: string
  description?: string
  price: number
  original_price?: number
  stock: number
  category: 't_shirt' | 'hoodie' | 'pants' | 'sneakers' | 'accessories'
  status: 'active' | 'hidden' | 'out_of_stock'
  main_image_url?: string
  images?: ProductImage[]
  sizes: string[]
  store?: Store
  store_id?: number
  is_new?: boolean
}

export interface CartItem {
  product_id: number
  product_name: string
  product_price: number
  product_category: string
  main_image_url?: string
  store_name: string
  size: string
  qty: number
}

export interface OrderItem {
  id: number
  product: Product
  size: string
  quantity: number
  unit_price: number
}

export interface Order {
  id: number
  reference: string
  status: 'pending' | 'confirmed' | 'delivery' | 'rejected'
  total: number
  phone: string
  city: string
  postal_code?: string
  address: string
  items: OrderItem[]
  store?: Store
  user?: User
  created_at: string
}

export interface ApiResponse<T> {
  data: T
  message?: string
  errors?: Record<string, string[]>
}

export interface PaginatedResponse<T> {
  data: T[]
  meta: {
    current_page: number
    last_page: number
    per_page: number
    total: number
  }
}

export type ProductCategory = 't_shirt' | 'hoodie' | 'pants' | 'sneakers' | 'accessories'
export const CATEGORY_LABELS: Record<string, string> = {
  t_shirt: 'T-SHIRTS',
  hoodie: 'HOODIES',
  pants: 'PANTS',
  sneakers: 'SNEAKERS',
  accessories: 'ACCESSORIES',
}
export const SIZES = ['XS', 'S', 'M', 'L', 'XL', 'XXL', 'ONE SIZE']
