import client from './client';
import type { ApiList, FamilyDetail, FamilySummary, Member } from '@/types/api';

export interface FamilyPayload {
  sector_id: number;
  alamat?: string | null;
  members: Member[];
}

export async function getFamilies(params: Record<string, string | number | null | undefined> = {}) {
  const response = await client.get<ApiList<FamilySummary>>('/families', { params });
  return response.data;
}

export async function getFamily(id: number) {
  const response = await client.get<FamilyDetail>(`/families/${id}`);
  return response.data;
}

export async function createFamily(data: FamilyPayload) {
  const response = await client.post<{ id: number; head_member_id: number }>('/families', data);
  return response.data;
}

export async function updateFamily(id: number, data: { sector_id: number; alamat?: string | null }) {
  const response = await client.put<{ id: number }>(`/families/${id}`, data);
  return response.data;
}

export async function deleteFamily(id: number) {
  await client.delete(`/families/${id}`);
}
