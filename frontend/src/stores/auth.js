import { defineStore } from 'pinia';
import api from '../api/axios';

export const useAuthStore = defineStore('auth', {
  state: () => ({
    token: localStorage.getItem('token') || null,
    user: JSON.parse(localStorage.getItem('user') || 'null'),
    loading: false,
    error: null,
  }),

  getters: {
    isAuthenticated: (state) => !!state.token,
    isSuperAdmin: (state) => state.user?.role === 'super_admin',
    isCentralWarehouse: (state) => state.user?.role === 'central_warehouse',
    isBranchCashier: (state) => state.user?.role === 'branch_cashier',
    userBranch: (state) => state.user?.branch || null,
    roleLabel: (state) => {
      switch (state.user?.role) {
        case 'super_admin':
          return 'Super Admin (Owner)';
        case 'central_warehouse':
          return 'Gudang Pusat';
        case 'branch_cashier':
          return 'Kasir Cabang';
        default:
          return 'Tamu';
      }
    },
  },

  actions: {
    async login(credentials) {
      this.loading = true;
      this.error = null;
      try {
        const response = await api.post('/auth/login', credentials);
        const { token, user } = response.data.data;

        this.token = token;
        this.user = user;

        localStorage.setItem('token', token);
        localStorage.setItem('user', JSON.stringify(user));

        return { success: true, user };
      } catch (err) {
        const message =
          err.response?.data?.message ||
          err.response?.data?.errors?.email?.[0] ||
          'Gagal melakukan login. Periksa koneksi Anda.';
        this.error = message;
        return { success: false, message };
      } finally {
        this.loading = false;
      }
    },

    async logout() {
      try {
        if (this.token) {
          await api.post('/auth/logout');
        }
      } catch (err) {
        console.warn('Gagal revoke token di server:', err);
      } finally {
        this.token = null;
        this.user = null;
        localStorage.removeItem('token');
        localStorage.removeItem('user');
      }
    },

    async fetchMe() {
      if (!this.token) return;
      try {
        const response = await api.get('/auth/me');
        this.user = response.data.data.user;
        localStorage.setItem('user', JSON.stringify(this.user));
      } catch (err) {
        console.error('Gagal mengambil data profil:', err);
      }
    },
  },
});
