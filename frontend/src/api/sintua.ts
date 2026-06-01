import client from './client';
import type { ApiList, Sintua } from '@/types/api';

export async function getSintua(params: Record<string, string | number | null | undefined> = {}) {
  const response = await client.get<ApiList<Sintua>>('/sintua', { params });
  return response.data;
}

export async function createSintua(data: { member_id: number }) {
  const response = await client.post<{ id: number }>('/sintua', data);
  return response.data;
}

export async function deleteSintua(id: number) {
  await client.delete(`/sintua/${id}`);
}
