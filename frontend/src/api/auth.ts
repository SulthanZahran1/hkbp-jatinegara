import client from './client';
import type { LoginResponse, User } from '@/types/api';

export async function login(payload: { username: string; password: string }) {
  const response = await client.post<LoginResponse>('/auth/login', payload);
  return response.data;
}

export async function refresh(refreshToken: string) {
  const response = await client.post<{ access_token: string; expires_in: number }>('/auth/refresh', {
    refresh_token: refreshToken
  });
  return response.data;
}

export async function me() {
  const response = await client.get<User>('/auth/me');
  return response.data;
}
