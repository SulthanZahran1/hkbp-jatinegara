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
import { useAuthStore } from '@/stores/auth';

const auth = useAuthStore();

// Logout revokes the server session and clears the cookie, then sends the
// browser through the IdP end-session URL so SSO can't silently re-enter on a
// shared computer.
function logout() {
  auth.logout();
}
</script>
