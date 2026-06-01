import client from './client';
import type { ApiList, Role } from '@/types/api';

export async function getRoles() {
  const response = await client.get<ApiList<Role>>('/roles');
  return response.data;
}
