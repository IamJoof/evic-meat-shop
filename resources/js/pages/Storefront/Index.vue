<script setup lang="ts">
import { Head, router } from '@inertiajs/vue3';
import { ref } from 'vue';
import CartDrawer from '@/components/Storefront/CartDrawer.vue';
import ProductCard from '@/components/Storefront/ProductCard.vue';
import HeroTrustBanner from '@/components/Storefront/HeroTrustBanner.vue';
import CustomerSupportChat from '@/components/Storefront/CustomerSupportChat.vue';
import { ShoppingBag, Search, Sparkles } from 'lucide-vue-next';

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
    <div class="min-h-screen bg-stone-50 text-stone-900 flex flex-col justify-between">
        <Head title="Evic Meat & Poultry Shop | Prime Butchery Delivered Fresh" />

        <!-- Top Announcement Bar -->
        <div class="bg-stone-900 px-4 py-2 text-center text-xs font-medium text-rose-200">
            ❄️ Same-day insulated delivery with frozen gel packs • Cut fresh daily at 4:00 AM • Free custom butchery slicing
        </div>

        <!-- Sticky Header -->
        <header class="sticky top-0 z-40 border-b border-stone-200 bg-white/95 backdrop-blur-md shadow-xs">
            <div class="mx-auto flex max-w-7xl items-center justify-between px-4 py-3 sm:px-6 lg:px-8">
                <div class="flex items-center gap-3">
                    <span class="flex h-11 w-11 items-center justify-center rounded-2xl bg-gradient-to-br from-rose-700 to-rose-950 font-serif text-xl font-black text-white shadow-sm">
                        E
                    </span>
                    <div>
                        <div class="flex items-center gap-2">
                            <h1 class="text-lg font-black tracking-tight text-stone-900 sm:text-xl font-serif">
                                EVIC MEAT & POULTRY
                            </h1>
                            <span class="hidden sm:inline-flex items-center gap-1 rounded-full bg-emerald-50 px-2 py-0.5 text-[10px] font-bold text-emerald-700 border border-emerald-200">
                                <Sparkles class="h-3 w-3" /> NMIS Inspected
                            </span>
                        </div>
                        <p class="text-xs font-medium text-stone-500">
                            Artisanal Quality • Weight-True Orders
                        </p>
                    </div>
                </div>

                <div class="flex items-center gap-3">
                    <!-- Search input -->
                    <form @submit.prevent="handleSearch" class="relative hidden md:block">
                        <Search class="absolute left-3 top-2.5 h-3.5 w-3.5 text-stone-400" />
                        <input
                            v-model="searchQuery"
                            type="text"
                            placeholder="Search cuts (pork, chicken, liempo)..."
                            class="w-64 rounded-xl border border-stone-300 bg-stone-50/70 pl-8 pr-3 py-1.5 text-xs text-stone-900 placeholder-stone-400 outline-hidden transition focus:border-rose-600 focus:bg-white focus:ring-1 focus:ring-rose-600"
                        />
                    </form>

                    <!-- Cart Trigger Button -->
                    <button
                        type="button"
                        @click="isCartOpen = true"
                        class="flex cursor-pointer items-center gap-2.5 rounded-xl bg-stone-900 px-4 py-2.5 text-xs font-bold text-white shadow-xs transition hover:bg-stone-800 active:scale-95"
                    >
                        <ShoppingBag class="h-4 w-4 text-rose-400" />
                        <span>Butcher Cart</span>
                        <span
                            v-if="cart?.items?.length"
                            class="rounded-full bg-rose-600 px-2 py-0.5 text-[10px] font-extrabold text-white"
                        >
                            {{ cart.items.length }}
                        </span>
                    </button>
                </div>
            </div>

            <!-- Categories Tabs Bar -->
            <div class="border-t border-stone-100 bg-stone-50/80">
                <nav class="mx-auto flex max-w-7xl gap-2 overflow-x-auto px-4 py-2 sm:px-6 lg:px-8">
                    <button
                        type="button"
                        @click="filterCategory(null)"
                        :class="[
                            'cursor-pointer rounded-lg px-3.5 py-1.5 text-xs font-semibold whitespace-nowrap transition',
                            !currentCategory
                                ? 'bg-rose-900 text-white shadow-xs'
                                : 'text-stone-600 hover:bg-stone-200/60',
                        ]"
                    >
                        All Prime Cuts
                    </button>
                    <button
                        v-for="cat in categories"
                        :key="cat.id"
                        type="button"
                        @click="filterCategory(cat.slug)"
                        :class="[
                            'cursor-pointer rounded-lg px-3.5 py-1.5 text-xs font-semibold whitespace-nowrap transition',
                            currentCategory === cat.slug
                                ? 'bg-rose-900 text-white shadow-xs'
                                : 'text-stone-600 hover:bg-stone-200/60',
                        ]"
                    >
                        {{ cat.name }} <span class="opacity-70 text-[11px]">({{ cat.products_count ?? 0 }})</span>
                    </button>
                </nav>
            </div>
        </header>

        <!-- Page Body Content -->
        <main class="mx-auto max-w-7xl px-4 py-8 sm:px-6 lg:px-8 space-y-10 flex-1 w-full">
            <!-- Hero Trust Banner -->
            <HeroTrustBanner :total-cuts-count="products.total" />

            <!-- Catalog Section -->
            <section id="catalog-section" class="space-y-6 pt-2">
                <div class="flex flex-col sm:flex-row sm:items-end sm:justify-between gap-3 border-b border-stone-200 pb-4">
                    <div>
                        <h2 class="text-2xl font-black tracking-tight text-stone-900 font-serif">
                            Fresh Counter Selections
                        </h2>
                        <p class="text-xs text-stone-500 mt-1">
                            Available in exact weights. Select custom butcher prep notes at no extra charge.
                        </p>
                    </div>

                    <!-- Search (Mobile) -->
                    <form @submit.prevent="handleSearch" class="block md:hidden">
                        <input
                            v-model="searchQuery"
                            type="text"
                            placeholder="Search cuts..."
                            class="w-full rounded-xl border border-stone-300 bg-white px-3 py-1.5 text-xs"
                        />
                    </form>
                </div>

                <!-- Empty state -->
                <div v-if="products.data.length === 0" class="py-16 text-center rounded-2xl border border-dashed border-stone-300 bg-white p-8">
                    <p class="text-base font-semibold text-stone-700">
                        No meat products found matching your current filter.
                    </p>
                    <p class="text-xs text-stone-500 mt-1">
                        Try resetting your search query or browsing all meat cuts.
                    </p>
                    <button
                        type="button"
                        @click="filterCategory(null)"
                        class="mt-4 rounded-xl bg-rose-700 px-4 py-2 text-xs font-bold text-white shadow-xs hover:bg-rose-800"
                    >
                        View All Fresh Cuts
                    </button>
                </div>

                <!-- Product Grid -->
                <div
                    v-else
                    class="grid grid-cols-1 gap-6 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4"
                >
                    <ProductCard
                        v-for="product in products.data"
                        :key="product.id"
                        :product="product"
                        @added="isCartOpen = true"
                    />
                </div>
            </section>
        </main>

        <!-- Trust Footer -->
        <footer class="mt-16 border-t border-stone-200 bg-white py-8 text-stone-500 text-xs">
            <div class="mx-auto max-w-7xl px-4 text-center sm:px-6 lg:px-8 space-y-2">
                <p class="font-semibold text-stone-700">
                    Evic Meat & Poultry Shop • Safe, Fresh, Hygienic Meat Handling
                </p>
                <p>
                    Every order is sealed in food-grade packaging with chilled gel pads. Satisfaction and freshness guaranteed.
                </p>
                <p class="text-[11px] text-stone-400">
                    &copy; 2026 Evic Meat & Poultry Shop. All rights reserved.
                </p>
            </div>
        </footer>

        <!-- Slide-out Cart Drawer -->
        <CartDrawer
            :is-open="isCartOpen"
            :cart="cart || null"
            :subtotal="subtotal || 0"
            @close="isCartOpen = false"
        />

        <!-- Floating Customer Support Chat & FAQ -->
        <CustomerSupportChat />
    </div>
</template>
