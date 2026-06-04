<template>
  <main class="login-page">
    <div class="login-panel grid">
      <div>
        <h1>HKBP Jatinegara</h1>
        <p>Administrasi Jemaat</p>
      </div>
      <p v-if="loggedOut" class="notice">Anda telah keluar.</p>
      <p v-if="error" class="error">{{ error }}</p>
      <button class="button" type="button" :disabled="auth.loading" @click="auth.login()">
        Masuk dengan Akun HKBP
      </button>
      <p class="hint">Anda akan diarahkan ke halaman masuk yang aman.</p>
    </div>
  </main>
</template>

<script setup lang="ts">
import { computed } from 'vue';
import { useRoute } from 'vue-router';
import { useAuthStore } from '@/stores/auth';

const auth = useAuthStore();
const route = useRoute();
const loggedOut = computed(() => route.query.logged_out === '1');
const error = computed(() => (route.query.error ? String(route.query.error) : ''));
</script>
