<template>
  <div class="pos-layout-wrapper d-flex flex-column vh-100 overflow-hidden bg-light">
    <!-- Minimalist POS Header -->
    <header class="pos-header bg-white border-bottom shadow-sm px-4 py-2 d-flex justify-content-between align-items-center">
      <div class="d-flex align-items-center gap-3">
        <router-link to="/dashboard" class="btn btn-sm btn-outline-secondary d-flex align-items-center gap-1">
          <i class="mdi mdi-arrow-left"></i>
          <span>Dashboard</span>
        </router-link>
        <div class="d-flex align-items-center gap-2">
          <i class="mdi mdi-store fs-22 text-success"></i>
          <div>
            <h6 class="mb-0 fw-bold fs-15 text-dark">
              POS Kasir: {{ authStore.user?.branch?.name || 'Pusat' }}
            </h6>
            <small class="text-muted">Kasir: {{ authStore.user?.name }}</small>
          </div>
        </div>
      </div>

      <!-- Center: Shortcut Key Hints -->
      <div class="d-none d-lg-flex align-items-center gap-2">
        <span class="badge bg-secondary-subtle text-secondary border px-2 py-1 fs-12">
          <kbd>F2</kbd> Cari Produk
        </span>
        <span class="badge bg-success-subtle text-success border border-success-subtle px-2 py-1 fs-12">
          <kbd>F8</kbd> Bayar
        </span>
        <span class="badge bg-danger-subtle text-danger border border-danger-subtle px-2 py-1 fs-12">
          <kbd>ESC</kbd> Reset
        </span>
      </div>

      <!-- Right: Clock & User -->
      <div class="d-flex align-items-center gap-3">
        <div class="text-end d-none d-sm-block">
          <div class="fw-bold fs-15 text-primary">{{ currentTime }}</div>
          <small class="text-muted">{{ currentDate }}</small>
        </div>
        <button class="btn btn-sm btn-outline-danger" @click="handleLogout" title="Keluar Kasir">
          <i class="mdi mdi-logout"></i>
        </button>
      </div>
    </header>

    <!-- POS Content Area -->
    <main class="pos-body flex-grow-1 overflow-hidden p-3">
      <slot />
    </main>
  </div>
</template>

<script setup>
import { onBeforeUnmount, onMounted, ref } from 'vue';
import { useRouter } from 'vue-router';
import { useAuthStore } from '../stores/auth';

const router = useRouter();
const authStore = useAuthStore();

const currentTime = ref('');
const currentDate = ref('');
let timer = null;

function updateTime() {
  const now = new Date();
  currentTime.value = now.toLocaleTimeString('id-ID', { hour: '2-digit', minute: '2-digit', second: '2-digit' });
  currentDate.value = now.toLocaleDateString('id-ID', { weekday: 'short', day: 'numeric', month: 'short', year: 'numeric' });
}

async function handleLogout() {
  await authStore.logout();
  router.push('/login');
}

onMounted(() => {
  updateTime();
  timer = setInterval(updateTime, 1000);
});

onBeforeUnmount(() => {
  if (timer) clearInterval(timer);
});
</script>

<style scoped>
.pos-layout-wrapper {
  background-color: #f3f6f9;
}
.pos-header {
  height: 60px;
}
.pos-body {
  height: calc(100vh - 60px);
}
kbd {
  background-color: #343a40;
  color: #fff;
  padding: 0.15rem 0.35rem;
  border-radius: 0.25rem;
  font-size: 0.75rem;
}
</style>
