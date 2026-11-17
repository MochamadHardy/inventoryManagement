<template>
  <AdminLayout>
    <!-- Page Header Title -->
    <div class="row mb-3">
      <div class="col-12">
        <div class="d-flex flex-wrap align-items-center justify-content-between gap-2">
          <div>
            <h4 class="fs-18 mb-1 fw-bold text-dark">Mutasi Stok & Surat Jalan Antar Cabang</h4>
            <p class="text-muted mb-0 fs-13">Alur logistik pengiriman barang: <strong>Pending</strong> &rarr; <strong>In Transit (Reserved)</strong> &rarr; <strong>Received (Selesai)</strong>.</p>
          </div>
          <button class="btn btn-primary d-flex align-items-center gap-1 shadow-sm" @click="openCreateModal">
            <i class="mdi mdi-plus-circle-outline fs-16"></i>
            <span>Buat Surat Jalan Baru</span>
          </button>
        </div>
      </div>
    </div>

    <!-- Alert Notifikasi -->
    <div v-if="alertMessage" :class="['alert', alertType === 'success' ? 'alert-success' : 'alert-danger', 'alert-dismissible', 'fade', 'show']" role="alert">
      <i :class="[alertType === 'success' ? 'mdi mdi-check-circle' : 'mdi mdi-alert-circle', 'me-2 fs-16']"></i>
      {{ alertMessage }}
      <button type="button" class="btn-close" @click="alertMessage = ''"></button>
    </div>

    <!-- Filter Status Tabs -->
    <div class="card shadow-sm border-0 rounded-3 mb-3">
      <div class="card-body p-2">
        <div class="d-flex flex-wrap align-items-center justify-content-between gap-2">
          <div class="btn-group" role="group">
            <button
              type="button"
              :class="['btn', 'btn-sm', statusFilter === '' ? 'btn-primary' : 'btn-light']"
              @click="setStatusFilter('')"
            >
              Semua Mutasi
            </button>
            <button
              type="button"
              :class="['btn', 'btn-sm', statusFilter === 'pending' ? 'btn-warning text-dark' : 'btn-light']"
              @click="setStatusFilter('pending')"
            >
              <i class="mdi mdi-clock-outline me-1"></i> Pending
            </button>
            <button
              type="button"
              :class="['btn', 'btn-sm', statusFilter === 'in_transit' ? 'btn-info text-white' : 'btn-light']"
              @click="setStatusFilter('in_transit')"
            >
              <i class="mdi mdi-truck-fast me-1"></i> In Transit
            </button>
            <button
              type="button"
              :class="['btn', 'btn-sm', statusFilter === 'received' ? 'btn-success' : 'btn-light']"
              @click="setStatusFilter('received')"
            >
              <i class="mdi mdi-check-circle me-1"></i> Selesai (Received)
            </button>
          </div>

          <div class="text-muted fs-13">
            Menampilkan: <strong>{{ transfers.length }}</strong> Dokumen Surat Jalan
          </div>
        </div>
      </div>
    </div>

    <!-- Tabel Surat Jalan -->
    <div class="card shadow-sm border-0 rounded-3">
      <div class="card-body p-0">
        <div class="table-responsive">
          <table class="table table-hover align-middle mb-0">
            <thead class="table-light fs-12 text-uppercase text-muted">
              <tr>
                <th style="width: 50px;" class="text-center">#</th>
                <th>No. Surat Jalan</th>
                <th>Rute Pengiriman (Asal &rarr; Tujuan)</th>
                <th>Tanggal</th>
                <th class="text-center">Total Item</th>
                <th class="text-center">Status</th>
                <th style="width: 170px;" class="text-center">Aksi / Tindakan</th>
              </tr>
            </thead>
            <tbody class="fs-13">
              <tr v-if="loading">
                <td colspan="7" class="text-center py-4 text-muted">
                  <div class="spinner-border spinner-border-sm text-primary me-2"></div> Memuat daftar surat jalan...
                </td>
              </tr>
              <tr v-else-if="transfers.length === 0">
                <td colspan="7" class="text-center py-4 text-muted">Belum ada dokumen mutasi yang sesuai filter.</td>
              </tr>
              <tr v-for="(t, idx) in transfers" :key="t.id">
                <td class="text-center fw-semibold text-muted">{{ idx + 1 }}</td>
                <td>
                  <span class="badge bg-light text-primary border font-monospace fs-13 px-2 py-1">
                    {{ t.transfer_number }}
                  </span>
                  <small class="text-muted d-block mt-1">Dibuat oleh: {{ t.creator?.name }}</small>
                </td>
                <td>
                  <div class="d-flex align-items-center gap-2">
                    <span class="fw-semibold text-dark">{{ t.from_branch?.name }}</span>
                    <i class="mdi mdi-arrow-right text-muted"></i>
                    <span class="fw-semibold text-primary">{{ t.to_branch?.name }}</span>
                  </div>
                  <small v-if="t.notes" class="text-muted d-block italic text-truncate" style="max-width: 280px;">
                    "{{ t.notes }}"
                  </small>
                </td>
                <td>
                  <div class="text-dark">{{ formatDate(t.transfer_date) }}</div>
                  <small v-if="t.received_date" class="text-success d-block">
                    Diterima: {{ formatDate(t.received_date) }}
                  </small>
                </td>
                <td class="text-center">
                  <span class="badge bg-light text-dark border px-2 py-1">
                    {{ t.items?.length || 0 }} Varian
                  </span>
                </td>
                <td class="text-center">
                  <span v-if="t.status === 'pending'" class="badge bg-warning-subtle text-warning border border-warning-subtle px-2 py-1 fs-12">
                    <i class="mdi mdi-clock-outline me-1"></i> Pending
                  </span>
                  <span v-else-if="t.status === 'in_transit'" class="badge bg-info-subtle text-info border border-info-subtle px-2 py-1 fs-12">
                    <i class="mdi mdi-truck-fast me-1"></i> In Transit
                  </span>
                  <span v-else-if="t.status === 'received'" class="badge bg-success-subtle text-success border border-success-subtle px-2 py-1 fs-12">
                    <i class="mdi mdi-check-all me-1"></i> Diterima
                  </span>
                  <span v-else class="badge bg-danger-subtle text-danger px-2 py-1 fs-12">
                    Ditolak
                  </span>
                </td>
                <td class="text-center">
                  <div class="d-flex justify-content-center gap-1">
                    <!-- Tombol Detail / Lihat Surat Jalan -->
                    <button class="btn btn-sm btn-outline-info" @click="openDetailModal(t)" title="Lihat Surat Jalan">
                      <i class="mdi mdi-file-document-outline"></i>
                    </button>

                    <!-- Tombol Kirim Barang (Khusus status pending & pengirim/owner) -->
                    <button
                      v-if="t.status === 'pending' && canSend(t)"
                      class="btn btn-sm btn-primary d-inline-flex align-items-center gap-1"
                      @click="sendTransfer(t)"
                      title="Kirim Barang Fisik (In Transit)"
                    >
                      <i class="mdi mdi-truck-fast"></i>
                      <span>Kirim</span>
                    </button>

                    <!-- Tombol Konfirmasi Terima (Khusus status in_transit & penerima/owner) -->
                    <button
                      v-if="t.status === 'in_transit' && canReceive(t)"
                      class="btn btn-sm btn-success d-inline-flex align-items-center gap-1"
                      @click="receiveTransfer(t)"
                      title="Konfirmasi Terima Barang di Cabang"
                    >
                      <i class="mdi mdi-check"></i>
                      <span>Terima</span>
                    </button>
                  </div>
                </td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>
    </div>

    <!-- MODAL 1: DETAIL SURAT JALAN & ITEM TRANSFER -->
    <div v-if="showDetailModal && selectedTransfer" class="modal fade show d-block" tabindex="-1" style="background-color: rgba(0,0,0,0.5);">
      <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content shadow-lg border-0 rounded-4">
          <div class="modal-header border-bottom py-3">
            <div>
              <h5 class="modal-title fw-bold text-dark">
                Surat Jalan: {{ selectedTransfer.transfer_number }}
              </h5>
              <small class="text-muted">Status: <strong class="text-uppercase">{{ selectedTransfer.status }}</strong></small>
            </div>
            <button type="button" class="btn-close" @click="showDetailModal = false"></button>
          </div>
          <div class="modal-body p-4">
            
            <!-- Dokumen Info Card -->
            <div class="row g-3 mb-4 bg-light p-3 rounded-3 fs-13">
              <div class="col-sm-6">
                <span class="text-muted d-block">Cabang Asal (Pengirim):</span>
                <strong class="text-dark fs-14">{{ selectedTransfer.from_branch?.name }}</strong>
                <small class="text-muted d-block">{{ selectedTransfer.from_branch?.address || '-' }}</small>
              </div>
              <div class="col-sm-6">
                <span class="text-muted d-block">Cabang Tujuan (Penerima):</span>
                <strong class="text-primary fs-14">{{ selectedTransfer.to_branch?.name }}</strong>
                <small class="text-muted d-block">{{ selectedTransfer.to_branch?.address || '-' }}</small>
              </div>
              <div class="col-sm-6">
                <span class="text-muted">Tanggal Dokumen:</span>
                <span class="fw-semibold ms-1">{{ formatDate(selectedTransfer.transfer_date) }}</span>
              </div>
              <div class="col-sm-6">
                <span class="text-muted">Petugas:</span>
                <span class="fw-semibold ms-1">{{ selectedTransfer.creator?.name }}</span>
              </div>
            </div>

            <!-- Rincian Barang Dikirim -->
            <h6 class="fw-bold text-dark mb-2">Daftar Barang dalam Surat Jalan</h6>
            <div class="table-responsive border rounded-3">
              <table class="table table-sm align-middle mb-0 fs-13">
                <thead class="table-light text-uppercase fs-11 text-muted">
                  <tr>
                    <th>SKU & Barcode</th>
                    <th>Nama Produk / Varian</th>
                    <th class="text-center" style="width: 120px;">Qty Dikirim</th>
                    <th class="text-center" style="width: 120px;">Qty Diterima</th>
                  </tr>
                </thead>
                <tbody>
                  <tr v-for="item in selectedTransfer.items" :key="item.id">
                    <td>
                      <span class="font-monospace fw-bold text-primary">{{ item.variant?.sku }}</span>
                      <small class="text-muted d-block font-monospace">{{ item.variant?.barcode || '-' }}</small>
                    </td>
                    <td>
                      <div class="fw-semibold text-dark">{{ item.variant?.product?.name }}</div>
                      <small class="text-muted">{{ item.variant?.variant_name }}</small>
                    </td>
                    <td class="text-center fw-bold fs-14 text-dark">
                      {{ Number(item.quantity_sent) }}
                    </td>
                    <td class="text-center fw-bold fs-14">
                      <span v-if="selectedTransfer.status === 'received'" class="text-success">
                        {{ Number(item.quantity_received) }}
                      </span>
                      <span v-else class="text-muted">-</span>
                    </td>
                  </tr>
                </tbody>
              </table>
            </div>

            <div v-if="selectedTransfer.notes" class="mt-3 fs-13">
              <span class="text-muted">Catatan Pengiriman:</span>
              <p class="mb-0 text-dark fst-italic">"{{ selectedTransfer.notes }}"</p>
            </div>

          </div>
          <div class="modal-footer bg-light border-top py-2 px-4">
            <button type="button" class="btn btn-secondary" @click="showDetailModal = false">Tutup</button>
            <button
              v-if="selectedTransfer.status === 'pending' && canSend(selectedTransfer)"
              type="button"
              class="btn btn-primary"
              @click="sendTransfer(selectedTransfer)"
            >
              <i class="mdi mdi-truck-fast me-1"></i> Kirim Barang Sekarang
            </button>
            <button
              v-if="selectedTransfer.status === 'in_transit' && canReceive(selectedTransfer)"
              type="button"
              class="btn btn-success"
              @click="receiveTransfer(selectedTransfer)"
            >
              <i class="mdi mdi-check-all me-1"></i> Konfirmasi Terima di Cabang
            </button>
          </div>
        </div>
      </div>
    </div>

    <!-- MODAL 2: FORM BUAT SURAT JALAN MUTASI BARU -->
    <div v-if="showCreateModal" class="modal fade show d-block" tabindex="-1" style="background-color: rgba(0,0,0,0.5);">
      <div class="modal-dialog modal-xl modal-dialog-centered modal-dialog-scrollable">
        <div class="modal-content shadow-lg border-0 rounded-4">
          <div class="modal-header border-bottom py-3">
            <div>
              <h5 class="modal-title fw-bold text-dark">Buat Dokumen Surat Jalan Transfer Baru</h5>
              <small class="text-muted">Pilih cabang asal pengirim, cabang tujuan, dan daftar barang yang akan dimutasi.</small>
            </div>
            <button type="button" class="btn-close" @click="showCreateModal = false"></button>
          </div>
          <form @submit.prevent="saveTransfer">
            <div class="modal-body p-4">

              <!-- Informasi Pengiriman -->
              <div class="row g-3 mb-4 bg-light p-3 rounded-3">
                <div class="col-md-4">
                  <label class="form-label fw-semibold">Cabang Asal (Pengirim) <span class="text-danger">*</span></label>
                  <select
                    class="form-select"
                    v-model="transferForm.from_branch_id"
                    @change="onSenderBranchChange"
                    required
                  >
                    <option v-for="b in branches" :key="b.id" :value="b.id">
                      {{ b.name }} ({{ b.code }})
                    </option>
                  </select>
                </div>
                <div class="col-md-4">
                  <label class="form-label fw-semibold">Cabang Tujuan (Penerima) <span class="text-danger">*</span></label>
                  <select class="form-select" v-model="transferForm.to_branch_id" required>
                    <option
                      v-for="b in branches"
                      :key="b.id"
                      :value="b.id"
                      :disabled="b.id === transferForm.from_branch_id"
                    >
                      {{ b.name }} ({{ b.code }})
                    </option>
                  </select>
                </div>
                <div class="col-md-4">
                  <label class="form-label fw-semibold">Tanggal Surat Jalan <span class="text-danger">*</span></label>
                  <input type="date" class="form-control" v-model="transferForm.transfer_date" required />
                </div>
                <div class="col-12">
                  <label class="form-label fw-semibold">Catatan / Instruksi Pengiriman</label>
                  <input
                    type="text"
                    class="form-control"
                    v-model="transferForm.notes"
                    placeholder="Misal: Dikirim dengan truk box inventaris ekspedisi #01..."
                  />
                </div>
              </div>

              <!-- Daftar Barang yang Ditransfer -->
              <div class="d-flex justify-content-between align-items-center mb-2">
                <h6 class="fw-bold text-dark mb-0">Daftar Barang Mutasi</h6>
                <button type="button" class="btn btn-sm btn-outline-primary" @click="addItemRow">
                  <i class="mdi mdi-plus me-1"></i> Tambah Barang
                </button>
              </div>

              <div class="table-responsive border rounded-3 mb-3">
                <table class="table table-bordered align-middle mb-0 fs-13">
                  <thead class="table-light fs-11 text-uppercase text-muted">
                    <tr>
                      <th style="min-width: 250px;">Produk & Varian <span class="text-danger">*</span></th>
                      <th style="width: 140px;" class="text-center">Stok Pengirim</th>
                      <th style="width: 140px;" class="text-center">Jumlah Kirim <span class="text-danger">*</span></th>
                      <th>Keterangan Item</th>
                      <th style="width: 40px;"></th>
                    </tr>
                  </thead>
                  <tbody>
                    <tr v-for="(item, idx) in transferForm.items" :key="idx">
                      <td>
                        <select
                          class="form-select form-select-sm"
                          v-model="item.product_variant_id"
                          @change="updateItemStock(item)"
                          required
                        >
                          <option :value="null">-- Pilih Produk / Varian --</option>
                          <option v-for="v in allVariants" :key="v.id" :value="v.id">
                            {{ v.product?.name }} - {{ v.variant_name }} ({{ v.sku }})
                          </option>
                        </select>
                      </td>
                      <td class="text-center">
                        <span class="badge bg-light text-dark border fs-12 px-2">
                          {{ item.availableStock }} Pcs
                        </span>
                      </td>
                      <td>
                        <input
                          type="number"
                          class="form-control form-control-sm text-center fw-bold"
                          v-model="item.quantity"
                          :max="item.availableStock"
                          min="1"
                          required
                        />
                      </td>
                      <td>
                        <input
                          type="text"
                          class="form-control form-control-sm"
                          v-model="item.notes"
                          placeholder="Keterangan..."
                        />
                      </td>
                      <td class="text-center">
                        <button
                          type="button"
                          class="btn btn-sm btn-link text-danger p-0"
                          @click="removeItemRow(idx)"
                          :disabled="transferForm.items.length === 1"
                        >
                          <i class="mdi mdi-close-circle fs-16"></i>
                        </button>
                      </td>
                    </tr>
                  </tbody>
                </table>
              </div>

              <div class="form-check form-switch">
                <input
                  class="form-check-input"
                  type="checkbox"
                  id="autoSendSwitch"
                  v-model="transferForm.auto_send"
                />
                <label class="form-check-label fw-semibold" for="autoSendSwitch">
                  Langsung ubah status menjadi "In Transit" (Barang fisik langsung dikirim hari ini)
                </label>
              </div>

            </div>
            <div class="modal-footer bg-light border-top py-2 px-4">
              <button type="button" class="btn btn-light" @click="showCreateModal = false">Batal</button>
              <button type="submit" class="btn btn-primary fw-semibold" :disabled="submitting">
                <span v-if="submitting" class="spinner-border spinner-border-sm me-1"></span>
                <span>Terbitkan Surat Jalan</span>
              </button>
            </div>
          </form>
        </div>
      </div>
    </div>

  </AdminLayout>
</template>

<script setup>
import { onMounted, reactive, ref } from 'vue';
import api from '../../api/axios';
import AdminLayout from '../../layouts/AdminLayout.vue';
import { useAuthStore } from '../../stores/auth';

const authStore = useAuthStore();

const transfers = ref([]);
const branches = ref([]);
const allVariants = ref([]);
const senderStocksMap = ref({});

const loading = ref(false);
const submitting = ref(false);
const statusFilter = ref('');

const alertMessage = ref('');
const alertType = ref('success');

// Detail Modal
const showDetailModal = ref(false);
const selectedTransfer = ref(null);

// Create Modal
const showCreateModal = ref(false);
const transferForm = reactive({
  from_branch_id: null,
  to_branch_id: null,
  transfer_date: new Date().toISOString().split('T')[0],
  notes: '',
  auto_send: true,
  items: [],
});

function setStatusFilter(status) {
  statusFilter.value = status;
  fetchTransfers();
}

async function fetchTransfers() {
  loading.value = true;
  try {
    const params = {};
    if (statusFilter.value) params.status = statusFilter.value;
    const res = await api.get('/stock-transfers', { params });
    transfers.value = res.data.data;
  } catch (err) {
    showAlert('Gagal memuat daftar mutasi.', 'danger');
  } finally {
    loading.value = false;
  }
}

async function fetchDependencies() {
  try {
    const [brRes, prRes] = await Promise.all([
      api.get('/branches'),
      api.get('/products'),
    ]);
    branches.value = brRes.data.data;

    // Flatten all variants with product name
    const variants = [];
    prRes.data.data.forEach(p => {
      (p.variants || []).forEach(v => {
        variants.push({
          ...v,
          product: p,
        });
      });
    });
    allVariants.value = variants;
  } catch (err) {
    console.error('Gagal memuat data pendukung:', err);
  }
}

async function fetchSenderStocks(branchId) {
  try {
    const res = await api.get('/inventory/stocks', { params: { branch_id: branchId } });
    const map = {};
    res.data.data.forEach(s => {
      map[s.product_variant_id] = Number(s.quantity);
    });
    senderStocksMap.value = map;

    // Update stock numbers for existing item rows
    transferForm.items.forEach(item => {
      updateItemStock(item);
    });
  } catch (err) {
    console.error('Gagal memuat stok cabang pengirim:', err);
  }
}

function onSenderBranchChange() {
  if (transferForm.from_branch_id) {
    fetchSenderStocks(transferForm.from_branch_id);
  }
}

function updateItemStock(item) {
  if (item.product_variant_id && senderStocksMap.value[item.product_variant_id] !== undefined) {
    item.availableStock = senderStocksMap.value[item.product_variant_id];
  } else {
    item.availableStock = 0;
  }
}

function openCreateModal() {
  // Set default sender: Gudang pusat jika admin, atau cabang kasir
  const defaultSender = branches.value.find(b => b.is_central_warehouse) || branches.value[0];
  const defaultReceiver = branches.value.find(b => !b.is_central_warehouse) || branches.value[1];

  transferForm.from_branch_id = defaultSender?.id || null;
  transferForm.to_branch_id = defaultReceiver?.id || null;
  transferForm.transfer_date = new Date().toISOString().split('T')[0];
  transferForm.notes = '';
  transferForm.auto_send = true;

  transferForm.items = [
    {
      product_variant_id: allVariants.value[0]?.id || null,
      quantity: 10,
      availableStock: 0,
      notes: '',
    },
  ];

  if (transferForm.from_branch_id) {
    fetchSenderStocks(transferForm.from_branch_id);
  }

  showCreateModal.value = true;
}

function addItemRow() {
  transferForm.items.push({
    product_variant_id: null,
    quantity: 1,
    availableStock: 0,
    notes: '',
  });
}

function removeItemRow(idx) {
  if (transferForm.items.length > 1) {
    transferForm.items.splice(idx, 1);
  }
}

async function saveTransfer() {
  submitting.value = true;
  try {
    await api.post('/stock-transfers', transferForm);
    showAlert('Surat jalan mutasi baru berhasil diterbitkan.', 'success');
    showCreateModal.value = false;
    fetchTransfers();
  } catch (err) {
    showAlert(err.response?.data?.message || 'Gagal menerbitkan surat jalan.', 'danger');
  } finally {
    submitting.value = false;
  }
}

function openDetailModal(t) {
  selectedTransfer.value = t;
  showDetailModal.value = true;
}

function canSend(t) {
  if (authStore.isSuperAdmin) return true;
  return Number(authStore.user?.branch_id) === Number(t.from_branch_id);
}

function canReceive(t) {
  if (authStore.isSuperAdmin) return true;
  return Number(authStore.user?.branch_id) === Number(t.to_branch_id);
}

async function sendTransfer(t) {
  if (!confirm(`Konfirmasi pengiriman fisik barang untuk Surat Jalan ${t.transfer_number}? Stok cabang pengirim akan dikunci (Reserved).`)) return;
  try {
    await api.post(`/stock-transfers/${t.id}/send`);
    showAlert(`Barang untuk ${t.transfer_number} resmi dikirim (Status: In Transit).`, 'success');
    showDetailModal.value = false;
    fetchTransfers();
  } catch (err) {
    showAlert(err.response?.data?.message || 'Gagal mengubah status transfer.', 'danger');
  }
}

async function receiveTransfer(t) {
  if (!confirm(`Konfirmasi penerimaan barang untuk Surat Jalan ${t.transfer_number}? Stok cabang tujuan akan resmi bertambah.`)) return;
  try {
    await api.post(`/stock-transfers/${t.id}/receive`);
    showAlert(`Barang untuk ${t.transfer_number} berhasil diterima dan dicatat di inventaris cabang.`, 'success');
    showDetailModal.value = false;
    fetchTransfers();
  } catch (err) {
    showAlert(err.response?.data?.message || 'Gagal mengonfirmasi penerimaan barang.', 'danger');
  }
}

function formatDate(d) {
  if (!d) return '-';
  const date = new Date(d);
  return date.toLocaleDateString('id-ID', { day: 'numeric', month: 'short', year: 'numeric' });
}

function showAlert(msg, type = 'success') {
  alertMessage.value = msg;
  alertType.value = type;
  setTimeout(() => {
    alertMessage.value = '';
  }, 4500);
}

onMounted(() => {
  fetchTransfers();
  fetchDependencies();
});
</script>
