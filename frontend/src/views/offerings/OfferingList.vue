<template>
  <section class="page">
    <div class="page-header">
      <h1 class="page-title">Persembahan Bulanan</h1>
      <RouterLink class="button secondary" to="/offerings/report">Laporan</RouterLink>
    </div>
    <form class="panel grid three" @submit.prevent="save">
      <FormField label="Nama Keluarga">
        <select v-model.number="form.family_id" required>
          <option :value="0">Pilih keluarga</option>
          <option v-for="family in families" :key="family.id" :value="family.id">
            {{ family.head_member_name ?? `Keluarga ${family.id}` }} · {{ family.sector_name }}
          </option>
        </select>
      </FormField>
      <FormField label="Nominal">
        <input v-model.number="form.amount" type="number" min="1" required />
      </FormField>
      <FormField label="Bulan">
        <select v-model.number="form.month" required>
          <option v-for="month in 12" :key="month" :value="month">{{ monthName(month) }}</option>
        </select>
      </FormField>
      <FormField label="Tahun">
        <input v-model.number="form.year" type="number" min="2000" required />
      </FormField>
      <FormField label="Catatan">
        <input v-model="form.notes" />
      </FormField>
      <div class="actions" style="align-items: end">
        <button class="button" type="submit">Tambah</button>
      </div>
    </form>
    <div class="panel">
      <DataTable :rows="offerings" :columns="columns">
        <template #actions="{ row }">
          <button class="button danger" type="button" @click="remove((row as Offering).id)">Hapus</button>
        </template>
      </DataTable>
      <Pagination v-model:page="pagination.page" :total-pages="pagination.total_pages" @update:page="loadOfferings" />
    </div>
  </section>
</template>

<script setup lang="ts">
import { onMounted, reactive, ref } from 'vue';
import DataTable from '@/components/DataTable.vue';
import FormField from '@/components/FormField.vue';
import Pagination from '@/components/Pagination.vue';
import { getFamilies } from '@/api/families';
import { createOffering, deleteOffering, getOfferings } from '@/api/offerings';
import type { FamilySummary, Offering } from '@/types/api';
import { monthName } from '@/utils/formatters';

const now = new Date();
const families = ref<FamilySummary[]>([]);
const offerings = ref<Offering[]>([]);
const pagination = reactive({ page: 1, per_page: 20, total: 0, total_pages: 0 });
const form = reactive({
  family_id: 0,
  amount: 0,
  month: now.getMonth() + 1,
  year: now.getFullYear(),
  notes: ''
});
const columns = [
  { key: 'family_head_name', label: 'Keluarga' },
  { key: 'sector_name', label: 'Sektor' },
  { key: 'amount', label: 'Nominal' },
  { key: 'month', label: 'Bulan' },
  { key: 'year', label: 'Tahun' },
  { key: 'notes', label: 'Catatan' }
] as const;

async function loadOfferings() {
  const result = await getOfferings({ page: pagination.page, per_page: pagination.per_page });
  offerings.value = result.data;
  Object.assign(pagination, result.pagination);
}

async function save() {
  await createOffering({ ...form, notes: form.notes || null });
  form.amount = 0;
  form.notes = '';
  await loadOfferings();
}

async function remove(id: number) {
  await deleteOffering(id);
  await loadOfferings();
}

onMounted(async () => {
  families.value = (await getFamilies({ per_page: 100 })).data;
  await loadOfferings();
});
</script>
