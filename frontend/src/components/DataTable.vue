<template>
  <div class="table-wrap">
    <table>
      <thead>
        <tr>
          <th v-for="column in columns" :key="column.key">{{ column.label }}</th>
          <th v-if="$slots.actions"></th>
        </tr>
      </thead>
      <tbody>
        <tr v-for="row in rows" :key="getRowKey(row)">
          <td v-for="column in columns" :key="column.key">{{ formatCell(row, column.key) }}</td>
          <td v-if="$slots.actions" class="table-actions">
            <slot name="actions" :row="row" />
          </td>
        </tr>
        <tr v-if="rows.length === 0">
          <td :colspan="columns.length + ($slots.actions ? 1 : 0)" class="empty-cell">Belum ada data</td>
        </tr>
      </tbody>
    </table>
  </div>
</template>

<script setup lang="ts" generic="T extends object">
const props = defineProps<{
  rows: readonly T[];
  columns: ReadonlyArray<{ readonly key: keyof T & string; readonly label: string }>;
  rowKey?: (row: T) => string | number;
}>();

function getRowKey(row: T) {
  return props.rowKey?.(row) ?? ((row as Record<string, unknown>).id as string | number | undefined) ?? JSON.stringify(row);
}

function formatCell(row: T, key: keyof T & string) {
  const value = (row as Record<string, unknown>)[key];
  return value === null || value === undefined || value === '' ? '-' : String(value);
}
</script>
