<template>
  <section class="page">
    <div class="page-header">
      <h1 class="page-title">Pengguna</h1>
    </div>
    <form class="panel grid three" @submit.prevent="save">
      <FormField label="Username">
        <input v-model="form.username" required />
      </FormField>
      <FormField label="Email">
        <input v-model="form.email" type="email" required />
      </FormField>
      <FormField label="Password">
        <input v-model="form.password" type="password" :required="!editingId" />
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
      <FormField label="Status">
        <select v-model="form.status">
          <option value="active">active</option>
          <option value="inactive">inactive</option>
        </select>
      </FormField>
      <div class="actions" style="align-items: end">
        <button class="button" type="submit">{{ editingId ? 'Simpan' : 'Tambah' }}</button>
        <button v-if="editingId" class="button secondary" type="button" @click="reset">Batal</button>
      </div>
    </form>
    <div class="panel">
      <DataTable :rows="users" :columns="columns">
        <template #actions="{ row }">
          <button class="button secondary" type="button" @click="edit(row as User)">Ubah</button>
          <button class="button danger" type="button" @click="remove((row as User).id)">Nonaktifkan</button>
        </template>
      </DataTable>
    </div>
  </section>
</template>

<script setup lang="ts">
import { onMounted, reactive, ref } from 'vue';
import DataTable from '@/components/DataTable.vue';
import FormField from '@/components/FormField.vue';
import { getRoles } from '@/api/roles';
import { getSectors } from '@/api/sectors';
import { createUser, deleteUser, getUsers, updateUser, type UserPayload } from '@/api/users';
import type { Role, Sector, User } from '@/types/api';

const users = ref<User[]>([]);
const roles = ref<Role[]>([]);
const sectors = ref<Sector[]>([]);
const editingId = ref<number | null>(null);
const form = reactive<UserPayload & { password: string }>({
  username: '',
  email: '',
  password: '',
  nama_depan: '',
  nama_belakang: '',
  role_id: 0,
  sektor_id: null,
  status: 'active'
});
const columns = [
  { key: 'username', label: 'Username' },
  { key: 'nama_depan', label: 'Nama' },
  { key: 'email', label: 'Email' },
  { key: 'role_name', label: 'Role' },
  { key: 'sector_name', label: 'Sektor' },
  { key: 'status', label: 'Status' }
] as const;

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
    email: user.email,
    password: '',
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
    password: '',
    nama_depan: '',
    nama_belakang: '',
    role_id: 0,
    sektor_id: null,
    status: 'active'
  });
}

async function save() {
  const payload = { ...form, nama_belakang: form.nama_belakang || null };
  if (editingId.value) {
    const { password: _password, ...withoutPassword } = payload;
    await updateUser(editingId.value, withoutPassword);
  } else {
    await createUser(payload);
  }
  reset();
  await load();
}

async function remove(id: number) {
  await deleteUser(id);
  await load();
}

onMounted(load);
</script>
