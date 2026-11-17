<template>
  <header id="page-topbar">
    <div class="layout-width">
      <div class="navbar-header">
        <div class="d-flex align-items-center">
          <!-- Logo Brand Box (Responsive) -->
          <div class="navbar-brand-box horizontal-logo me-2">
            <router-link to="/dashboard" class="logo logo-dark">
              <span class="logo-sm">
                <i class="mdi mdi-store text-primary fs-22"></i>
              </span>
              <span class="logo-lg text-primary fw-bold fs-18">
                <i class="mdi mdi-store me-1"></i> Multi-POS
              </span>
            </router-link>
          </div>

          <!-- Hamburger Button to collapse/expand Sidebar -->
          <button
            type="button"
            class="btn btn-sm px-3 fs-18 header-item vertical-menu-btn topnav-hamburger"
            @click="$emit('toggle-sidebar')"
            title="Sembunyikan/Tampilkan Menu"
          >
            <i class="mdi mdi-menu"></i>
          </button>

          <!-- Current Active Branch Indicator -->
          <div class="ms-3 d-none d-sm-flex align-items-center">
            <span class="badge bg-light text-dark border fs-12 px-3 py-2 d-flex align-items-center gap-1 shadow-sm">
              <i class="mdi mdi-map-marker text-danger fs-14"></i>
              <span class="fw-semibold">Lokasi:</span>
              <span class="text-primary fw-bold">
                {{ authStore.user?.branch ? authStore.user.branch.name + ' (' + authStore.user.branch.code + ')' : 'Pusat (Akses Global)' }}
              </span>
            </span>
          </div>
        </div>

        <!-- Right Side Header Icons -->
        <div class="d-flex align-items-center">

          <!-- Shortcut Button ke POS Kasir (Khusus Kasir & Owner) -->
          <router-link
            v-if="authStore.isSuperAdmin || authStore.isBranchCashier"
            to="/pos"
            class="btn btn-sm btn-success me-2 d-none d-md-inline-flex align-items-center gap-1 shadow-sm fw-semibold"
          >
            <i class="mdi mdi-calculator fs-15"></i>
            <span>Buka POS Kasir</span>
          </router-link>

          <!-- Safety Stock Alert Bell -->
          <div class="dropdown topbar-head-dropdown ms-1 header-item">
            <router-link
              to="/alerts/low-stock"
              class="btn btn-icon btn-topbar btn-ghost-secondary rounded-circle position-relative"
              title="Peringatan Stok Menipis"
            >
              <i class="mdi mdi-bell-outline fs-22"></i>
              <!-- Indikator alert stok rendah -->
              <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger fs-10">
                3
                <span class="visually-hidden">Stok menipis</span>
              </span>
            </router-link>
          </div>

          <!-- User Profile Dropdown -->
          <div class="dropdown ms-sm-3 header-item">
            <button
              type="button"
              class="btn shadow-none"
              id="page-header-user-dropdown"
              data-bs-toggle="dropdown"
              aria-haspopup="true"
              aria-expanded="false"
            >
              <span class="d-flex align-items-center">
                <span class="avatar-title bg-primary-subtle text-primary rounded-circle fs-16 p-2 me-2">
                  <i class="mdi mdi-account"></i>
                </span>
                <span class="text-start d-none d-xl-inline-block">
                  <span class="d-block fw-semibold user-name-text fs-14 lh-1">{{ authStore.user?.name }}</span>
                  <small class="text-muted fs-11">{{ authStore.roleLabel }}</small>
                </span>
                <i class="mdi mdi-chevron-down fs-14 ms-2 text-muted"></i>
              </span>
            </button>
            <div class="dropdown-menu dropdown-menu-end shadow-sm">
              <h6 class="dropdown-header">Halo, {{ authStore.user?.name }}</h6>
              <div class="dropdown-divider"></div>
              <router-link to="/dashboard" class="dropdown-item">
                <i class="mdi mdi-view-dashboard-outline fs-16 align-middle me-2 text-primary"></i>
                <span>Dashboard</span>
              </router-link>
              <div class="dropdown-divider"></div>
              <button class="dropdown-item text-danger" @click="handleLogout">
                <i class="mdi mdi-logout fs-16 align-middle me-2"></i>
                <span>Keluar (Logout)</span>
              </button>
            </div>
          </div>

        </div>
      </div>
    </div>
  </header>
</template>

<script setup>
import { useRouter } from 'vue-router';
import { useAuthStore } from '../../stores/auth';

defineEmits(['toggle-sidebar']);

const router = useRouter();
const authStore = useAuthStore();

async function handleLogout() {
  await authStore.logout();
  router.push('/login');
}
</script>

<style scoped>
.user-name-text {
  max-width: 140px;
  overflow: hidden;
  text-overflow: ellipsis;
  white-space: nowrap;
}
</style>
