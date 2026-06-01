<template>
  <section class="page">
    <div class="page-header">
      <h1 class="page-title">Sektor</h1>
    </div>
    <form class="panel grid two" @submit.prevent="save">
      <FormField label="Nama Sektor">
        <input v-model="form.name" required />
      </FormField>
      <div class="actions" style="align-items: end">
        <button class="button" type="submit">{{ editingId ? 'Simpan' : 'Tambah' }}</button>
        <button v-if="editingId" class="button secondary" type="button" @click="reset">Batal</button>
      </div>
    </form>
    <div class="panel">
      <DataTable :rows="sectors" :columns="columns">
        <template #actions="{ row }">
          <button class="button secondary" type="button" @click="edit(row as Sector)">Ubah</button>
          <button class="button danger" type="button" @click="remove((row as Sector).id)">Hapus</button>
        </template>
      </DataTable>
    </div>
  </section>
</template>

<script setup lang="ts">
import { onMounted, reactive, ref } from 'vue';
import DataTable from '@/components/DataTable.vue';
import FormField from '@/components/FormField.vue';
import { createSector, deleteSector, getSectors, updateSector } from '@/api/sectors';
import type { Sector } from '@/types/api';

const sectors = ref<Sector[]>([]);
const editingId = ref<number | null>(null);
const form = reactive({ name: '' });
const columns = [{ key: 'name', label: 'Nama' }] as const;

async function load() {
  sectors.value = (await getSectors()).data;
}

function edit(sector: Sector) {
  editingId.value = sector.id;
  form.name = sector.name;
}

function reset() {
  editingId.value = null;
  form.name = '';
}

async function save() {
  if (editingId.value) {
    await updateSector(editingId.value, form);
  } else {
    await createSector(form);
  }
  reset();
  await load();
}

async function remove(id: number) {
  await deleteSector(id);
  await load();
}

onMounted(load);
</script>
