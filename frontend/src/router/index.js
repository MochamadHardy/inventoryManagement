import { createRouter, createWebHistory } from 'vue-router';
import { useAuthStore } from '../stores/auth';

const routes = [
  {
    path: '/',
    redirect: '/dashboard',
  },
  {
    path: '/login',
    name: 'login',
    component: () => import('../views/auth/LoginView.vue'),
    meta: { guestOnly: true },
  },
  {
    path: '/dashboard',
    name: 'dashboard',
    component: () => import('../views/dashboard/DashboardView.vue'),
    meta: { requiresAuth: true },
  },
  {
    path: '/pos',
    name: 'pos',
    component: () => import('../views/pos/PosView.vue'),
    meta: { requiresAuth: true, roles: ['super_admin', 'branch_cashier'] },
  },

  // Modul Master Data
  {
    path: '/branches',
    name: 'branches',
    component: () => import('../views/master/BranchesView.vue'),
    meta: { requiresAuth: true, roles: ['super_admin'], title: 'Manajemen Data Cabang' },
  },
  {
    path: '/categories-units',
    name: 'categories-units',
    component: () => import('../views/master/CategoriesUnitsView.vue'),
    meta: { requiresAuth: true, roles: ['super_admin'], title: 'Kategori & Satuan Unit' },
  },
  {
    path: '/products',
    name: 'products',
    component: () => import('../views/master/ProductsView.vue'),
    meta: { requiresAuth: true, roles: ['super_admin', 'central_warehouse'], title: 'Master Produk & Varian' },
  },

  // Modul Inventaris & Mutasi
  {
    path: '/inventory',
    name: 'inventory',
    component: () => import('../views/inventory/InventoryStocksView.vue'),
    meta: { requiresAuth: true, title: 'Stok Cabang & Gudang' },
  },
  {
    path: '/transfers',
    name: 'transfers',
    component: () => import('../views/inventory/StockTransfersView.vue'),
    meta: { requiresAuth: true, title: 'Mutasi Stok & Surat Jalan' },
  },
  {
    path: '/alerts/low-stock',
    name: 'alerts-low-stock',
    component: () => import('../views/PlaceholderView.vue'),
    meta: { requiresAuth: true, title: 'Peringatan Stok Menipis (Safety Stock)', phase: 'Fase 6: Safety Stock Alert' },
  },
  {
    path: '/stock-movements',
    name: 'stock-movements',
    component: () => import('../views/inventory/StockMovementsView.vue'),
    meta: { requiresAuth: true, roles: ['super_admin', 'central_warehouse'], title: 'Kartu Stok & Audit Mutasi' },
  },

  // Modul Penjualan
  {
    path: '/sales',
    name: 'sales',
    component: () => import('../views/PlaceholderView.vue'),
    meta: { requiresAuth: true, roles: ['super_admin', 'branch_cashier'], title: 'Riwayat Transaksi Penjualan', phase: 'Fase 7: Modul POS' },
  },

  // Modul Laporan (Owner Only)
  {
    path: '/reports/sales',
    name: 'reports-sales',
    component: () => import('../views/PlaceholderView.vue'),
    meta: { requiresAuth: true, roles: ['super_admin'], title: 'Laporan Omzet Penjualan', phase: 'Fase 9: Laporan & Analitik' },
  },
  {
    path: '/reports/profit-loss',
    name: 'reports-profit-loss',
    component: () => import('../views/PlaceholderView.vue'),
    meta: { requiresAuth: true, roles: ['super_admin'], title: 'Analisis Laba Kotor (HPP vs Jual)', phase: 'Fase 9: Laporan & Analitik' },
  },

  // Modul Pengguna
  {
    path: '/users',
    name: 'users',
    component: () => import('../views/PlaceholderView.vue'),
    meta: { requiresAuth: true, roles: ['super_admin'], title: 'Manajemen Pengguna & Karyawan', phase: 'Fase 2 & Fase 4' },
  },

  // Error Pages
  {
    path: '/unauthorized',
    name: 'unauthorized',
    component: () => import('../views/UnauthorizedView.vue'),
    meta: { requiresAuth: true },
  },
  {
    path: '/:pathMatch(.*)*',
    redirect: '/dashboard',
  },
];

const router = createRouter({
  history: createWebHistory(),
  routes,
});

// Navigation Guards: Memproteksi rute berdasarkan status autentikasi dan batasan role
router.beforeEach(async (to, from, next) => {
  const authStore = useAuthStore();

  // Jika halaman butuh autentikasi dan user belum login
  if (to.meta.requiresAuth && !authStore.isAuthenticated) {
    return next({ name: 'login' });
  }

  // Jika halaman khusus tamu (seperti /login) dan user sudah login
  if (to.meta.guestOnly && authStore.isAuthenticated) {
    return next({ name: 'dashboard' });
  }

  // Jika rute memiliki batasan role tertentu
  if (to.meta.roles && Array.isArray(to.meta.roles)) {
    const userRole = authStore.user?.role;
    // Super admin selalu diizinkan, selain itu cek apakah role ada di daftar yang diizinkan
    if (userRole !== 'super_admin' && !to.meta.roles.includes(userRole)) {
      return next({ name: 'unauthorized' });
    }
  }

  next();
});

export default router;
