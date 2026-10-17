<script setup lang="ts">
import { Head, router } from '@inertiajs/vue3';

interface OrderItem {
    id: number;
    product_name: string;
    weight_kg: string | number;
    cutting_notes?: string | null;
}

interface Order {
    id: number;
    order_number: string;
    customer_name: string;
    customer_phone: string;
    fulfillment_type: string;
    expected_delivery_date: string;
    total_amount: string | number;
    status: string;
    items: OrderItem[];
    payment?: {
        option: string;
        status: string;
    } | null;
}

interface PaginatedOrders {
    data: Order[];
    current_page: number;
    last_page: number;
    total: number;
}

defineProps<{
    orders: PaginatedOrders;
    currentStatus?: string | null;
}>();

const statuses = [
    { key: null, label: 'All Orders' },
    { key: 'pending', label: 'Pending' },
    { key: 'preparing', label: 'Preparing / Cut' },
    { key: 'shipped', label: 'Out / Ready' },
    { key: 'received', label: 'Completed' },
    { key: 'cancelled', label: 'Cancelled' },
];

function filterStatus(status: string | null) {
    router.get(
        '/admin/orders',
        { status: status || undefined },
        { preserveState: true },
    );
}

function updateOrderStatus(orderId: number, status: string) {
    router.patch(
        `/admin/orders/${orderId}/status`,
        { status },
        { preserveScroll: true },
    );
}
</script>

<template>
    <div class="min-h-screen bg-neutral-100 pb-16 text-neutral-900">
        <Head title="Order Fulfillment Board - Admin" />

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
                        Meat Fulfillment Board
                    </h1>
                </div>
                <div class="flex items-center gap-3">
                    <a
                        href="/admin/products"
                        class="text-xs font-bold text-neutral-600 hover:text-rose-600"
                    >
                        Product Catalog
                    </a>
                    <a
                        href="/"
                        class="text-xs font-bold text-rose-600 hover:underline"
                    >
                        View Storefront →
                    </a>
                </div>
            </div>
        </header>

        <main class="mx-auto mt-6 max-w-7xl space-y-6 px-4 sm:px-6 lg:px-8">
            <!-- Status Tabs -->
            <div
                class="flex gap-2 overflow-x-auto border-b border-neutral-200 pb-2"
            >
                <button
                    v-for="st in statuses"
                    :key="st.label"
                    type="button"
                    @click="filterStatus(st.key)"
                    :class="[
                        'cursor-pointer rounded-xl px-4 py-2 text-xs font-bold whitespace-nowrap transition',
                        currentStatus === st.key ||
                        (!currentStatus && st.key === null)
                            ? 'bg-neutral-900 text-white shadow-xs'
                            : 'bg-white text-neutral-600 hover:bg-neutral-200',
                    ]"
                >
                    {{ st.label }}
                </button>
            </div>

            <!-- Orders Table -->
            <div
                class="overflow-hidden rounded-2xl border border-neutral-200 bg-white shadow-xs"
            >
                <table class="w-full text-left text-xs">
                    <thead
                        class="border-b border-neutral-200 bg-neutral-50 font-semibold text-neutral-500 uppercase"
                    >
                        <tr>
                            <th class="px-5 py-3.5">Order Ref</th>
                            <th class="px-5 py-3.5">Customer</th>
                            <th class="px-5 py-3.5">Fulfillment & Slot</th>
                            <th class="px-5 py-3.5">Cuts</th>
                            <th class="px-5 py-3.5">Amount</th>
                            <th class="px-5 py-3.5">Status</th>
                            <th class="px-5 py-3.5 text-right">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-neutral-100">
                        <tr v-if="orders.data.length === 0">
                            <td
                                colspan="7"
                                class="py-12 text-center text-neutral-500"
                            >
                                No orders found for this status.
                            </td>
                        </tr>
                        <tr
                            v-for="order in orders.data"
                            :key="order.id"
                            class="transition hover:bg-neutral-50"
                        >
                            <td
                                class="px-5 py-4 font-mono font-bold text-neutral-900"
                            >
                                <a
                                    :href="`/admin/orders/${order.id}`"
                                    class="text-rose-600 hover:underline"
                                >
                                    #{{ order.order_number }}
                                </a>
                            </td>
                            <td class="px-5 py-4">
                                <p class="font-bold text-neutral-900">
                                    {{ order.customer_name }}
                                </p>
                                <p class="text-[11px] text-neutral-500">
                                    {{ order.customer_phone }}
                                </p>
                            </td>
                            <td class="px-5 py-4">
                                <span
                                    class="font-semibold text-neutral-800 capitalize"
                                    >{{ order.fulfillment_type }}</span
                                >
                                <p class="text-[11px] text-neutral-500">
                                    {{ order.expected_delivery_date }}
                                </p>
                            </td>
                            <td class="px-5 py-4">
                                <span
                                    class="rounded bg-neutral-100 px-2 py-0.5 font-bold text-neutral-700"
                                >
                                    {{ order.items.length }} items
                                </span>
                            </td>
                            <td class="px-5 py-4 font-bold text-neutral-900">
                                ₱{{ Number(order.total_amount).toFixed(2) }}
                                <span
                                    class="block text-[10px] font-normal text-neutral-500"
                                >
                                    {{ order.payment?.option }} •
                                    {{ order.payment?.status }}
                                </span>
                            </td>
                            <td class="px-5 py-4">
                                <span
                                    :class="[
                                        'rounded-full px-2.5 py-1 text-[11px] font-bold capitalize',
                                        order.status === 'pending'
                                            ? 'bg-amber-100 text-amber-800'
                                            : order.status === 'preparing'
                                              ? 'bg-blue-100 text-blue-800'
                                              : order.status === 'shipped'
                                                ? 'bg-purple-100 text-purple-800'
                                                : order.status === 'received'
                                                  ? 'bg-emerald-100 text-emerald-800'
                                                  : 'bg-red-100 text-red-800',
                                    ]"
                                >
                                    {{ order.status }}
                                </span>
                            </td>
                            <td class="space-x-1.5 px-5 py-4 text-right">
                                <a
                                    :href="`/admin/orders/${order.id}/slip`"
                                    class="inline-block rounded-lg border border-neutral-300 px-2.5 py-1 text-[11px] font-semibold text-neutral-700 hover:bg-neutral-100"
                                >
                                    Prep Slip
                                </a>

                                <button
                                    v-if="order.status === 'pending'"
                                    type="button"
                                    @click="
                                        updateOrderStatus(order.id, 'preparing')
                                    "
                                    class="cursor-pointer rounded-lg bg-blue-600 px-2.5 py-1 text-[11px] font-bold text-white hover:bg-blue-700"
                                >
                                    Start Cutting
                                </button>
                                <button
                                    v-else-if="order.status === 'preparing'"
                                    type="button"
                                    @click="
                                        updateOrderStatus(order.id, 'shipped')
                                    "
                                    class="cursor-pointer rounded-lg bg-purple-600 px-2.5 py-1 text-[11px] font-bold text-white hover:bg-purple-700"
                                >
                                    Dispatch
                                </button>
                                <button
                                    v-else-if="order.status === 'shipped'"
                                    type="button"
                                    @click="
                                        updateOrderStatus(order.id, 'received')
                                    "
                                    class="cursor-pointer rounded-lg bg-emerald-600 px-2.5 py-1 text-[11px] font-bold text-white hover:bg-emerald-700"
                                >
                                    Mark Delivered
                                </button>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </main>
    </div>
</template>
