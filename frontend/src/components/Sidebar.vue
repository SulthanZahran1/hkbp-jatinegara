<template>
  <aside class="sidebar">
    <RouterLink class="brand" to="/dashboard">
      <span class="brand-mark">H</span>
      <span>
        <strong>HKBP Jatinegara</strong>
        <small>Administrasi Jemaat</small>
      </span>
    </RouterLink>
    <nav>
      <RouterLink v-for="item in navItems" :key="item.to" :to="item.to">
        <span>{{ item.label }}</span>
      </RouterLink>
    </nav>
  </aside>
</template>

<script setup lang="ts">
import { computed } from 'vue';
import { useAuthStore } from '@/stores/auth';

const auth = useAuthStore();

const navItems = computed(() => {
  const items = [
    { label: 'Dashboard', to: '/dashboard' },
    { label: 'Keluarga', to: '/families' },
    { label: 'Ruas', to: '/members' },
    { label: 'Persembahan', to: '/offerings' },
    { label: 'Laporan', to: '/offerings/report' },
    { label: 'Sintua', to: '/sintua' },
    { label: 'Presensi', to: '/attendance' }
  ];
  if (auth.isAdmin) {
    items.splice(1, 0, { label: 'Sektor', to: '/sectors' }, { label: 'Pengguna', to: '/users' });
  }
  return items;
});
</script>
