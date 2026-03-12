<script setup lang="ts">
import type { Transaction } from '@/types';
import TransactionBadge from '@/components/TransactionBadge.vue';

withDefaults(
    defineProps<{
        transactions: Transaction[];
        showActions?: boolean;
    }>(),
    { showActions: false },
);

const emit = defineEmits<{
    view: [transaction: Transaction];
}>();

function formatDate(dateStr: string): string {
    const date = new Date(dateStr);
    const day = String(date.getDate()).padStart(2, '0');
    const month = String(date.getMonth() + 1).padStart(2, '0');
    const year = date.getFullYear();
    const hours = String(date.getHours()).padStart(2, '0');
    const minutes = String(date.getMinutes()).padStart(2, '0');
    return `${day}/${month}/${year} ${hours}:${minutes}`;
}

function amountColorClass(type: Transaction['type']): string {
    return type === 'transfer_out' ? 'text-red-600' : 'text-green-600';
}
</script>

<template>
    <div v-if="transactions.length === 0" class="py-8 text-center text-muted-foreground">
        Nenhuma transação encontrada.
    </div>

    <div v-else class="overflow-x-auto">
        <table class="w-full text-sm">
            <thead>
                <tr class="border-b text-left text-muted-foreground">
                    <th class="px-4 py-3 font-medium">Data</th>
                    <th class="px-4 py-3 font-medium">Tipo</th>
                    <th class="px-4 py-3 font-medium">Valor</th>
                    <th class="px-4 py-3 font-medium">Saldo Após</th>
                    <th class="px-4 py-3 font-medium">Contrapartida</th>
                </tr>
            </thead>
            <tbody>
                <tr
                    v-for="transaction in transactions"
                    :key="transaction.id"
                    class="cursor-pointer border-b transition-colors hover:bg-muted/50"
                    @click="emit('view', transaction)"
                >
                    <td class="whitespace-nowrap px-4 py-3">
                        {{ formatDate(transaction.created_at) }}
                    </td>
                    <td class="px-4 py-3">
                        <TransactionBadge :type="transaction.type" :status="transaction.status" />
                    </td>
                    <td class="whitespace-nowrap px-4 py-3 font-medium" :class="amountColorClass(transaction.type)">
                        {{ transaction.formatted_amount }}
                    </td>
                    <td class="whitespace-nowrap px-4 py-3">
                        {{ transaction.formatted_balance_after }}
                    </td>
                    <td class="px-4 py-3">
                        <template v-if="transaction.counterpart_name">
                            <span class="block">{{ transaction.counterpart_name }}</span>
                            <span v-if="transaction.counterpart_email" class="block text-xs text-muted-foreground">
                                {{ transaction.counterpart_email }}
                            </span>
                        </template>
                        <span v-else class="text-muted-foreground">—</span>
                    </td>
                </tr>
            </tbody>
        </table>
    </div>
</template>
