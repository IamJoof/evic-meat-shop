<script setup lang="ts">
import { Head } from '@inertiajs/vue3';

defineProps<{
    product?: any;
    categories?: any;
}>();
</script>

<template>
    <div class="min-h-screen bg-neutral-100 p-8">
        <Head title="Create / Edit Product" />
        <h1 class="text-2xl font-bold">Product Form</h1>
    </div>
</template>
