<script setup lang="ts">
import { Head } from '@inertiajs/vue3';

defineProps<{
    metrics?: any;
}>();
</script>

<template>
    <div class="min-h-screen bg-neutral-100 p-8">
        <Head title="Admin Dashboard - Evic Meat Shop" />
        <h1 class="text-2xl font-bold">Admin Dashboard</h1>
    </div>
</template>
