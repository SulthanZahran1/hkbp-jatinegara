<template>
  <section class="page">
    <div class="page-header">
      <h1 class="page-title">Permintaan Akses</h1>
      <span v-if="pendingCount" class="badge">{{ pendingCount }} menunggu</span>
    </div>

    <p v-if="message" class="notice">{{ message }}</p>
    <p v-if="error" class="error">{{ error }}</p>

    <div class="panel filters">
      <label>
        Status
        <select v-model="statusFilter" @change="load">
          <option value="">Semua</option>
          <option value="pending">Menunggu</option>
          <option value="approved">Disetujui</option>
          <option value="rejected">Ditolak</option>
        </select>
      </label>
    </div>

    <div v-for="req in requests" :key="req.id" class="panel request-card">
      <div class="request-head">
        <div>
          <strong>{{ req.preferred_username }}</strong>
          <small>{{ typeLabel(req.request_type) }} · {{ req.status }}</small>
          <small v-if="req.email">{{ req.email }} {{ req.email_verified ? '(terverifikasi)' : '' }}</small>
        </div>
        <small>{{ req.created_at }}</small>
      </div>

      <template v-if="req.status === 'pending'">
        <div class="request-form grid three">
          <template v-if="req.request_type === 'new_user'">
            <FormField label="Role">
              <select v-model.number="decisions[req.id].role_id" required>
                <option :value="0">Pilih role</option>
                <option v-for="role in roles" :key="role.id" :value="role.id">{{ role.name }}</option>
              </select>
            </FormField>
            <FormField label="Sektor">
              <select v-model="decisions[req.id].sektor_id">
                <option :value="null">Semua sektor</option>
                <option v-for="sector in sectors" :key="sector.id" :value="sector.id">{{ sector.name }}</option>
              </select>
            </FormField>
            <FormField label="Nama Depan">
              <input v-model="decisions[req.id].nama_depan" :placeholder="req.preferred_username" />
            </FormField>
          </template>

          <template v-else-if="req.request_type === 'link_existing_user'">
            <FormField label="Tautkan ke pengguna">
              <select v-model="decisions[req.id].target_user_id">
                <option :value="null">Pilih pengguna</option>
                <option v-for="u in users" :key="u.id" :value="u.id">{{ u.username }} ({{ u.nama_depan }})</option>
              </select>
            </FormField>
            <small v-if="req.suggested_username">Saran: {{ req.suggested_username }}</small>
          </template>

          <template v-else>
            <small>Aktifkan kembali {{ req.target_username ?? req.preferred_username }}.</small>
          </template>
        </div>

        <div class="actions">
          <button class="button" type="button" @click="approve(req)">Setujui</button>
          <button class="button danger" type="button" @click="reject(req)">Tolak</button>
        </div>
      </template>

      <div v-else class="actions">
        <small>
          {{ req.status }}<span v-if="req.decided_by_username"> oleh {{ req.decided_by_username }}</span>
          <span v-if="req.decided_at"> · {{ req.decided_at }}</span>
        </small>
        <button class="button secondary" type="button" @click="reopen(req.id)">Buka Kembali</button>
      </div>
    </div>

    <p v-if="!requests.length" class="hint">Tidak ada permintaan.</p>
  </section>
</template>

<script setup lang="ts">
import { onMounted, reactive, ref } from 'vue';
import axios from 'axios';
import FormField from '@/components/FormField.vue';
import { getRoles } from '@/api/roles';
import { getSectors } from '@/api/sectors';
import { getUsers } from '@/api/users';
import {
  approveAccessRequest,
  getAccessRequests,
  rejectAccessRequest,
  reopenAccessRequest
} from '@/api/accessRequests';
import type { AccessRequest, AccessRequestType, Role, Sector, User } from '@/types/api';

interface Decision {
  role_id: number;
  sektor_id: number | null;
  nama_depan: string;
  target_user_id: number | null;
}

const requests = ref<AccessRequest[]>([]);
const roles = ref<Role[]>([]);
const sectors = ref<Sector[]>([]);
const users = ref<User[]>([]);
const pendingCount = ref(0);
const statusFilter = ref('pending');
const message = ref('');
const error = ref('');
const decisions = reactive<Record<number, Decision>>({});

function typeLabel(t: AccessRequestType) {
  return t === 'new_user' ? 'Pengguna Baru' : t === 'link_existing_user' ? 'Tautkan Pengguna' : 'Aktivasi Ulang';
}

function describeError(err: unknown, fallback: string) {
  if (axios.isAxiosError(err)) {
    const data = err.response?.data as { error?: string; detail?: string } | undefined;
    return data?.error ?? data?.detail ?? fallback;
  }
  return fallback;
}

async function load() {
  const [reqResult, roleResult, sectorResult, userResult] = await Promise.all([
    getAccessRequests(statusFilter.value ? { status: statusFilter.value } : {}),
    getRoles(),
    getSectors(),
    getUsers()
  ]);
  requests.value = reqResult.data;
  pendingCount.value = reqResult.counts.pending;
  roles.value = roleResult.data;
  sectors.value = sectorResult.data;
  users.value = userResult.data;
  for (const req of requests.value) {
    if (!decisions[req.id]) {
      decisions[req.id] = {
        role_id: 0,
        sektor_id: null,
        nama_depan: '',
        target_user_id: req.suggested_user_id ?? null
      };
    }
  }
}

async function approve(req: AccessRequest) {
  error.value = '';
  message.value = '';
  const d = decisions[req.id];
  try {
    await approveAccessRequest(req.id, {
      role_id: d.role_id || undefined,
      sektor_id: d.sektor_id,
      nama_depan: d.nama_depan || undefined,
      target_user_id: d.target_user_id
    });
    message.value = 'Disetujui. Pengguna harus masuk kembali untuk mendapatkan sesi.';
    await load();
  } catch (err) {
    error.value = describeError(err, 'Gagal menyetujui permintaan.');
  }
}

async function reject(req: AccessRequest) {
  const note = window.prompt('Alasan penolakan (opsional):') ?? '';
  error.value = '';
  try {
    await rejectAccessRequest(req.id, note);
    message.value = 'Permintaan ditolak.';
    await load();
  } catch (err) {
    error.value = describeError(err, 'Gagal menolak permintaan.');
  }
}

async function reopen(id: number) {
  error.value = '';
  try {
    await reopenAccessRequest(id);
    await load();
  } catch (err) {
    error.value = describeError(err, 'Gagal membuka kembali permintaan.');
  }
}

onMounted(load);
</script>
