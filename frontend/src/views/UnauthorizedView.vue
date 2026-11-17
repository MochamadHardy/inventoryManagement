<template>
  <div class="account-pages my-5 pt-5">
    <div class="container">
      <div class="row justify-content-center">
        <div class="col-md-8 col-lg-6">
          <div class="card shadow-sm border-0 rounded-4 text-center p-5">
            <div class="mb-4">
              <span class="avatar-title bg-danger-subtle text-danger rounded-circle fs-48 p-4 d-inline-flex">
                <i class="mdi mdi-shield-lock-outline"></i>
              </span>
            </div>
            <h2 class="fw-bold text-dark">403 - Akses Ditolak</h2>
            <p class="text-muted fs-15 mt-2">
              Akun Anda dengan role <strong>{{ authStore.roleLabel }}</strong> tidak memiliki hak akses untuk membuka halaman ini.
            </p>
            <div class="mt-4">
              <router-link to="/dashboard" class="btn btn-primary px-4 py-2">
                <i class="mdi mdi-arrow-left me-1"></i> Kembali ke Dashboard
              </router-link>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { useAuthStore } from '../stores/auth';
const authStore = useAuthStore();
</script>
