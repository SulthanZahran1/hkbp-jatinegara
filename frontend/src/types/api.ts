export interface Pagination {
  page: number;
  per_page: number;
  total: number;
  total_pages: number;
}

export interface ApiList<T> {
  data: T[];
  pagination?: Pagination;
}

export interface LoginResponse {
  access_token: string;
  refresh_token: string;
  expires_in: number;
  user: User;
}

export interface Role {
  id: number;
  name: string;
  created_at?: string;
}

export interface Sector {
  id: number;
  name: string;
  created_at?: string;
  updated_at?: string;
}

export interface User {
  id: number;
  username: string;
  email: string;
  nama_depan: string;
  nama_belakang: string | null;
  role_id: number;
  role_name?: string;
  sektor_id: number | null;
  sector_name?: string | null;
  status: 'active' | 'inactive';
  last_access?: string | null;
  created_at?: string;
}

export interface Member {
  id?: number;
  family_id?: number;
  sector_id?: number;
  sector_name?: string;
  nama: string;
  marga: string | null;
  gender: 'Laki-laki' | 'Perempuan' | '';
  tempat_lahir: string | null;
  tanggal_lahir: string | null;
  gol_darah: 'A' | 'B' | 'O' | 'AB' | '';
  hubungan_keluarga: 'Kepala Keluarga' | 'Istri' | 'Anak' | '';
  pendidikan: 'SD' | 'SMP' | 'SMA' | 'D3' | 'S1' | 'S2' | 'S3' | '';
  pekerjaan: string | null;
  talenta: string | null;
  no_hp: string | null;
  alamat: string | null;
  provinsi: string | null;
  kota: string | null;
  kecamatan: string | null;
  kelurahan: string | null;
  kode_pos: string | null;
  foto_url: string | null;
  tgl_baptis: string | null;
  gereja_baptis: string | null;
  pendeta_baptis: string | null;
  tgl_sidi: string | null;
  gereja_sidi: string | null;
  pendeta_sidi: string | null;
  nats_sidi: string | null;
  tgl_perkawinan: string | null;
  gereja_perkawinan: string | null;
  pendeta_perkawinan: string | null;
  nats_perkawinan: string | null;
  is_head_of_family?: boolean;
  created_at?: string;
}

export interface FamilySummary {
  id: number;
  sector_id: number;
  sector_name: string;
  head_member_id: number | null;
  head_member_name: string | null;
  alamat: string | null;
  member_count: number;
  created_at: string;
}

export interface FamilyDetail {
  id: number;
  sector_id: number;
  sector_name: string;
  head_member_id: number | null;
  alamat: string | null;
  members: Member[];
  created_at: string;
}

export interface Offering {
  id: number;
  family_id: number;
  sector_id: number;
  sector_name: string;
  family_head_name: string | null;
  amount: number;
  month: number;
  year: number;
  notes: string | null;
  created_by: number;
  created_at: string;
}

export interface OfferingReport {
  total: number;
  by_sector: Array<{
    sektor_id: number;
    sektor_name: string;
    sector_id?: number;
    sector_name?: string;
    total: number;
    family_count: number;
  }>;
  entries: Offering[];
}

export interface Sintua {
  id: number;
  member_id: number;
  member_name: string;
  sektor_id: number;
  sektor_name: string;
  sector_id?: number;
  sector_name?: string;
  created_at?: string;
}

export interface Attendance {
  id: number;
  member_id: number;
  member_name: string;
  date: string;
  status: 'hadir' | 'tidak_hadir' | 'izin' | 'sakit';
  seksi: 'musik' | 'multimedia';
  created_by?: number;
  created_at?: string;
}
