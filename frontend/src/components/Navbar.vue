<template>
  <header class="navbar">
    <div>
      <strong>{{ auth.currentUser?.nama_depan }}</strong>
      <small>{{ auth.currentUser?.role_name ?? 'user' }}</small>
    </div>
    <button class="button secondary" type="button" @click="logout">Keluar</button>
  </header>
</template>

<script setup lang="ts">
import { useRouter } from 'vue-router';
import { useAuthStore } from '@/stores/auth';

const router = useRouter();
const auth = useAuthStore();

function logout() {
  auth.logout();
  router.push('/login');
}
</script>
