<template>
  <main class="login-page">
    <form class="login-panel grid" @submit.prevent="submit">
      <div>
        <h1>HKBP Jatinegara</h1>
        <p>Masuk ke sistem administrasi jemaat.</p>
      </div>
      <p v-if="error" class="error">{{ error }}</p>
      <FormField label="Username">
        <input v-model="form.username" autocomplete="username" required />
      </FormField>
      <FormField label="Password">
        <input v-model="form.password" autocomplete="current-password" type="password" required />
      </FormField>
      <button class="button" type="submit" :disabled="auth.loading">
        {{ auth.loading ? 'Memproses...' : 'Masuk' }}
      </button>
    </form>
  </main>
</template>

<script setup lang="ts">
import { reactive, ref } from 'vue';
import { useRoute, useRouter } from 'vue-router';
import FormField from '@/components/FormField.vue';
import { useAuthStore } from '@/stores/auth';

const auth = useAuthStore();
const router = useRouter();
const route = useRoute();
const error = ref('');
const form = reactive({ username: 'admin', password: 'admin123' });

async function submit() {
  error.value = '';
  try {
    await auth.login(form);
    await router.push((route.query.redirect as string) || '/dashboard');
  } catch {
    error.value = 'Username atau password tidak valid.';
  }
}
</script>
