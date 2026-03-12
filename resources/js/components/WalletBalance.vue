<script setup lang="ts">
import type { Wallet } from '@/types';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { Wallet as WalletIcon } from 'lucide-vue-next';

defineProps<{
    wallet: Wallet;
}>();
</script>

<template>
    <Card class="h-full">
        <CardHeader>
            <CardTitle class="flex items-center gap-2">
                <WalletIcon class="size-5" />
                Saldo Disponível
            </CardTitle>
        </CardHeader>
        <CardContent>
            <p class="text-3xl font-bold text-green-600">
                {{ wallet.formatted_balance }}
            </p>
        </CardContent>
    </Card>
</template>
