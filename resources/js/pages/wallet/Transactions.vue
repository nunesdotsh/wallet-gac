<script setup lang="ts">
import { Head, router } from '@inertiajs/vue3';
import AppLayout from '@/layouts/AppLayout.vue';
import TransactionList from '@/components/TransactionList.vue';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { dashboard } from '@/routes';
import type { BreadcrumbItem, Transaction, TransactionPaginated } from '@/types';

const props = defineProps<{
    transactions: TransactionPaginated;
}>();

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Dashboard', href: dashboard() },
    { title: 'Transações', href: '/transactions' },
];

function viewTransaction(transaction: Transaction) {
    router.get(`/transactions/${transaction.id}`);
}

function goToPage(page: number) {
    router.get('/transactions', { page }, { preserveState: true });
}
</script>

<template>
    <Head title="Transações" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex flex-col gap-4 p-4">
            <Card>
                <CardHeader>
                    <CardTitle>Transações</CardTitle>
                </CardHeader>
                <CardContent>
                    <TransactionList :transactions="transactions.data" @view="viewTransaction" />

                    <div v-if="transactions.last_page > 1" class="mt-6 flex items-center justify-between">
                        <Button
                            variant="outline"
                            size="sm"
                            :disabled="transactions.current_page <= 1"
                            @click="goToPage(transactions.current_page - 1)"
                        >
                            Anterior
                        </Button>

                        <span class="text-sm text-muted-foreground">
                            Página {{ transactions.current_page }} de {{ transactions.last_page }}
                        </span>

                        <Button
                            variant="outline"
                            size="sm"
                            :disabled="transactions.current_page >= transactions.last_page"
                            @click="goToPage(transactions.current_page + 1)"
                        >
                            Próximo
                        </Button>
                    </div>
                </CardContent>
            </Card>
        </div>
    </AppLayout>
</template>
