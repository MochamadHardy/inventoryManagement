<template>
  <AdminLayout>
    <div class="row justify-content-center my-4">
      <div class="col-lg-8">
        <div class="card shadow-sm border-0 rounded-4 text-center p-5">
          <div class="mb-4">
            <span class="avatar-title bg-primary-subtle text-primary rounded-circle fs-40 p-4 d-inline-flex">
              <i class="mdi mdi-hammer-wrench"></i>
            </span>
          </div>
          <h4 class="fw-bold text-dark">{{ pageTitle }}</h4>
          <p class="text-muted fs-15 mt-2">
            Halaman ini adalah bagian dari <strong>{{ phaseName }}</strong>. Kita akan membangun logika backend API dan komponen antarmukanya secara bertahap!
          </p>
          <div class="mt-4 d-flex justify-content-center gap-2">
            <router-link to="/dashboard" class="btn btn-primary px-4 py-2">
              <i class="mdi mdi-arrow-left me-1"></i> Kembali ke Dashboard
            </router-link>
            <router-link to="/pos" class="btn btn-outline-success px-4 py-2">
              <i class="mdi mdi-calculator me-1"></i> Buka POS Kasir
            </router-link>
          </div>
        </div>
      </div>
    </div>
  </AdminLayout>
</template>

<script setup>
import { computed } from 'vue';
import { useRoute } from 'vue-router';
import AdminLayout from '../layouts/AdminLayout.vue';

const route = useRoute();

const pageTitle = computed(() => {
  return route.meta?.title || 'Modul Sedang Disiapkan';
});

const phaseName = computed(() => {
  return route.meta?.phase || 'Roadmap Pengerjaan';
});
</script>
