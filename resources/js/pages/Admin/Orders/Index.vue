<script setup lang="ts">
import { Head } from '@inertiajs/vue3';

defineProps<{
    orders?: any;
}>();
</script>

<template>
    <div class="min-h-screen bg-neutral-100 p-8">
        <Head title="Order Fulfillment Board - Admin" />
        <h1 class="text-2xl font-bold">Orders Fulfillment</h1>
    </div>
</template>
