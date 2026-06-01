<template>
  <section class="page">
    <div class="page-header">
      <h1 class="page-title">Presensi</h1>
    </div>
    <form class="panel grid three" @submit.prevent="save">
      <FormField label="Tanggal">
        <input v-model="form.date" type="date" required />
      </FormField>
      <FormField label="Seksi">
        <select v-model="form.seksi" required>
          <option value="musik">Musik</option>
          <option value="multimedia">Multimedia</option>
        </select>
      </FormField>
      <FormField label="Ruas">
        <select v-model.number="form.member_id" required>
          <option :value="0">Pilih ruas</option>
          <option v-for="member in members" :key="member.id" :value="member.id">
            {{ member.nama }} · {{ member.sector_name }}
          </option>
        </select>
      </FormField>
      <FormField label="Status">
        <select v-model="form.status" required>
          <option value="hadir">Hadir</option>
          <option value="tidak_hadir">Tidak Hadir</option>
          <option value="izin">Izin</option>
          <option value="sakit">Sakit</option>
        </select>
      </FormField>
      <div class="actions" style="align-items: end">
        <button class="button" type="submit">Simpan</button>
        <button class="button secondary" type="button" @click="loadAttendance">Muat Ulang</button>
      </div>
    </form>
    <div class="panel">
      <DataTable :rows="attendance" :columns="columns" />
    </div>
  </section>
</template>

<script setup lang="ts">
import { onMounted, reactive, ref } from 'vue';
import DataTable from '@/components/DataTable.vue';
import FormField from '@/components/FormField.vue';
import { getMembers } from '@/api/members';
import { getAttendance, recordAttendance } from '@/api/attendance';
import type { Attendance, Member } from '@/types/api';
import { todayISO } from '@/utils/formatters';

const members = ref<Member[]>([]);
const attendance = ref<Attendance[]>([]);
const form = reactive({
  member_id: 0,
  date: todayISO(),
  status: 'hadir' as Attendance['status'],
  seksi: 'musik' as Attendance['seksi']
});
const columns = [
  { key: 'member_name', label: 'Nama' },
  { key: 'date', label: 'Tanggal' },
  { key: 'seksi', label: 'Seksi' },
  { key: 'status', label: 'Status' }
] as const;

async function loadAttendance() {
  attendance.value = (await getAttendance({ date: form.date, seksi: form.seksi })).data;
}

async function save() {
  if (!form.member_id) return;
  await recordAttendance(form);
  await loadAttendance();
}

onMounted(async () => {
  members.value = (await getMembers({ per_page: 100 })).data;
  await loadAttendance();
});
</script>
