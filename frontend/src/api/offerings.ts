import client from './client';
import type { ApiList, Offering, OfferingReport } from '@/types/api';

export interface OfferingPayload {
  family_id: number;
  amount: number;
  month: number;
  year: number;
  notes?: string | null;
}

export async function getOfferings(params: Record<string, string | number | null | undefined> = {}) {
  const response = await client.get<ApiList<Offering>>('/offerings', { params });
  return response.data;
}

export async function createOffering(data: OfferingPayload) {
  const response = await client.post<{ id: number }>('/offerings', data);
  return response.data;
}

export async function getOfferingReport(params: Record<string, string | number | null | undefined> = {}) {
  const response = await client.get<OfferingReport>('/offerings/report', { params });
  return response.data;
}

export async function deleteOffering(id: number) {
  await client.delete(`/offerings/${id}`);
}
