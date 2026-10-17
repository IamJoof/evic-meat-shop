<script setup lang="ts">
import { Head, router } from '@inertiajs/vue3';

interface Product {
    id: number;
    name: string;
    slug: string;
    price_per_kg: string | number;
    is_available: boolean;
    description: string | null;
    category?: {
        id: number;
        name: string;
    } | null;
}

interface PaginatedProducts {
    data: Product[];
    current_page: number;
    last_page: number;
    total: number;
}

defineProps<{
    products: PaginatedProducts;
}>();

function toggleAvailability(productId: number) {
    router.patch(
        `/admin/products/${productId}/toggle-availability`,
        {},
        { preserveScroll: true },
    );
}

function deleteProduct(productId: number) {
    if (
        confirm(
            'Are you sure you want to remove this product from the catalog?',
        )
    ) {
        router.delete(`/admin/products/${productId}`);
    }
}
</script>

<template>
    <div class="min-h-screen bg-neutral-100 pb-16 text-neutral-900">
        <Head title="Meat Products Catalog - Admin" />

        <header class="border-b border-neutral-200 bg-white py-4 shadow-xs">
            <div
                class="mx-auto flex max-w-7xl items-center justify-between px-4 sm:px-6 lg:px-8"
            >
                <div class="flex items-center gap-4">
                    <span
                        class="rounded-lg bg-rose-600 px-2.5 py-1 text-xs font-black text-white"
                        >ADMIN</span
                    >
                    <h1 class="text-xl font-black text-neutral-900">
                        Meat Catalog Management
                    </h1>
                </div>
                <div class="flex items-center gap-3">
                    <a
                        href="/admin/orders"
                        class="text-xs font-bold text-neutral-600 hover:text-rose-600"
                    >
                        Orders Board
                    </a>
                    <a
                        href="/admin/products/create"
                        class="rounded-xl bg-rose-600 px-3.5 py-2 text-xs font-bold text-white shadow-xs hover:bg-rose-700"
                    >
                        + Add New Meat Cut
                    </a>
                </div>
            </div>
        </header>

        <main class="mx-auto mt-6 max-w-7xl px-4 sm:px-6 lg:px-8">
            <div
                class="overflow-hidden rounded-2xl border border-neutral-200 bg-white shadow-xs"
            >
                <table class="w-full text-left text-xs">
                    <thead
                        class="border-b border-neutral-200 bg-neutral-50 font-semibold text-neutral-500 uppercase"
                    >
                        <tr>
                            <th class="px-5 py-3.5">Cut Name</th>
                            <th class="px-5 py-3.5">Category</th>
                            <th class="px-5 py-3.5">Price / kg</th>
                            <th class="px-5 py-3.5">Status</th>
                            <th class="px-5 py-3.5 text-right">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-neutral-100">
                        <tr v-if="products.data.length === 0">
                            <td
                                colspan="5"
                                class="py-12 text-center text-neutral-500"
                            >
                                No products in catalog. Click "+ Add New Meat
                                Cut" to create one.
                            </td>
                        </tr>
                        <tr
                            v-for="product in products.data"
                            :key="product.id"
                            class="transition hover:bg-neutral-50"
                        >
                            <td class="px-5 py-4 font-bold text-neutral-900">
                                {{ product.name }}
                                <p
                                    v-if="product.description"
                                    class="line-clamp-1 text-[11px] font-normal text-neutral-500"
                                >
                                    {{ product.description }}
                                </p>
                            </td>
                            <td class="px-5 py-4">
                                <span
                                    class="rounded-full bg-neutral-100 px-2 py-0.5 font-semibold text-neutral-700"
                                >
                                    {{
                                        product.category?.name ||
                                        'Uncategorized'
                                    }}
                                </span>
                            </td>
                            <td class="px-5 py-4 font-bold text-neutral-900">
                                ₱{{ Number(product.price_per_kg).toFixed(2) }}
                            </td>
                            <td class="px-5 py-4">
                                <button
                                    type="button"
                                    @click="toggleAvailability(product.id)"
                                    :class="[
                                        'cursor-pointer rounded-full px-2.5 py-1 text-[11px] font-bold transition',
                                        product.is_available
                                            ? 'bg-emerald-100 text-emerald-800 hover:bg-emerald-200'
                                            : 'bg-neutral-200 text-neutral-600 hover:bg-neutral-300',
                                    ]"
                                >
                                    {{
                                        product.is_available
                                            ? 'In Stock (Click to Disable)'
                                            : 'Sold Out (Click to Enable)'
                                    }}
                                </button>
                            </td>
                            <td class="space-x-2 px-5 py-4 text-right">
                                <a
                                    :href="`/admin/products/${product.id}/edit`"
                                    class="font-semibold text-neutral-700 hover:text-rose-600"
                                >
                                    Edit
                                </a>
                                <button
                                    type="button"
                                    @click="deleteProduct(product.id)"
                                    class="cursor-pointer font-semibold text-red-600 hover:text-red-800"
                                >
                                    Delete
                                </button>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </main>
    </div>
</template>
