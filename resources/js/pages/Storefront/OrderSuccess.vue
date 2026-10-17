<script setup lang="ts">
import { Head } from '@inertiajs/vue3';

interface OrderItem {
    id: number;
    product_name: string;
    price_per_kg: string | number;
    weight_kg: string | number;
    line_total: string | number;
    cutting_notes?: string | null;
}

interface Order {
    id: number;
    order_number: string;
    customer_name: string;
    fulfillment_type: string;
    expected_delivery_date: string;
    total_amount: string | number;
    status: string;
    items: OrderItem[];
}

defineProps<{
    order: Order;
}>();
</script>

<template>
    <div class="min-h-screen bg-neutral-50 text-neutral-900">
        <Head :title="`Order #${order.order_number}`" />
        <main class="mx-auto max-w-3xl px-4 py-8">
            <h1 class="text-2xl font-bold">Order Received!</h1>
            <p class="text-neutral-600">Order Number: {{ order.order_number }}</p>
            <p class="mt-2 font-medium">Status: <span class="capitalize">{{ order.status }}</span></p>
        </main>
    </div>
</template>
