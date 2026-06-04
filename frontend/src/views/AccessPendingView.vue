<template>
  <main class="login-page">
    <div class="login-panel grid">
      <div>
        <h1>Menunggu Persetujuan</h1>
        <p>Akun Anda belum memiliki akses ke aplikasi HKBP Jatinegara.</p>
      </div>
      <p v-if="hasError" class="error">Masuk dibatalkan atau gagal. Silakan coba lagi.</p>
      <p class="hint">
        Permintaan akses Anda telah dicatat dan menunggu persetujuan administrator.
        Setelah disetujui, masuk kembali untuk mengakses aplikasi.
      </p>
      <button class="button" type="button" @click="auth.login()">Masuk Lagi</button>
      <a class="button secondary" href="/login">Kembali</a>
    </div>
  </main>
</template>

<script setup lang="ts">
import { computed } from 'vue';
import { useRoute } from 'vue-router';
import { useAuthStore } from '@/stores/auth';

const auth = useAuthStore();
const route = useRoute();
const hasError = computed(() => Boolean(route.query.error));
</script>
