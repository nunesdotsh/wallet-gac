<script setup lang="ts">
import { Head, useForm, usePage } from '@inertiajs/vue3';
import { computed } from 'vue';
import AppLayout from '@/layouts/AppLayout.vue';
import MoneyInput from '@/components/MoneyInput.vue';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { Alert, AlertDescription } from '@/components/ui/alert';
import { dashboard } from '@/routes';
import type { BreadcrumbItem } from '@/types';

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Dashboard', href: dashboard() },
    { title: 'Depositar', href: '/deposit' },
];

const page = usePage();
const flash = computed(() => page.props.flash);

const form = useForm({ amount: 0 });

const submit = () => {
    form.post('/deposit');
};
</script>

<template>
    <Head title="Depositar" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="mx-auto w-full max-w-lg p-4">
            <Alert v-if="flash.success" class="mb-6 border-green-200 bg-green-50 text-green-800">
                <AlertDescription>{{ flash.success }}</AlertDescription>
            </Alert>

            <Alert v-if="flash.error" variant="destructive" class="mb-6">
                <AlertDescription>{{ flash.error }}</AlertDescription>
            </Alert>

            <Card>
                <CardHeader>
                    <CardTitle>Realizar Depósito</CardTitle>
                </CardHeader>
                <CardContent>
                    <form @submit.prevent="submit" class="space-y-6">
                        <MoneyInput
                            v-model="form.amount"
                            label="Valor do Depósito"
                            :error="form.errors.amount"
                            id="amount"
                        />

                        <Button type="submit" :disabled="form.processing" class="w-full">
                            Depositar
                        </Button>
                    </form>
                </CardContent>
            </Card>
        </div>
    </AppLayout>
</template>
