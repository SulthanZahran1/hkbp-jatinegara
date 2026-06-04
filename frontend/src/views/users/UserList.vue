<template>
  <section class="page">
    <div class="page-header">
      <h1 class="page-title">Pengguna</h1>
    </div>

    <p v-if="message" class="notice">{{ message }}</p>
    <p v-if="error" class="error">{{ error }}</p>

    <div v-if="setupLink" class="panel setup-link">
      <strong>Tautan setup kata sandi (tampil sekali):</strong>
      <code>{{ setupLink }}</code>
      <div class="actions">
        <button class="button secondary" type="button" @click="copySetupLink">Salin</button>
        <button class="button secondary" type="button" @click="setupLink = ''">Tutup</button>
      </div>
      <small>Bagikan tautan ini ke pengguna. Mereka memilih kata sandi sendiri; HKBP tidak menyimpan kata sandi.</small>
    </div>

    <form class="panel grid three" @submit.prevent="save">
      <FormField label="Username (IdP)">
        <input v-model="form.username" :readonly="!!editingId" required />
      </FormField>
      <FormField label="Email (opsional)">
        <input v-model="form.email" type="email" />
      </FormField>
      <FormField label="Nama Depan">
        <input v-model="form.nama_depan" required />
      </FormField>
      <FormField label="Nama Belakang">
        <input v-model="form.nama_belakang" />
      </FormField>
      <FormField label="Role">
        <select v-model.number="form.role_id" required>
          <option :value="0">Pilih role</option>
          <option v-for="role in roles" :key="role.id" :value="role.id">{{ role.name }}</option>
        </select>
      </FormField>
      <FormField label="Sektor">
        <select v-model="form.sektor_id">
          <option :value="null">Semua sektor</option>
          <option v-for="sector in sectors" :key="sector.id" :value="sector.id">{{ sector.name }}</option>
        </select>
      </FormField>
      <FormField v-if="editingId" label="Status">
        <select v-model="form.status">
          <option value="active">active</option>
          <option value="inactive">inactive</option>
        </select>
      </FormField>
      <div class="actions" style="align-items: end">
        <button class="button" type="submit" :disabled="busy">
          {{ editingId ? 'Simpan Profil' : 'Sediakan Pengguna' }}
        </button>
        <button v-if="editingId" class="button secondary" type="button" @click="reset">Batal</button>
      </div>
    </form>

    <div class="panel">
      <DataTable :rows="users" :columns="columns">
        <template #actions="{ row }">
          <button class="button secondary" type="button" @click="edit(row as User)">Ubah</button>
          <button
            v-if="(row as User).provisioning_status === 'failed_idp'"
            class="button"
            type="button"
            @click="retry((row as User).id)"
          >
            Coba Lagi
          </button>
          <button
            v-if="(row as User).has_identity"
            class="button secondary"
            type="button"
            @click="setupLinkFor((row as User).id)"
          >
            Tautan Setup
          </button>
          <button class="button secondary" type="button" @click="rename(row as User)">Ganti Username</button>
          <button class="button danger" type="button" @click="remove((row as User).id)">Nonaktifkan</button>
        </template>
      </DataTable>
    </div>
  </section>
</template>

<script setup lang="ts">
import { onMounted, reactive, ref } from 'vue';
import axios from 'axios';
import DataTable from '@/components/DataTable.vue';
import FormField from '@/components/FormField.vue';
import { getRoles } from '@/api/roles';
import { getSectors } from '@/api/sectors';
import {
  createSetupLink,
  deleteUser,
  getUsers,
  provisionUser,
  renameUser,
  retryProvisioning,
  updateUser
} from '@/api/users';
import type { Role, Sector, User } from '@/types/api';

const users = ref<User[]>([]);
const roles = ref<Role[]>([]);
const sectors = ref<Sector[]>([]);
const editingId = ref<number | null>(null);
const setupLink = ref('');
const message = ref('');
const error = ref('');
const busy = ref(false);

const form = reactive({
  username: '',
  email: '',
  nama_depan: '',
  nama_belakang: '',
  role_id: 0,
  sektor_id: null as number | null,
  status: 'active' as 'active' | 'inactive'
});

const columns = [
  { key: 'username', label: 'Username' },
  { key: 'nama_depan', label: 'Nama' },
  { key: 'role_name', label: 'Role' },
  { key: 'sector_name', label: 'Sektor' },
  { key: 'status', label: 'Status' },
  { key: 'provisioning_status', label: 'Provisioning' }
] as const;

function describeError(err: unknown, fallback: string) {
  if (axios.isAxiosError(err)) {
    const data = err.response?.data as { error?: string; detail?: string } | undefined;
    return data?.error ?? data?.detail ?? fallback;
  }
  return fallback;
}

async function load() {
  const [userResult, roleResult, sectorResult] = await Promise.all([getUsers(), getRoles(), getSectors()]);
  users.value = userResult.data;
  roles.value = roleResult.data;
  sectors.value = sectorResult.data;
}

function edit(user: User) {
  editingId.value = user.id;
  Object.assign(form, {
    username: user.username,
    email: user.email ?? '',
    nama_depan: user.nama_depan,
    nama_belakang: user.nama_belakang ?? '',
    role_id: user.role_id,
    sektor_id: user.sektor_id,
    status: user.status
  });
}

function reset() {
  editingId.value = null;
  Object.assign(form, {
    username: '',
    email: '',
    nama_depan: '',
    nama_belakang: '',
    role_id: 0,
    sektor_id: null,
    status: 'active'
  });
}

async function save() {
  error.value = '';
  message.value = '';
  busy.value = true;
  try {
    if (editingId.value) {
      await updateUser(editingId.value, {
        email: form.email || null,
        nama_depan: form.nama_depan,
        nama_belakang: form.nama_belakang || null,
        role_id: form.role_id,
        sektor_id: form.sektor_id,
        status: form.status
      });
      message.value = 'Profil pengguna diperbarui.';
    } else {
      const result = await provisionUser({
        username: form.username,
        email: form.email || undefined,
        nama_depan: form.nama_depan,
        nama_belakang: form.nama_belakang || null,
        role_id: form.role_id,
        sektor_id: form.sektor_id
      });
      if (result.setup_url) {
        setupLink.value = result.setup_url;
      }
      message.value = 'Pengguna disediakan di IdP.';
    }
    reset();
    await load();
  } catch (err) {
    error.value = describeError(err, 'Gagal menyimpan pengguna.');
  } finally {
    busy.value = false;
  }
}

async function setupLinkFor(id: number) {
  error.value = '';
  try {
    const result = await createSetupLink(id);
    setupLink.value = result.setup_url;
  } catch (err) {
    error.value = describeError(err, 'Gagal membuat tautan setup.');
  }
}

async function retry(id: number) {
  error.value = '';
  try {
    const result = await retryProvisioning(id);
    if (result.setup_url) {
      setupLink.value = result.setup_url;
    }
    message.value = 'Provisioning berhasil.';
    await load();
  } catch (err) {
    error.value = describeError(err, 'Provisioning ulang gagal.');
  }
}

async function rename(user: User) {
  const next = window.prompt(`Ganti username untuk ${user.username} (perlu rename di IdP):`, user.username);
  if (!next || next === user.username) {
    return;
  }
  error.value = '';
  try {
    await renameUser(user.id, next);
    message.value = 'Username diganti.';
    await load();
  } catch (err) {
    error.value = describeError(err, 'Ganti username diblokir/gagal (lihat kebijakan IdP rename).');
  }
}

async function remove(id: number) {
  if (!window.confirm('Nonaktifkan pengguna ini? Sesi aktif akan dicabut.')) {
    return;
  }
  await deleteUser(id);
  await load();
}

async function copySetupLink() {
  try {
    await navigator.clipboard.writeText(setupLink.value);
    message.value = 'Tautan disalin.';
  } catch {
    message.value = 'Salin manual tautan di atas.';
  }
}

onMounted(load);
</script>
