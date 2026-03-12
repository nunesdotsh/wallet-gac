<script setup lang="ts">
import { Head, useForm, usePage } from '@inertiajs/vue3';
import { computed } from 'vue';
import AppLayout from '@/layouts/AppLayout.vue';
import MoneyInput from '@/components/MoneyInput.vue';
import InputError from '@/components/InputError.vue';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Alert, AlertDescription } from '@/components/ui/alert';
import { dashboard } from '@/routes';
import type { BreadcrumbItem } from '@/types';

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Dashboard', href: dashboard() },
    { title: 'Transferir', href: '/transfer' },
];

const page = usePage();
const flash = computed(() => page.props.flash);

const form = useForm({
    email: '',
    amount: 0,
});

const submit = () => {
    form.post('/transfer');
};
</script>

<template>
    <Head title="Transferir" />

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
                    <CardTitle>Realizar Transferência</CardTitle>
                </CardHeader>
                <CardContent>
                    <form @submit.prevent="submit" class="space-y-6">
                        <div class="grid gap-2">
                            <Label for="email">E-mail do Destinatário</Label>
                            <Input
                                id="email"
                                v-model="form.email"
                                type="email"
                                placeholder="email@exemplo.com"
                                required
                            />
                            <InputError :message="form.errors.email" />
                        </div>

                        <MoneyInput
                            v-model="form.amount"
                            label="Valor da Transferência"
                            :error="form.errors.amount"
                            id="amount"
                        />

                        <Button type="submit" :disabled="form.processing" class="w-full">
                            Transferir
                        </Button>
                    </form>
                </CardContent>
            </Card>
        </div>
    </AppLayout>
</template>
