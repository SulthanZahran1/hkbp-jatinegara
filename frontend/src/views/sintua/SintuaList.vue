<template>
  <section class="page">
    <div class="page-header">
      <h1 class="page-title">Sintua</h1>
    </div>
    <form class="panel grid three" @submit.prevent="save">
      <FormField label="Nama Ruas">
        <select v-model.number="memberId" required>
          <option :value="0">Pilih ruas</option>
          <option v-for="member in members" :key="member.id" :value="member.id">
            {{ member.nama }} · {{ member.sector_name }}
          </option>
        </select>
      </FormField>
      <div class="actions" style="align-items: end">
        <button class="button" type="submit">Tetapkan Sintua</button>
      </div>
    </form>
    <div class="panel">
      <DataTable :rows="sintua" :columns="columns">
        <template #actions="{ row }">
          <button class="button danger" type="button" @click="remove((row as Sintua).id)">Hapus</button>
        </template>
      </DataTable>
    </div>
  </section>
</template>

<script setup lang="ts">
import { onMounted, ref } from 'vue';
import DataTable from '@/components/DataTable.vue';
import FormField from '@/components/FormField.vue';
import { getMembers } from '@/api/members';
import { createSintua, deleteSintua, getSintua } from '@/api/sintua';
import type { Member, Sintua } from '@/types/api';

const members = ref<Member[]>([]);
const sintua = ref<Sintua[]>([]);
const memberId = ref(0);
const columns = [
  { key: 'member_name', label: 'Nama' },
  { key: 'sektor_name', label: 'Sektor' },
  { key: 'created_at', label: 'Ditetapkan' }
] as const;

async function load() {
  const [memberResult, sintuaResult] = await Promise.all([getMembers({ per_page: 100 }), getSintua()]);
  members.value = memberResult.data;
  sintua.value = sintuaResult.data;
}

async function save() {
  if (!memberId.value) return;
  await createSintua({ member_id: memberId.value });
  memberId.value = 0;
  await load();
}

async function remove(id: number) {
  await deleteSintua(id);
  await load();
}

onMounted(load);
</script>
