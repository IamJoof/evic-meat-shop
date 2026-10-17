<script setup lang="ts">
import { router } from '@inertiajs/vue3';

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
            class="fixed inset-0 bg-neutral-900/50 backdrop-blur-xs transition-opacity"
        />

        <section class="absolute inset-y-0 right-0 flex max-w-full pl-10">
            <div
                class="flex w-screen max-w-md flex-col justify-between bg-white shadow-2xl"
            >
                <!-- Header -->
                <div
                    class="flex items-center justify-between border-b border-neutral-200 px-6 py-4"
                >
                    <h2 class="text-lg font-bold text-neutral-900">
                        Your Fresh Meat Order
                    </h2>
                    <button
                        type="button"
                        @click="emit('close')"
                        class="cursor-pointer text-xl font-bold text-neutral-400 hover:text-neutral-600"
                        aria-label="Close cart"
                    >
                        ✕
                    </button>
                </div>

                <!-- Items list -->
                <div class="flex-1 space-y-4 overflow-y-auto px-6 py-4">
                    <div
                        v-if="!cart || cart.items.length === 0"
                        class="flex flex-col items-center justify-center py-12 text-center text-neutral-500"
                    >
                        <p class="text-sm">Your order is empty.</p>
                        <p class="mt-1 text-xs text-neutral-400">
                            Select meat cuts from the catalog to get started.
                        </p>
                    </div>

                    <div
                        v-else
                        v-for="item in cart.items"
                        :key="item.id"
                        class="flex items-start justify-between border-b border-neutral-100 pb-4"
                    >
                        <div>
                            <h4 class="text-sm font-bold text-neutral-800">
                                {{ item.product.name }}
                            </h4>
                            <p class="text-xs text-neutral-500">
                                ₱{{
                                    Number(item.product.price_per_kg).toFixed(2)
                                }}
                                / kg
                            </p>
                            <p
                                v-if="item.cutting_notes"
                                class="mt-1 rounded bg-rose-50 px-2 py-0.5 text-xs font-medium text-rose-600"
                            >
                                Cut: {{ item.cutting_notes }}
                            </p>

                            <!-- Weight Adjustment -->
                            <div class="mt-2 flex items-center gap-2">
                                <button
                                    type="button"
                                    @click="
                                        updateWeight(
                                            item.id,
                                            Number(item.weight_kg),
                                            -0.5,
                                        )
                                    "
                                    class="h-6 w-6 rounded border border-neutral-300 text-xs font-bold hover:bg-neutral-100"
                                >
                                    -
                                </button>
                                <span class="text-xs font-semibold"
                                    >{{
                                        Number(item.weight_kg).toFixed(2)
                                    }}
                                    kg</span
                                >
                                <button
                                    type="button"
                                    @click="
                                        updateWeight(
                                            item.id,
                                            Number(item.weight_kg),
                                            0.5,
                                        )
                                    "
                                    class="h-6 w-6 rounded border border-neutral-300 text-xs font-bold hover:bg-neutral-100"
                                >
                                    +
                                </button>
                            </div>
                        </div>

                        <div class="text-right">
                            <p class="text-sm font-bold text-neutral-900">
                                ₱{{
                                    (
                                        Number(item.weight_kg) *
                                        Number(item.product.price_per_kg)
                                    ).toFixed(2)
                                }}
                            </p>
                            <button
                                type="button"
                                @click="removeItem(item.id)"
                                class="mt-2 cursor-pointer text-xs text-red-500 underline hover:text-red-700"
                            >
                                Remove
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Footer Summary -->
                <div
                    class="space-y-3 border-t border-neutral-200 bg-neutral-50 px-6 py-4"
                >
                    <div
                        class="flex justify-between text-base font-bold text-neutral-900"
                    >
                        <span>Estimated Subtotal</span>
                        <span>₱{{ subtotal.toFixed(2) }}</span>
                    </div>
                    <p class="text-xs text-neutral-500">
                        Final weight may vary slightly depending on exact cut.
                    </p>
                    <button
                        type="button"
                        @click="proceedToCheckout"
                        :disabled="!cart || cart.items.length === 0"
                        class="w-full rounded-xl bg-rose-600 py-3 text-sm font-bold text-white shadow-xs transition hover:bg-rose-700 disabled:cursor-not-allowed disabled:opacity-50"
                    >
                        Proceed to Checkout
                    </button>
                </div>
            </div>
        </section>
    </div>
</template>
