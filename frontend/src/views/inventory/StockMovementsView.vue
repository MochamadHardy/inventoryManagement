<template>
  <AdminLayout>
    <!-- Page Header Title -->
    <div class="row mb-3">
      <div class="col-12">
        <div class="d-flex flex-wrap align-items-center justify-content-between gap-2">
          <div>
            <h4 class="fs-18 mb-1 fw-bold text-dark">Kartu Stok & Audit Trail Mutasi (Stock Ledger)</h4>
            <p class="text-muted mb-0 fs-13">Pencatatan riwayat kronologis setiap perubahan saldo stok secara mendetail dan akuntabel.</p>
          </div>
          <button class="btn btn-outline-secondary btn-sm d-flex align-items-center gap-1" @click="fetchMovements">
            <i class="mdi mdi-refresh fs-14"></i>
            <span>Segarkan Data</span>
          </button>
        </div>
      </div>
    </div>

    <!-- Filter Bar -->
    <div class="card shadow-sm border-0 rounded-3 mb-3">
      <div class="card-body p-3">
        <div class="row g-2 align-items-center">
          <div class="col-md-4">
            <select
              class="form-select"
              v-model="selectedBranch"
              @change="fetchMovements"
              :disabled="authStore.isBranchCashier"
            >
              <option v-if="!authStore.isBranchCashier" value="">Semua Cabang & Gudang</option>
              <option v-for="b in branches" :key="b.id" :value="b.id">
                {{ b.name }} ({{ b.code }})
              </option>
            </select>
          </div>
          <div class="col-md-4">
            <select class="form-select" v-model="selectedType" @change="fetchMovements">
              <option value="">Semua Jenis Pergerakan Stok</option>
              <option value="in">Barang Masuk / Saldo Awal (In)</option>
              <option value="transfer_out">Mutasi Keluar Surat Jalan (Transfer Out)</option>
              <option value="transfer_in">Mutasi Masuk Surat Jalan (Transfer In)</option>
              <option value="sale">Penjualan Kasir POS (Sale)</option>
              <option value="adjustment">Penyesuaian / Stok Opname</option>
            </select>
          </div>
          <div class="col-md-4 text-md-end text-muted fs-13">
            Menampilkan: <strong>{{ movements.length }}</strong> Riwayat Terkini
          </div>
        </div>
      </div>
    </div>

    <!-- Tabel Kartu Stok -->
    <div class="card shadow-sm border-0 rounded-3">
      <div class="card-body p-0">
        <div class="table-responsive">
          <table class="table table-hover align-middle mb-0">
            <thead class="table-light fs-12 text-uppercase text-muted">
              <tr>
                <th style="width: 50px;" class="text-center">#</th>
                <th>Waktu Kejadian</th>
                <th>Lokasi Cabang</th>
                <th>Produk & Varian</th>
                <th class="text-center">Tipe Transaksi</th>
                <th class="text-end">Mutasi</th>
                <th class="text-end">Saldo Awal</th>
                <th class="text-end">Saldo Akhir</th>
                <th>Keterangan / Dokumen</th>
                <th>Petugas</th>
              </tr>
            </thead>
            <tbody class="fs-13">
              <tr v-if="loading">
                <td colspan="10" class="text-center py-4 text-muted">
                  <div class="spinner-border spinner-border-sm text-primary me-2"></div> Memuat riwayat kartu stok...
                </td>
              </tr>
              <tr v-else-if="movements.length === 0">
                <td colspan="10" class="text-center py-4 text-muted">Belum ada riwayat pergerakan stok.</td>
              </tr>
              <tr v-for="(m, idx) in movements" :key="m.id">
                <td class="text-center fw-semibold text-muted">{{ idx + 1 }}</td>
                <td>
                  <div class="fw-semibold text-dark">{{ formatDateTime(m.created_at) }}</div>
                </td>
                <td>
                  <span class="badge bg-light text-dark border font-monospace fs-11">
                    {{ m.branch?.code }}
                  </span>
                  <small class="text-muted d-block">{{ m.branch?.name }}</small>
                </td>
                <td>
                  <strong class="text-dark">{{ m.variant?.product?.name }}</strong>
                  <div class="d-flex align-items-center gap-1">
                    <span class="badge bg-primary-subtle text-primary font-monospace fs-11">{{ m.variant?.sku }}</span>
                    <small class="text-muted">{{ m.variant?.variant_name }}</small>
                  </div>
                </td>
                <td class="text-center">
                  <span v-if="m.type === 'in'" class="badge bg-success-subtle text-success border border-success-subtle px-2 py-1">
                    <i class="mdi mdi-arrow-down-bold me-1"></i> Masuk (In)
                  </span>
                  <span v-else-if="m.type === 'transfer_out'" class="badge bg-warning-subtle text-warning border border-warning-subtle px-2 py-1">
                    <i class="mdi mdi-truck-delivery me-1"></i> Mutasi Keluar
                  </span>
                  <span v-else-if="m.type === 'transfer_in'" class="badge bg-info-subtle text-info border border-info-subtle px-2 py-1">
                    <i class="mdi mdi-truck-check me-1"></i> Mutasi Masuk
                  </span>
                  <span v-else-if="m.type === 'sale'" class="badge bg-danger-subtle text-danger border border-danger-subtle px-2 py-1">
                    <i class="mdi mdi-cart-arrow-right me-1"></i> Penjualan POS
                  </span>
                  <span v-else class="badge bg-secondary-subtle text-secondary px-2 py-1">
                    {{ m.type }}
                  </span>
                </td>
                <td class="text-end">
                  <span :class="['fw-bold', 'fs-14', Number(m.quantity) > 0 ? 'text-success' : 'text-danger']">
                    {{ Number(m.quantity) > 0 ? '+' : '' }}{{ Number(m.quantity) }}
                  </span>
                </td>
                <td class="text-end text-muted font-monospace">
                  {{ Number(m.stock_before) }}
                </td>
                <td class="text-end fw-bold font-monospace text-dark">
                  {{ Number(m.stock_after) }}
                </td>
                <td>
                  <div class="text-dark fs-12 text-truncate" style="max-width: 250px;">{{ m.notes || '-' }}</div>
                </td>
                <td>
                  <span class="text-muted fs-12">{{ m.user?.name || 'Sistem' }}</span>
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

const movements = ref([]);
const branches = ref([]);
const loading = ref(false);
const selectedBranch = ref(authStore.isBranchCashier ? authStore.user?.branch_id : '');
const selectedType = ref('');

async function fetchMovements() {
  loading.value = true;
  try {
    const params = {};
    if (selectedBranch.value) params.branch_id = selectedBranch.value;
    if (selectedType.value) params.type = selectedType.value;

    const res = await api.get('/inventory/movements', { params });
    movements.value = res.data.data;
  } catch (err) {
    console.error('Gagal mengambil kartu stok:', err);
  } finally {
    loading.value = false;
  }
}

async function fetchBranches() {
  try {
    const res = await api.get('/branches');
    branches.value = res.data.data;
  } catch (err) {
    console.error('Gagal mengambil cabang:', err);
  }
}

function formatDateTime(d) {
  if (!d) return '-';
  const date = new Date(d);
  return date.toLocaleString('id-ID', {
    day: 'numeric',
    month: 'short',
    year: 'numeric',
    hour: '2-digit',
    minute: '2-digit',
  });
}

onMounted(() => {
  fetchBranches();
  fetchMovements();
});
</script>
