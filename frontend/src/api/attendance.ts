import client from './client';
import type { ApiList, Attendance } from '@/types/api';

export async function getAttendance(params: Record<string, string | number | null | undefined> = {}) {
  const response = await client.get<ApiList<Attendance>>('/attendance', { params });
  return response.data;
}

export async function recordAttendance(data: {
  member_id: number;
  date: string;
  status: 'hadir' | 'tidak_hadir' | 'izin' | 'sakit';
  seksi: 'musik' | 'multimedia';
}) {
  const response = await client.post<{ id: number }>('/attendance', data);
  return response.data;
}
