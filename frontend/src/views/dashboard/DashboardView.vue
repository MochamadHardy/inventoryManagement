<template>
  <AdminLayout>
    <!-- Page Header Title -->
    <div class="row mb-3 pb-1">
      <div class="col-12">
        <div class="d-flex align-items-lg-center flex-lg-row flex-column justify-content-between">
          <div class="flex-grow-1">
            <h4 class="fs-18 mb-1 fw-bold text-dark">
              Selamat Datang, {{ authStore.user?.name }}!
            </h4>
            <p class="text-muted mb-0 fs-13">
              Role: <span class="badge bg-primary-subtle text-primary fw-semibold">{{ authStore.roleLabel }}</span>
              • Cabang: <span class="fw-semibold text-dark">{{ authStore.user?.branch?.name || 'Kantor Pusat' }}</span>
            </p>
          </div>
          <div class="mt-3 mt-lg-0 d-flex gap-2">
            <router-link
              v-if="authStore.isSuperAdmin || authStore.isBranchCashier"
              to="/pos"
              class="btn btn-success d-flex align-items-center gap-1 shadow-sm"
            >
              <i class="mdi mdi-cash-register fs-16"></i>
              <span>Mulai Transaksi Kasir</span>
            </router-link>
            <router-link
              to="/transfers"
              class="btn btn-outline-primary d-flex align-items-center gap-1"
            >
              <i class="mdi mdi-truck-fast-outline fs-16"></i>
              <span>Mutasi Stok</span>
            </router-link>
          </div>
        </div>
      </div>
    </div>

    <!-- Quick Stat Cards (Invoika Template Style) -->
    <div class="row">
      <!-- Card 1: Produk -->
      <div class="col-xl-3 col-md-6 mb-4">
        <div class="card card-animate border-0 shadow-sm rounded-3 h-100">
          <div class="card-body">
            <div class="d-flex align-items-center justify-content-between">
              <div>
                <p class="text-uppercase fw-semibold text-muted text-truncate mb-2 fs-12">Total Master Produk</p>
                <h4 class="fs-22 fw-bold mb-0 text-dark">2 Produk</h4>
                <small class="text-muted fs-11">5 Varian SKU Aktif</small>
              </div>
              <div class="avatar-sm flex-shrink-0">
                <span class="avatar-title bg-primary-subtle text-primary rounded-circle fs-20 p-3">
                  <i class="mdi mdi-package-variant-closed"></i>
                </span>
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- Card 2: Cabang -->
      <div class="col-xl-3 col-md-6 mb-4">
        <div class="card card-animate border-0 shadow-sm rounded-3 h-100">
          <div class="card-body">
            <div class="d-flex align-items-center justify-content-between">
              <div>
                <p class="text-uppercase fw-semibold text-muted text-truncate mb-2 fs-12">Cabang & Gudang</p>
                <h4 class="fs-22 fw-bold mb-0 text-dark">3 Lokasi</h4>
                <small class="text-success fs-11"><i class="mdi mdi-check-circle me-1"></i>1 Pusat & 2 Cabang</small>
              </div>
              <div class="avatar-sm flex-shrink-0">
                <span class="avatar-title bg-info-subtle text-info rounded-circle fs-20 p-3">
                  <i class="mdi mdi-store-outline"></i>
                </span>
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- Card 3: Alert Stok Rendah -->
      <div class="col-xl-3 col-md-6 mb-4">
        <div class="card card-animate border-0 shadow-sm rounded-3 h-100">
          <div class="card-body">
            <div class="d-flex align-items-center justify-content-between">
              <div>
                <p class="text-uppercase fw-semibold text-muted text-truncate mb-2 fs-12">Safety Stock Alert</p>
                <h4 class="fs-22 fw-bold mb-0 text-danger">3 Item</h4>
                <small class="text-danger fs-11"><i class="mdi mdi-alert-circle me-1"></i>Cabang Bandung (&le; 10 pcs)</small>
              </div>
              <div class="avatar-sm flex-shrink-0">
                <span class="avatar-title bg-danger-subtle text-danger rounded-circle fs-20 p-3">
                  <i class="mdi mdi-alert-octagon-outline"></i>
                </span>
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- Card 4: Status Sistem -->
      <div class="col-xl-3 col-md-6 mb-4">
        <div class="card card-animate border-0 shadow-sm rounded-3 h-100">
          <div class="card-body">
            <div class="d-flex align-items-center justify-content-between">
              <div>
                <p class="text-uppercase fw-semibold text-muted text-truncate mb-2 fs-12">Status Backend</p>
                <h4 class="fs-22 fw-bold mb-0 text-success">Terhubung</h4>
                <small class="text-muted fs-11">Laravel 11 • Sanctum Active</small>
              </div>
              <div class="avatar-sm flex-shrink-0">
                <span class="avatar-title bg-success-subtle text-success rounded-circle fs-20 p-3">
                  <i class="mdi mdi-server-network"></i>
                </span>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Playground Pengujian RBAC (Fase 2 & Fase 3) -->
    <div class="row">
      <div class="col-12">
        <div class="card shadow-sm border-0 rounded-3 mb-4">
          <div class="card-header bg-white border-bottom py-3">
            <div class="d-flex align-items-center justify-content-between">
              <div>
                <h5 class="fw-bold mb-0 text-dark">
                  <i class="mdi mdi-shield-check text-success me-2 fs-18"></i>
                  Verifikasi Hak Akses (RBAC Playground)
                </h5>
                <small class="text-muted">Uji proteksi endpoint API backend langsung dari layout template Invoika ini.</small>
              </div>
              <span class="badge bg-primary text-white fs-12">Peran: {{ authStore.user?.role }}</span>
            </div>
          </div>
          <div class="card-body p-4">
            <div class="row g-3">
              <!-- Test 1: Owner Route -->
              <div class="col-md-4">
                <div class="card border border-primary-subtle h-100 p-3 bg-light-subtle rounded-3">
                  <h6 class="fw-bold text-primary mb-1">
                    <i class="mdi mdi-crown text-warning me-1"></i> Endpoint Khusus Owner
                  </h6>
                  <small class="text-muted d-block mb-3">GET /api/rbac-test/owner-only</small>
                  <button
                    class="btn btn-primary btn-sm w-100 py-2 fw-semibold"
                    @click="testEndpoint('/rbac-test/owner-only', 'Owner Endpoint')"
                    :disabled="isTesting"
                  >
                    Uji Akses Owner
                  </button>
                </div>
              </div>

              <!-- Test 2: Gudang Route -->
              <div class="col-md-4">
                <div class="card border border-info-subtle h-100 p-3 bg-light-subtle rounded-3">
                  <h6 class="fw-bold text-info mb-1">
                    <i class="mdi mdi-warehouse text-info me-1"></i> Endpoint Khusus Gudang
                  </h6>
                  <small class="text-muted d-block mb-3">GET /api/rbac-test/warehouse-only</small>
                  <button
                    class="btn btn-info text-white btn-sm w-100 py-2 fw-semibold"
                    @click="testEndpoint('/rbac-test/warehouse-only', 'Warehouse Endpoint')"
                    :disabled="isTesting"
                  >
                    Uji Akses Gudang
                  </button>
                </div>
              </div>

              <!-- Test 3: Kasir Route -->
              <div class="col-md-4">
                <div class="card border border-success-subtle h-100 p-3 bg-light-subtle rounded-3">
                  <h6 class="fw-bold text-success mb-1">
                    <i class="mdi mdi-cart-outline text-success me-1"></i> Endpoint Khusus Kasir
                  </h6>
                  <small class="text-muted d-block mb-3">GET /api/rbac-test/cashier-only</small>
                  <button
                    class="btn btn-success btn-sm w-100 py-2 fw-semibold"
                    @click="testEndpoint('/rbac-test/cashier-only', 'Cashier Endpoint')"
                    :disabled="isTesting"
                  >
                    Uji Akses Kasir
                  </button>
                </div>
              </div>
            </div>

            <!-- Output Panel Test -->
            <div v-if="testResult" class="mt-4">
              <div :class="['alert', testResult.isSuccess ? 'alert-success' : 'alert-danger', 'border-0', 'shadow-sm']">
                <div class="d-flex align-items-center mb-2">
                  <i :class="[testResult.isSuccess ? 'mdi mdi-check-circle' : 'mdi mdi-close-circle', 'fs-20 me-2']"></i>
                  <strong>Status: {{ testResult.status }} ({{ testResult.endpoint }})</strong>
                </div>
                <pre class="bg-dark text-white p-3 rounded-2 fs-12 mb-0 overflow-auto"><code>{{ testResult.data }}</code></pre>
              </div>
            </div>

          </div>
        </div>
      </div>
    </div>
  </AdminLayout>
</template>

<script setup>
import { ref } from 'vue';
import api from '../../api/axios';
import AdminLayout from '../../layouts/AdminLayout.vue';
import { useAuthStore } from '../../stores/auth';

const authStore = useAuthStore();
const isTesting = ref(false);
const testResult = ref(null);

async function testEndpoint(endpoint, label) {
  isTesting.value = true;
  testResult.value = null;
  try {
    const res = await api.get(endpoint);
    testResult.value = {
      isSuccess: true,
      status: `200 OK - Diizinkan`,
      endpoint: label,
      data: JSON.stringify(res.data, null, 2),
    };
  } catch (err) {
    testResult.value = {
      isSuccess: false,
      status: `${err.response?.status || 500} - Ditolak (Forbidden)`,
      endpoint: label,
      data: JSON.stringify(err.response?.data || { message: err.message }, null, 2),
    };
  } finally {
    isTesting.value = false;
  }
}
</script>
