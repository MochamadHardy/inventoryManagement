<template>
  <div id="layout-wrapper" :class="{ 'vertical-sidebar-enable': mobileSidebarOpen }">
    <!-- Topbar Component -->
    <Topbar @toggle-sidebar="toggleSidebar" />

    <!-- Sidebar Component -->
    <Sidebar />

    <!-- Mobile Backdrop Overlay -->
    <div
      v-if="mobileSidebarOpen"
      class="vertical-overlay"
      @click="mobileSidebarOpen = false"
    ></div>

    <!-- Main Content Area -->
    <div class="main-content">
      <div class="page-content">
        <div class="container-fluid">
          <!-- Child views are rendered here -->
          <slot />
        </div>
      </div>
      <Footer />
    </div>
  </div>
</template>

<script setup>
import { onMounted, ref } from 'vue';
import Footer from '../components/layout/Footer.vue';
import Sidebar from '../components/layout/Sidebar.vue';
import Topbar from '../components/layout/Topbar.vue';

const mobileSidebarOpen = ref(false);
const sidebarCollapsed = ref(false);

function toggleSidebar() {
  if (window.innerWidth < 992) {
    mobileSidebarOpen.value = !mobileSidebarOpen.value;
  } else {
    sidebarCollapsed.value = !sidebarCollapsed.value;
    const size = sidebarCollapsed.value ? 'sm' : 'lg';
    document.documentElement.setAttribute('data-sidebar-size', size);
  }
}

onMounted(() => {
  // Ensure default layout data attributes are present
  document.documentElement.setAttribute('data-layout', 'vertical');
  document.documentElement.setAttribute('data-topbar', 'light');
  document.documentElement.setAttribute('data-sidebar', 'light');
  document.documentElement.setAttribute('data-sidebar-size', 'lg');
});
</script>

<style scoped>
.vertical-overlay {
  position: fixed;
  top: 0;
  left: 0;
  width: 100vw;
  height: 100vh;
  background-color: rgba(33, 37, 41, 0.35);
  z-index: 1002;
}

@media (max-width: 991.98px) {
  #layout-wrapper.vertical-sidebar-enable :deep(.app-menu) {
    display: block !important;
    left: 0 !important;
    z-index: 1003;
  }
}
</style>
