<template>
  <div class="app-shell">
    <Sidebar />
    <div class="main-shell">
      <Navbar />
      <main class="content-shell">
        <RouterView />
      </main>
    </div>
  </div>
</template>

<script setup lang="ts">
import Sidebar from '@/components/Sidebar.vue';
import Navbar from '@/components/Navbar.vue';
</script>
