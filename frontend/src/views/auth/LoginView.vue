<template>
  <div class="account-pages">
    <div class="container">
      <div class="row justify-content-center">
        <div class="col-md-11">
          <div class="auth-full-page-content d-flex min-vh-100 py-sm-5 py-4">
            <div class="w-100">
              <div class="d-flex flex-column h-100 py-0 py-xl-4">
                
                <!-- Logo Header -->
                <div class="text-center mb-4">
                  <h3 class="fw-bold text-primary mb-1">
                    <i class="mdi mdi-store fs-24 align-middle me-1"></i> Multi-Branch POS
                  </h3>
                  <p class="text-muted fs-14">Sistem Manajemen Inventaris & Kasir Multi-Cabang</p>
                </div>

                <div class="card my-auto overflow-hidden shadow-lg border-0 rounded-4">
                  <div class="row g-0">
                    
                    <!-- Left: Form Login -->
                    <div class="col-lg-6">
                      <div class="p-lg-5 p-4">
                        <div class="text-center mb-4">
                          <h4 class="mb-1 fw-bold text-dark">Selamat Datang Kembali!</h4>
                          <p class="text-muted fs-14">Silakan masuk menggunakan akun cabang Anda.</p>
                        </div>

                        <!-- Alert Error -->
                        <div v-if="errorMessage" class="alert alert-danger alert-dismissible fade show" role="alert">
                          <i class="mdi mdi-alert-circle-outline me-2 fs-16"></i>
                          {{ errorMessage }}
                          <button type="button" class="btn-close" @click="errorMessage = ''" aria-label="Close"></button>
                        </div>

                        <form @submit.prevent="handleLogin" class="auth-input">
                          <div class="mb-3">
                            <label for="email" class="form-label fw-semibold">Email Pengguna</label>
                            <div class="input-group">
                              <span class="input-group-text bg-light border-end-0">
                                <i class="mdi mdi-email-outline text-muted"></i>
                              </span>
                              <input
                                type="email"
                                class="form-control border-start-0 ps-0"
                                id="email"
                                v-model="form.email"
                                placeholder="Masukkan email terdaftar"
                                required
                                autofocus
                              />
                            </div>
                          </div>

                          <div class="mb-3">
                            <label for="password" class="form-label fw-semibold">Kata Sandi</label>
                            <div class="input-group">
                              <span class="input-group-text bg-light border-end-0">
                                <i class="mdi mdi-lock-outline text-muted"></i>
                              </span>
                              <input
                                :type="showPassword ? 'text' : 'password'"
                                class="form-control border-start-0 border-end-0 ps-0"
                                id="password"
                                v-model="form.password"
                                placeholder="Masukkan password"
                                required
                              />
                              <button
                                class="btn btn-light border border-start-0 text-muted"
                                type="button"
                                @click="showPassword = !showPassword"
                              >
                                <i :class="showPassword ? 'mdi mdi-eye-off-outline' : 'mdi mdi-eye-outline'"></i>
                              </button>
                            </div>
                          </div>

                          <div class="mt-4">
                            <button
                              class="btn btn-primary w-100 py-2 fs-15 fw-semibold d-flex align-items-center justify-content-center"
                              type="submit"
                              :disabled="authStore.loading"
                            >
                              <span v-if="authStore.loading" class="spinner-border spinner-border-sm me-2" role="status"></span>
                              <span>{{ authStore.loading ? 'Sedang Memverifikasi...' : 'Masuk ke Sistem' }}</span>
                            </button>
                          </div>
                        </form>

                        <!-- Quick Login Shortcuts untuk Testing & Pembelajaran -->
                        <div class="mt-4 pt-3 border-top">
                          <div class="d-flex align-items-center justify-content-between mb-2">
                            <span class="fs-12 fw-bold text-uppercase text-muted">
                              <i class="mdi mdi-lightning-bolt text-warning me-1"></i> Quick Login (Mode Belajar):
                            </span>
                          </div>
                          <div class="row g-2">
                            <div class="col-6">
                              <button
                                type="button"
                                class="btn btn-sm btn-outline-primary w-100 text-start py-2"
                                @click="setAccount('owner@pos.com', 'password123')"
                              >
                                <strong class="d-block text-truncate">👑 Owner (Pusat)</strong>
                                <small class="text-muted d-block text-truncate fs-11">owner@pos.com</small>
                              </button>
                            </div>
                            <div class="col-6">
                              <button
                                type="button"
                                class="btn btn-sm btn-outline-info w-100 text-start py-2"
                                @click="setAccount('gudang@pos.com', 'password123')"
                              >
                                <strong class="d-block text-truncate">📦 Gudang Pusat</strong>
                                <small class="text-muted d-block text-truncate fs-11">gudang@pos.com</small>
                              </button>
                            </div>
                            <div class="col-6">
                              <button
                                type="button"
                                class="btn btn-sm btn-outline-success w-100 text-start py-2"
                                @click="setAccount('kasir.jkt@pos.com', 'password123')"
                              >
                                <strong class="d-block text-truncate">🛒 Kasir Jakarta</strong>
                                <small class="text-muted d-block text-truncate fs-11">kasir.jkt@pos.com</small>
                              </button>
                            </div>
                            <div class="col-6">
                              <button
                                type="button"
                                class="btn btn-sm btn-outline-warning w-100 text-start py-2"
                                @click="setAccount('kasir.bdg@pos.com', 'password123')"
                              >
                                <strong class="d-block text-truncate">🛒 Kasir Bandung</strong>
                                <small class="text-muted d-block text-truncate fs-11">kasir.bdg@pos.com</small>
                              </button>
                            </div>
                          </div>
                        </div>

                      </div>
                    </div>

                    <!-- Right: Info Panel Banner Invoika -->
                    <div class="col-lg-6 d-none d-lg-block">
                      <div class="d-flex h-100 bg-primary bg-gradient align-items-center justify-content-center text-white p-5 position-relative">
                        <div class="text-center position-relative z-1">
                          <div class="mb-4">
                            <span class="avatar-title bg-white bg-opacity-10 text-white rounded-circle fs-36 p-4 d-inline-flex">
                              <i class="mdi mdi-shield-account-variant-outline"></i>
                            </span>
                          </div>
                          <h4 class="text-white fw-bold mb-3">Multi-Branch Architecture</h4>
                          <p class="text-white-50 fs-15 mb-4">
                            Sistem inventaris terintegrasi dengan proteksi Role-Based Access Control (RBAC), multi-gudang, kartu stok real-time, dan transaksi POS cepat.
                          </p>
                          <div class="d-flex justify-content-center gap-3 fs-13 text-white-50">
                            <div><i class="mdi mdi-check-circle text-success me-1"></i> Multi-Varian</div>
                            <div><i class="mdi mdi-check-circle text-success me-1"></i> Transfer Stok</div>
                            <div><i class="mdi mdi-check-circle text-success me-1"></i> POS Cepat</div>
                          </div>
                        </div>
                      </div>
                    </div>

                  </div>
                </div>

                <div class="text-center mt-4">
                  <p class="text-muted fs-13 mb-0">© 2026 Multi-Branch Inventory & POS System</p>
                </div>

              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { reactive, ref } from 'vue';
import { useRouter } from 'vue-router';
import { useAuthStore } from '../../stores/auth';

const router = useRouter();
const authStore = useAuthStore();

const showPassword = ref(false);
const errorMessage = ref('');

const form = reactive({
  email: 'owner@pos.com',
  password: 'password123',
});

function setAccount(email, password) {
  form.email = email;
  form.password = password;
  errorMessage.value = '';
}

async function handleLogin() {
  errorMessage.value = '';
  const result = await authStore.login({
    email: form.email,
    password: form.password,
  });

  if (result.success) {
    // Redirect ke dashboard sesuai role
    router.push('/dashboard');
  } else {
    errorMessage.value = result.message;
  }
}
</script>

<style scoped>
.account-pages {
  background-color: #f3f6f9;
  min-height: 100vh;
}
</style>
