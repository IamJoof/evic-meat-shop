<script setup lang="ts">
import { Head } from '@inertiajs/vue3';

defineProps<{
    cart: any;
    subtotal: number;
}>();
</script>

<template>
    <div class="min-h-screen bg-neutral-50 text-neutral-900">
        <Head title="Shopping Cart - Evic Meat Shop" />
        <main class="mx-auto max-w-4xl px-4 py-8">
            <h1 class="text-2xl font-bold">Shopping Cart</h1>
        </main>
    </div>
</template>
