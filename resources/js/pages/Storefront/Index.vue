<script setup lang="ts">
import { Head } from '@inertiajs/vue3';

interface Category {
    id: number;
    name: string;
    slug: string;
    description: string | null;
    products_count?: number;
}

interface Product {
    id: number;
    name: string;
    slug: string;
    price_per_kg: string | number;
    is_available: boolean;
    description: string | null;
    category?: Category;
}

interface PaginatedProducts {
    data: Product[];
    current_page: number;
    last_page: number;
    total: number;
}

defineProps<{
    products: PaginatedProducts;
    categories: Category[];
    currentCategory?: string | null;
    search?: string | null;
}>();
</script>

<template>
    <div class="min-h-screen bg-neutral-50 text-neutral-900">
        <Head title="Fresh Meat & Poultry Catalog" />
        <main class="mx-auto max-w-7xl px-4 py-8 sm:px-6 lg:px-8">
            <h1 class="text-3xl font-bold tracking-tight">Evic Meat & Poultry Shop</h1>
            <p class="mt-2 text-neutral-600">Fresh cuts daily, weighed and butchered to your preference.</p>

            <div class="mt-8 grid grid-cols-1 gap-6 sm:grid-cols-2 lg:grid-cols-3">
                <article
                    v-for="product in products.data"
                    :key="product.id"
                    class="rounded-xl border border-neutral-200 bg-white p-5 shadow-sm"
                >
                    <h2 class="text-lg font-semibold">{{ product.name }}</h2>
                    <p class="text-sm font-medium text-emerald-600">₱{{ product.price_per_kg }} / kg</p>
                    <span
                        v-if="!product.is_available"
                        class="mt-2 inline-block rounded bg-red-100 px-2 py-0.5 text-xs font-semibold text-red-700"
                    >
                        Sold Out Today
                    </span>
                </article>
            </div>
        </main>
    </div>
</template>
