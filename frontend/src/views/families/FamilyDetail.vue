<template>
  <section class="page" v-if="family">
    <div class="page-header">
      <div>
        <h1 class="page-title">{{ family.members.find((m) => m.id === family?.head_member_id)?.nama ?? 'Keluarga' }}</h1>
        <p>{{ family.sector_name }} · {{ family.alamat ?? '-' }}</p>
      </div>
      <RouterLink class="button secondary" to="/families">Kembali</RouterLink>
    </div>
    <div class="panel">
      <DataTable :rows="family.members" :columns="columns" />
    </div>
  </section>
</template>

<script setup lang="ts">
import { onMounted, ref } from 'vue';
import { useRoute } from 'vue-router';
import DataTable from '@/components/DataTable.vue';
import { getFamily } from '@/api/families';
import type { FamilyDetail } from '@/types/api';

const route = useRoute();
const family = ref<FamilyDetail | null>(null);
const columns = [
  { key: 'nama', label: 'Nama' },
  { key: 'marga', label: 'Marga' },
  { key: 'hubungan_keluarga', label: 'Hubungan' },
  { key: 'gender', label: 'Gender' },
  { key: 'tanggal_lahir', label: 'Tanggal Lahir' },
  { key: 'no_hp', label: 'No HP' }
] as const;

onMounted(async () => {
  family.value = await getFamily(Number(route.params.id));
});
</script>
