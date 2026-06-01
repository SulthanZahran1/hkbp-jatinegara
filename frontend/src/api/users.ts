import client from './client';
import type { ApiList, User } from '@/types/api';

export interface UserPayload {
  username: string;
  email: string;
  password?: string;
  nama_depan: string;
  nama_belakang?: string | null;
  role_id: number;
  sektor_id?: number | null;
  status?: 'active' | 'inactive';
}

export async function getUsers(params: Record<string, string | number | null | undefined> = {}) {
  const response = await client.get<ApiList<User>>('/users', { params });
  return response.data;
}

export async function createUser(data: UserPayload & { password: string }) {
  const response = await client.post<{ id: number }>('/users', data);
  return response.data;
}

export async function updateUser(id: number, data: UserPayload) {
  const response = await client.put<{ id: number }>(`/users/${id}`, data);
  return response.data;
}

export async function changeUserPassword(id: number, data: { password?: string; new_password: string }) {
  const response = await client.put<{ status: string }>(`/users/${id}/password`, data);
  return response.data;
}

export async function deleteUser(id: number) {
  await client.delete(`/users/${id}`);
}
