<script setup lang="ts">
import { Head, router } from '@inertiajs/vue3';
import { ref } from 'vue';
import CartDrawer from '@/components/Storefront/CartDrawer.vue';
import ProductCard from '@/components/Storefront/ProductCard.vue';

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
    category?: {
        id: number;
        name: string;
    };
    primary_image?: {
        image_path: string;
    } | null;
}

interface PaginatedProducts {
    data: Product[];
    current_page: number;
    last_page: number;
    total: number;
}

const props = defineProps<{
    products: PaginatedProducts;
    categories: Category[];
    currentCategory?: string | null;
    search?: string | null;
    cart?: any;
    subtotal?: number;
}>();

const isCartOpen = ref<boolean>(false);
const searchQuery = ref<string>(props.search || '');

function filterCategory(slug: string | null) {
    router.get(
        '/',
        {
            category: slug || undefined,
            search: searchQuery.value || undefined,
        },
        { preserveState: true },
    );
}

function handleSearch() {
    router.get(
        '/',
        {
            category: props.currentCategory || undefined,
            search: searchQuery.value || undefined,
        },
        { preserveState: true },
    );
}
</script>

<template>
    <div class="min-h-screen bg-neutral-50 text-neutral-900">
        <Head title="Fresh Meat & Poultry Catalog - Evic Meat Shop" />

        <!-- Navigation Bar -->
        <header
            class="sticky top-0 z-40 border-b border-neutral-200 bg-white/95 backdrop-blur-xs"
        >
            <div
                class="mx-auto flex max-w-7xl items-center justify-between px-4 py-3 sm:px-6 lg:px-8"
            >
                <div class="flex items-center gap-3">
                    <span
                        class="flex h-10 w-10 items-center justify-center rounded-xl bg-rose-600 text-lg font-black text-white"
                    >
                        E
                    </span>
                    <div>
                        <h1
                            class="text-lg font-black tracking-tight text-neutral-900"
                        >
                            Evic Meat & Poultry
                        </h1>
                        <p class="text-xs font-medium text-neutral-500">
                            Butchered Fresh Daily
                        </p>
                    </div>
                </div>

                <div class="flex items-center gap-4">
                    <!-- Search bar -->
                    <form
                        @submit.prevent="handleSearch"
                        class="hidden sm:block"
                    >
                        <input
                            v-model="searchQuery"
                            type="text"
                            placeholder="Search cuts (chicken, pork, ribs)..."
                            class="w-64 rounded-xl border border-neutral-300 px-3 py-1.5 text-xs outline-hidden focus:border-rose-500"
                        />
                    </form>

                    <!-- Cart Trigger Button -->
                    <button
                        type="button"
                        @click="isCartOpen = true"
                        class="flex cursor-pointer items-center gap-2 rounded-xl bg-neutral-900 px-4 py-2 text-xs font-bold text-white shadow-xs transition hover:bg-neutral-800"
                    >
                        <span>Cart</span>
                        <span
                            v-if="cart?.items?.length"
                            class="rounded-full bg-rose-600 px-1.5 py-0.5 text-[10px] font-extrabold text-white"
                        >
                            {{ cart.items.length }}
                        </span>
                    </button>
                </div>
            </div>

            <!-- Categories Tabs -->
            <div class="border-t border-neutral-100 bg-white">
                <nav
                    class="mx-auto flex max-w-7xl gap-2 overflow-x-auto px-4 py-2 sm:px-6 lg:px-8"
                >
                    <button
                        type="button"
                        @click="filterCategory(null)"
                        :class="[
                            'cursor-pointer rounded-lg px-3 py-1.5 text-xs font-semibold whitespace-nowrap transition',
                            !currentCategory
                                ? 'bg-rose-600 text-white'
                                : 'text-neutral-600 hover:bg-neutral-100',
                        ]"
                    >
                        All Cuts
                    </button>
                    <button
                        v-for="cat in categories"
                        :key="cat.id"
                        type="button"
                        @click="filterCategory(cat.slug)"
                        :class="[
                            'cursor-pointer rounded-lg px-3 py-1.5 text-xs font-semibold whitespace-nowrap transition',
                            currentCategory === cat.slug
                                ? 'bg-rose-600 text-white'
                                : 'text-neutral-600 hover:bg-neutral-100',
                        ]"
                    >
                        {{ cat.name }} ({{ cat.products_count ?? 0 }})
                    </button>
                </nav>
            </div>
        </header>

        <!-- Main Product Grid -->
        <main class="mx-auto max-w-7xl px-4 py-8 sm:px-6 lg:px-8">
            <div v-if="products.data.length === 0" class="py-16 text-center">
                <p class="text-base font-medium text-neutral-600">
                    No meat products found matching your filter.
                </p>
                <button
                    type="button"
                    @click="filterCategory(null)"
                    class="mt-3 text-xs font-bold text-rose-600 hover:underline"
                >
                    View All Meat Cuts
                </button>
            </div>

            <div
                v-else
                class="grid grid-cols-1 gap-6 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4"
            >
                <ProductCard
                    v-for="product in products.data"
                    :key="product.id"
                    :product="product"
                />
            </div>
        </main>

        <!-- Slide-out Cart Drawer -->
        <CartDrawer
            :is-open="isCartOpen"
            :cart="cart || null"
            :subtotal="subtotal || 0"
            @close="isCartOpen = false"
        />
    </div>
</template>
