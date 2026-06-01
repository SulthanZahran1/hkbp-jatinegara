import { reactive } from 'vue';

export function usePagination(perPage = 20) {
  const pagination = reactive({
    page: 1,
    per_page: perPage,
    total: 0,
    total_pages: 0
  });

  function setPagination(next?: Partial<typeof pagination>) {
    Object.assign(pagination, next ?? {});
  }

  return { pagination, setPagination };
}
