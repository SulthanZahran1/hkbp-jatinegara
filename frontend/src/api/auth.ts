import client from './client';
import type { User } from '@/types/api';

const apiBase = import.meta.env.VITE_API_BASE_URL ?? '/api/v1';

// The backend owns the OIDC authorization-URL construction, so "sign in" is a
// full-page navigation to GET /auth/login (which 302-redirects to the IdP).
export function loginUrl() {
  return `${apiBase}/auth/login`;
}

export function startLogin() {
  window.location.assign(loginUrl());
}

export async function me() {
  const response = await client.get<User>('/auth/me');
  return response.data;
}

export async function logout() {
  const response = await client.post<{ logout_url: string; post_logout_redirect: string }>('/auth/logout');
  return response.data;
}
