import axios, { type InternalAxiosRequestConfig } from 'axios';

const baseURL = import.meta.env.VITE_API_BASE_URL ?? 'http://localhost:8080/api/v1';

const client = axios.create({
  baseURL,
  headers: {
    'Content-Type': 'application/json'
  }
});

let refreshPromise: Promise<string | null> | null = null;

client.interceptors.request.use((config) => {
  const token = localStorage.getItem('access_token');
  if (token) {
    config.headers.Authorization = `Bearer ${token}`;
  }
  return config;
});

client.interceptors.response.use(
  (response) => response,
  async (error) => {
    const original = error.config as (InternalAxiosRequestConfig & { _retry?: boolean }) | undefined;
    const status = error.response?.status;
    const isAuthRoute = original?.url?.includes('/auth/login') || original?.url?.includes('/auth/refresh');

    if (!original || status !== 401 || original._retry || isAuthRoute) {
      return Promise.reject(error);
    }

    const refreshToken = localStorage.getItem('refresh_token');
    if (!refreshToken) {
      clearAuthAndRedirect();
      return Promise.reject(error);
    }

    original._retry = true;
    refreshPromise ??= axios
      .post<{ access_token: string }>(`${baseURL}/auth/refresh`, { refresh_token: refreshToken })
      .then((response) => {
        localStorage.setItem('access_token', response.data.access_token);
        return response.data.access_token;
      })
      .catch(() => {
        clearAuthAndRedirect();
        return null;
      })
      .finally(() => {
        refreshPromise = null;
      });

    const token = await refreshPromise;
    if (!token) {
      return Promise.reject(error);
    }
    original.headers.Authorization = `Bearer ${token}`;
    return client(original);
  }
);

function clearAuthAndRedirect() {
  localStorage.removeItem('access_token');
  localStorage.removeItem('refresh_token');
  localStorage.removeItem('current_user');
  if (window.location.pathname !== '/login') {
    window.location.assign('/login');
  }
}

export default client;
