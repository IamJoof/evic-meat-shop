<script setup lang="ts">
import { Head } from '@inertiajs/vue3';

defineProps<{
    products?: any;
}>();
</script>

<template>
    <div class="min-h-screen bg-neutral-100 p-8">
        <Head title="Products Management - Admin" />
        <h1 class="text-2xl font-bold">Meat Catalog Management</h1>
    </div>
</template>
