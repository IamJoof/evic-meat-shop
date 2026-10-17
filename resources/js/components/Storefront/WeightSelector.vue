<script setup lang="ts">
import { computed } from 'vue';

const props = withDefaults(
    defineProps<{
        modelValue: number;
        pricePerKg: number;
        step?: number;
        min?: number;
        max?: number;
    }>(),
    {
        step: 0.5,
        min: 0.25,
        max: 50.0,
    },
);

const emit = defineEmits<{
    (e: 'update:modelValue', value: number): void;
}>();

const calculatedPrice = computed(() => {
    return Math.round(props.modelValue * props.pricePerKg * 100) / 100;
});

function adjustWeight(delta: number) {
    const next = Math.max(
        props.min,
        Math.min(props.max, Math.round((props.modelValue + delta) * 100) / 100),
    );
    emit('update:modelValue', next);
}

function onInput(e: Event) {
    const target = e.target as HTMLInputElement;
    const val = parseFloat(target.value);

    if (!isNaN(val) && val >= props.min && val <= props.max) {
        emit('update:modelValue', Math.round(val * 100) / 100);
    }
}
</script>

<template>
    <div class="space-y-2">
        <div
            class="flex items-center justify-between text-xs font-medium text-neutral-500"
        >
            <span>Select Weight (kg)</span>
            <span class="font-semibold text-emerald-700"
                >Est. ₱{{ calculatedPrice.toFixed(2) }}</span
            >
        </div>
        <div
            class="flex items-center rounded-lg border border-neutral-300 bg-white p-1 shadow-xs"
        >
            <button
                type="button"
                @click="adjustWeight(-step)"
                :disabled="modelValue <= min"
                class="flex h-8 w-8 items-center justify-center rounded-md border border-neutral-200 bg-neutral-50 text-neutral-700 hover:bg-neutral-100 disabled:opacity-40"
                aria-label="Decrease weight"
            >
                -
            </button>
            <input
                type="number"
                :value="modelValue"
                @input="onInput"
                :step="step"
                :min="min"
                :max="max"
                class="w-full text-center font-semibold text-neutral-800 outline-hidden"
            />
            <span class="pr-2 text-xs text-neutral-400">kg</span>
            <button
                type="button"
                @click="adjustWeight(step)"
                :disabled="modelValue >= max"
                class="flex h-8 w-8 items-center justify-center rounded-md border border-neutral-200 bg-neutral-50 text-neutral-700 hover:bg-neutral-100 disabled:opacity-40"
                aria-label="Increase weight"
            >
                +
            </button>
        </div>
        <div class="flex gap-1.5 pt-1">
            <button
                v-for="preset in [0.5, 1.0, 1.5, 2.0]"
                :key="preset"
                type="button"
                @click="emit('update:modelValue', preset)"
                :class="[
                    'rounded-md px-2 py-0.5 text-xs font-medium transition',
                    modelValue === preset
                        ? 'bg-rose-600 text-white'
                        : 'bg-neutral-100 text-neutral-600 hover:bg-neutral-200',
                ]"
            >
                {{ preset }} kg
            </button>
        </div>
    </div>
</template>
