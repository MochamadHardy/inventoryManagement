<template>
  <AdminLayout>
    <!-- Page Header Title -->
    <div class="row mb-3">
      <div class="col-12">
        <div class="d-flex align-items-center justify-content-between">
          <div>
            <h4 class="fs-18 mb-1 fw-bold text-dark">Manajemen Data Cabang & Gudang</h4>
            <p class="text-muted mb-0 fs-13">Kelola kantor pusat logistik dan cabang toko fisik di seluruh wilayah.</p>
          </div>
          <button class="btn btn-primary d-flex align-items-center gap-1 shadow-sm" @click="openModal()">
            <i class="mdi mdi-plus-circle-outline fs-16"></i>
            <span>Tambah Cabang Baru</span>
          </button>
        </div>
      </div>
    </div>

    <!-- Alert Notifikasi Sukses / Error -->
    <div v-if="alertMessage" :class="['alert', alertType === 'success' ? 'alert-success' : 'alert-danger', 'alert-dismissible', 'fade', 'show']" role="alert">
      <i :class="[alertType === 'success' ? 'mdi mdi-check-circle' : 'mdi mdi-alert-circle', 'me-2 fs-16']"></i>
      {{ alertMessage }}
      <button type="button" class="btn-close" @click="alertMessage = ''"></button>
    </div>

    <!-- Tabel Data Cabang -->
    <div class="card shadow-sm border-0 rounded-3">
      <div class="card-header bg-white border-bottom py-3 d-flex justify-content-between align-items-center">
        <h6 class="mb-0 fw-bold text-dark">Daftar Cabang Aktif ({{ branches.length }})</h6>
        <span class="badge bg-light text-dark border">Role Akses: Super Admin</span>
      </div>
      <div class="card-body p-0">
        <div class="table-responsive">
          <table class="table table-hover align-middle mb-0">
            <thead class="table-light fs-12 text-uppercase text-muted">
              <tr>
                <th style="width: 50px;" class="text-center">#</th>
                <th>Kode</th>
                <th>Nama Cabang / Gudang</th>
                <th>Tipe Fasilitas</th>
                <th>Kontak & Alamat</th>
                <th class="text-center">Karyawan</th>
                <th class="text-center">Status</th>
                <th style="width: 120px;" class="text-center">Aksi</th>
              </tr>
            </thead>
            <tbody class="fs-13">
              <tr v-if="loading">
                <td colspan="8" class="text-center py-4 text-muted">
                  <div class="spinner-border spinner-border-sm text-primary me-2"></div> Memuat data cabang...
                </td>
              </tr>
              <tr v-else-if="branches.length === 0">
                <td colspan="8" class="text-center py-4 text-muted">Belum ada cabang terdaftar.</td>
              </tr>
              <tr v-for="(b, idx) in branches" :key="b.id">
                <td class="text-center fw-semibold text-muted">{{ idx + 1 }}</td>
                <td>
                  <span class="badge bg-light text-dark border font-monospace fs-12">{{ b.code }}</span>
                </td>
                <td>
                  <div class="fw-bold text-dark">{{ b.name }}</div>
                </td>
                <td>
                  <span v-if="b.is_central_warehouse" class="badge bg-primary text-white d-inline-flex align-items-center gap-1">
                    <i class="mdi mdi-warehouse fs-12"></i> Gudang Pusat
                  </span>
                  <span v-else class="badge bg-info text-white d-inline-flex align-items-center gap-1">
                    <i class="mdi mdi-store fs-12"></i> Cabang Toko POS
                  </span>
                </td>
                <td>
                  <div class="text-dark">{{ b.phone || '-' }}</div>
                  <small class="text-muted text-truncate d-block" style="max-width: 250px;">{{ b.address || '-' }}</small>
                </td>
                <td class="text-center">
                  <span class="badge bg-light text-dark border">{{ b.users_count || 0 }} User</span>
                </td>
                <td class="text-center">
                  <span :class="['badge', b.is_active ? 'bg-success-subtle text-success' : 'bg-danger-subtle text-danger']">
                    {{ b.is_active ? 'Aktif' : 'Nonaktif' }}
                  </span>
                </td>
                <td class="text-center">
                  <div class="d-flex justify-content-center gap-1">
                    <button class="btn btn-sm btn-outline-primary" @click="openModal(b)" title="Edit">
                      <i class="mdi mdi-pencil-outline"></i>
                    </button>
                    <button
                      class="btn btn-sm btn-outline-danger"
                      @click="deleteBranch(b)"
                      :disabled="b.is_central_warehouse"
                      title="Hapus"
                    >
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

    <!-- Modal Form Tambah / Edit Cabang -->
    <div v-if="showModal" class="modal fade show d-block" tabindex="-1" style="background-color: rgba(0,0,0,0.5);">
      <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content shadow-lg border-0 rounded-4">
          <div class="modal-header border-bottom py-3">
            <h5 class="modal-title fw-bold text-dark">
              {{ isEditing ? 'Edit Data Cabang' : 'Tambah Cabang Baru' }}
            </h5>
            <button type="button" class="btn-close" @click="showModal = false"></button>
          </div>
          <form @submit.prevent="saveBranch">
            <div class="modal-body p-4">
              <div class="mb-3">
                <label class="form-label fw-semibold">Kode Cabang (Unik) <span class="text-danger">*</span></label>
                <input
                  type="text"
                  class="form-control text-uppercase"
                  v-model="form.code"
                  placeholder="Misal: BR-SBY01"
                  required
                />
                <small class="text-muted">Kode cabang digunakan untuk pelacakan transaksi & surat jalan.</small>
              </div>

              <div class="mb-3">
                <label class="form-label fw-semibold">Nama Cabang / Gudang <span class="text-danger">*</span></label>
                <input
                  type="text"
                  class="form-control"
                  v-model="form.name"
                  placeholder="Misal: Toko Cabang Surabaya Timur"
                  required
                />
              </div>

              <div class="mb-3">
                <label class="form-label fw-semibold">Nomor Telepon</label>
                <input
                  type="text"
                  class="form-control"
                  v-model="form.phone"
                  placeholder="Misal: 031-888999"
                />
              </div>

              <div class="mb-3">
                <label class="form-label fw-semibold">Alamat Lengkap</label>
                <textarea
                  class="form-control"
                  v-model="form.address"
                  rows="3"
                  placeholder="Alamat fisik toko/gudang..."
                ></textarea>
              </div>

              <div class="form-check form-switch mb-2">
                <input
                  class="form-check-input"
                  type="checkbox"
                  id="centralCheck"
                  v-model="form.is_central_warehouse"
                />
                <label class="form-check-label fw-semibold" for="centralCheck">
                  Jadikan sebagai Gudang Pusat Logistik
                </label>
              </div>
              <small class="text-muted d-block mb-3">Gudang pusat bertindak sebagai sumber pengiriman utama barang ke cabang-cabang.</small>

              <div class="form-check form-switch">
                <input
                  class="form-check-input"
                  type="checkbox"
                  id="activeCheck"
                  v-model="form.is_active"
                />
                <label class="form-check-label fw-semibold" for="activeCheck">
                  Status Cabang Aktif
                </label>
              </div>
            </div>

            <div class="modal-footer bg-light border-top py-2 px-4">
              <button type="button" class="btn btn-light" @click="showModal = false">Batal</button>
              <button type="submit" class="btn btn-primary fw-semibold" :disabled="submitting">
                <span v-if="submitting" class="spinner-border spinner-border-sm me-1"></span>
                <span>{{ isEditing ? 'Simpan Perubahan' : 'Tambah Cabang' }}</span>
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

const branches = ref([]);
const loading = ref(false);
const submitting = ref(false);
const showModal = ref(false);
const isEditing = ref(false);
const editingId = ref(null);

const alertMessage = ref('');
const alertType = ref('success');

const form = reactive({
  code: '',
  name: '',
  phone: '',
  address: '',
  is_central_warehouse: false,
  is_active: true,
});

async function fetchBranches() {
  loading.value = true;
  try {
    const res = await api.get('/branches');
    branches.value = res.data.data;
  } catch (err) {
    showAlert(err.response?.data?.message || 'Gagal memuat daftar cabang.', 'danger');
  } finally {
    loading.value = false;
  }
}

function openModal(branch = null) {
  if (branch) {
    isEditing.value = true;
    editingId.value = branch.id;
    form.code = branch.code;
    form.name = branch.name;
    form.phone = branch.phone || '';
    form.address = branch.address || '';
    form.is_central_warehouse = !!branch.is_central_warehouse;
    form.is_active = !!branch.is_active;
  } else {
    isEditing.value = false;
    editingId.value = null;
    form.code = '';
    form.name = '';
    form.phone = '';
    form.address = '';
    form.is_central_warehouse = false;
    form.is_active = true;
  }
  showModal.value = true;
}

async function saveBranch() {
  submitting.value = true;
  try {
    if (isEditing.value) {
      await api.put(`/branches/${editingId.value}`, form);
      showAlert('Data cabang berhasil diperbarui.', 'success');
    } else {
      await api.post('/branches', form);
      showAlert('Cabang baru berhasil ditambahkan.', 'success');
    }
    showModal.value = false;
    fetchBranches();
  } catch (err) {
    showAlert(err.response?.data?.message || 'Terjadi kesalahan saat menyimpan data.', 'danger');
  } finally {
    submitting.value = false;
  }
}

async function deleteBranch(branch) {
  if (!confirm(`Yakin ingin menghapus cabang "${branch.name}"?`)) return;
  try {
    await api.delete(`/branches/${branch.id}`);
    showAlert('Cabang berhasil dihapus.', 'success');
    fetchBranches();
  } catch (err) {
    showAlert(err.response?.data?.message || 'Gagal menghapus cabang.', 'danger');
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
  fetchBranches();
});
</script>
