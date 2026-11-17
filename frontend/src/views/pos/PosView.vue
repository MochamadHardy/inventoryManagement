<template>
  <PosLayout>
    <div class="row h-100 g-3">
      
      <!-- Left Column: Search & Product Catalog Grid -->
      <div class="col-lg-8 d-flex flex-column h-100">
        <!-- Search Bar with Barcode Scanner Icon -->
        <div class="card shadow-sm border-0 mb-3 rounded-3">
          <div class="card-body p-3">
            <div class="input-group">
              <span class="input-group-text bg-white border-end-0">
                <i class="mdi mdi-barcode-scan fs-20 text-primary"></i>
              </span>
              <input
                type="text"
                class="form-control form-control-lg border-start-0 ps-0 fs-15"
                placeholder="Scan barcode fisik atau ketik nama barang/SKU (Tekan F2)..."
                autofocus
              />
              <button class="btn btn-primary px-4 fw-semibold" type="button">
                <i class="mdi mdi-magnify me-1"></i> Cari
              </button>
            </div>
          </div>
        </div>

        <!-- Catalog Grid Preview -->
        <div class="card shadow-sm border-0 flex-grow-1 overflow-auto rounded-3">
          <div class="card-header bg-white border-bottom py-3 d-flex justify-content-between align-items-center">
            <h6 class="mb-0 fw-bold text-dark">
              <i class="mdi mdi-package-variant me-1 text-primary"></i> Katalog Produk Cabang Aktif
            </h6>
            <span class="badge bg-light text-muted border">Katalog Demo (Fase 3 Templating)</span>
          </div>
          <div class="card-body p-3">
            <div class="row g-3">
              <!-- Item 1 -->
              <div class="col-xl-3 col-md-4 col-sm-6">
                <div class="card border h-100 product-card shadow-sm">
                  <div class="card-body p-3 d-flex flex-column">
                    <span class="badge bg-primary-subtle text-primary mb-2 align-self-start fs-11">Kaos Polos</span>
                    <h6 class="fw-bold mb-1 fs-14 text-dark text-truncate">Kaos Combed 30s</h6>
                    <small class="text-muted mb-2">Hitam - L (TSH-BLK-L)</small>
                    <div class="mt-auto d-flex justify-content-between align-items-center">
                      <span class="fw-bold text-primary fs-15">Rp 47.000</span>
                      <button class="btn btn-sm btn-soft-primary">
                        <i class="mdi mdi-plus"></i> Tambah
                      </button>
                    </div>
                  </div>
                </div>
              </div>

              <!-- Item 2 -->
              <div class="col-xl-3 col-md-4 col-sm-6">
                <div class="card border h-100 product-card shadow-sm">
                  <div class="card-body p-3 d-flex flex-column">
                    <span class="badge bg-primary-subtle text-primary mb-2 align-self-start fs-11">Kaos Polos</span>
                    <h6 class="fw-bold mb-1 fs-14 text-dark text-truncate">Kaos Combed 30s</h6>
                    <small class="text-muted mb-2">Putih - M (TSH-WHT-M)</small>
                    <div class="mt-auto d-flex justify-content-between align-items-center">
                      <span class="fw-bold text-primary fs-15">Rp 45.000</span>
                      <button class="btn btn-sm btn-soft-primary">
                        <i class="mdi mdi-plus"></i> Tambah
                      </button>
                    </div>
                  </div>
                </div>
              </div>

              <!-- Item 3 -->
              <div class="col-xl-3 col-md-4 col-sm-6">
                <div class="card border h-100 product-card shadow-sm">
                  <div class="card-body p-3 d-flex flex-column">
                    <span class="badge bg-info-subtle text-info mb-2 align-self-start fs-11">Aksesoris</span>
                    <h6 class="fw-bold mb-1 fs-14 text-dark text-truncate">Kabel Type-C 65W</h6>
                    <small class="text-muted mb-2">Hitam 1M (CBL-TYPC-1M)</small>
                    <div class="mt-auto d-flex justify-content-between align-items-center">
                      <span class="fw-bold text-primary fs-15">Rp 25.000</span>
                      <button class="btn btn-sm btn-soft-primary">
                        <i class="mdi mdi-plus"></i> Tambah
                      </button>
                    </div>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- Right Column: POS Cashier Cart -->
      <div class="col-lg-4 d-flex flex-column h-100">
        <div class="card shadow-sm border-0 flex-grow-1 d-flex flex-column rounded-3 overflow-hidden">
          <div class="card-header bg-primary text-white py-3 d-flex justify-content-between align-items-center">
            <h6 class="mb-0 fw-bold text-white">
              <i class="mdi mdi-cart-outline me-1"></i> Keranjang Kasir
            </h6>
            <span class="badge bg-white text-primary">Invoice: INV-TEMP-001</span>
          </div>

          <!-- Cart Table -->
          <div class="card-body p-0 flex-grow-1 overflow-auto">
            <table class="table table-hover align-middle mb-0">
              <thead class="table-light fs-12 text-uppercase text-muted">
                <tr>
                  <th>Item</th>
                  <th class="text-center" style="width: 70px;">Qty</th>
                  <th class="text-end" style="width: 90px;">Subtotal</th>
                  <th style="width: 30px;"></th>
                </tr>
              </thead>
              <tbody class="fs-13">
                <tr>
                  <td>
                    <div class="fw-semibold text-dark">Kaos Combed 30s</div>
                    <small class="text-muted">Hitam - L @ Rp 47.000</small>
                  </td>
                  <td class="text-center">1</td>
                  <td class="text-end fw-semibold">Rp 47.000</td>
                  <td class="text-center text-muted"><i class="mdi mdi-close"></i></td>
                </tr>
                <tr>
                  <td>
                    <div class="fw-semibold text-dark">Kabel Type-C 65W</div>
                    <small class="text-muted">1M @ Rp 25.000</small>
                  </td>
                  <td class="text-center">2</td>
                  <td class="text-end fw-semibold">Rp 50.000</td>
                  <td class="text-center text-muted"><i class="mdi mdi-close"></i></td>
                </tr>
              </tbody>
            </table>
          </div>

          <!-- Cart Summary & Checkout Button -->
          <div class="card-footer bg-light p-3 border-top">
            <div class="d-flex justify-content-between mb-1 fs-13">
              <span class="text-muted">Subtotal:</span>
              <span class="fw-semibold">Rp 97.000</span>
            </div>
            <div class="d-flex justify-content-between mb-2 fs-13">
              <span class="text-muted">Diskon:</span>
              <span class="text-danger fw-semibold">Rp 0</span>
            </div>
            <div class="d-flex justify-content-between mb-3 pt-2 border-top">
              <span class="fs-16 fw-bold text-dark">Total Bayar:</span>
              <span class="fs-18 fw-bold text-success">Rp 97.000</span>
            </div>
            <button class="btn btn-success w-100 py-3 fs-16 fw-bold shadow-sm d-flex align-items-center justify-content-center gap-2">
              <i class="mdi mdi-cash-check fs-20"></i>
              <span>PROSES BAYAR (F8)</span>
            </button>
          </div>
        </div>
      </div>

    </div>
  </PosLayout>
</template>

<script setup>
import PosLayout from '../../layouts/PosLayout.vue';
</script>

<style scoped>
.product-card {
  transition: transform 0.15s ease, box-shadow 0.15s ease;
  cursor: pointer;
}
.product-card:hover {
  transform: translateY(-2px);
  box-shadow: 0 4px 12px rgba(0, 0, 0, 0.08) !important;
}
.btn-soft-primary {
  background-color: rgba(64, 81, 137, 0.1);
  color: #405189;
  border: none;
}
.btn-soft-primary:hover {
  background-color: #405189;
  color: #fff;
}
</style>
