<template>
  <AdminLayout>
    <!-- Page Header Title -->
    <div class="row mb-3">
      <div class="col-12">
        <div class="d-flex flex-wrap align-items-center justify-content-between gap-2">
          <div>
            <h4 class="fs-18 mb-1 fw-bold text-dark">Monitoring Stok Cabang & Gudang</h4>
            <p class="text-muted mb-0 fs-13">Pantau posisi stok fisik, stok yang sedang dalam perjalanan (Reserved), dan status batas aman secara real-time.</p>
          </div>
          <div class="d-flex gap-2">
            <router-link to="/transfers" class="btn btn-primary d-flex align-items-center gap-1 shadow-sm">
              <i class="mdi mdi-truck-fast-outline fs-16"></i>
              <span>Kelola Mutasi & Surat Jalan</span>
            </router-link>
          </div>
        </div>
      </div>
    </div>

    <!-- Filter Bar -->
    <div class="card shadow-sm border-0 rounded-3 mb-3">
      <div class="card-body p-3">
        <div class="row g-2 align-items-center">
          <div class="col-md-4">
            <div class="input-group">
              <span class="input-group-text bg-light border-end-0">
                <i class="mdi mdi-magnify text-muted fs-16"></i>
              </span>
              <input
                type="text"
                class="form-control border-start-0 ps-0"
                v-model="searchKeyword"
                @input="handleSearch"
                placeholder="Cari SKU, Barcode, atau nama produk..."
              />
            </div>
          </div>
          <div class="col-md-3">
            <select
              class="form-select"
              v-model="selectedBranch"
              @change="fetchStocks"
              :disabled="authStore.isBranchCashier"
            >
              <option v-if="!authStore.isBranchCashier" value="">Semua Cabang & Gudang</option>
              <option v-for="b in branches" :key="b.id" :value="b.id">
                {{ b.name }} ({{ b.code }})
              </option>
            </select>
          </div>
          <div class="col-md-3">
            <div class="form-check form-switch pt-2">
              <input
                class="form-check-input"
                type="checkbox"
                id="lowStockToggle"
                v-model="lowStockOnly"
                @change="fetchStocks"
              />
              <label class="form-check-label fw-semibold text-danger" for="lowStockToggle">
                Hanya Stok Menipis (&le; Batas Aman)
              </label>
            </div>
          </div>
          <div class="col-md-2 text-md-end text-muted fs-13">
            Total Record: <strong>{{ stocks.length }}</strong>
          </div>
        </div>
      </div>
    </div>

    <!-- Tabel Data Stok Fisik -->
    <div class="card shadow-sm border-0 rounded-3">
      <div class="card-body p-0">
        <div class="table-responsive">
          <table class="table table-hover align-middle mb-0">
            <thead class="table-light fs-12 text-uppercase text-muted">
              <tr>
                <th style="width: 50px;" class="text-center">#</th>
                <th>Lokasi Cabang</th>
                <th>SKU & Barcode</th>
                <th>Produk & Varian</th>
                <th class="text-center">Stok Fisik Tersedia</th>
                <th class="text-center">Stok Terkunci (In Transit)</th>
                <th class="text-center">Batas Aman</th>
                <th class="text-center">Status</th>
                <th style="width: 120px;" class="text-center">Aksi</th>
              </tr>
            </thead>
            <tbody class="fs-13">
              <tr v-if="loading">
                <td colspan="9" class="text-center py-4 text-muted">
                  <div class="spinner-border spinner-border-sm text-primary me-2"></div> Memuat data stok cabang...
                </td>
              </tr>
              <tr v-else-if="stocks.length === 0">
                <td colspan="9" class="text-center py-4 text-muted">Tidak ada data stok yang sesuai dengan filter.</td>
              </tr>
              <tr v-for="(s, idx) in stocks" :key="s.id">
                <td class="text-center fw-semibold text-muted">{{ idx + 1 }}</td>
                <td>
                  <div class="fw-bold text-dark">{{ s.branch?.name }}</div>
                  <span class="badge bg-light text-dark border font-monospace fs-11">{{ s.branch?.code }}</span>
                </td>
                <td>
                  <span class="font-monospace fw-bold text-primary">{{ s.variant?.sku }}</span>
                  <small class="text-muted d-block font-monospace">{{ s.variant?.barcode || '-' }}</small>
                </td>
                <td>
                  <div class="fw-semibold text-dark">{{ s.variant?.product?.name }}</div>
                  <small class="text-muted">Varian: {{ s.variant?.variant_name }}</small>
                </td>
                <td class="text-center">
                  <span :class="['fs-15', 'fw-bold', Number(s.quantity) <= Number(s.min_stock_alert) ? 'text-danger' : 'text-success']">
                    {{ formatNumber(s.quantity) }}
                  </span>
                </td>
                <td class="text-center">
                  <span v-if="Number(s.reserved_quantity) > 0" class="badge bg-warning text-dark px-2 py-1 fs-12">
                    <i class="mdi mdi-truck-fast me-1"></i> {{ formatNumber(s.reserved_quantity) }}
                  </span>
                  <span v-else class="text-muted">-</span>
                </td>
                <td class="text-center text-muted font-monospace">
                  {{ s.min_stock_alert }}
                </td>
                <td class="text-center">
                  <span v-if="Number(s.quantity) <= 0" class="badge bg-danger-subtle text-danger px-2 py-1">
                    Habis (Kosong)
                  </span>
                  <span v-else-if="Number(s.quantity) <= Number(s.min_stock_alert)" class="badge bg-danger text-white px-2 py-1">
                    <i class="mdi mdi-alert-circle me-1"></i> Menipis
                  </span>
                  <span v-else class="badge bg-success-subtle text-success px-2 py-1">
                    <i class="mdi mdi-check-circle me-1"></i> Aman
                  </span>
                </td>
                <td class="text-center">
                  <router-link
                    v-if="Number(s.quantity) <= Number(s.min_stock_alert)"
                    :to="{ path: '/transfers', query: { restock_variant: s.product_variant_id, target_branch: s.branch_id } }"
                    class="btn btn-sm btn-outline-warning text-dark py-1 px-2 fs-12"
                    title="Ajukan Pengiriman Restock dari Pusat"
                  >
                    <i class="mdi mdi-truck-delivery-outline me-1"></i> Minta Stok
                  </router-link>
                  <span v-else class="text-muted fs-12">-</span>
                </td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>
    </div>
  </AdminLayout>
</template>

<script setup>
import { onMounted, ref } from 'vue';
import api from '../../api/axios';
import AdminLayout from '../../layouts/AdminLayout.vue';
import { useAuthStore } from '../../stores/auth';

const authStore = useAuthStore();

const stocks = ref([]);
const branches = ref([]);
const loading = ref(false);
const searchKeyword = ref('');
const selectedBranch = ref(authStore.isBranchCashier ? authStore.user?.branch_id : '');
const lowStockOnly = ref(false);

let searchTimeout = null;
function handleSearch() {
  clearTimeout(searchTimeout);
  searchTimeout = setTimeout(() => {
    fetchStocks();
  }, 350);
}

async function fetchStocks() {
  loading.value = true;
  try {
    const params = {};
    if (searchKeyword.value) params.search = searchKeyword.value;
    if (selectedBranch.value) params.branch_id = selectedBranch.value;
    if (lowStockOnly.value) params.low_stock_only = 1;

    const res = await api.get('/inventory/stocks', { params });
    stocks.value = res.data.data;
  } catch (err) {
    console.error('Gagal mengambil data stok:', err);
  } finally {
    loading.value = false;
  }
}

async function fetchBranches() {
  try {
    const res = await api.get('/branches');
    branches.value = res.data.data;
  } catch (err) {
    console.error('Gagal mengambil daftar cabang:', err);
  }
}

function formatNumber(num) {
  return Number(num || 0).toLocaleString('id-ID');
}

onMounted(() => {
  fetchBranches();
  fetchStocks();
});
</script>
