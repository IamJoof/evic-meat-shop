<script setup lang="ts">
import { Head, router, useForm, usePage } from '@inertiajs/vue3';
import { computed } from 'vue';
import { ShieldCheck, Snowflake, UserPlus, CheckCircle, PackageCheck, AlertCircle } from 'lucide-vue-next';
import CustomerSupportChat from '@/components/Storefront/CustomerSupportChat.vue';

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
    customer_email: string;
    customer_phone: string;
    customer_address: string;
    fulfillment_type: string;
    expected_delivery_date: string;
    actual_delivery_date?: string | null;
    subtotal: string | number;
    delivery_fee: string | number;
    total_amount: string | number;
    status: string;
    user_id?: number | null;
    notes?: string | null;
    items: OrderItem[];
    payment?: Payment | null;
}

const props = defineProps<{
    order: Order;
}>();

const page = usePage();
const currentUser = computed(() => page.props.auth?.user);

const claimForm = useForm({
    order_number: props.order.order_number,
    password: '',
    password_confirmation: '',
});

const steps = [
    { key: 'pending', label: 'Order Received' },
    { key: 'preparing', label: 'Butcher Cutting' },
    { key: 'shipped', label: 'Cold-Chain En Route' },
    { key: 'received', label: 'Delivered Fresh' },
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

function submitClaimAccount() {
    claimForm.post('/claim-account', {
        preserveScroll: true,
    });
}

function cancelOrder() {
    if (confirm('Are you sure you want to cancel this order? Cancellations are only permitted before the butcher begins slicing.')) {
        router.post(`/orders/${props.order.order_number}/cancel`);
    }
}
</script>

<template>
    <div class="min-h-screen bg-stone-50 pb-20 text-stone-900">
        <Head :title="`Order #${order.order_number} Tracking - Evic Meat Shop`" />

        <!-- Navigation header -->
        <header class="border-b border-stone-200 bg-white py-4 shadow-xs">
            <div class="mx-auto flex max-w-4xl items-center justify-between px-4 sm:px-6">
                <a
                    href="/"
                    class="flex items-center gap-2 text-xs font-bold text-rose-700 hover:text-rose-800 transition"
                >
                    ← Return to Fresh Counter
                </a>
                <span class="text-xs font-semibold text-stone-500 font-mono">
                    Ref #{{ order.order_number }}
                </span>
            </div>
        </header>

        <main class="mx-auto mt-8 max-w-4xl space-y-6 px-4 sm:px-6">
            <!-- Hero Status Confirmation Card -->
            <div class="space-y-4 rounded-2xl border border-stone-200 bg-white p-6 text-center shadow-xs sm:p-8">
                <span class="inline-flex h-16 w-16 items-center justify-center rounded-2xl bg-emerald-50 border border-emerald-200 text-emerald-600 shadow-xs">
                    <PackageCheck class="h-8 w-8" />
                </span>

                <div>
                    <h1 class="text-2xl font-black tracking-tight text-stone-900 font-serif sm:text-3xl">
                        Order Received & Queued!
                    </h1>
                    <p class="mt-1 text-sm text-stone-600">
                        Thank you, <strong class="text-stone-900">{{ order.customer_name }}</strong>. Our master butchers will prep your custom cuts fresh for dispatch.
                    </p>
                    <p class="mt-0.5 text-xs text-stone-500">
                        Confirmation and receipt sent to <strong class="text-stone-800">{{ order.customer_email }}</strong>.
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
                                    'flex h-9 w-9 items-center justify-center rounded-xl text-xs font-bold transition shadow-xs',
                                    idx <= currentStepIndex
                                        ? 'bg-rose-800 text-white shadow-rose-900/20'
                                        : 'bg-stone-100 text-stone-400 border border-stone-200',
                                ]"
                            >
                                {{ idx + 1 }}
                            </div>
                            <span class="mt-2 text-[11px] font-semibold leading-tight text-stone-700">
                                {{ step.label }}
                            </span>
                        </div>
                    </div>
                </div>

                <div
                    v-else
                    class="rounded-xl bg-rose-50 p-4 text-xs font-semibold text-rose-800 border border-rose-200"
                >
                    This order has been cancelled.
                </div>
            </div>

            <!-- Post-Order Account Creation Card (For Guest Shoppers) -->
            <div
                v-if="!currentUser && !order.user_id"
                class="rounded-2xl border border-rose-200 bg-gradient-to-br from-rose-950 via-stone-900 to-rose-950 p-6 text-white shadow-md sm:p-8"
            >
                <div class="max-w-2xl space-y-4">
                    <div class="inline-flex items-center gap-1.5 rounded-full bg-rose-500/20 px-3 py-0.5 text-xs font-semibold text-rose-300 border border-rose-500/30">
                        <UserPlus class="h-3.5 w-3.5" />
                        1-Click Account Creation
                    </div>

                    <div>
                        <h2 class="text-xl font-bold tracking-tight font-serif sm:text-2xl">
                            Save your Evic Account for 1-Click Butcher Reorders
                        </h2>
                        <p class="mt-1 text-xs text-rose-200 sm:text-sm">
                            Create a password to save your delivery addresses and track past custom cuts. All orders placed under <span class="font-bold underline">{{ order.customer_email }}</span> will automatically link to your new account.
                        </p>
                    </div>

                    <form @submit.prevent="submitClaimAccount" class="pt-2">
                        <div class="grid grid-cols-1 gap-3 sm:grid-cols-2">
                            <div>
                                <label class="block text-xs font-semibold text-rose-200">
                                    Set Password (min 8 chars)
                                </label>
                                <input
                                    v-model="claimForm.password"
                                    type="password"
                                    required
                                    placeholder="••••••••"
                                    class="mt-1 w-full rounded-xl border border-rose-800/80 bg-white/10 px-3 py-2 text-xs text-white placeholder-rose-300/50 outline-hidden backdrop-blur-xs transition focus:border-rose-400 focus:bg-white/20"
                                />
                                <span v-if="claimForm.errors.password" class="mt-1 block text-xs text-rose-400">
                                    {{ claimForm.errors.password }}
                                </span>
                            </div>

                            <div>
                                <label class="block text-xs font-semibold text-rose-200">
                                    Confirm Password
                                </label>
                                <input
                                    v-model="claimForm.password_confirmation"
                                    type="password"
                                    required
                                    placeholder="••••••••"
                                    class="mt-1 w-full rounded-xl border border-rose-800/80 bg-white/10 px-3 py-2 text-xs text-white placeholder-rose-300/50 outline-hidden backdrop-blur-xs transition focus:border-rose-400 focus:bg-white/20"
                                />
                            </div>
                        </div>

                        <div class="mt-4 flex flex-wrap items-center gap-3">
                            <button
                                type="submit"
                                :disabled="claimForm.processing"
                                class="inline-flex cursor-pointer items-center justify-center rounded-xl bg-rose-600 px-5 py-2.5 text-xs font-bold text-white shadow-md transition hover:bg-rose-500 disabled:opacity-50 active:scale-95"
                            >
                                <span v-if="claimForm.processing">Saving Account...</span>
                                <span v-else>Create Password & Save Account</span>
                            </button>
                            <span class="text-[11px] text-stone-400">
                                Completely optional. You can still track your order anytime via reference number.
                            </span>
                        </div>
                    </form>
                </div>
            </div>

            <div
                v-else-if="currentUser"
                class="rounded-xl border border-emerald-200 bg-emerald-50/70 p-4 text-xs text-emerald-900 flex items-center justify-between"
            >
                <div class="flex items-center gap-2">
                    <CheckCircle class="h-4 w-4 text-emerald-600 shrink-0" />
                    <span>
                        Linked to your authenticated account: <strong>{{ currentUser.email }}</strong>
                    </span>
                </div>
                <a href="/dashboard" class="font-bold text-emerald-800 hover:underline">
                    View Account Dashboard →
                </a>
            </div>

            <!-- Order Details Card -->
            <div class="space-y-4 rounded-2xl border border-stone-200 bg-white p-6 shadow-xs">
                <div class="flex items-center justify-between border-b border-stone-100 pb-3">
                    <h2 class="text-base font-bold text-stone-900">
                        Butchered Items Summary
                    </h2>
                    <span class="rounded-full bg-stone-100 px-2.5 py-0.5 text-xs font-semibold text-stone-600 capitalize">
                        {{ order.fulfillment_type }} • {{ order.payment?.option || 'COD' }}
                    </span>
                </div>

                <div class="divide-y divide-stone-100">
                    <div
                        v-for="item in order.items"
                        :key="item.id"
                        class="flex items-start justify-between py-3 text-xs"
                    >
                        <div>
                            <p class="text-sm font-bold text-stone-900">
                                {{ item.product_name }}
                            </p>
                            <p class="font-medium text-stone-500">
                                {{ Number(item.weight_kg).toFixed(2) }} kg @ ₱{{
                                    Number(item.price_per_kg).toFixed(2)
                                }}/kg
                            </p>
                            <p
                                v-if="item.cutting_notes"
                                class="mt-1 inline-block rounded bg-rose-50 px-2 py-0.5 font-medium text-rose-800 border border-rose-100"
                            >
                                Preparation: {{ item.cutting_notes }}
                            </p>
                        </div>
                        <span class="text-sm font-bold text-stone-900">
                            ₱{{ Number(item.line_total).toFixed(2) }}
                        </span>
                    </div>
                </div>

                <!-- Financial Breakdown -->
                <div class="space-y-2 border-t border-stone-200 pt-4 text-xs">
                    <div class="flex justify-between text-stone-600">
                        <span>Meat Subtotal</span>
                        <span class="font-semibold text-stone-800">₱{{ Number(order.subtotal).toFixed(2) }}</span>
                    </div>
                    <div class="flex justify-between text-stone-600">
                        <span>Insulated Delivery Fee</span>
                        <span class="font-semibold text-stone-800">₱{{ Number(order.delivery_fee).toFixed(2) }}</span>
                    </div>
                    <div class="flex justify-between border-t border-stone-200 pt-3 text-base font-black text-stone-900">
                        <span>Total Payable Amount</span>
                        <span class="text-rose-700">₱{{ Number(order.total_amount).toFixed(2) }}</span>
                    </div>
                </div>

                <!-- Order Cancellation Button -->
                <div v-if="order.status === 'pending'" class="flex items-center justify-between pt-3 border-t border-stone-100">
                    <p class="text-[11px] text-stone-400">
                        Need to make changes? Orders can only be cancelled before butcher preparation begins.
                    </p>
                    <button
                        type="button"
                        @click="cancelOrder"
                        class="cursor-pointer rounded-xl border border-rose-200 px-3.5 py-1.5 text-xs font-semibold text-rose-700 hover:bg-rose-50 transition"
                    >
                        Cancel Order
                    </button>
                </div>
            </div>
        </main>

        <!-- Live Customer Support Drawer -->
        <CustomerSupportChat />
    </div>
</template>
