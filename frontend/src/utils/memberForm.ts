import type { Member } from '@/types/api';

export const genderOptions = ['Laki-laki', 'Perempuan'] as const;
export const bloodOptions = ['', 'A', 'B', 'O', 'AB'] as const;
export const relationOptions = ['Kepala Keluarga', 'Istri', 'Anak'] as const;
export const educationOptions = ['', 'SD', 'SMP', 'SMA', 'D3', 'S1', 'S2', 'S3'] as const;

export function emptyMember(relation: Member['hubungan_keluarga'] = 'Anak'): Member {
  return {
    nama: '',
    marga: '',
    gender: '',
    tempat_lahir: '',
    tanggal_lahir: '',
    gol_darah: '',
    hubungan_keluarga: relation,
    pendidikan: '',
    pekerjaan: '',
    talenta: '',
    no_hp: '',
    alamat: '',
    provinsi: '',
    kota: '',
    kecamatan: '',
    kelurahan: '',
    kode_pos: '',
    foto_url: '',
    tgl_baptis: '',
    gereja_baptis: '',
    pendeta_baptis: '',
    tgl_sidi: '',
    gereja_sidi: '',
    pendeta_sidi: '',
    nats_sidi: '',
    tgl_perkawinan: '',
    gereja_perkawinan: '',
    pendeta_perkawinan: '',
    nats_perkawinan: '',
    is_head_of_family: relation === 'Kepala Keluarga'
  };
}

export function normalizeMember(member: Member): Member {
  const next = { ...member };
  for (const key of Object.keys(next) as Array<keyof Member>) {
    if (typeof next[key] === 'string' && next[key] === '') {
      (next as Record<string, unknown>)[key] = null;
    }
  }
  next.is_head_of_family = next.hubungan_keluarga === 'Kepala Keluarga';
  return next;
}
