<template>
  <section class="page">
    <div class="page-header">
      <h1 class="page-title">Data Ruas</h1>
    </div>
    <form class="panel grid three" @submit.prevent="load">
      <FormField label="Cari Nama, Marga, No HP">
        <input v-model="filters.search" />
      </FormField>
      <FormField label="Sektor">
        <select v-model="filters.sektor_id">
          <option value="">Semua sektor</option>
          <option v-for="sector in sectors" :key="sector.id" :value="sector.id">{{ sector.name }}</option>
        </select>
      </FormField>
      <FormField label="Hubungan Keluarga">
        <select v-model="filters.hubungan">
          <option value="">Semua hubungan</option>
          <option v-for="option in relationOptions" :key="option" :value="option">{{ option }}</option>
        </select>
      </FormField>
      <div class="actions">
        <button class="button" type="submit">Cari</button>
      </div>
    </form>

    <div class="panel">
      <DataTable :rows="members" :columns="columns">
        <template #actions="{ row }">
          <button class="button secondary" type="button" @click="edit(row as Member)">Ubah</button>
        </template>
      </DataTable>
      <Pagination v-model:page="pagination.page" :total-pages="pagination.total_pages" @update:page="load" />
    </div>

    <form v-if="editing" class="panel grid" @submit.prevent="save">
      <div class="page-header">
        <h2>Ubah Data Ruas</h2>
        <button class="button secondary" type="button" @click="editing = null">Tutup</button>
      </div>
      <div class="grid three">
        <FormField label="Nama Lengkap">
          <input v-model="editing.nama" required />
        </FormField>
        <FormField label="Marga">
          <input v-model="editing.marga" />
        </FormField>
        <FormField label="Gender">
          <select v-model="editing.gender" required>
            <option v-for="option in genderOptions" :key="option" :value="option">{{ option }}</option>
          </select>
        </FormField>
        <FormField label="Tempat Lahir">
          <input v-model="editing.tempat_lahir" />
        </FormField>
        <FormField label="Tanggal Lahir">
          <input v-model="editing.tanggal_lahir" type="date" />
        </FormField>
        <FormField label="Golongan Darah">
          <select v-model="editing.gol_darah">
            <option v-for="option in bloodOptions" :key="option" :value="option">{{ option || '-' }}</option>
          </select>
        </FormField>
        <FormField label="Hubungan Keluarga">
          <select v-model="editing.hubungan_keluarga" required>
            <option v-for="option in relationOptions" :key="option" :value="option">{{ option }}</option>
          </select>
        </FormField>
        <FormField label="Pendidikan Terakhir">
          <select v-model="editing.pendidikan">
            <option v-for="option in educationOptions" :key="option" :value="option">{{ option || '-' }}</option>
          </select>
        </FormField>
        <FormField label="Pekerjaan">
          <input v-model="editing.pekerjaan" />
        </FormField>
        <FormField label="Talenta">
          <input v-model="editing.talenta" />
        </FormField>
        <FormField label="No Handphone">
          <input v-model="editing.no_hp" />
        </FormField>
        <FormField label="Alamat">
          <input v-model="editing.alamat" />
        </FormField>
        <FormField label="Propinsi">
          <input v-model="editing.provinsi" />
        </FormField>
        <FormField label="Kota">
          <input v-model="editing.kota" />
        </FormField>
        <FormField label="Kecamatan">
          <input v-model="editing.kecamatan" />
        </FormField>
        <FormField label="Kelurahan">
          <input v-model="editing.kelurahan" />
        </FormField>
        <FormField label="Kode Pos">
          <input v-model="editing.kode_pos" />
        </FormField>
        <FormField label="Foto URL">
          <input v-model="editing.foto_url" />
        </FormField>
      </div>
      <strong class="section-label">Baptis, Sidi, dan Perkawinan</strong>
      <div class="grid three">
        <FormField label="Tanggal Baptis">
          <input v-model="editing.tgl_baptis" type="date" />
        </FormField>
        <FormField label="Gereja Baptis">
          <input v-model="editing.gereja_baptis" />
        </FormField>
        <FormField label="Pendeta Baptis">
          <input v-model="editing.pendeta_baptis" />
        </FormField>
        <FormField label="Tanggal Sidi">
          <input v-model="editing.tgl_sidi" type="date" />
        </FormField>
        <FormField label="Gereja Sidi">
          <input v-model="editing.gereja_sidi" />
        </FormField>
        <FormField label="Pendeta Sidi">
          <input v-model="editing.pendeta_sidi" />
        </FormField>
        <FormField label="Nats Sidi">
          <input v-model="editing.nats_sidi" />
        </FormField>
        <FormField label="Tanggal Perkawinan">
          <input v-model="editing.tgl_perkawinan" type="date" />
        </FormField>
        <FormField label="Gereja Perkawinan">
          <input v-model="editing.gereja_perkawinan" />
        </FormField>
        <FormField label="Pendeta Perkawinan">
          <input v-model="editing.pendeta_perkawinan" />
        </FormField>
        <FormField label="Nats Perkawinan">
          <input v-model="editing.nats_perkawinan" />
        </FormField>
      </div>
      <div class="actions">
        <button class="button" type="submit">Simpan</button>
      </div>
    </form>
  </section>
</template>

<script setup lang="ts">
import { onMounted, reactive, ref } from 'vue';
import DataTable from '@/components/DataTable.vue';
import FormField from '@/components/FormField.vue';
import Pagination from '@/components/Pagination.vue';
import { getMembers, updateMember } from '@/api/members';
import { getSectors } from '@/api/sectors';
import type { Member, Sector } from '@/types/api';
import { bloodOptions, educationOptions, genderOptions, normalizeMember, relationOptions } from '@/utils/memberForm';

const members = ref<Member[]>([]);
const sectors = ref<Sector[]>([]);
const editing = ref<Member | null>(null);
const filters = reactive({ search: '', sektor_id: '' as number | '', hubungan: '' });
const pagination = reactive({ page: 1, per_page: 20, total: 0, total_pages: 0 });
const columns = [
  { key: 'nama', label: 'Nama' },
  { key: 'marga', label: 'Marga' },
  { key: 'sector_name', label: 'Sektor' },
  { key: 'hubungan_keluarga', label: 'Hubungan' },
  { key: 'no_hp', label: 'No HP' }
] as const;

async function load() {
  const result = await getMembers({
    page: pagination.page,
    per_page: pagination.per_page,
    search: filters.search || undefined,
    sektor_id: filters.sektor_id || undefined,
    hubungan: filters.hubungan || undefined
  });
  members.value = result.data;
  Object.assign(pagination, result.pagination);
}

function edit(member: Member) {
  editing.value = { ...member };
}

async function save() {
  if (!editing.value?.id) return;
  await updateMember(editing.value.id, normalizeMember(editing.value));
  editing.value = null;
  await load();
}

onMounted(async () => {
  sectors.value = (await getSectors()).data;
  await load();
});
</script>
