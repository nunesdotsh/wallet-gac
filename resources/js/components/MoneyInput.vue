<script setup lang="ts">
import { watch } from 'vue';
import { useCurrencyInput } from 'vue-currency-input';

defineProps<{
    modelValue: number;
    label?: string;
    error?: string;
    id?: string;
}>();

const emit = defineEmits<{
    'update:modelValue': [value: number];
}>();

const { inputRef, numberValue } = useCurrencyInput(
    {
        currency: 'BRL',
        locale: 'pt-BR',
        autoDecimalDigits: true,
        hideCurrencySymbolOnFocus: false,
        hideGroupingSeparatorOnFocus: false,
        valueRange: { min: 0 },
    },
    false,
);

watch(numberValue, (val) => {
    emit('update:modelValue', val ?? 0);
});
</script>

<template>
    <div>
        <label v-if="label" :for="id" class="mb-1 block text-sm font-medium">
            {{ label }}
        </label>
        <input
            :id="id"
            ref="inputRef"
            data-slot="input"
            :class="[
                'border-input placeholder:text-muted-foreground h-9 w-full min-w-0 rounded-md border bg-transparent px-3 py-1 text-base shadow-xs transition-[color,box-shadow] outline-none md:text-sm',
                'focus-visible:border-ring focus-visible:ring-ring/50 focus-visible:ring-[3px]',
                error
                    ? 'border-destructive ring-destructive/20 dark:ring-destructive/40'
                    : '',
            ]"
        />
        <p v-if="error" class="mt-1 text-sm text-red-500">
            {{ error }}
        </p>
    </div>
</template>
