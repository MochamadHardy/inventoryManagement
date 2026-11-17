<template>
  <div class="app-menu navbar-menu">
    <!-- Brand Logo -->
    <div class="navbar-brand-box">
      <router-link to="/dashboard" class="logo logo-dark d-flex align-items-center justify-content-center py-3">
        <span class="logo-sm">
          <i class="mdi mdi-store text-primary fs-24"></i>
        </span>
        <span class="logo-lg d-flex align-items-center gap-2">
          <i class="mdi mdi-store text-primary fs-24"></i>
          <span class="fw-bold fs-18 text-primary letter-spacing-1">INVENTORY POS</span>
        </span>
      </router-link>
    </div>

    <!-- Navigation Menu Items -->
    <div id="scrollbar" class="h-100 overflow-auto">
      <div class="container-fluid">
        <ul class="navbar-nav py-3" id="navbar-nav">

          <!-- SECTION: UTAMA -->
          <li class="menu-title"><span class="text-uppercase fs-11 fw-bold text-muted">Menu Utama</span></li>
          <li class="nav-item">
            <router-link to="/dashboard" class="nav-link menu-link" active-class="active">
              <i class="mdi mdi-view-dashboard-outline fs-18 me-2"></i>
              <span>Dashboard</span>
            </router-link>
          </li>

          <!-- SECTION: POINT OF SALE (KASIR CEPAT) -->
          <li v-if="authStore.isSuperAdmin || authStore.isBranchCashier" class="menu-title mt-2">
            <span class="text-uppercase fs-11 fw-bold text-muted">Point of Sale</span>
          </li>
          <li v-if="authStore.isSuperAdmin || authStore.isBranchCashier" class="nav-item">
            <router-link to="/pos" class="nav-link menu-link text-success fw-semibold" active-class="active">
              <i class="mdi mdi-calculator fs-18 me-2 text-success"></i>
              <span>Kasir Cepat (POS)</span>
            </router-link>
          </li>
          <li v-if="authStore.isSuperAdmin || authStore.isBranchCashier" class="nav-item">
            <router-link to="/sales" class="nav-link menu-link" active-class="active">
              <i class="mdi mdi-receipt-text-outline fs-18 me-2"></i>
              <span>Riwayat Penjualan</span>
            </router-link>
          </li>

          <!-- SECTION: INVENTARIS & LOGISTIK -->
          <li class="menu-title mt-2">
            <span class="text-uppercase fs-11 fw-bold text-muted">Inventaris & Stok</span>
          </li>
          <li class="nav-item">
            <router-link to="/inventory" class="nav-link menu-link" active-class="active">
              <i class="mdi mdi-warehouse fs-18 me-2"></i>
              <span>Stok Cabang</span>
            </router-link>
          </li>
          <li class="nav-item">
            <router-link to="/transfers" class="nav-link menu-link" active-class="active">
              <i class="mdi mdi-truck-fast-outline fs-18 me-2"></i>
              <span>Mutasi & Surat Jalan</span>
            </router-link>
          </li>
          <li class="nav-item">
            <router-link to="/alerts/low-stock" class="nav-link menu-link" active-class="active">
              <i class="mdi mdi-alert-octagon-outline fs-18 me-2 text-warning"></i>
              <span>Alert Stok Menipis</span>
            </router-link>
          </li>
          <li v-if="authStore.isSuperAdmin || authStore.isCentralWarehouse" class="nav-item">
            <router-link to="/stock-movements" class="nav-link menu-link" active-class="active">
              <i class="mdi mdi-file-document-outline fs-18 me-2"></i>
              <span>Kartu Stok (Audit)</span>
            </router-link>
          </li>

          <!-- SECTION: MASTER DATA (OWNER & GUDANG) -->
          <li v-if="authStore.isSuperAdmin || authStore.isCentralWarehouse" class="menu-title mt-2">
            <span class="text-uppercase fs-11 fw-bold text-muted">Master Data</span>
          </li>
          <li v-if="authStore.isSuperAdmin" class="nav-item">
            <router-link to="/branches" class="nav-link menu-link" active-class="active">
              <i class="mdi mdi-store-cog-outline fs-18 me-2"></i>
              <span>Data Cabang</span>
            </router-link>
          </li>
          <li v-if="authStore.isSuperAdmin" class="nav-item">
            <router-link to="/categories-units" class="nav-link menu-link" active-class="active">
              <i class="mdi mdi-tag-multiple-outline fs-18 me-2"></i>
              <span>Kategori & Satuan</span>
            </router-link>
          </li>
          <li v-if="authStore.isSuperAdmin || authStore.isCentralWarehouse" class="nav-item">
            <router-link to="/products" class="nav-link menu-link" active-class="active">
              <i class="mdi mdi-package-variant-closed fs-18 me-2"></i>
              <span>Produk & Varian</span>
            </router-link>
          </li>

          <!-- SECTION: LAPORAN & KEUANGAN (KHUSUS OWNER) -->
          <li v-if="authStore.isSuperAdmin" class="menu-title mt-2">
            <span class="text-uppercase fs-11 fw-bold text-muted">Laporan & Profit</span>
          </li>
          <li v-if="authStore.isSuperAdmin" class="nav-item">
            <router-link to="/reports/sales" class="nav-link menu-link" active-class="active">
              <i class="mdi mdi-chart-line fs-18 me-2"></i>
              <span>Laporan Omzet</span>
            </router-link>
          </li>
          <li v-if="authStore.isSuperAdmin" class="nav-item">
            <router-link to="/reports/profit-loss" class="nav-link menu-link" active-class="active">
              <i class="mdi mdi-cash-multiple fs-18 me-2"></i>
              <span>Laba Kotor & HPP</span>
            </router-link>
          </li>

          <!-- SECTION: MANAJEMEN SISTEM -->
          <li v-if="authStore.isSuperAdmin" class="menu-title mt-2">
            <span class="text-uppercase fs-11 fw-bold text-muted">Sistem</span>
          </li>
          <li v-if="authStore.isSuperAdmin" class="nav-item">
            <router-link to="/users" class="nav-link menu-link" active-class="active">
              <i class="mdi mdi-account-group-outline fs-18 me-2"></i>
              <span>Karyawan & Role</span>
            </router-link>
          </li>

        </ul>
      </div>
    </div>
  </div>
</template>

<script setup>
import { useAuthStore } from '../../stores/auth';

const authStore = useAuthStore();
</script>

<style scoped>
.navbar-nav .nav-link {
  display: flex;
  align-items: center;
  padding: 0.65rem 1.25rem;
  font-size: 0.9rem;
  color: #555b6d;
  transition: all 0.2s ease;
  border-radius: 0.35rem;
  margin: 0.15rem 0.75rem;
}

.navbar-nav .nav-link:hover,
.navbar-nav .nav-link.active {
  color: #405189;
  background-color: rgba(64, 81, 137, 0.08);
  font-weight: 600;
}

.menu-title {
  padding: 0.75rem 1.25rem 0.25rem;
  letter-spacing: 0.05em;
}
</style>
