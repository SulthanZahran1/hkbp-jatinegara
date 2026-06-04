import client from './client';
import type { AccessRequest } from '@/types/api';

export interface AccessRequestList {
  data: AccessRequest[];
  counts: { pending: number };
}

export interface ApprovePayload {
  role_id?: number;
  sektor_id?: number | null;
  nama_depan?: string;
  nama_belakang?: string | null;
  target_user_id?: number | null;
  note?: string;
}

export async function getAccessRequests(params: { status?: string } = {}) {
  const response = await client.get<AccessRequestList>('/access-requests', { params });
  return response.data;
}

export async function approveAccessRequest(id: number, data: ApprovePayload) {
  const response = await client.post<{ id: number; status: string; user_id: number; message: string }>(
    `/access-requests/${id}/approve`,
    data
  );
  return response.data;
}

export async function rejectAccessRequest(id: number, note?: string) {
  const response = await client.post<{ id: number; status: string }>(`/access-requests/${id}/reject`, { note });
  return response.data;
}

export async function reopenAccessRequest(id: number) {
  const response = await client.post<{ id: number; status: string }>(`/access-requests/${id}/reopen`);
  return response.data;
}
