<script setup lang="ts">
import { Head, router } from '@inertiajs/vue3';
import { computed } from 'vue';

interface OrderItem {
    id: number;
    product_name: string;
    price_per_kg: string | number;
    weight_kg: string | number;
    line_total: string | number;
    cutting_notes?: string | null;
}

interface Payment {
    id: number;
    amount: string | number;
    option: string;
    status: string;
}

interface Order {
    id: number;
    order_number: string;
    customer_name: string;
    customer_phone: string;
    customer_address: string;
    fulfillment_type: string;
    expected_delivery_date: string;
    actual_delivery_date?: string | null;
    subtotal: string | number;
    delivery_fee: string | number;
    total_amount: string | number;
    status: string;
    notes?: string | null;
    items: OrderItem[];
    payment?: Payment | null;
}

const props = defineProps<{
    order: Order;
}>();

const steps = [
    { key: 'pending', label: 'Order Placed' },
    { key: 'preparing', label: 'Butcher Cutting' },
    { key: 'shipped', label: 'Out for Delivery / Pickup' },
    { key: 'received', label: 'Delivered' },
];

const currentStepIndex = computed(() => {
    switch (props.order.status) {
        case 'pending':
            return 0;
        case 'preparing':
            return 1;
        case 'shipped':
            return 2;
        case 'received':
            return 3;
        default:
            return -1;
    }
});

function cancelOrder() {
    if (confirm('Are you sure you want to cancel this order?')) {
        router.post(`/orders/${props.order.order_number}/cancel`);
    }
}
</script>

<template>
    <div class="min-h-screen bg-neutral-50 pb-16 text-neutral-900">
        <Head
            :title="`Order #${order.order_number} Tracking - Evic Meat Shop`"
        />

        <header class="border-b border-neutral-200 bg-white py-4">
            <div
                class="mx-auto flex max-w-4xl items-center justify-between px-4 sm:px-6"
            >
                <a
                    href="/"
                    class="text-sm font-bold text-rose-600 hover:underline"
                >
                    ← Return to Meat Shop
                </a>
                <span class="text-xs font-semibold text-neutral-500"
                    >Order Reference #{{ order.order_number }}</span
                >
            </div>
        </header>

        <main class="mx-auto mt-8 max-w-4xl space-y-6 px-4 sm:px-6">
            <!-- Hero Status Card -->
            <div
                class="space-y-4 rounded-2xl border border-neutral-200 bg-white p-6 text-center shadow-xs sm:p-8"
            >
                <span
                    class="inline-flex h-14 w-14 items-center justify-center rounded-full bg-emerald-100 text-2xl font-bold text-emerald-600"
                >
                    ✓
                </span>
                <div>
                    <h1 class="text-2xl font-extrabold text-neutral-900">
                        Order Confirmed!
                    </h1>
                    <p class="mt-1 text-sm text-neutral-600">
                        Thank you,
                        <strong class="text-neutral-900">{{
                            order.customer_name
                        }}</strong
                        >! We've received your meat order.
                    </p>
                </div>

                <!-- Fulfillment Tracker Timeline -->
                <div v-if="order.status !== 'cancelled'" class="pt-6">
                    <div class="grid grid-cols-4 gap-2 text-center">
                        <div
                            v-for="(step, idx) in steps"
                            :key="step.key"
                            class="flex flex-col items-center"
                        >
                            <div
                                :class="[
                                    'flex h-8 w-8 items-center justify-center rounded-full text-xs font-bold transition',
                                    idx <= currentStepIndex
                                        ? 'bg-rose-600 text-white'
                                        : 'bg-neutral-200 text-neutral-500',
                                ]"
                            >
                                {{ idx + 1 }}
                            </div>
                            <span
                                class="mt-2 text-[11px] leading-tight font-semibold text-neutral-700"
                            >
                                {{ step.label }}
                            </span>
                        </div>
                    </div>
                </div>

                <div
                    v-else
                    class="rounded-xl bg-red-50 p-4 text-sm font-semibold text-red-700"
                >
                    This order has been cancelled.
                </div>
            </div>

            <!-- Order Details Card -->
            <div
                class="space-y-4 rounded-2xl border border-neutral-200 bg-white p-6 shadow-xs"
            >
                <div
                    class="flex items-center justify-between border-b border-neutral-100 pb-3"
                >
                    <h2 class="text-base font-bold text-neutral-900">
                        Items to be Butchered
                    </h2>
                    <span class="text-xs text-neutral-500 capitalize">{{
                        order.fulfillment_type
                    }}</span>
                </div>

                <div class="divide-y divide-neutral-100">
                    <div
                        v-for="item in order.items"
                        :key="item.id"
                        class="flex items-start justify-between py-3 text-xs"
                    >
                        <div>
                            <p class="text-sm font-bold text-neutral-900">
                                {{ item.product_name }}
                            </p>
                            <p class="font-medium text-neutral-500">
                                {{ Number(item.weight_kg).toFixed(2) }} kg @ ₱{{
                                    Number(item.price_per_kg).toFixed(2)
                                }}/kg
                            </p>
                            <p
                                v-if="item.cutting_notes"
                                class="mt-1 inline-block rounded bg-rose-50 px-2 py-0.5 font-medium text-rose-700"
                            >
                                Preparation: {{ item.cutting_notes }}
                            </p>
                        </div>
                        <span class="text-sm font-bold text-neutral-900">
                            ₱{{ Number(item.line_total).toFixed(2) }}
                        </span>
                    </div>
                </div>

                <!-- Financial Breakdown -->
                <div class="space-y-2 border-t border-neutral-200 pt-4 text-xs">
                    <div class="flex justify-between text-neutral-600">
                        <span>Subtotal</span>
                        <span>₱{{ Number(order.subtotal).toFixed(2) }}</span>
                    </div>
                    <div class="flex justify-between text-neutral-600">
                        <span>Delivery Fee</span>
                        <span
                            >₱{{ Number(order.delivery_fee).toFixed(2) }}</span
                        >
                    </div>
                    <div
                        class="flex justify-between border-t border-neutral-200 pt-2 text-base font-extrabold text-neutral-900"
                    >
                        <span
                            >Total ({{ order.payment?.option || 'COD' }})</span
                        >
                        <span class="text-rose-600"
                            >₱{{ Number(order.total_amount).toFixed(2) }}</span
                        >
                    </div>
                </div>

                <!-- Action Button -->
                <div v-if="order.status === 'pending'" class="pt-2 text-right">
                    <button
                        type="button"
                        @click="cancelOrder"
                        class="cursor-pointer rounded-lg border border-red-200 px-3 py-1.5 text-xs font-semibold text-red-600 hover:bg-red-50"
                    >
                        Cancel Order
                    </button>
                </div>
            </div>
        </main>
    </div>
</template>
