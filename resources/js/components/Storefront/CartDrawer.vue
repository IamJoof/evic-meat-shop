<script setup lang="ts">
import { router } from '@inertiajs/vue3';
import { ShoppingBag, X, Trash2, ArrowRight } from 'lucide-vue-next';

interface CartItem {
    id: number;
    weight_kg: string | number;
    cutting_notes?: string | null;
    product: {
        id: number;
        name: string;
        price_per_kg: string | number;
    };
}

interface Cart {
    id: number;
    items: CartItem[];
}

defineProps<{
    isOpen: boolean;
    cart: Cart | null;
    subtotal: number;
}>();

const emit = defineEmits<{
    (e: 'close'): void;
}>();

function removeItem(itemId: number) {
    router.delete(`/cart/items/${itemId}`, {
        preserveScroll: true,
    });
}

function updateWeight(itemId: number, currentWeight: number, delta: number) {
    const newWeight = Math.max(
        0.25,
        Math.round((currentWeight + delta) * 100) / 100,
    );
    router.patch(
        `/cart/items/${itemId}`,
        { weight_kg: newWeight },
        { preserveScroll: true },
    );
}

function proceedToCheckout() {
    emit('close');
    router.visit('/checkout');
}
</script>

<template>
    <div v-if="isOpen" class="fixed inset-0 z-50 overflow-hidden">
        <!-- Backdrop -->
        <div
            @click="emit('close')"
            class="fixed inset-0 bg-stone-900/60 backdrop-blur-xs transition-opacity"
        />

        <section class="absolute inset-y-0 right-0 flex max-w-full pl-6 sm:pl-10">
            <div
                class="flex w-screen max-w-md flex-col justify-between bg-white shadow-2xl"
            >
                <!-- Header -->
                <div
                    class="flex items-center justify-between border-b border-stone-200 px-6 py-4 bg-stone-50/70"
                >
                    <div class="flex items-center gap-2.5">
                        <span class="flex h-8 w-8 items-center justify-center rounded-lg bg-rose-900 text-white">
                            <ShoppingBag class="h-4 w-4" />
                        </span>
                        <div>
                            <h2 class="text-base font-black tracking-tight text-stone-900 font-serif">
                                Your Butcher Cart
                            </h2>
                            <p class="text-xs text-stone-500">
                                {{ cart?.items?.length || 0 }} {{ (cart?.items?.length === 1) ? 'cut' : 'cuts' }} selected
                            </p>
                        </div>
                    </div>

                    <button
                        type="button"
                        @click="emit('close')"
                        class="cursor-pointer rounded-lg p-1.5 text-stone-400 hover:bg-stone-200/60 hover:text-stone-700 transition"
                        aria-label="Close cart"
                    >
                        <X class="h-5 w-5" />
                    </button>
                </div>

                <!-- Items list -->
                <div class="flex-1 space-y-4 overflow-y-auto px-6 py-4">
                    <div
                        v-if="!cart || cart.items.length === 0"
                        class="flex flex-col items-center justify-center py-16 text-center text-stone-500 space-y-3"
                    >
                        <div class="flex h-14 w-14 items-center justify-center rounded-2xl bg-stone-100 text-stone-400">
                            <ShoppingBag class="h-7 w-7" />
                        </div>
                        <div>
                            <p class="text-sm font-bold text-stone-700">Your basket is empty</p>
                            <p class="mt-1 text-xs text-stone-400 max-w-xs">
                                Select fresh meat cuts and custom butcher preparations from the counter to begin.
                            </p>
                        </div>
                    </div>

                    <div
                        v-else
                        v-for="item in cart.items"
                        :key="item.id"
                        class="flex items-start justify-between border-b border-stone-100 pb-4 pt-1"
                    >
                        <div class="space-y-1 pr-2">
                            <h4 class="text-sm font-bold text-stone-900">
                                {{ item.product?.name || 'Fresh Meat Cut' }}
                            </h4>
                            <p class="text-xs text-stone-500">
                                ₱{{ Number(item.product?.price_per_kg || 0).toFixed(2) }} / kg
                            </p>
                            <p
                                v-if="item.cutting_notes"
                                class="inline-block rounded-md bg-rose-50 px-2 py-0.5 text-[11px] font-medium text-rose-800 border border-rose-100"
                            >
                                Prep: {{ item.cutting_notes }}
                            </p>

                            <!-- Weight Adjustment Controls -->
                            <div class="mt-2 flex items-center gap-2 pt-1">
                                <div class="flex items-center rounded-lg border border-stone-200 bg-stone-50 p-0.5">
                                    <button
                                        type="button"
                                        @click="
                                            updateWeight(
                                                item.id,
                                                Number(item.weight_kg),
                                                -0.5,
                                            )
                                        "
                                        :disabled="Number(item.weight_kg) <= 0.25"
                                        class="h-6 w-6 cursor-pointer rounded bg-white text-xs font-bold text-stone-700 shadow-2xs hover:bg-stone-100 disabled:opacity-40"
                                    >
                                        -
                                    </button>
                                    <span class="px-2.5 text-xs font-bold text-stone-800">
                                        {{ Number(item.weight_kg).toFixed(2) }} kg
                                    </span>
                                    <button
                                        type="button"
                                        @click="
                                            updateWeight(
                                                item.id,
                                                Number(item.weight_kg),
                                                0.5,
                                            )
                                        "
                                        class="h-6 w-6 cursor-pointer rounded bg-white text-xs font-bold text-stone-700 shadow-2xs hover:bg-stone-100"
                                    >
                                        +
                                    </button>
                                </div>

                                <button
                                    type="button"
                                    @click="removeItem(item.id)"
                                    class="cursor-pointer text-stone-400 hover:text-rose-600 transition p-1"
                                    title="Remove item"
                                >
                                    <Trash2 class="h-4 w-4" />
                                </button>
                            </div>
                        </div>

                        <div class="text-right">
                            <p class="text-sm font-black text-stone-900">
                                ₱{{
                                    (
                                        Number(item.weight_kg) *
                                        Number(item.product?.price_per_kg || 0)
                                    ).toFixed(2)
                                }}
                            </p>
                        </div>
                    </div>
                </div>

                <!-- Footer Summary -->
                <div
                    class="space-y-3.5 border-t border-stone-200 bg-stone-50 px-6 py-5"
                >
                    <div class="space-y-1">
                        <div
                            class="flex justify-between text-base font-black text-stone-900"
                        >
                            <span>Estimated Subtotal</span>
                            <span class="text-rose-700">₱{{ Number(subtotal).toFixed(2) }}</span>
                        </div>
                        <p class="text-[11px] text-stone-500">
                            Packed with frozen gel pads. Exact weight certified at butcher station.
                        </p>
                    </div>

                    <button
                        type="button"
                        @click="proceedToCheckout"
                        :disabled="!cart || cart.items.length === 0"
                        class="flex w-full cursor-pointer items-center justify-center gap-2 rounded-xl bg-rose-700 py-3.5 text-sm font-bold text-white shadow-md transition hover:bg-rose-800 disabled:cursor-not-allowed disabled:bg-stone-200 disabled:text-stone-400 active:scale-[0.99]"
                    >
                        <span>Proceed to Checkout</span>
                        <ArrowRight class="h-4 w-4" />
                    </button>
                </div>
            </div>
        </section>
    </div>
</template>
