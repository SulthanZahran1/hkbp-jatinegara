<template>
  <section class="page">
    <div class="page-header">
      <h1 class="page-title">Laporan Persembahan</h1>
      <RouterLink class="button secondary" to="/offerings">Input Persembahan</RouterLink>
    </div>
    <form class="panel grid three" @submit.prevent="load">
      <FormField label="Bulan">
        <select v-model.number="filters.month">
          <option v-for="month in 12" :key="month" :value="month">{{ monthName(month) }}</option>
        </select>
      </FormField>
      <FormField label="Tahun">
        <input v-model.number="filters.year" type="number" />
      </FormField>
      <FormField label="Sektor">
        <select v-model="filters.sektor_id">
          <option value="">Semua sektor</option>
          <option v-for="sector in sectors" :key="sector.id" :value="sector.id">{{ sector.name }}</option>
        </select>
      </FormField>
      <div class="actions">
        <button class="button" type="submit">Cari</button>
      </div>
    </form>
    <div class="stat-grid">
      <div class="stat">
        <span>Total Persembahan</span>
        <strong>{{ formatCurrency(report?.total ?? 0) }}</strong>
      </div>
    </div>
    <div class="panel">
      <h2>Total Per Sektor</h2>
      <DataTable :rows="report?.by_sector ?? []" :columns="sectorColumns" />
    </div>
    <div class="panel">
      <h2>Rincian</h2>
      <DataTable :rows="report?.entries ?? []" :columns="entryColumns" />
    </div>
  </section>
</template>

<script setup lang="ts">
import { onMounted, reactive, ref } from 'vue';
import DataTable from '@/components/DataTable.vue';
import FormField from '@/components/FormField.vue';
import { getOfferingReport } from '@/api/offerings';
import { getSectors } from '@/api/sectors';
import type { OfferingReport, Sector } from '@/types/api';
import { formatCurrency, monthName } from '@/utils/formatters';

const now = new Date();
const sectors = ref<Sector[]>([]);
const report = ref<OfferingReport | null>(null);
const filters = reactive({ month: now.getMonth() + 1, year: now.getFullYear(), sektor_id: '' as number | '' });
const sectorColumns = [
  { key: 'sektor_name', label: 'Sektor' },
  { key: 'total', label: 'Total' },
  { key: 'family_count', label: 'Jumlah Keluarga' }
] as const;
const entryColumns = [
  { key: 'family_head_name', label: 'Keluarga' },
  { key: 'sector_name', label: 'Sektor' },
  { key: 'amount', label: 'Nominal' },
  { key: 'notes', label: 'Catatan' }
] as const;

async function load() {
  report.value = await getOfferingReport({
    month: filters.month,
    year: filters.year,
    sektor_id: filters.sektor_id || undefined
  });
}

onMounted(async () => {
  sectors.value = (await getSectors()).data;
  await load();
});
</script>
