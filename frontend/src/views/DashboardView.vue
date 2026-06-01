<template>
  <section class="page">
    <div class="page-header">
      <h1 class="page-title">Dashboard</h1>
    </div>
    <div class="stat-grid">
      <div class="stat">
        <span>Ruas</span>
        <strong>{{ stats.members }}</strong>
      </div>
      <div class="stat">
        <span>Keluarga</span>
        <strong>{{ stats.families }}</strong>
      </div>
      <div class="stat">
        <span>Sektor</span>
        <strong>{{ stats.sectors }}</strong>
      </div>
      <div class="stat">
        <span>Persembahan Bulan Ini</span>
        <strong>{{ formatCurrency(stats.offerings) }}</strong>
      </div>
    </div>
    <div class="panel">
      <h2>Ringkasan Sektor</h2>
      <DataTable :rows="sectors" :columns="sectorColumns" />
    </div>
  </section>
</template>

<script setup lang="ts">
import { onMounted, reactive, ref } from 'vue';
import DataTable from '@/components/DataTable.vue';
import { getSectors } from '@/api/sectors';
import { getFamilies } from '@/api/families';
import { getMembers } from '@/api/members';
import { getOfferingReport } from '@/api/offerings';
import type { Sector } from '@/types/api';
import { formatCurrency } from '@/utils/formatters';

const sectors = ref<Sector[]>([]);
const stats = reactive({ members: 0, families: 0, sectors: 0, offerings: 0 });
const sectorColumns = [
  { key: 'name', label: 'Nama Sektor' },
  { key: 'created_at', label: 'Dibuat' }
] as const;

onMounted(async () => {
  const now = new Date();
  const [sectorResult, familyResult, memberResult, offeringResult] = await Promise.all([
    getSectors(),
    getFamilies({ per_page: 1 }),
    getMembers({ per_page: 1 }),
    getOfferingReport({ month: now.getMonth() + 1, year: now.getFullYear() })
  ]);
  sectors.value = sectorResult.data;
  stats.sectors = sectorResult.data.length;
  stats.families = familyResult.pagination?.total ?? familyResult.data.length;
  stats.members = memberResult.pagination?.total ?? memberResult.data.length;
  stats.offerings = offeringResult.total;
});
</script>
