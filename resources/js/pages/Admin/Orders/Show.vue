<script setup lang="ts">
import { Head } from '@inertiajs/vue3';

interface OrderItem {
    id: number;
    product_name: string;
    price_per_kg: string | number;
    weight_kg: string | number;
    line_total: string | number;
    cutting_notes?: string | null;
}

interface Order {
    id: number;
    order_number: string;
    customer_name: string;
    customer_phone: string;
    customer_address: string;
    fulfillment_type: string;
    expected_delivery_date: string;
    total_amount: string | number;
    subtotal: string | number;
    delivery_fee: string | number;
    status: string;
    notes?: string | null;
    items: OrderItem[];
    payment?: {
        option: string;
        status: string;
    } | null;
}

defineProps<{
    order: Order;
}>();

function printSlip() {
    window.print();
}
</script>

<template>
    <div class="min-h-screen bg-neutral-100 text-neutral-900 pb-16">
        <Head :title="`Butcher Prep Slip - #${order.order_number}`" />

        <!-- Print-hidden navigation -->
        <header class="print:hidden border-b border-neutral-200 bg-white py-4 shadow-xs">
            <div class="mx-auto flex max-w-4xl items-center justify-between px-4 sm:px-6">
                <a href="/admin/orders" class="text-xs font-bold text-neutral-600 hover:text-rose-600">
                    ← Back to Fulfillment Board
                </a>
                <button
                    type="button"
                    @click="printSlip"
                    class="rounded-xl bg-neutral-900 px-4 py-2 text-xs font-bold text-white shadow-xs hover:bg-neutral-800 cursor-pointer"
                >
                    🖨️ Print Butcher Slip
                </button>
            </div>
        </header>

        <!-- Printable Butcher Slip Container -->
        <main class="mx-auto mt-6 max-w-4xl px-4 sm:px-6 print:m-0 print:p-0">
            <div class="rounded-2xl border border-neutral-200 bg-white p-8 shadow-xs print:border-none print:shadow-none print:p-0">
                <!-- Shop Letterhead -->
                <div class="border-b-2 border-neutral-900 pb-4 flex justify-between items-start">
                    <div>
                        <h1 class="text-xl font-black uppercase tracking-wider text-neutral-900">Evic Meat & Poultry Shop</h1>
                        <p class="text-xs text-neutral-500 font-semibold">Butchery Prep & Dispatch Slip</p>
                    </div>
                    <div class="text-right">
                        <p class="text-lg font-mono font-black text-rose-600">#{{ order.order_number }}</p>
                        <p class="text-xs text-neutral-500 capitalize">Status: {{ order.status }}</p>
                    </div>
                </div>

                <!-- Customer & Fulfillment Metadata -->
                <div class="grid grid-cols-2 gap-4 py-4 border-b border-neutral-200 text-xs">
                    <div>
                        <p class="font-bold text-neutral-500 uppercase text-[10px]">Customer Information</p>
                        <p class="text-sm font-bold text-neutral-900 mt-1">{{ order.customer_name }}</p>
                        <p class="text-neutral-600">{{ order.customer_phone }}</p>
                        <p class="text-neutral-600 mt-0.5">{{ order.customer_address }}</p>
                    </div>
                    <div>
                        <p class="font-bold text-neutral-500 uppercase text-[10px]">Fulfillment Details</p>
                        <p class="text-sm font-bold text-neutral-900 mt-1 capitalize">{{ order.fulfillment_type }}</p>
                        <p class="text-neutral-600">Schedule: {{ order.expected_delivery_date }}</p>
                        <p class="text-neutral-600 font-semibold mt-0.5">
                            Payment: {{ order.payment?.option }} ({{ order.payment?.status }})
                        </p>
                    </div>
                </div>

                <!-- Special Notes -->
                <div v-if="order.notes" class="my-3 rounded-lg bg-amber-50 p-3 text-xs text-amber-900 border border-amber-200">
                    <strong>Customer Note:</strong> {{ order.notes }}
                </div>

                <!-- Cuts & Butchery Specifications -->
                <div class="mt-4">
                    <h2 class="text-xs font-bold uppercase tracking-wider text-neutral-500 mb-2">Cuts Required</h2>
                    <table class="w-full text-left text-xs border border-neutral-200">
                        <thead class="bg-neutral-100 text-neutral-700 font-bold">
                            <tr>
                                <th class="p-3 border-b border-neutral-200">Meat Cut</th>
                                <th class="p-3 border-b border-neutral-200">Target Weight</th>
                                <th class="p-3 border-b border-neutral-200">Cutting Instructions</th>
                                <th class="p-3 border-b border-neutral-200 text-right">Estimated Line Total</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-neutral-200">
                            <tr v-for="item in order.items" :key="item.id">
                                <td class="p-3 font-bold text-neutral-900 text-sm">{{ item.product_name }}</td>
                                <td class="p-3 font-mono font-bold text-sm">{{ Number(item.weight_kg).toFixed(2) }} kg</td>
                                <td class="p-3">
                                    <span v-if="item.cutting_notes" class="font-semibold text-rose-700 bg-rose-50 px-2 py-0.5 rounded">
                                        {{ item.cutting_notes }}
                                    </span>
                                    <span v-else class="text-neutral-400 italic">Standard Cut</span>
                                </td>
                                <td class="p-3 text-right font-bold">₱{{ Number(item.line_total).toFixed(2) }}</td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <!-- Butcher Checklist / Verification -->
                <div class="mt-8 border-t-2 border-dashed border-neutral-300 pt-4 flex justify-between items-end text-xs">
                    <div class="space-y-1">
                        <p class="text-neutral-500">Total Due Upon Delivery/Pickup: <strong class="text-sm font-black text-neutral-900">₱{{ Number(order.total_amount).toFixed(2) }}</strong></p>
                        <p class="text-[11px] text-neutral-400">Keep temperature below 4°C during transport.</p>
                    </div>
                    <div class="text-center">
                        <div class="w-40 border-b border-neutral-400 pb-8"></div>
                        <p class="text-[10px] text-neutral-500 mt-1">Butcher Signature & Weighed By</p>
                    </div>
                </div>
            </div>
        </main>
    </div>
</template>
