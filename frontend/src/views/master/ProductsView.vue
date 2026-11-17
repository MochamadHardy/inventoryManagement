<template>
  <AdminLayout>
    <!-- Page Header Title -->
    <div class="row mb-3">
      <div class="col-12">
        <div class="d-flex flex-wrap align-items-center justify-content-between gap-2">
          <div>
            <h4 class="fs-18 mb-1 fw-bold text-dark">Master Produk & Varian Multi-Harga</h4>
            <p class="text-muted mb-0 fs-13">Kelola katalog produk, atribut varian (warna, ukuran, SKU), multi-satuan, dan multi-harga (eceran vs grosir).</p>
          </div>
          <button class="btn btn-primary d-flex align-items-center gap-1 shadow-sm" @click="openCreateModal">
            <i class="mdi mdi-plus-circle-outline fs-16"></i>
            <span>Tambah Produk Baru</span>
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

    <!-- Filter & Search Bar -->
    <div class="card shadow-sm border-0 rounded-3 mb-3">
      <div class="card-body p-3">
        <div class="row g-2 align-items-center">
          <div class="col-md-6 col-lg-5">
            <div class="input-group">
              <span class="input-group-text bg-light border-end-0">
                <i class="mdi mdi-magnify text-muted fs-16"></i>
              </span>
              <input
                type="text"
                class="form-control border-start-0 ps-0"
                v-model="searchKeyword"
                @input="handleSearch"
                placeholder="Cari nama produk, SKU, barcode, varian..."
              />
            </div>
          </div>
          <div class="col-md-4 col-lg-4">
            <select class="form-select" v-model="selectedCategory" @change="fetchProducts">
              <option value="">Semua Kategori Produk</option>
              <option v-for="c in categories" :key="c.id" :value="c.id">{{ c.name }}</option>
            </select>
          </div>
          <div class="col-md-2 col-lg-3 text-md-end text-muted fs-13">
            Total: <strong>{{ products.length }}</strong> Produk Terdaftar
          </div>
        </div>
      </div>
    </div>

    <!-- Tabel Daftar Produk -->
    <div class="card shadow-sm border-0 rounded-3">
      <div class="card-body p-0">
        <div class="table-responsive">
          <table class="table table-hover align-middle mb-0">
            <thead class="table-light fs-12 text-uppercase text-muted">
              <tr>
                <th style="width: 50px;" class="text-center">#</th>
                <th>Produk</th>
                <th>Kategori</th>
                <th>Satuan Dasar</th>
                <th class="text-center">Jumlah Varian</th>
                <th>Rentang Harga Jual</th>
                <th class="text-center">Total Stok Fisik</th>
                <th style="width: 100px;" class="text-center">Aksi</th>
              </tr>
            </thead>
            <tbody class="fs-13">
              <tr v-if="loading">
                <td colspan="8" class="text-center py-4 text-muted">
                  <div class="spinner-border spinner-border-sm text-primary me-2"></div> Memuat katalog produk...
                </td>
              </tr>
              <tr v-else-if="products.length === 0">
                <td colspan="8" class="text-center py-4 text-muted">Tidak ada produk yang cocok dengan pencarian.</td>
              </tr>
              <tr v-for="(p, idx) in products" :key="p.id">
                <td class="text-center fw-semibold text-muted">{{ idx + 1 }}</td>
                <td>
                  <div class="fw-bold text-dark">{{ p.name }}</div>
                  <small class="text-muted d-block text-truncate" style="max-width: 250px;">{{ p.description || '-' }}</small>
                </td>
                <td>
                  <span class="badge bg-light text-dark border">{{ p.category?.name || 'Uncategorized' }}</span>
                </td>
                <td>
                  <span class="badge bg-secondary-subtle text-secondary font-monospace">
                    {{ p.product_units?.find(u => u.is_base_unit)?.unit?.name || 'Pcs' }}
                  </span>
                </td>
                <td class="text-center">
                  <span class="badge bg-info-subtle text-info border border-info-subtle px-2 py-1 fs-12">
                    {{ p.variants?.length || 0 }} SKU
                  </span>
                </td>
                <td>
                  <div class="fw-semibold text-dark">{{ formatPriceRange(p.variants) }}</div>
                  <small class="text-muted fs-11">Eceran & Grosir</small>
                </td>
                <td class="text-center">
                  <span class="badge bg-success-subtle text-success fs-12 px-2 py-1">
                    {{ calculateTotalStock(p.variants) }} {{ p.product_units?.find(u => u.is_base_unit)?.unit?.short_name || 'pcs' }}
                  </span>
                </td>
                <td class="text-center">
                  <div class="d-flex justify-content-center gap-1">
                    <button class="btn btn-sm btn-outline-info" @click="openDetailModal(p)" title="Lihat Detail Varian & Stok Cabang">
                      <i class="mdi mdi-eye-outline"></i>
                    </button>
                    <button class="btn btn-sm btn-outline-danger" @click="deleteProduct(p)" title="Hapus Produk">
                      <i class="mdi mdi-trash-can-outline"></i>
                    </button>
                  </div>
                </td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>
    </div>

    <!-- MODAL 1: DETAIL PRODUK LENGKAP DENGAN STOK PER CABANG -->
    <div v-if="showDetailModal && selectedProduct" class="modal fade show d-block" tabindex="-1" style="background-color: rgba(0,0,0,0.5);">
      <div class="modal-dialog modal-xl modal-dialog-centered modal-dialog-scrollable">
        <div class="modal-content shadow-lg border-0 rounded-4">
          <div class="modal-header border-bottom py-3">
            <div>
              <h5 class="modal-title fw-bold text-dark">{{ selectedProduct.name }}</h5>
              <small class="text-muted">Kategori: {{ selectedProduct.category?.name }} • Base Unit: {{ selectedProduct.product_units?.find(u => u.is_base_unit)?.unit?.name }}</small>
            </div>
            <button type="button" class="btn-close" @click="showDetailModal = false"></button>
          </div>
          <div class="modal-body p-4">
            
            <!-- Varian & Harga Table -->
            <h6 class="fw-bold text-dark mb-2"><i class="mdi mdi-tag-outline text-primary me-1"></i> Rincian Varian & Harga Bertingkat</h6>
            <div class="table-responsive border rounded-3 mb-4">
              <table class="table table-sm table-striped align-middle mb-0 fs-13">
                <thead class="table-light text-uppercase fs-11 text-muted">
                  <tr>
                    <th>SKU</th>
                    <th>Barcode</th>
                    <th>Nama Varian</th>
                    <th class="text-end">HPP (Modal)</th>
                    <th>Harga Eceran</th>
                    <th>Harga Grosir</th>
                  </tr>
                </thead>
                <tbody>
                  <tr v-for="v in selectedProduct.variants" :key="v.id">
                    <td class="font-monospace fw-bold text-primary">{{ v.sku }}</td>
                    <td class="font-monospace text-muted">{{ v.barcode || '-' }}</td>
                    <td class="fw-semibold text-dark">{{ v.variant_name }}</td>
                    <td class="text-end text-muted">Rp {{ formatNumber(v.purchase_price) }}</td>
                    <td>
                      <span class="text-success fw-bold">
                        Rp {{ formatNumber(getVariantPrice(v, 'retail')) }}
                      </span>
                    </td>
                    <td>
                      <span class="text-info fw-bold">
                        Rp {{ formatNumber(getVariantPrice(v, 'wholesale')) }}
                      </span>
                      <small class="text-muted d-block fs-11">
                        (Min {{ getVariantMinQty(v, 'wholesale') }} {{ selectedProduct.product_units?.find(u => u.is_base_unit)?.unit?.short_name }})
                      </small>
                    </td>
                  </tr>
                </tbody>
              </table>
            </div>

            <!-- Stok Fisik Terdistribusi per Cabang -->
            <h6 class="fw-bold text-dark mb-2"><i class="mdi mdi-warehouse text-info me-1"></i> Posisi Stok Fisik di Seluruh Cabang</h6>
            <div class="table-responsive border rounded-3">
              <table class="table table-sm table-hover align-middle mb-0 fs-13">
                <thead class="table-light text-uppercase fs-11 text-muted">
                  <tr>
                    <th>Varian / SKU</th>
                    <th v-for="branch in allBranches" :key="branch.id" class="text-center">
                      <div class="text-dark">{{ branch.name }}</div>
                      <small class="text-muted font-monospace">({{ branch.code }})</small>
                    </th>
                    <th class="text-center bg-light">Total Stok</th>
                  </tr>
                </thead>
                <tbody>
                  <tr v-for="v in selectedProduct.variants" :key="'stk-' + v.id">
                    <td>
                      <strong>{{ v.variant_name }}</strong>
                      <small class="text-muted d-block font-monospace">{{ v.sku }}</small>
                    </td>
                    <td v-for="branch in allBranches" :key="'br-' + branch.id" class="text-center">
                      <span :class="getStockBadgeClass(getBranchStock(v, branch.id), 10)">
                        {{ getBranchStock(v, branch.id) }}
                      </span>
                    </td>
                    <td class="text-center bg-light fw-bold text-primary fs-14">
                      {{ calculateSingleVariantTotal(v) }}
                    </td>
                  </tr>
                </tbody>
              </table>
            </div>

          </div>
          <div class="modal-footer bg-light border-top py-2 px-4">
            <button type="button" class="btn btn-secondary" @click="showDetailModal = false">Tutup</button>
          </div>
        </div>
      </div>
    </div>

    <!-- MODAL 2: FORM TAMBAH PRODUK BARU LENGKAP -->
    <div v-if="showCreateModal" class="modal fade show d-block" tabindex="-1" style="background-color: rgba(0,0,0,0.5);">
      <div class="modal-dialog modal-xl modal-dialog-centered modal-dialog-scrollable">
        <div class="modal-content shadow-lg border-0 rounded-4">
          <div class="modal-header border-bottom py-3">
            <div>
              <h5 class="modal-title fw-bold text-dark">Pendaftaran Master Produk & Varian Baru</h5>
              <small class="text-muted">Masukkan data produk, satuan konversi, variasi barang, harga eceran/grosir, dan stok awal di Gudang Pusat.</small>
            </div>
            <button type="button" class="btn-close" @click="showCreateModal = false"></button>
          </div>
          <form @submit.prevent="saveProduct">
            <div class="modal-body p-4">

              <!-- Step 1: Info Produk Induk -->
              <div class="card border border-light-subtle shadow-none bg-light-subtle rounded-3 p-3 mb-4">
                <h6 class="fw-bold text-primary mb-3">
                  <i class="mdi mdi-numeric-1-circle me-1"></i> Informasi Dasar Produk
                </h6>
                <div class="row g-3">
                  <div class="col-md-6">
                    <label class="form-label fw-semibold">Nama Produk <span class="text-danger">*</span></label>
                    <input
                      type="text"
                      class="form-control"
                      v-model="productForm.name"
                      placeholder="Misal: Kemeja Flanel Slimfit"
                      required
                    />
                  </div>
                  <div class="col-md-3">
                    <label class="form-label fw-semibold">Kategori Produk</label>
                    <select class="form-select" v-model="productForm.category_id">
                      <option :value="null">Pilih Kategori</option>
                      <option v-for="c in categories" :key="c.id" :value="c.id">{{ c.name }}</option>
                    </select>
                  </div>
                  <div class="col-md-3">
                    <label class="form-label fw-semibold">Satuan Dasar (Base Unit) <span class="text-danger">*</span></label>
                    <select class="form-select" v-model="productForm.base_unit_id" required>
                      <option v-for="u in units" :key="u.id" :value="u.id">{{ u.name }} ({{ u.short_name }})</option>
                    </select>
                    <small class="text-muted fs-11">Satuan terkecil untuk kalkulasi stok.</small>
                  </div>
                  <div class="col-12">
                    <label class="form-label fw-semibold">Deskripsi Singkat</label>
                    <input
                      type="text"
                      class="form-control"
                      v-model="productForm.description"
                      placeholder="Keterangan bahan, fitur, spesifikasi produk..."
                    />
                  </div>
                </div>
              </div>

              <!-- Step 2: Varian Produk Dinamis -->
              <div class="card border border-light-subtle shadow-none bg-light-subtle rounded-3 p-3 mb-4">
                <div class="d-flex justify-content-between align-items-center mb-3">
                  <div>
                    <h6 class="fw-bold text-primary mb-0">
                      <i class="mdi mdi-numeric-2-circle me-1"></i> Varian Produk, Multi-Harga & Stok Awal
                    </h6>
                    <small class="text-muted">Tambahkan variasi ukuran/warna atau daftarkan varian default.</small>
                  </div>
                  <button type="button" class="btn btn-sm btn-outline-primary fw-semibold" @click="addVariantRow">
                    <i class="mdi mdi-plus me-1"></i> Tambah Varian
                  </button>
                </div>

                <div class="table-responsive">
                  <table class="table table-bordered bg-white align-middle mb-0 fs-13">
                    <thead class="table-light fs-11 text-uppercase text-muted">
                      <tr>
                        <th style="min-width: 140px;">Nama Varian <span class="text-danger">*</span></th>
                        <th style="min-width: 120px;">SKU <span class="text-danger">*</span></th>
                        <th style="min-width: 110px;">Barcode (Opt)</th>
                        <th style="min-width: 110px;">HPP (Modal) <span class="text-danger">*</span></th>
                        <th style="min-width: 110px;">Harga Eceran <span class="text-danger">*</span></th>
                        <th style="min-width: 110px;">Harga Grosir</th>
                        <th style="min-width: 90px;">Min Grosir</th>
                        <th style="min-width: 100px;">Stok Awal Pusat</th>
                        <th style="width: 40px;"></th>
                      </tr>
                    </thead>
                    <tbody>
                      <tr v-for="(v, vIdx) in productForm.variants" :key="vIdx">
                        <td>
                          <input
                            type="text"
                            class="form-control form-control-sm"
                            v-model="v.variant_name"
                            placeholder="Misal: Hitam - XL"
                            required
                          />
                        </td>
                        <td>
                          <input
                            type="text"
                            class="form-control form-control-sm font-monospace text-uppercase"
                            v-model="v.sku"
                            placeholder="Misal: KFL-BLK-XL"
                            required
                          />
                        </td>
                        <td>
                          <input
                            type="text"
                            class="form-control form-control-sm font-monospace"
                            v-model="v.barcode"
                            placeholder="Scan/ketik"
                          />
                        </td>
                        <td>
                          <input
                            type="number"
                            class="form-control form-control-sm text-end"
                            v-model="v.purchase_price"
                            min="0"
                            required
                          />
                        </td>
                        <td>
                          <input
                            type="number"
                            class="form-control form-control-sm text-end"
                            v-model="v.retail_price"
                            min="0"
                            required
                          />
                        </td>
                        <td>
                          <input
                            type="number"
                            class="form-control form-control-sm text-end"
                            v-model="v.wholesale_price"
                            min="0"
                          />
                        </td>
                        <td>
                          <input
                            type="number"
                            class="form-control form-control-sm text-center"
                            v-model="v.min_wholesale_qty"
                            min="2"
                          />
                        </td>
                        <td>
                          <input
                            type="number"
                            class="form-control form-control-sm text-center"
                            v-model="v.initial_stock"
                            min="0"
                            placeholder="0"
                          />
                        </td>
                        <td class="text-center">
                          <button
                            type="button"
                            class="btn btn-sm btn-link text-danger p-0"
                            @click="removeVariantRow(vIdx)"
                            :disabled="productForm.variants.length === 1"
                            title="Hapus Baris"
                          >
                            <i class="mdi mdi-close-circle fs-16"></i>
                          </button>
                        </td>
                      </tr>
                    </tbody>
                  </table>
                </div>

                <div class="mt-2 text-muted fs-12">
                  <i class="mdi mdi-information-outline text-info me-1"></i>
                  Stok awal otomatis dimasukkan ke <strong>Gudang Pusat Logistik</strong> dan dicatat ke dalam <strong>Kartu Stok (Stock Movements)</strong>.
                </div>
              </div>

            </div>
            <div class="modal-footer bg-light border-top py-2 px-4">
              <button type="button" class="btn btn-light" @click="showCreateModal = false">Batal</button>
              <button type="submit" class="btn btn-primary fw-semibold" :disabled="submitting">
                <span v-if="submitting" class="spinner-border spinner-border-sm me-1"></span>
                <span>Simpan Produk & Stok Awal</span>
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

const products = ref([]);
const categories = ref([]);
const units = ref([]);
const allBranches = ref([]);

const loading = ref(false);
const submitting = ref(false);
const searchKeyword = ref('');
const selectedCategory = ref('');

const alertMessage = ref('');
const alertType = ref('success');

// Detail Modal
const showDetailModal = ref(false);
const selectedProduct = ref(null);

// Create Modal
const showCreateModal = ref(false);
const productForm = reactive({
  name: '',
  category_id: null,
  description: '',
  base_unit_id: null,
  variants: [],
});

let searchTimeout = null;
function handleSearch() {
  clearTimeout(searchTimeout);
  searchTimeout = setTimeout(() => {
    fetchProducts();
  }, 350);
}

async function fetchProducts() {
  loading.value = true;
  try {
    const params = {};
    if (searchKeyword.value) params.search = searchKeyword.value;
    if (selectedCategory.value) params.category_id = selectedCategory.value;

    const res = await api.get('/products', { params });
    products.value = res.data.data;
  } catch (err) {
    showAlert('Gagal memuat katalog produk.', 'danger');
  } finally {
    loading.value = false;
  }
}

async function fetchMasterDependencies() {
  try {
    const [catRes, unitRes, branchRes] = await Promise.all([
      api.get('/categories'),
      api.get('/units'),
      api.get('/branches'),
    ]);
    categories.value = catRes.data.data;
    units.value = unitRes.data.data;
    allBranches.value = branchRes.data.data;
  } catch (err) {
    console.error('Gagal memuat dependencies master:', err);
  }
}

function openCreateModal() {
  productForm.name = '';
  productForm.category_id = categories.value[0]?.id || null;
  productForm.description = '';
  productForm.base_unit_id = units.value.find(u => u.short_name === 'pcs')?.id || units.value[0]?.id || null;
  
  // Default 1 initial variant row
  productForm.variants = [
    {
      variant_name: 'Standar',
      sku: 'SKU-' + Date.now().toString().slice(-4),
      barcode: '',
      purchase_price: 20000,
      retail_price: 35000,
      wholesale_price: 30000,
      min_wholesale_qty: 12,
      initial_stock: 100,
    },
  ];

  showCreateModal.value = true;
}

function addVariantRow() {
  productForm.variants.push({
    variant_name: '',
    sku: 'SKU-' + Math.floor(1000 + Math.random() * 9000),
    barcode: '',
    purchase_price: 20000,
    retail_price: 35000,
    wholesale_price: 30000,
    min_wholesale_qty: 12,
    initial_stock: 50,
  });
}

function removeVariantRow(idx) {
  if (productForm.variants.length > 1) {
    productForm.variants.splice(idx, 1);
  }
}

async function saveProduct() {
  submitting.value = true;
  try {
    // Format payload sesuai schema ProductRequest
    const payload = {
      name: productForm.name,
      category_id: productForm.category_id,
      description: productForm.description,
      base_unit_id: productForm.base_unit_id,
      has_variants: productForm.variants.length > 1,
      variants: productForm.variants.map(v => ({
        variant_name: v.variant_name,
        sku: v.sku,
        barcode: v.barcode,
        purchase_price: v.purchase_price,
        initial_stock: v.initial_stock || 0,
        prices: [
          {
            unit_id: productForm.base_unit_id,
            price_type: 'retail',
            min_quantity: 1,
            selling_price: v.retail_price,
          },
          {
            unit_id: productForm.base_unit_id,
            price_type: 'wholesale',
            min_quantity: v.min_wholesale_qty || 12,
            selling_price: v.wholesale_price || v.retail_price,
          },
        ],
      })),
    };

    await api.post('/products', payload);
    showAlert('Produk dan varian baru berhasil didaftarkan ke Gudang Pusat.', 'success');
    showCreateModal.value = false;
    fetchProducts();
  } catch (err) {
    const msg = err.response?.data?.message || 'Gagal menyimpan produk.';
    showAlert(msg, 'danger');
  } finally {
    submitting.value = false;
  }
}

function openDetailModal(product) {
  selectedProduct.value = product;
  showDetailModal.value = true;
}

async function deleteProduct(product) {
  if (!confirm(`Yakin ingin menghapus produk "${product.name}"?`)) return;
  try {
    await api.delete(`/products/${product.id}`);
    showAlert('Produk berhasil dihapus.', 'success');
    fetchProducts();
  } catch (err) {
    showAlert(err.response?.data?.message || 'Gagal menghapus produk.', 'danger');
  }
}

// Helpers
function formatNumber(num) {
  return Number(num || 0).toLocaleString('id-ID');
}

function formatPriceRange(variants) {
  if (!variants || variants.length === 0) return '-';
  const allPrices = variants.flatMap(v => (v.prices || []).map(p => Number(p.selling_price)));
  if (allPrices.length === 0) return '-';
  const min = Math.min(...allPrices);
  const max = Math.max(...allPrices);
  if (min === max) return `Rp ${formatNumber(min)}`;
  return `Rp ${formatNumber(min)} - Rp ${formatNumber(max)}`;
}

function calculateTotalStock(variants) {
  if (!variants) return 0;
  return variants.reduce((total, v) => {
    const variantTotal = (v.branch_stocks || []).reduce((acc, bs) => acc + Number(bs.quantity), 0);
    return total + variantTotal;
  }, 0);
}

function calculateSingleVariantTotal(variant) {
  return (variant.branch_stocks || []).reduce((acc, bs) => acc + Number(bs.quantity), 0);
}

function getVariantPrice(variant, type) {
  const p = (variant.prices || []).find(price => price.price_type === type);
  return p ? p.selling_price : 0;
}

function getVariantMinQty(variant, type) {
  const p = (variant.prices || []).find(price => price.price_type === type);
  return p ? p.min_quantity : 1;
}

function getBranchStock(variant, branchId) {
  const bs = (variant.branch_stocks || []).find(s => s.branch_id === branchId);
  return bs ? Number(bs.quantity) : 0;
}

function getStockBadgeClass(qty, minAlert = 10) {
  if (qty <= 0) return 'badge bg-secondary-subtle text-secondary';
  if (qty <= minAlert) return 'badge bg-danger text-white';
  return 'badge bg-success text-white';
}

function showAlert(msg, type = 'success') {
  alertMessage.value = msg;
  alertType.value = type;
  setTimeout(() => {
    alertMessage.value = '';
  }, 4500);
}

onMounted(() => {
  fetchProducts();
  fetchMasterDependencies();
});
</script>
