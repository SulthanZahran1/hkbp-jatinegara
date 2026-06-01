<template>
  <div class="pagination" v-if="totalPages > 1">
    <button class="button secondary" type="button" :disabled="page <= 1" @click="$emit('update:page', page - 1)">
      Sebelumnya
    </button>
    <span>Halaman {{ page }} dari {{ totalPages }}</span>
    <button class="button secondary" type="button" :disabled="page >= totalPages" @click="$emit('update:page', page + 1)">
      Berikutnya
    </button>
  </div>
</template>

<script setup lang="ts">
defineProps<{ page: number; totalPages: number }>();
defineEmits<{ 'update:page': [value: number] }>();
</script>
