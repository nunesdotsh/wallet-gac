<script setup lang="ts">
import type { Transaction } from '@/types';
import { computed } from 'vue';

const props = defineProps<{
    type: Transaction['type'];
    status: Transaction['status'];
}>();

const typeConfig = {
    deposit: { label: 'Depósito', classes: 'bg-green-100 text-green-800' },
    transfer_in: { label: 'Recebimento', classes: 'bg-blue-100 text-blue-800' },
    transfer_out: { label: 'Transferência', classes: 'bg-red-100 text-red-800' },
} as const;

const reversedClasses = 'bg-orange-100 text-orange-800';

const badgeClasses = computed(() =>
    props.status === 'reversed' ? reversedClasses : typeConfig[props.type].classes,
);

const label = computed(() => {
    const base = typeConfig[props.type].label;
    return props.status === 'reversed' ? `${base} (Revertida)` : base;
});
</script>

<template>
    <span
        :class="[
            'inline-flex items-center rounded-full px-2 py-0.5 text-xs font-medium',
            badgeClasses,
        ]"
    >
        {{ label }}
    </span>
</template>
