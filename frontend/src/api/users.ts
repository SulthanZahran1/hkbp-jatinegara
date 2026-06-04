import client from './client';
import type { ApiList, User } from '@/types/api';

// Provisioning a user creates the HKBP row and the matching IdP account, then
// returns a one-time setup link the user uses to choose their own password.
export interface ProvisionPayload {
  username: string;
  email?: string;
  nama_depan: string;
  nama_belakang?: string | null;
  role_id: number;
  sektor_id?: number | null;
}

export interface ProvisionResult {
  user_id: number;
  provisioning_status: string;
  setup_url?: string;
  expires_at?: string;
}

// Editable profile/authorization fields only — username is an IdP-coordinated
// rename and credentials live in the IdP.
export interface UserUpdatePayload {
  email?: string | null;
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

export async function provisionUser(data: ProvisionPayload) {
  const response = await client.post<ProvisionResult>('/users', data);
  return response.data;
}

export async function updateUser(id: number, data: UserUpdatePayload) {
  const response = await client.put<{ id: number }>(`/users/${id}`, data);
  return response.data;
}

export async function createSetupLink(id: number) {
  const response = await client.post<{ setup_url: string; expires_at?: string }>(`/users/${id}/setup-link`);
  return response.data;
}

export async function retryProvisioning(id: number) {
  const response = await client.post<ProvisionResult>(`/users/${id}/retry-provisioning`);
  return response.data;
}

export async function renameUser(id: number, username: string) {
  const response = await client.post<{ id: number; username: string; status: string }>(`/users/${id}/rename`, { username });
  return response.data;
}

export async function deleteUser(id: number) {
  await client.delete(`/users/${id}`);
}
