<script setup lang="ts">
import { Head, useForm } from '@inertiajs/vue3';
import { computed } from 'vue';
import { ShieldCheck, Truck, Store, CreditCard, Banknote, Sparkles } from 'lucide-vue-next';

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
    customer_email: '',
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
    <div class="min-h-screen bg-stone-50 pb-20 text-stone-900">
        <Head title="Checkout - Evic Meat & Poultry Shop" />

        <!-- Navigation header -->
        <header class="border-b border-stone-200 bg-white py-4 shadow-xs">
            <div class="mx-auto flex max-w-5xl items-center justify-between px-4 sm:px-6">
                <a
                    href="/"
                    class="flex items-center gap-2 text-xs font-bold text-rose-700 hover:text-rose-800 transition"
                >
                    ← Back to Meat Catalog
                </a>
                <div class="flex items-center gap-2">
                    <span class="flex h-7 w-7 items-center justify-center rounded-lg bg-rose-900 text-xs font-bold text-white">
                        E
                    </span>
                    <span class="text-sm font-black tracking-tight text-stone-900 font-serif">
                        Evic Checkout
                    </span>
                </div>
            </div>
        </header>

        <main class="mx-auto mt-8 max-w-5xl px-4 sm:px-6">
            <!-- Trust banner -->
            <div class="mb-6 rounded-xl border border-rose-100 bg-rose-50/70 p-4 text-xs text-rose-900 flex items-center justify-between">
                <div class="flex items-center gap-2.5">
                    <ShieldCheck class="h-5 w-5 text-rose-600 shrink-0" />
                    <div>
                        <strong class="font-bold">Fast & Guest-Friendly Ordering:</strong>
                        <span class="text-rose-800 ml-1">No registration required. You can optionally save your account after placing your order.</span>
                    </div>
                </div>
            </div>

            <div class="grid grid-cols-1 gap-8 lg:grid-cols-12">
                <!-- Checkout Form -->
                <div class="space-y-6 lg:col-span-7">
                    <form @submit.prevent="submitOrder" class="space-y-6">
                        <!-- Customer Details Card -->
                        <div class="space-y-4 rounded-2xl border border-stone-200 bg-white p-6 shadow-xs">
                            <div class="flex items-center justify-between border-b border-stone-100 pb-3">
                                <h2 class="text-base font-bold text-stone-900">
                                    1. Recipient Information
                                </h2>
                                <span class="text-[11px] font-semibold text-emerald-700 bg-emerald-50 px-2 py-0.5 rounded-full border border-emerald-100">
                                    Guest Checkout Active
                                </span>
                            </div>

                            <!-- Full Name -->
                            <div>
                                <label class="block text-xs font-semibold text-stone-700">
                                    Full Name <span class="text-rose-600">*</span>
                                </label>
                                <input
                                    v-model="form.customer_name"
                                    type="text"
                                    required
                                    placeholder="e.g. Maria Santos"
                                    class="mt-1 w-full rounded-xl border border-stone-300 px-3.5 py-2 text-sm text-stone-900 outline-hidden transition focus:border-rose-600 focus:ring-1 focus:ring-rose-600"
                                />
                                <span v-if="form.errors.customer_name" class="mt-1 block text-xs text-rose-600">
                                    {{ form.errors.customer_name }}
                                </span>
                            </div>

                            <!-- Email Address (Required for guest tracking & post-order claim) -->
                            <div>
                                <div class="flex items-center justify-between">
                                    <label class="block text-xs font-semibold text-stone-700">
                                        Email Address <span class="text-rose-600">*</span>
                                    </label>
                                    <span class="text-[11px] text-stone-400">For receipts & butcher tracking</span>
                                </div>
                                <input
                                    v-model="form.customer_email"
                                    type="email"
                                    required
                                    placeholder="maria.santos@gmail.com"
                                    class="mt-1 w-full rounded-xl border border-stone-300 px-3.5 py-2 text-sm text-stone-900 outline-hidden transition focus:border-rose-600 focus:ring-1 focus:ring-rose-600"
                                />
                                <p class="mt-1 text-[11px] text-stone-500">
                                    We'll email your cold-chain dispatch notice here. If you have an existing Evic account, this order links automatically.
                                </p>
                                <span v-if="form.errors.customer_email" class="mt-1 block text-xs text-rose-600">
                                    {{ form.errors.customer_email }}
                                </span>
                            </div>

                            <!-- Mobile Phone -->
                            <div>
                                <label class="block text-xs font-semibold text-stone-700">
                                    Mobile Phone <span class="text-rose-600">*</span>
                                </label>
                                <input
                                    v-model="form.customer_phone"
                                    type="tel"
                                    required
                                    placeholder="0917 123 4567"
                                    class="mt-1 w-full rounded-xl border border-stone-300 px-3.5 py-2 text-sm text-stone-900 outline-hidden transition focus:border-rose-600 focus:ring-1 focus:ring-rose-600"
                                />
                                <span v-if="form.errors.customer_phone" class="mt-1 block text-xs text-rose-600">
                                    {{ form.errors.customer_phone }}
                                </span>
                            </div>

                            <!-- Delivery Address -->
                            <div>
                                <label class="block text-xs font-semibold text-stone-700">
                                    Delivery Address <span class="text-rose-600">*</span>
                                </label>
                                <textarea
                                    v-model="form.customer_address"
                                    required
                                    rows="2"
                                    placeholder="House/Unit #, Street, Barangay, City, Landmark"
                                    class="mt-1 w-full rounded-xl border border-stone-300 px-3.5 py-2 text-sm text-stone-900 outline-hidden transition focus:border-rose-600 focus:ring-1 focus:ring-rose-600"
                                ></textarea>
                                <span v-if="form.errors.customer_address" class="mt-1 block text-xs text-rose-600">
                                    {{ form.errors.customer_address }}
                                </span>
                            </div>
                        </div>

                        <!-- Fulfillment & Schedule Card -->
                        <div class="space-y-4 rounded-2xl border border-stone-200 bg-white p-6 shadow-xs">
                            <h2 class="text-base font-bold text-stone-900">
                                2. Fulfillment & Schedule
                            </h2>

                            <div class="grid grid-cols-2 gap-3">
                                <label
                                    :class="[
                                        'flex cursor-pointer flex-col rounded-xl border p-3.5 text-center transition',
                                        form.fulfillment_type === 'delivery'
                                            ? 'border-rose-700 bg-rose-50/70 text-rose-950 font-bold ring-1 ring-rose-600'
                                            : 'border-stone-200 text-stone-700 hover:bg-stone-50',
                                    ]"
                                >
                                    <input
                                        type="radio"
                                        v-model="form.fulfillment_type"
                                        value="delivery"
                                        class="sr-only"
                                    />
                                    <div class="flex items-center justify-center gap-1.5">
                                        <Truck class="h-4 w-4 text-rose-600" />
                                        <span class="text-sm">Insulated Delivery</span>
                                    </div>
                                    <span class="text-xs text-stone-500 mt-1">+₱50.00 chilled courier</span>
                                </label>

                                <label
                                    :class="[
                                        'flex cursor-pointer flex-col rounded-xl border p-3.5 text-center transition',
                                        form.fulfillment_type === 'pickup'
                                            ? 'border-rose-700 bg-rose-50/70 text-rose-950 font-bold ring-1 ring-rose-600'
                                            : 'border-stone-200 text-stone-700 hover:bg-stone-50',
                                    ]"
                                >
                                    <input
                                        type="radio"
                                        v-model="form.fulfillment_type"
                                        value="pickup"
                                        class="sr-only"
                                    />
                                    <div class="flex items-center justify-center gap-1.5">
                                        <Store class="h-4 w-4 text-rose-600" />
                                        <span class="text-sm">Store Pickup</span>
                                    </div>
                                    <span class="text-xs font-semibold text-emerald-700 mt-1">Free of charge</span>
                                </label>
                            </div>

                            <div>
                                <label class="block text-xs font-semibold text-stone-700">
                                    Target Arrival Date & Time <span class="text-rose-600">*</span>
                                </label>
                                <input
                                    v-model="form.expected_delivery_date"
                                    type="datetime-local"
                                    :min="minDate"
                                    required
                                    class="mt-1 w-full rounded-xl border border-stone-300 px-3.5 py-2 text-sm text-stone-900 outline-hidden transition focus:border-rose-600 focus:ring-1 focus:ring-rose-600"
                                />
                                <span v-if="form.errors.expected_delivery_date" class="mt-1 block text-xs text-rose-600">
                                    {{ form.errors.expected_delivery_date }}
                                </span>
                            </div>

                            <div>
                                <label class="block text-xs font-semibold text-stone-700">
                                    Butcher or Delivery Handling Instructions
                                </label>
                                <input
                                    v-model="form.notes"
                                    type="text"
                                    placeholder="e.g. Leave with condo front desk, pack with extra ice gel..."
                                    class="mt-1 w-full rounded-xl border border-stone-300 px-3.5 py-2 text-sm text-stone-900 outline-hidden transition focus:border-rose-600 focus:ring-1 focus:ring-rose-600"
                                />
                            </div>
                        </div>

                        <!-- Payment Option -->
                        <div class="space-y-4 rounded-2xl border border-stone-200 bg-white p-6 shadow-xs">
                            <h2 class="text-base font-bold text-stone-900">
                                3. Payment Method
                            </h2>
                            <div class="space-y-2.5">
                                <label
                                    :class="[
                                        'flex cursor-pointer items-start gap-3 rounded-xl border p-3.5 transition',
                                        form.payment_option === 'COD'
                                            ? 'border-rose-600 bg-rose-50/40 ring-1 ring-rose-500'
                                            : 'border-stone-200 hover:bg-stone-50',
                                    ]"
                                >
                                    <input
                                        type="radio"
                                        v-model="form.payment_option"
                                        value="COD"
                                        class="mt-0.5 text-rose-600"
                                    />
                                    <div>
                                        <div class="flex items-center gap-1.5">
                                            <Banknote class="h-4 w-4 text-emerald-600" />
                                            <p class="text-sm font-bold text-stone-900">
                                                Cash on Delivery (COD) / Pay upon Pickup
                                            </p>
                                        </div>
                                        <p class="text-xs text-stone-500 mt-0.5">
                                            Hand exact payment to the delivery rider or counter cashier upon receiving your cuts.
                                        </p>
                                    </div>
                                </label>

                                <label
                                    :class="[
                                        'flex cursor-pointer items-start gap-3 rounded-xl border p-3.5 transition',
                                        form.payment_option === 'online'
                                            ? 'border-rose-600 bg-rose-50/40 ring-1 ring-rose-500'
                                            : 'border-stone-200 hover:bg-stone-50',
                                    ]"
                                >
                                    <input
                                        type="radio"
                                        v-model="form.payment_option"
                                        value="online"
                                        class="mt-0.5 text-rose-600"
                                    />
                                    <div>
                                        <div class="flex items-center gap-1.5">
                                            <CreditCard class="h-4 w-4 text-indigo-600" />
                                            <p class="text-sm font-bold text-stone-900">
                                                Online GCash / Maya / Bank Transfer
                                            </p>
                                        </div>
                                        <p class="text-xs text-stone-500 mt-0.5">
                                            Digital QR code and account details provided on order confirmation screen.
                                        </p>
                                    </div>
                                </label>
                            </div>
                        </div>

                        <!-- Submit Button -->
                        <button
                            type="submit"
                            :disabled="form.processing"
                            class="w-full cursor-pointer rounded-2xl bg-rose-700 py-3.5 text-base font-bold text-white shadow-md transition hover:bg-rose-800 disabled:opacity-50 active:scale-[0.99]"
                        >
                            {{
                                form.processing
                                    ? 'Submitting to Butcher Counter...'
                                    : `Place Order (₱${grandTotal.toFixed(2)})`
                            }}
                        </button>
                    </form>
                </div>

                <!-- Order Summary Sidebar -->
                <div class="lg:col-span-5">
                    <div class="sticky top-20 space-y-4 rounded-2xl border border-stone-200 bg-white p-6 shadow-xs">
                        <div class="flex items-center justify-between border-b border-stone-100 pb-3">
                            <h3 class="text-base font-bold text-stone-900">
                                Fresh Butcher Basket
                            </h3>
                            <span class="text-xs font-semibold text-stone-500">
                                {{ cart.items.length }} {{ cart.items.length === 1 ? 'item' : 'items' }}
                            </span>
                        </div>

                        <div class="max-h-96 space-y-3 divide-y divide-stone-100 overflow-y-auto pt-1">
                            <div
                                v-for="item in cart.items"
                                :key="item.id"
                                class="flex items-start justify-between pt-3 text-xs"
                            >
                                <div class="pr-2">
                                    <p class="font-bold text-stone-900">
                                        {{ item.product.name }}
                                    </p>
                                    <p class="text-stone-500">
                                        {{ Number(item.weight_kg).toFixed(2) }} kg @ ₱{{
                                            Number(item.product.price_per_kg).toFixed(2)
                                        }}/kg
                                    </p>
                                    <p
                                        v-if="item.cutting_notes"
                                        class="mt-1 inline-block rounded bg-rose-50 px-2 py-0.5 text-[11px] font-medium text-rose-800"
                                    >
                                        Prep: {{ item.cutting_notes }}
                                    </p>
                                </div>
                                <span class="font-bold text-stone-900">
                                    ₱{{
                                        (
                                            Number(item.weight_kg) *
                                            Number(item.product.price_per_kg)
                                        ).toFixed(2)
                                    }}
                                </span>
                            </div>
                        </div>

                        <div class="space-y-2 border-t border-stone-200 pt-4 text-xs">
                            <div class="flex justify-between text-stone-600">
                                <span>Meat Subtotal</span>
                                <span class="font-semibold text-stone-900">₱{{ subtotal.toFixed(2) }}</span>
                            </div>
                            <div class="flex justify-between text-stone-600">
                                <span>Packaging & Cold-Chain Courier</span>
                                <span class="font-semibold text-stone-900">₱{{ deliveryFee.toFixed(2) }}</span>
                            </div>
                            <div class="flex justify-between border-t border-stone-200 pt-3 text-base font-black text-stone-900">
                                <span>Total Payable</span>
                                <span class="text-rose-700">₱{{ grandTotal.toFixed(2) }}</span>
                            </div>
                        </div>

                        <div class="rounded-xl bg-stone-50 p-3 text-[11px] text-stone-500 flex items-center gap-2 border border-stone-200/60">
                            <Sparkles class="h-4 w-4 text-rose-600 shrink-0" />
                            <span>100% guarantee: if your cuts arrive compromised or incorrect, we replace them immediately.</span>
                        </div>
                    </div>
                </div>
            </div>
        </main>
    </div>
</template>
