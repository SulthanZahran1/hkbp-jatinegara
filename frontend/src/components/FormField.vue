<template>
  <label class="field">
    <span>{{ label }}</span>
    <slot />
  </label>
</template>

<script setup lang="ts">
defineProps<{ label: string }>();
</script>
