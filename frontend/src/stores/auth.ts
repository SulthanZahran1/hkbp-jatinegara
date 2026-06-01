import { defineStore } from 'pinia';
import { computed, ref } from 'vue';
import * as authApi from '@/api/auth';
import type { User } from '@/types/api';

function storedUser(): User | null {
  const raw = localStorage.getItem('current_user');
  if (!raw) return null;
  try {
    return JSON.parse(raw) as User;
  } catch {
    localStorage.removeItem('current_user');
    return null;
  }
}

export const useAuthStore = defineStore('auth', () => {
  const accessToken = ref(localStorage.getItem('access_token'));
  const refreshToken = ref(localStorage.getItem('refresh_token'));
  const user = ref<User | null>(storedUser());
  const loading = ref(false);

  const isAuthenticated = computed(() => Boolean(accessToken.value));
  const isAdmin = computed(() => user.value?.role_name === 'admin');
  const currentUser = computed(() => user.value);

  function persist(tokens: { access_token: string; refresh_token?: string }, nextUser?: User) {
    accessToken.value = tokens.access_token;
    localStorage.setItem('access_token', tokens.access_token);
    if (tokens.refresh_token) {
      refreshToken.value = tokens.refresh_token;
      localStorage.setItem('refresh_token', tokens.refresh_token);
    }
    if (nextUser) {
      user.value = nextUser;
      localStorage.setItem('current_user', JSON.stringify(nextUser));
    }
  }

  async function login(payload: { username: string; password: string }) {
    loading.value = true;
    try {
      const response = await authApi.login(payload);
      persist(response, response.user);
      return response.user;
    } finally {
      loading.value = false;
    }
  }

  async function loadMe() {
    if (!accessToken.value) return null;
    const profile = await authApi.me();
    user.value = profile;
    localStorage.setItem('current_user', JSON.stringify(profile));
    return profile;
  }

  async function refreshAccessToken() {
    if (!refreshToken.value) return null;
    const response = await authApi.refresh(refreshToken.value);
    persist({ access_token: response.access_token });
    return response.access_token;
  }

  function logout() {
    accessToken.value = null;
    refreshToken.value = null;
    user.value = null;
    localStorage.removeItem('access_token');
    localStorage.removeItem('refresh_token');
    localStorage.removeItem('current_user');
  }

  return {
    accessToken,
    refreshToken,
    user,
    loading,
    isAuthenticated,
    isAdmin,
    currentUser,
    login,
    loadMe,
    refreshAccessToken,
    logout
  };
});
