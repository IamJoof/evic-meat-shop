<script setup lang="ts">
import { Head } from '@inertiajs/vue3';

interface CartItem {
    id: number;
    weight_kg: string | number;
    cutting_notes?: string | null;
    product: {
        id: number;
        name: string;
        price_per_kg: string | number;
    };
}

interface Cart {
    id: number;
    items: CartItem[];
}

defineProps<{
    cart: Cart;
    subtotal: number;
}>();
</script>

<template>
    <div class="min-h-screen bg-neutral-50 text-neutral-900">
        <Head title="Checkout - Evic Meat Shop" />
        <main class="mx-auto max-w-4xl px-4 py-8">
            <h1 class="text-2xl font-bold">Complete Your Meat Order</h1>
            <p class="text-neutral-600">Review your fresh cuts and specify delivery schedule.</p>
        </main>
    </div>
</template>
