import { defineStore } from 'pinia';
import { computed, ref } from 'vue';
import * as authApi from '@/api/auth';
import type { User } from '@/types/api';

// Auth is cookie-backed: there is no token in the browser. The store only mirrors
// the current user, resolved from GET /auth/me.
export const useAuthStore = defineStore('auth', () => {
  const user = ref<User | null>(null);
  const loaded = ref(false);
  const loading = ref(false);

  const isAuthenticated = computed(() => Boolean(user.value));
  const isAdmin = computed(() => user.value?.role_name === 'admin');
  const currentUser = computed(() => user.value);

  async function loadMe() {
    loading.value = true;
    try {
      user.value = await authApi.me();
    } catch {
      user.value = null;
    } finally {
      loaded.value = true;
      loading.value = false;
    }
    return user.value;
  }

  function login() {
    authApi.startLogin();
  }

  async function logout() {
    try {
      const { logout_url } = await authApi.logout();
      user.value = null;
      window.location.assign(logout_url);
    } catch {
      user.value = null;
      window.location.assign('/login');
    }
  }

  return { user, loaded, loading, isAuthenticated, isAdmin, currentUser, loadMe, login, logout };
});
