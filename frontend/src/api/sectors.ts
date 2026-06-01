import client from './client';
import type { ApiList, Sector } from '@/types/api';

export async function getSectors() {
  const response = await client.get<ApiList<Sector>>('/sectors');
  return response.data;
}

export async function createSector(data: { name: string }) {
  const response = await client.post<Sector>('/sectors', data);
  return response.data;
}

export async function updateSector(id: number, data: { name: string }) {
  const response = await client.put<Sector>(`/sectors/${id}`, data);
  return response.data;
}

export async function deleteSector(id: number) {
  await client.delete(`/sectors/${id}`);
}
