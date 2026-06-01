import client from './client';
import type { ApiList, Member } from '@/types/api';

export async function getMembers(params: Record<string, string | number | null | undefined> = {}) {
  const response = await client.get<ApiList<Member>>('/members', { params });
  return response.data;
}

export async function getMember(id: number) {
  const response = await client.get<Member>(`/members/${id}`);
  return response.data;
}

export async function updateMember(id: number, data: Member) {
  const response = await client.put<{ id: number }>(`/members/${id}`, data);
  return response.data;
}

export async function uploadMemberPhoto(id: number, file: File) {
  const form = new FormData();
  form.append('foto', file);
  const response = await client.post<{ foto_url: string }>(`/members/${id}/foto`, form, {
    headers: { 'Content-Type': 'multipart/form-data' }
  });
  return response.data;
}

export async function deleteMember(id: number) {
  await client.delete(`/members/${id}`);
}
