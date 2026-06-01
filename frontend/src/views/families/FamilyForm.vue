<template>
  <section class="page">
    <div class="page-header">
      <h1 class="page-title">Input Data Keluarga</h1>
      <RouterLink class="button secondary" to="/families">Kembali</RouterLink>
    </div>
    <form class="grid" @submit.prevent="submit">
      <div class="panel grid two">
        <FormField label="Sektor">
          <select v-model.number="form.sector_id" required>
            <option :value="0">Pilih sektor</option>
            <option v-for="sector in sectors" :key="sector.id" :value="sector.id">{{ sector.name }}</option>
          </select>
        </FormField>
        <FormField label="Alamat Keluarga">
          <input v-model="form.alamat" />
        </FormField>
      </div>

      <div v-for="(member, index) in form.members" :key="index" class="panel member-card grid">
        <div class="page-header">
          <strong>Ruas {{ index + 1 }}</strong>
          <button v-if="form.members.length > 1" class="button danger" type="button" @click="form.members.splice(index, 1)">
            Hapus
          </button>
        </div>
        <div class="grid three">
          <FormField label="Nama Lengkap">
            <input v-model="member.nama" required />
          </FormField>
          <FormField label="Marga">
            <input v-model="member.marga" />
          </FormField>
          <FormField label="Gender">
            <select v-model="member.gender" required>
              <option value="">Pilih gender</option>
              <option v-for="option in genderOptions" :key="option" :value="option">{{ option }}</option>
            </select>
          </FormField>
          <FormField label="Tempat Lahir">
            <input v-model="member.tempat_lahir" />
          </FormField>
          <FormField label="Tanggal Lahir">
            <input v-model="member.tanggal_lahir" type="date" />
          </FormField>
          <FormField label="Golongan Darah">
            <select v-model="member.gol_darah">
              <option v-for="option in bloodOptions" :key="option" :value="option">{{ option || '-' }}</option>
            </select>
          </FormField>
          <FormField label="Hubungan Keluarga">
            <select v-model="member.hubungan_keluarga" required>
              <option value="">Pilih hubungan</option>
              <option v-for="option in relationOptions" :key="option" :value="option">{{ option }}</option>
            </select>
          </FormField>
          <FormField label="Pendidikan Terakhir">
            <select v-model="member.pendidikan">
              <option v-for="option in educationOptions" :key="option" :value="option">{{ option || '-' }}</option>
            </select>
          </FormField>
          <FormField label="Pekerjaan">
            <input v-model="member.pekerjaan" />
          </FormField>
          <FormField label="Talenta">
            <input v-model="member.talenta" />
          </FormField>
          <FormField label="No Handphone">
            <input v-model="member.no_hp" />
          </FormField>
          <FormField label="Alamat">
            <input v-model="member.alamat" />
          </FormField>
          <FormField label="Propinsi">
            <input v-model="member.provinsi" />
          </FormField>
          <FormField label="Kota">
            <input v-model="member.kota" />
          </FormField>
          <FormField label="Kecamatan">
            <input v-model="member.kecamatan" />
          </FormField>
          <FormField label="Kelurahan">
            <input v-model="member.kelurahan" />
          </FormField>
          <FormField label="Kode Pos">
            <input v-model="member.kode_pos" />
          </FormField>
          <FormField label="Foto URL">
            <input v-model="member.foto_url" />
          </FormField>
        </div>

        <strong class="section-label">Baptis</strong>
        <div class="grid three">
          <FormField label="Tanggal Baptis">
            <input v-model="member.tgl_baptis" type="date" />
          </FormField>
          <FormField label="Gereja Tempat Baptis">
            <input v-model="member.gereja_baptis" />
          </FormField>
          <FormField label="Pendeta Pelayan Baptis">
            <input v-model="member.pendeta_baptis" />
          </FormField>
        </div>

        <strong class="section-label">Sidi</strong>
        <div class="grid three">
          <FormField label="Tanggal Sidi">
            <input v-model="member.tgl_sidi" type="date" />
          </FormField>
          <FormField label="Gereja Tempat Sidi">
            <input v-model="member.gereja_sidi" />
          </FormField>
          <FormField label="Pendeta Pelayan Sidi">
            <input v-model="member.pendeta_sidi" />
          </FormField>
          <FormField label="Nats Sidi">
            <input v-model="member.nats_sidi" />
          </FormField>
        </div>

        <strong class="section-label">Perkawinan</strong>
        <div class="grid three">
          <FormField label="Tanggal Perkawinan">
            <input v-model="member.tgl_perkawinan" type="date" />
          </FormField>
          <FormField label="Gereja Tempat Perkawinan">
            <input v-model="member.gereja_perkawinan" />
          </FormField>
          <FormField label="Pendeta Pelayan Perkawinan">
            <input v-model="member.pendeta_perkawinan" />
          </FormField>
          <FormField label="Nats Perkawinan">
            <input v-model="member.nats_perkawinan" />
          </FormField>
        </div>
      </div>

      <div class="actions">
        <button class="button secondary" type="button" @click="addMember">Tambah Ruas</button>
        <button class="button" type="submit">Simpan Keluarga</button>
      </div>
      <p v-if="message" class="success">{{ message }}</p>
      <p v-if="error" class="error">{{ error }}</p>
    </form>
  </section>
</template>

<script setup lang="ts">
import { onMounted, reactive, ref } from 'vue';
import { useRouter } from 'vue-router';
import FormField from '@/components/FormField.vue';
import { createFamily } from '@/api/families';
import { getSectors } from '@/api/sectors';
import type { Member, Sector } from '@/types/api';
import { bloodOptions, educationOptions, emptyMember, genderOptions, normalizeMember, relationOptions } from '@/utils/memberForm';

const router = useRouter();
const sectors = ref<Sector[]>([]);
const message = ref('');
const error = ref('');
const form = reactive<{ sector_id: number; alamat: string; members: Member[] }>({
  sector_id: 0,
  alamat: '',
  members: [emptyMember('Kepala Keluarga')]
});

function addMember() {
  form.members.push(emptyMember('Anak'));
}

async function submit() {
  message.value = '';
  error.value = '';
  const heads = form.members.filter((member) => member.hubungan_keluarga === 'Kepala Keluarga');
  if (heads.length !== 1) {
    error.value = 'Data keluarga harus memiliki tepat satu Kepala Keluarga.';
    return;
  }
  const response = await createFamily({
    sector_id: form.sector_id,
    alamat: form.alamat || null,
    members: form.members.map(normalizeMember)
  });
  message.value = 'Data keluarga tersimpan.';
  await router.push(`/families/${response.id}`);
}

onMounted(async () => {
  sectors.value = (await getSectors()).data;
});
</script>
