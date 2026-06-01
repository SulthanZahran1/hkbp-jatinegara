export function formatCurrency(value: number): string {
  return new Intl.NumberFormat('id-ID', {
    style: 'currency',
    currency: 'IDR',
    maximumFractionDigits: 0
  }).format(value);
}

export function formatName(first?: string | null, last?: string | null): string {
  return [first, last].filter(Boolean).join(' ');
}

export function monthName(month: number): string {
  return new Intl.DateTimeFormat('id-ID', { month: 'long' }).format(new Date(2025, month - 1, 1));
}

export function todayISO(): string {
  return new Date().toISOString().slice(0, 10);
}
