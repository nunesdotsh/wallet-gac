<script setup lang="ts">
import { Head, router, usePage } from '@inertiajs/vue3';
import { computed } from 'vue';
import { ArrowRight, PiggyBank, SendHorizontal } from 'lucide-vue-next';
import AppLayout from '@/layouts/AppLayout.vue';
import WalletBalance from '@/components/WalletBalance.vue';
import TransactionList from '@/components/TransactionList.vue';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { Alert, AlertDescription } from '@/components/ui/alert';
import { dashboard } from '@/routes';
import type { BreadcrumbItem, Wallet, Transaction } from '@/types';

const props = defineProps<{
    wallet?: Wallet;
    transactions: Transaction[];
}>();

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Dashboard', href: dashboard() },
];

const page = usePage();
const flash = computed(() => page.props.flash);

function viewTransaction(transaction: Transaction) {
    router.get(`/transactions/${transaction.id}`);
}
</script>

<template>
    <Head title="Dashboard" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex flex-col gap-6 p-4">
            <Alert v-if="flash.success" class="border-green-200 bg-green-50 text-green-800">
                <AlertDescription>{{ flash.success }}</AlertDescription>
            </Alert>

            <Alert v-if="flash.error" variant="destructive">
                <AlertDescription>{{ flash.error }}</AlertDescription>
            </Alert>

            <div class="grid items-stretch gap-4 md:grid-cols-3">
                <div class="h-full md:col-span-2">
                    <WalletBalance v-if="wallet" :wallet="wallet" class="h-full" />
                    <Card v-else class="h-full">
                        <CardHeader>
                            <CardTitle>Carteira</CardTitle>
                        </CardHeader>
                        <CardContent>
                            <p class="text-muted-foreground">
                                Você ainda não possui uma carteira. Realize seu primeiro depósito para criar uma.
                            </p>
                        </CardContent>
                    </Card>
                </div>

                <Card class="h-full">
                    <CardHeader>
                        <CardTitle>Ações Rápidas</CardTitle>
                    </CardHeader>
                    <CardContent class="flex flex-col gap-3">
                        <Button as="a" href="/deposit" class="w-full justify-start gap-2">
                            <PiggyBank class="size-4" />
                            Depositar
                        </Button>
                        <Button as="a" href="/transfer" variant="outline" class="w-full justify-start gap-2">
                            <SendHorizontal class="size-4" />
                            Transferir
                        </Button>
                    </CardContent>
                </Card>
            </div>

            <Card>
                <CardHeader class="flex flex-row items-center justify-between">
                    <CardTitle>Últimas Transações</CardTitle>
                    <Button v-if="transactions.length > 0" as="a" href="/transactions" variant="ghost" size="sm" class="gap-1">
                        Ver todas
                        <ArrowRight class="size-4" />
                    </Button>
                </CardHeader>
                <CardContent>
                    <TransactionList :transactions="transactions" @view="viewTransaction" />
                </CardContent>
            </Card>
        </div>
    </AppLayout>
</template>
