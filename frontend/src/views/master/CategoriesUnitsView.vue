<template>
  <AdminLayout>
    <!-- Page Header Title -->
    <div class="row mb-3">
      <div class="col-12">
        <div class="d-flex align-items-center justify-content-between">
          <div>
            <h4 class="fs-18 mb-1 fw-bold text-dark">Kategori & Satuan Unit Produk</h4>
            <p class="text-muted mb-0 fs-13">Kelola pengelompokan produk dan satuan konversi (Pieces, Lusin, Dus, Pack).</p>
          </div>
          <div>
            <button v-if="activeTab === 'categories'" class="btn btn-primary d-flex align-items-center gap-1 shadow-sm" @click="openCategoryModal()">
              <i class="mdi mdi-plus-circle-outline fs-16"></i>
              <span>Tambah Kategori</span>
            </button>
            <button v-else class="btn btn-primary d-flex align-items-center gap-1 shadow-sm" @click="openUnitModal()">
              <i class="mdi mdi-plus-circle-outline fs-16"></i>
              <span>Tambah Satuan</span>
            </button>
          </div>
        </div>
      </div>
    </div>

    <!-- Alert Notifikasi -->
    <div v-if="alertMessage" :class="['alert', alertType === 'success' ? 'alert-success' : 'alert-danger', 'alert-dismissible', 'fade', 'show']" role="alert">
      <i :class="[alertType === 'success' ? 'mdi mdi-check-circle' : 'mdi mdi-alert-circle', 'me-2 fs-16']"></i>
      {{ alertMessage }}
      <button type="button" class="btn-close" @click="alertMessage = ''"></button>
    </div>

    <!-- Tabs Nav -->
    <div class="card shadow-sm border-0 rounded-3 mb-4">
      <div class="card-header bg-white border-bottom p-0">
        <ul class="nav nav-tabs nav-tabs-custom card-header-tabs border-bottom-0">
          <li class="nav-item">
            <button
              :class="['nav-link', 'py-3', 'px-4', 'fw-semibold', activeTab === 'categories' ? 'active text-primary' : 'text-muted']"
              @click="activeTab = 'categories'"
            >
              <i class="mdi mdi-tag-multiple-outline me-1 fs-16"></i>
              <span>Kategori Produk ({{ categories.length }})</span>
            </button>
          </li>
          <li class="nav-item">
            <button
              :class="['nav-link', 'py-3', 'px-4', 'fw-semibold', activeTab === 'units' ? 'active text-primary' : 'text-muted']"
              @click="activeTab = 'units'"
            >
              <i class="mdi mdi-scale me-1 fs-16"></i>
              <span>Satuan Unit & Konversi ({{ units.length }})</span>
            </button>
          </li>
        </ul>
      </div>

      <!-- TAB 1: KATEGORI -->
      <div v-if="activeTab === 'categories'" class="card-body p-0">
        <div class="table-responsive">
          <table class="table table-hover align-middle mb-0">
            <thead class="table-light fs-12 text-uppercase text-muted">
              <tr>
                <th style="width: 50px;" class="text-center">#</th>
                <th>Nama Kategori</th>
                <th>Slug URL</th>
                <th>Deskripsi</th>
                <th class="text-center">Jumlah Produk</th>
                <th class="text-center">Status</th>
                <th style="width: 120px;" class="text-center">Aksi</th>
              </tr>
            </thead>
            <tbody class="fs-13">
              <tr v-if="loadingCategories">
                <td colspan="7" class="text-center py-4 text-muted">
                  <div class="spinner-border spinner-border-sm text-primary me-2"></div> Memuat kategori...
                </td>
              </tr>
              <tr v-else-if="categories.length === 0">
                <td colspan="7" class="text-center py-4 text-muted">Belum ada kategori terdaftar.</td>
              </tr>
              <tr v-for="(cat, idx) in categories" :key="cat.id">
                <td class="text-center fw-semibold text-muted">{{ idx + 1 }}</td>
                <td>
                  <strong class="text-dark">{{ cat.name }}</strong>
                </td>
                <td>
                  <span class="badge bg-light text-muted border font-monospace">{{ cat.slug }}</span>
                </td>
                <td class="text-muted">{{ cat.description || '-' }}</td>
                <td class="text-center">
                  <span class="badge bg-primary-subtle text-primary border border-primary-subtle px-2 py-1">
                    {{ cat.products_count || 0 }} Produk
                  </span>
                </td>
                <td class="text-center">
                  <span :class="['badge', cat.is_active ? 'bg-success-subtle text-success' : 'bg-danger-subtle text-danger']">
                    {{ cat.is_active ? 'Aktif' : 'Nonaktif' }}
                  </span>
                </td>
                <td class="text-center">
                  <div class="d-flex justify-content-center gap-1">
                    <button class="btn btn-sm btn-outline-primary" @click="openCategoryModal(cat)" title="Edit">
                      <i class="mdi mdi-pencil-outline"></i>
                    </button>
                    <button class="btn btn-sm btn-outline-danger" @click="deleteCategory(cat)" title="Hapus">
                      <i class="mdi mdi-trash-can-outline"></i>
                    </button>
                  </div>
                </td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>

      <!-- TAB 2: SATUAN UNIT -->
      <div v-if="activeTab === 'units'" class="card-body p-0">
        <div class="table-responsive">
          <table class="table table-hover align-middle mb-0">
            <thead class="table-light fs-12 text-uppercase text-muted">
              <tr>
                <th style="width: 50px;" class="text-center">#</th>
                <th>Nama Satuan</th>
                <th>Singkatan (Simbol)</th>
                <th class="text-center">Digunakan di Produk</th>
                <th style="width: 120px;" class="text-center">Aksi</th>
              </tr>
            </thead>
            <tbody class="fs-13">
              <tr v-if="loadingUnits">
                <td colspan="5" class="text-center py-4 text-muted">
                  <div class="spinner-border spinner-border-sm text-primary me-2"></div> Memuat satuan...
                </td>
              </tr>
              <tr v-else-if="units.length === 0">
                <td colspan="5" class="text-center py-4 text-muted">Belum ada satuan terdaftar.</td>
              </tr>
              <tr v-for="(u, idx) in units" :key="u.id">
                <td class="text-center fw-semibold text-muted">{{ idx + 1 }}</td>
                <td>
                  <strong class="text-dark">{{ u.name }}</strong>
                </td>
                <td>
                  <span class="badge bg-light text-dark border font-monospace fs-12 px-2">{{ u.short_name }}</span>
                </td>
                <td class="text-center">
                  <span class="badge bg-info-subtle text-info border border-info-subtle px-2 py-1">
                    {{ u.product_units_count || 0 }} Produk
                  </span>
                </td>
                <td class="text-center">
                  <div class="d-flex justify-content-center gap-1">
                    <button class="btn btn-sm btn-outline-primary" @click="openUnitModal(u)" title="Edit">
                      <i class="mdi mdi-pencil-outline"></i>
                    </button>
                    <button class="btn btn-sm btn-outline-danger" @click="deleteUnit(u)" title="Hapus">
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

    <!-- Modal Form Kategori -->
    <div v-if="showCategoryModal" class="modal fade show d-block" tabindex="-1" style="background-color: rgba(0,0,0,0.5);">
      <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content shadow-lg border-0 rounded-4">
          <div class="modal-header border-bottom py-3">
            <h5 class="modal-title fw-bold text-dark">
              {{ isEditingCategory ? 'Edit Kategori' : 'Tambah Kategori Baru' }}
            </h5>
            <button type="button" class="btn-close" @click="showCategoryModal = false"></button>
          </div>
          <form @submit.prevent="saveCategory">
            <div class="modal-body p-4">
              <div class="mb-3">
                <label class="form-label fw-semibold">Nama Kategori <span class="text-danger">*</span></label>
                <input
                  type="text"
                  class="form-control"
                  v-model="categoryForm.name"
                  placeholder="Misal: Pakaian Pria"
                  required
                />
              </div>
              <div class="mb-3">
                <label class="form-label fw-semibold">Deskripsi</label>
                <textarea
                  class="form-control"
                  v-model="categoryForm.description"
                  rows="3"
                  placeholder="Keterangan kategori..."
                ></textarea>
              </div>
              <div class="form-check form-switch">
                <input
                  class="form-check-input"
                  type="checkbox"
                  id="catActive"
                  v-model="categoryForm.is_active"
                />
                <label class="form-check-label fw-semibold" for="catActive">Status Kategori Aktif</label>
              </div>
            </div>
            <div class="modal-footer bg-light border-top py-2 px-4">
              <button type="button" class="btn btn-light" @click="showCategoryModal = false">Batal</button>
              <button type="submit" class="btn btn-primary fw-semibold" :disabled="submitting">
                <span>{{ isEditingCategory ? 'Simpan Perubahan' : 'Tambah Kategori' }}</span>
              </button>
            </div>
          </form>
        </div>
      </div>
    </div>

    <!-- Modal Form Satuan -->
    <div v-if="showUnitModal" class="modal fade show d-block" tabindex="-1" style="background-color: rgba(0,0,0,0.5);">
      <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content shadow-lg border-0 rounded-4">
          <div class="modal-header border-bottom py-3">
            <h5 class="modal-title fw-bold text-dark">
              {{ isEditingUnit ? 'Edit Satuan Unit' : 'Tambah Satuan Unit' }}
            </h5>
            <button type="button" class="btn-close" @click="showUnitModal = false"></button>
          </div>
          <form @submit.prevent="saveUnit">
            <div class="modal-body p-4">
              <div class="mb-3">
                <label class="form-label fw-semibold">Nama Satuan <span class="text-danger">*</span></label>
                <input
                  type="text"
                  class="form-control"
                  v-model="unitForm.name"
                  placeholder="Misal: Lusin, Dus, Pack, Meter"
                  required
                />
              </div>
              <div class="mb-3">
                <label class="form-label fw-semibold">Singkatan / Simbol <span class="text-danger">*</span></label>
                <input
                  type="text"
                  class="form-control font-monospace"
                  v-model="unitForm.short_name"
                  placeholder="Misal: lsn, dus, pck, m"
                  required
                />
                <small class="text-muted">Singkatan ini akan tampil di nota pembayaran & tabel keranjang.</small>
              </div>
            </div>
            <div class="modal-footer bg-light border-top py-2 px-4">
              <button type="button" class="btn btn-light" @click="showUnitModal = false">Batal</button>
              <button type="submit" class="btn btn-primary fw-semibold" :disabled="submitting">
                <span>{{ isEditingUnit ? 'Simpan Perubahan' : 'Tambah Satuan' }}</span>
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

const activeTab = ref('categories');

const categories = ref([]);
const units = ref([]);
const loadingCategories = ref(false);
const loadingUnits = ref(false);
const submitting = ref(false);

const alertMessage = ref('');
const alertType = ref('success');

// Category Modal State
const showCategoryModal = ref(false);
const isEditingCategory = ref(false);
const editingCategoryId = ref(null);
const categoryForm = reactive({
  name: '',
  description: '',
  is_active: true,
});

// Unit Modal State
const showUnitModal = ref(false);
const isEditingUnit = ref(false);
const editingUnitId = ref(null);
const unitForm = reactive({
  name: '',
  short_name: '',
});

async function fetchCategories() {
  loadingCategories.value = true;
  try {
    const res = await api.get('/categories');
    categories.value = res.data.data;
  } catch (err) {
    showAlert(err.response?.data?.message || 'Gagal memuat kategori.', 'danger');
  } finally {
    loadingCategories.value = false;
  }
}

async function fetchUnits() {
  loadingUnits.value = true;
  try {
    const res = await api.get('/units');
    units.value = res.data.data;
  } catch (err) {
    showAlert(err.response?.data?.message || 'Gagal memuat satuan unit.', 'danger');
  } finally {
    loadingUnits.value = false;
  }
}

// Category Actions
function openCategoryModal(cat = null) {
  if (cat) {
    isEditingCategory.value = true;
    editingCategoryId.value = cat.id;
    categoryForm.name = cat.name;
    categoryForm.description = cat.description || '';
    categoryForm.is_active = !!cat.is_active;
  } else {
    isEditingCategory.value = false;
    editingCategoryId.value = null;
    categoryForm.name = '';
    categoryForm.description = '';
    categoryForm.is_active = true;
  }
  showCategoryModal.value = true;
}

async function saveCategory() {
  submitting.value = true;
  try {
    if (isEditingCategory.value) {
      await api.put(`/categories/${editingCategoryId.value}`, categoryForm);
      showAlert('Kategori berhasil diperbarui.', 'success');
    } else {
      await api.post('/categories', categoryForm);
      showAlert('Kategori berhasil ditambahkan.', 'success');
    }
    showCategoryModal.value = false;
    fetchCategories();
  } catch (err) {
    showAlert(err.response?.data?.message || 'Gagal menyimpan kategori.', 'danger');
  } finally {
    submitting.value = false;
  }
}

async function deleteCategory(cat) {
  if (!confirm(`Yakin ingin menghapus kategori "${cat.name}"?`)) return;
  try {
    await api.delete(`/categories/${cat.id}`);
    showAlert('Kategori berhasil dihapus.', 'success');
    fetchCategories();
  } catch (err) {
    showAlert(err.response?.data?.message || 'Gagal menghapus kategori.', 'danger');
  }
}

// Unit Actions
function openUnitModal(unit = null) {
  if (unit) {
    isEditingUnit.value = true;
    editingUnitId.value = unit.id;
    unitForm.name = unit.name;
    unitForm.short_name = unit.short_name;
  } else {
    isEditingUnit.value = false;
    editingUnitId.value = null;
    unitForm.name = '';
    unitForm.short_name = '';
  }
  showUnitModal.value = true;
}

async function saveUnit() {
  submitting.value = true;
  try {
    if (isEditingUnit.value) {
      await api.put(`/units/${editingUnitId.value}`, unitForm);
      showAlert('Satuan berhasil diperbarui.', 'success');
    } else {
      await api.post('/units', unitForm);
      showAlert('Satuan berhasil ditambahkan.', 'success');
    }
    showUnitModal.value = false;
    fetchUnits();
  } catch (err) {
    showAlert(err.response?.data?.message || 'Gagal menyimpan satuan.', 'danger');
  } finally {
    submitting.value = false;
  }
}

async function deleteUnit(unit) {
  if (!confirm(`Yakin ingin menghapus satuan "${unit.name}"?`)) return;
  try {
    await api.delete(`/units/${unit.id}`);
    showAlert('Satuan unit berhasil dihapus.', 'success');
    fetchUnits();
  } catch (err) {
    showAlert(err.response?.data?.message || 'Gagal menghapus satuan.', 'danger');
  }
}

function showAlert(msg, type = 'success') {
  alertMessage.value = msg;
  alertType.value = type;
  setTimeout(() => {
    alertMessage.value = '';
  }, 4000);
}

onMounted(() => {
  fetchCategories();
  fetchUnits();
});
</script>
