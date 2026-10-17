<script setup lang="ts">
import { Head } from '@inertiajs/vue3';

defineProps<{
    order?: any;
}>();
</script>

<template>
    <div class="min-h-screen bg-neutral-100 p-8">
        <Head title="Order Details & Prep Slip" />
        <h1 class="text-2xl font-bold">Order Details</h1>
    </div>
</template>
