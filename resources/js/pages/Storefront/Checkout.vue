<script setup lang="ts">
import { Head, useForm } from '@inertiajs/vue3';
import { computed } from 'vue';

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

const props = defineProps<{
    cart: Cart;
    subtotal: number;
}>();

const form = useForm({
    customer_name: '',
    customer_phone: '',
    customer_address: '',
    fulfillment_type: 'delivery',
    expected_delivery_date: '',
    payment_option: 'COD',
    notes: '',
});

const deliveryFee = computed(() => {
    return form.fulfillment_type === 'delivery' ? 50.0 : 0.0;
});

const grandTotal = computed(() => {
    return Math.round((props.subtotal + deliveryFee.value) * 100) / 100;
});

// Format minimum date as tomorrow morning
const minDate = computed(() => {
    const tomorrow = new Date();
    tomorrow.setDate(tomorrow.getDate() + 1);
    tomorrow.setHours(8, 0, 0, 0);

    return tomorrow.toISOString().slice(0, 16);
});

function submitOrder() {
    form.post('/checkout');
}
</script>

<template>
    <div class="min-h-screen bg-neutral-50 pb-16 text-neutral-900">
        <Head title="Checkout - Evic Meat & Poultry Shop" />

        <header class="border-b border-neutral-200 bg-white py-4">
            <div
                class="mx-auto flex max-w-5xl items-center justify-between px-4 sm:px-6"
            >
                <a
                    href="/"
                    class="flex items-center gap-2 text-sm font-bold text-rose-600 hover:underline"
                >
                    ← Back to Meat Catalog
                </a>
                <span class="text-sm font-extrabold text-neutral-900"
                    >Secure Checkout</span
                >
            </div>
        </header>

        <main class="mx-auto mt-8 max-w-5xl px-4 sm:px-6">
            <div class="grid grid-cols-1 gap-8 lg:grid-cols-12">
                <!-- Checkout Form -->
                <div class="space-y-6 lg:col-span-7">
                    <form @submit.prevent="submitOrder" class="space-y-6">
                        <!-- Customer Details Card -->
                        <div
                            class="space-y-4 rounded-2xl border border-neutral-200 bg-white p-6 shadow-xs"
                        >
                            <h2 class="text-base font-bold text-neutral-900">
                                1. Customer Details
                            </h2>

                            <div>
                                <label
                                    class="block text-xs font-semibold text-neutral-700"
                                    >Full Name</label
                                >
                                <input
                                    v-model="form.customer_name"
                                    type="text"
                                    required
                                    placeholder="e.g. Maria Santos"
                                    class="mt-1 w-full rounded-xl border border-neutral-300 px-3 py-2 text-sm outline-hidden focus:border-rose-500"
                                />
                                <span
                                    v-if="form.errors.customer_name"
                                    class="text-xs text-red-600"
                                    >{{ form.errors.customer_name }}</span
                                >
                            </div>

                            <div>
                                <label
                                    class="block text-xs font-semibold text-neutral-700"
                                    >Mobile Phone</label
                                >
                                <input
                                    v-model="form.customer_phone"
                                    type="tel"
                                    required
                                    placeholder="e.g. 09171234567"
                                    class="mt-1 w-full rounded-xl border border-neutral-300 px-3 py-2 text-sm outline-hidden focus:border-rose-500"
                                />
                                <span
                                    v-if="form.errors.customer_phone"
                                    class="text-xs text-red-600"
                                    >{{ form.errors.customer_phone }}</span
                                >
                            </div>

                            <div>
                                <label
                                    class="block text-xs font-semibold text-neutral-700"
                                    >Delivery Address</label
                                >
                                <textarea
                                    v-model="form.customer_address"
                                    required
                                    rows="2"
                                    placeholder="House #, Street, Barangay, Landmark"
                                    class="mt-1 w-full rounded-xl border border-neutral-300 px-3 py-2 text-sm outline-hidden focus:border-rose-500"
                                ></textarea>
                                <span
                                    v-if="form.errors.customer_address"
                                    class="text-xs text-red-600"
                                    >{{ form.errors.customer_address }}</span
                                >
                            </div>
                        </div>

                        <!-- Fulfillment & Schedule Card -->
                        <div
                            class="space-y-4 rounded-2xl border border-neutral-200 bg-white p-6 shadow-xs"
                        >
                            <h2 class="text-base font-bold text-neutral-900">
                                2. Fulfillment & Fresh Arrival Schedule
                            </h2>

                            <div class="grid grid-cols-2 gap-3">
                                <label
                                    :class="[
                                        'flex cursor-pointer flex-col rounded-xl border p-3 text-center transition',
                                        form.fulfillment_type === 'delivery'
                                            ? 'border-rose-600 bg-rose-50/50 text-rose-900'
                                            : 'border-neutral-200 hover:bg-neutral-50',
                                    ]"
                                >
                                    <input
                                        type="radio"
                                        v-model="form.fulfillment_type"
                                        value="delivery"
                                        class="sr-only"
                                    />
                                    <span class="text-sm font-bold"
                                        >Cold Delivery</span
                                    >
                                    <span class="text-xs text-neutral-500"
                                        >+₱50.00 fee</span
                                    >
                                </label>

                                <label
                                    :class="[
                                        'flex cursor-pointer flex-col rounded-xl border p-3 text-center transition',
                                        form.fulfillment_type === 'pickup'
                                            ? 'border-rose-600 bg-rose-50/50 text-rose-900'
                                            : 'border-neutral-200 hover:bg-neutral-50',
                                    ]"
                                >
                                    <input
                                        type="radio"
                                        v-model="form.fulfillment_type"
                                        value="pickup"
                                        class="sr-only"
                                    />
                                    <span class="text-sm font-bold"
                                        >Store Pickup</span
                                    >
                                    <span
                                        class="text-xs font-medium text-emerald-600"
                                        >Free</span
                                    >
                                </label>
                            </div>

                            <div>
                                <label
                                    class="block text-xs font-semibold text-neutral-700"
                                    >Expected Delivery / Pickup Time</label
                                >
                                <input
                                    v-model="form.expected_delivery_date"
                                    type="datetime-local"
                                    :min="minDate"
                                    required
                                    class="mt-1 w-full rounded-xl border border-neutral-300 px-3 py-2 text-sm outline-hidden focus:border-rose-500"
                                />
                                <span
                                    v-if="form.errors.expected_delivery_date"
                                    class="text-xs text-red-600"
                                >
                                    {{ form.errors.expected_delivery_date }}
                                </span>
                            </div>

                            <div>
                                <label
                                    class="block text-xs font-semibold text-neutral-700"
                                    >Special Butchery or Delivery
                                    Instructions</label
                                >
                                <input
                                    v-model="form.notes"
                                    type="text"
                                    placeholder="e.g. Leave with security guard, please pack ice"
                                    class="mt-1 w-full rounded-xl border border-neutral-300 px-3 py-2 text-sm outline-hidden focus:border-rose-500"
                                />
                            </div>
                        </div>

                        <!-- Payment Option -->
                        <div
                            class="space-y-4 rounded-2xl border border-neutral-200 bg-white p-6 shadow-xs"
                        >
                            <h2 class="text-base font-bold text-neutral-900">
                                3. Payment Method
                            </h2>
                            <div class="space-y-2">
                                <label
                                    class="flex cursor-pointer items-center gap-3 rounded-xl border border-neutral-200 p-3 hover:bg-neutral-50"
                                >
                                    <input
                                        type="radio"
                                        v-model="form.payment_option"
                                        value="COD"
                                        class="text-rose-600"
                                    />
                                    <div>
                                        <p class="text-sm font-bold">
                                            Cash on Delivery (COD) / Pay upon
                                            Pickup
                                        </p>
                                        <p class="text-xs text-neutral-500">
                                            Settle directly with the delivery
                                            rider or cashier.
                                        </p>
                                    </div>
                                </label>
                                <label
                                    class="flex cursor-pointer items-center gap-3 rounded-xl border border-neutral-200 p-3 hover:bg-neutral-50"
                                >
                                    <input
                                        type="radio"
                                        v-model="form.payment_option"
                                        value="online"
                                        class="text-rose-600"
                                    />
                                    <div>
                                        <p class="text-sm font-bold">
                                            Online Payment / E-Wallet (GCash /
                                            Card)
                                        </p>
                                        <p class="text-xs text-neutral-500">
                                            Pay via digital transfer prior to
                                            cut dispatch.
                                        </p>
                                    </div>
                                </label>
                            </div>
                        </div>

                        <!-- Submit Button -->
                        <button
                            type="submit"
                            :disabled="form.processing"
                            class="w-full cursor-pointer rounded-2xl bg-rose-600 py-3.5 text-base font-bold text-white shadow-md transition hover:bg-rose-700 disabled:opacity-50"
                        >
                            {{
                                form.processing
                                    ? 'Placing Order...'
                                    : `Place Order (₱${grandTotal.toFixed(2)})`
                            }}
                        </button>
                    </form>
                </div>

                <!-- Order Summary Sidebar -->
                <div class="lg:col-span-5">
                    <div
                        class="sticky top-20 space-y-4 rounded-2xl border border-neutral-200 bg-white p-6 shadow-xs"
                    >
                        <h3 class="text-base font-bold text-neutral-900">
                            Order Summary
                        </h3>

                        <div
                            class="max-h-96 space-y-3 divide-y divide-neutral-100 overflow-y-auto pt-1"
                        >
                            <div
                                v-for="item in cart.items"
                                :key="item.id"
                                class="flex items-start justify-between pt-3 text-xs"
                            >
                                <div>
                                    <p class="font-bold text-neutral-900">
                                        {{ item.product.name }}
                                    </p>
                                    <p class="text-neutral-500">
                                        {{ Number(item.weight_kg).toFixed(2) }}
                                        kg @ ₱{{
                                            Number(
                                                item.product.price_per_kg,
                                            ).toFixed(2)
                                        }}/kg
                                    </p>
                                    <p
                                        v-if="item.cutting_notes"
                                        class="mt-0.5 text-rose-600 italic"
                                    >
                                        Cut: {{ item.cutting_notes }}
                                    </p>
                                </div>
                                <span class="font-bold text-neutral-900">
                                    ₱{{
                                        (
                                            Number(item.weight_kg) *
                                            Number(item.product.price_per_kg)
                                        ).toFixed(2)
                                    }}
                                </span>
                            </div>
                        </div>

                        <div
                            class="space-y-2 border-t border-neutral-200 pt-4 text-xs"
                        >
                            <div class="flex justify-between text-neutral-600">
                                <span>Meat Subtotal</span>
                                <span>₱{{ subtotal.toFixed(2) }}</span>
                            </div>
                            <div class="flex justify-between text-neutral-600">
                                <span>Delivery Fee</span>
                                <span>₱{{ deliveryFee.toFixed(2) }}</span>
                            </div>
                            <div
                                class="flex justify-between border-t border-neutral-200 pt-2 text-sm font-extrabold text-neutral-900"
                            >
                                <span>Total Estimated Amount</span>
                                <span class="text-rose-600"
                                    >₱{{ grandTotal.toFixed(2) }}</span
                                >
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </main>
    </div>
</template>
