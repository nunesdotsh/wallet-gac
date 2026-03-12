<script setup lang="ts">
import { Head, useForm, usePage } from '@inertiajs/vue3';
import { computed, ref } from 'vue';
import { ArrowLeft } from 'lucide-vue-next';
import AppLayout from '@/layouts/AppLayout.vue';
import TransactionBadge from '@/components/TransactionBadge.vue';
import InputError from '@/components/InputError.vue';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { Alert, AlertDescription } from '@/components/ui/alert';
import {
    Dialog,
    DialogContent,
    DialogDescription,
    DialogFooter,
    DialogHeader,
    DialogTitle,
    DialogTrigger,
    DialogClose,
} from '@/components/ui/dialog';
import { dashboard } from '@/routes';
import type { BreadcrumbItem, Transaction } from '@/types';

const props = defineProps<{
    transaction: Transaction;
}>();

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Dashboard', href: dashboard() },
    { title: 'Transações', href: '/transactions' },
    { title: 'Detalhes', href: `/transactions/${props.transaction.id}` },
];

const page = usePage();
const flash = computed(() => page.props.flash);

const confirmOpen = ref(false);

const reverseForm = useForm<{ reversal?: string }>({});

function formatDate(dateStr: string): string {
    const date = new Date(dateStr);
    const day = String(date.getDate()).padStart(2, '0');
    const month = String(date.getMonth() + 1).padStart(2, '0');
    const year = date.getFullYear();
    const hours = String(date.getHours()).padStart(2, '0');
    const minutes = String(date.getMinutes()).padStart(2, '0');
    return `${day}/${month}/${year} às ${hours}:${minutes}`;
}

const statusLabels: Record<Transaction['status'], string> = {
    completed: 'Concluída',
    reversed: 'Revertida',
    failed: 'Falhou',
};

const canReverse = computed(() =>
    props.transaction.status === 'completed' && props.transaction.type !== 'transfer_in',
);

function submitReverse() {
    reverseForm.post(`/transactions/${props.transaction.id}/reverse`, {
        onSuccess: () => {
            confirmOpen.value = false;
        },
    });
}
</script>

<template>
    <Head title="Detalhes da Transação" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="mx-auto w-full max-w-2xl p-4">
            <Alert v-if="flash.success" class="mb-6 border-green-200 bg-green-50 text-green-800">
                <AlertDescription>{{ flash.success }}</AlertDescription>
            </Alert>

            <Alert v-if="flash.error" variant="destructive" class="mb-6">
                <AlertDescription>{{ flash.error }}</AlertDescription>
            </Alert>

            <InputError :message="reverseForm.errors.reversal" />

            <Alert v-if="transaction.status === 'reversed'" class="mb-6 border-orange-200 bg-orange-50 text-orange-800">
                <AlertDescription>
                    Esta transação foi revertida em {{ transaction.reversed_at ? formatDate(transaction.reversed_at) : '' }}.
                </AlertDescription>
            </Alert>

            <Card>
                <CardHeader>
                    <CardTitle>Detalhes da Transação</CardTitle>
                </CardHeader>
                <CardContent>
                    <dl class="grid gap-4">
                        <div class="flex items-center justify-between border-b pb-3">
                            <dt class="text-sm text-muted-foreground">Tipo</dt>
                            <dd>
                                <TransactionBadge :type="transaction.type" :status="transaction.status" />
                            </dd>
                        </div>

                        <div class="flex items-center justify-between border-b pb-3">
                            <dt class="text-sm text-muted-foreground">Status</dt>
                            <dd class="text-sm font-medium">{{ statusLabels[transaction.status] }}</dd>
                        </div>

                        <div class="flex items-center justify-between border-b pb-3">
                            <dt class="text-sm text-muted-foreground">Valor</dt>
                            <dd class="text-lg font-bold" :class="transaction.type === 'transfer_out' ? 'text-red-600' : 'text-green-600'">
                                {{ transaction.formatted_amount }}
                            </dd>
                        </div>

                        <div class="flex items-center justify-between border-b pb-3">
                            <dt class="text-sm text-muted-foreground">Saldo Antes</dt>
                            <dd class="text-sm font-medium">{{ transaction.formatted_balance_before }}</dd>
                        </div>

                        <div class="flex items-center justify-between border-b pb-3">
                            <dt class="text-sm text-muted-foreground">Saldo Após</dt>
                            <dd class="text-sm font-medium">{{ transaction.formatted_balance_after }}</dd>
                        </div>

                        <div class="flex items-center justify-between border-b pb-3">
                            <dt class="text-sm text-muted-foreground">Data</dt>
                            <dd class="text-sm font-medium">{{ formatDate(transaction.created_at) }}</dd>
                        </div>

                        <template v-if="transaction.counterpart_name || transaction.counterpart_email">
                            <div class="flex items-center justify-between border-b pb-3">
                                <dt class="text-sm text-muted-foreground">
                                    {{ transaction.type === 'transfer_out' ? 'Enviado para' : 'Recebido de' }}
                                </dt>
                                <dd class="text-right text-sm">
                                    <span v-if="transaction.counterpart_name" class="block font-medium">
                                        {{ transaction.counterpart_name }}
                                    </span>
                                    <span v-if="transaction.counterpart_email" class="block text-muted-foreground">
                                        {{ transaction.counterpart_email }}
                                    </span>
                                </dd>
                            </div>
                        </template>

                        <div v-if="transaction.description" class="flex items-center justify-between">
                            <dt class="text-sm text-muted-foreground">Descrição</dt>
                            <dd class="text-sm font-medium">{{ transaction.description }}</dd>
                        </div>
                    </dl>

                    <div class="mt-6 flex items-center gap-3">
                        <Button as="a" href="/transactions" variant="outline" class="gap-2">
                            <ArrowLeft class="size-4" />
                            Voltar
                        </Button>

                        <Dialog v-if="canReverse" v-model:open="confirmOpen">
                            <DialogTrigger as-child>
                                <Button variant="destructive">
                                    Reverter Transação
                                </Button>
                            </DialogTrigger>
                            <DialogContent>
                                <DialogHeader>
                                    <DialogTitle>Confirmar Reversão</DialogTitle>
                                    <DialogDescription>
                                        Tem certeza de que deseja reverter esta transação de {{ transaction.formatted_amount }}?
                                        Esta ação não pode ser desfeita.
                                    </DialogDescription>
                                </DialogHeader>
                                <DialogFooter>
                                    <DialogClose as-child>
                                        <Button variant="outline">Cancelar</Button>
                                    </DialogClose>
                                    <Button
                                        variant="destructive"
                                        :disabled="reverseForm.processing"
                                        @click="submitReverse"
                                    >
                                        Confirmar Reversão
                                    </Button>
                                </DialogFooter>
                            </DialogContent>
                        </Dialog>
                    </div>
                </CardContent>
            </Card>
        </div>
    </AppLayout>
</template>
