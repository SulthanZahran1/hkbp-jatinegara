<template>
  <section class="page">
    <div class="page-header">
      <h1 class="page-title">Keluarga</h1>
      <RouterLink class="button" to="/families/new">Tambah Keluarga</RouterLink>
    </div>
    <form class="panel grid three" @submit.prevent="load">
      <FormField label="Cari Kepala Keluarga">
        <input v-model="filters.search" />
      </FormField>
      <FormField label="Sektor">
        <select v-model="filters.sektor_id">
          <option value="">Semua sektor</option>
          <option v-for="sector in sectors" :key="sector.id" :value="sector.id">{{ sector.name }}</option>
        </select>
      </FormField>
      <div class="actions" style="align-items: end">
        <button class="button" type="submit">Cari</button>
      </div>
    </form>
    <div class="panel">
      <DataTable :rows="families" :columns="columns">
        <template #actions="{ row }">
          <RouterLink class="button secondary" :to="`/families/${(row as FamilySummary).id}`">Detail</RouterLink>
        </template>
      </DataTable>
      <Pagination v-model:page="pagination.page" :total-pages="pagination.total_pages" @update:page="load" />
    </div>
  </section>
</template>

<script setup lang="ts">
import { onMounted, reactive, ref } from 'vue';
import DataTable from '@/components/DataTable.vue';
import FormField from '@/components/FormField.vue';
import Pagination from '@/components/Pagination.vue';
import { getFamilies } from '@/api/families';
import { getSectors } from '@/api/sectors';
import type { FamilySummary, Sector } from '@/types/api';

const families = ref<FamilySummary[]>([]);
const sectors = ref<Sector[]>([]);
const filters = reactive({ search: '', sektor_id: '' as number | '' });
const pagination = reactive({ page: 1, per_page: 20, total: 0, total_pages: 0 });
const columns = [
  { key: 'head_member_name', label: 'Kepala Keluarga' },
  { key: 'sector_name', label: 'Sektor' },
  { key: 'alamat', label: 'Alamat' },
  { key: 'member_count', label: 'Jumlah Ruas' }
] as const;

async function load() {
  const result = await getFamilies({
    page: pagination.page,
    per_page: pagination.per_page,
    search: filters.search || undefined,
    sektor_id: filters.sektor_id || undefined
  });
  families.value = result.data;
  Object.assign(pagination, result.pagination);
}

onMounted(async () => {
  sectors.value = (await getSectors()).data;
  await load();
});
</script>
