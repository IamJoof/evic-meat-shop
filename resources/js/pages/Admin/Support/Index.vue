<script setup lang="ts">
import { Head, router, useForm } from '@inertiajs/vue3';
import { ref } from 'vue';
import {
    MessageSquare,
    Search,
    CheckCircle,
    Clock,
    User,
    Package,
    Send,
    ArrowLeft,
    Check
} from 'lucide-vue-next';

interface Message {
    id: number;
    sender_type: 'customer' | 'staff';
    message: string;
    created_at: string;
    user?: {
        id: number;
        name: string;
    } | null;
}

interface OrderItem {
    id: number;
    product_name: string;
    weight_kg: string | number;
    price_per_kg: string | number;
    line_total: string | number;
    cutting_notes?: string | null;
}

interface Order {
    id: number;
    order_number: string;
    status: string;
    total_amount: string | number;
    created_at: string;
    items?: OrderItem[];
}

interface Conversation {
    id: number;
    access_token: string;
    customer_name: string;
    customer_email: string;
    customer_phone?: string | null;
    subject?: string | null;
    status: 'open' | 'replied' | 'resolved';
    last_message_at: string;
    messages: Message[];
    order?: Order | null;
}

interface PaginatedConversations {
    data: Conversation[];
    current_page: number;
    last_page: number;
    total: number;
}

const props = defineProps<{
    conversations: PaginatedConversations;
    selectedConversation?: Conversation | null;
    customerOrders: Order[];
    currentStatus: string;
    search?: string | null;
    counts: {
        open: number;
        replied: number;
        resolved: number;
    };
}>();

const searchQuery = ref(props.search || '');

const replyForm = useForm({
    message: '',
});

function selectConversation(id: number) {
    router.get(
        '/admin/support',
        {
            status: props.currentStatus,
            search: searchQuery.value || undefined,
            selected: id,
        },
        { preserveState: true },
    );
}

function filterStatus(status: string) {
    router.get(
        '/admin/support',
        {
            status,
            search: searchQuery.value || undefined,
        },
        { preserveState: true },
    );
}

function handleSearch() {
    router.get(
        '/admin/support',
        {
            status: props.currentStatus,
            search: searchQuery.value || undefined,
        },
        { preserveState: true },
    );
}

function submitReply() {
    if (!props.selectedConversation) {
        return;
    }

    replyForm.post(`/admin/support/${props.selectedConversation.id}/reply`, {
        preserveScroll: true,
        onSuccess: () => {
            replyForm.reset();
        },
    });
}

function updateStatus(newStatus: 'open' | 'replied' | 'resolved') {
    if (!props.selectedConversation) {
        return;
    }

    router.patch(
        `/admin/support/${props.selectedConversation.id}/status`,
        { status: newStatus },
        { preserveScroll: true },
    );
}
</script>

<template>
    <div class="min-h-screen bg-stone-100 text-stone-900 flex flex-col">
        <Head title="Customer Support Inbox - Admin" />

        <!-- Admin Sub-header -->
        <header class="border-b border-stone-200 bg-white px-6 py-4 shadow-xs">
            <div class="mx-auto flex max-w-7xl items-center justify-between">
                <div class="flex items-center gap-3">
                    <a
                        href="/admin/orders"
                        class="text-xs font-semibold text-stone-500 hover:text-stone-800 transition"
                    >
                        ← Admin Orders
                    </a>
                    <span class="text-stone-300">|</span>
                    <div class="flex items-center gap-2">
                        <MessageSquare class="h-5 w-5 text-rose-700" />
                        <h1 class="text-lg font-black tracking-tight text-stone-900 font-serif">
                            Customer Support Inbox
                        </h1>
                    </div>
                </div>

                <!-- Status Filter Pills -->
                <div class="flex items-center gap-2">
                    <button
                        type="button"
                        @click="filterStatus('open')"
                        :class="[
                            'cursor-pointer rounded-lg px-3 py-1 text-xs font-bold transition flex items-center gap-1.5',
                            currentStatus === 'open'
                                ? 'bg-rose-900 text-white shadow-xs'
                                : 'bg-stone-100 text-stone-600 hover:bg-stone-200',
                        ]"
                    >
                        <span>Open Inquiries</span>
                        <span class="rounded-full bg-rose-700 px-1.5 py-0.2 text-[10px] text-white">
                            {{ counts.open }}
                        </span>
                    </button>

                    <button
                        type="button"
                        @click="filterStatus('replied')"
                        :class="[
                            'cursor-pointer rounded-lg px-3 py-1 text-xs font-bold transition flex items-center gap-1.5',
                            currentStatus === 'replied'
                                ? 'bg-rose-900 text-white shadow-xs'
                                : 'bg-stone-100 text-stone-600 hover:bg-stone-200',
                        ]"
                    >
                        <span>Replied</span>
                        <span class="rounded-full bg-stone-300 px-1.5 py-0.2 text-[10px] text-stone-700">
                            {{ counts.replied }}
                        </span>
                    </button>

                    <button
                        type="button"
                        @click="filterStatus('resolved')"
                        :class="[
                            'cursor-pointer rounded-lg px-3 py-1 text-xs font-bold transition flex items-center gap-1.5',
                            currentStatus === 'resolved'
                                ? 'bg-rose-900 text-white shadow-xs'
                                : 'bg-stone-100 text-stone-600 hover:bg-stone-200',
                        ]"
                    >
                        <span>Resolved</span>
                        <span class="rounded-full bg-stone-300 px-1.5 py-0.2 text-[10px] text-stone-700">
                            {{ counts.resolved }}
                        </span>
                    </button>

                    <button
                        type="button"
                        @click="filterStatus('all')"
                        :class="[
                            'cursor-pointer rounded-lg px-3 py-1 text-xs font-bold transition',
                            currentStatus === 'all'
                                ? 'bg-rose-900 text-white shadow-xs'
                                : 'bg-stone-100 text-stone-600 hover:bg-stone-200',
                        ]"
                    >
                        All
                    </button>
                </div>
            </div>
        </header>

        <!-- Main Content Area: 2 Columns -->
        <main class="mx-auto flex w-full max-w-7xl flex-1 gap-6 p-6">
            <!-- Left Column: Conversation List -->
            <div class="w-80 sm:w-96 shrink-0 flex flex-col rounded-2xl border border-stone-200 bg-white shadow-xs overflow-hidden">
                <!-- Search bar -->
                <div class="border-b border-stone-100 p-3">
                    <form @submit.prevent="handleSearch" class="relative">
                        <Search class="absolute left-3 top-2.5 h-3.5 w-3.5 text-stone-400" />
                        <input
                            v-model="searchQuery"
                            type="text"
                            placeholder="Search customer, email..."
                            class="w-full rounded-xl border border-stone-200 bg-stone-50 pl-8 pr-3 py-1.5 text-xs text-stone-800 outline-hidden transition focus:border-rose-600 focus:bg-white"
                        />
                    </form>
                </div>

                <!-- List Items -->
                <div class="flex-1 overflow-y-auto divide-y divide-stone-100">
                    <div
                        v-if="conversations.data.length === 0"
                        class="p-8 text-center text-xs text-stone-400"
                    >
                        No inquiries found in this status view.
                    </div>

                    <div
                        v-for="conv in conversations.data"
                        :key="conv.id"
                        @click="selectConversation(conv.id)"
                        :class="[
                            'cursor-pointer p-4 transition text-left flex flex-col gap-1',
                            selectedConversation?.id === conv.id
                                ? 'bg-rose-50/70 border-l-4 border-rose-700'
                                : 'hover:bg-stone-50',
                        ]"
                    >
                        <div class="flex items-center justify-between">
                            <h3 class="text-xs font-bold text-stone-900 truncate">
                                {{ conv.customer_name }}
                            </h3>
                            <span
                                :class="[
                                    'text-[10px] font-bold px-2 py-0.5 rounded-full capitalize',
                                    conv.status === 'open' ? 'bg-rose-100 text-rose-800' :
                                    conv.status === 'replied' ? 'bg-amber-100 text-amber-800' :
                                    'bg-emerald-100 text-emerald-800'
                                ]"
                            >
                                {{ conv.status }}
                            </span>
                        </div>

                        <p class="text-[11px] text-stone-500 truncate">
                            {{ conv.customer_email }}
                        </p>

                        <p
                            v-if="conv.messages?.length"
                            class="text-xs text-stone-600 line-clamp-1 mt-0.5"
                        >
                            {{ conv.messages[conv.messages.length - 1].message }}
                        </p>

                        <div class="mt-1 flex items-center justify-between text-[10px] text-stone-400">
                            <span>{{ conv.messages?.length || 0 }} msgs</span>
                            <span>{{ new Date(conv.last_message_at).toLocaleDateString() }}</span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Right Column: Active Thread Detail & Reply Composer -->
            <div
                v-if="selectedConversation"
                class="flex-1 flex flex-col rounded-2xl border border-stone-200 bg-white shadow-xs overflow-hidden"
            >
                <!-- Detail Header -->
                <div class="flex items-center justify-between border-b border-stone-200 bg-stone-50/70 p-4">
                    <div>
                        <div class="flex items-center gap-2">
                            <h2 class="text-sm font-black text-stone-900 font-serif">
                                {{ selectedConversation.customer_name }}
                            </h2>
                            <span class="text-xs text-stone-400">•</span>
                            <span class="text-xs text-stone-600">{{ selectedConversation.customer_email }}</span>
                            <span v-if="selectedConversation.customer_phone" class="text-xs text-stone-500">
                                ({{ selectedConversation.customer_phone }})
                            </span>
                        </div>
                        <p class="text-[11px] text-stone-400 mt-0.5">
                            Customer Access Token: <span class="font-mono text-stone-500">{{ selectedConversation.access_token.slice(0, 12) }}...</span>
                        </p>
                    </div>

                    <!-- Status Action Buttons -->
                    <div class="flex items-center gap-2">
                        <button
                            v-if="selectedConversation.status !== 'resolved'"
                            type="button"
                            @click="updateStatus('resolved')"
                            class="cursor-pointer inline-flex items-center gap-1 rounded-lg border border-emerald-300 bg-emerald-50 px-2.5 py-1.5 text-xs font-semibold text-emerald-800 hover:bg-emerald-100 transition"
                        >
                            <CheckCircle class="h-3.5 w-3.5 text-emerald-600" />
                            Mark as Resolved
                        </button>
                        <button
                            v-else
                            type="button"
                            @click="updateStatus('open')"
                            class="cursor-pointer inline-flex items-center gap-1 rounded-lg border border-stone-300 bg-white px-2.5 py-1.5 text-xs font-semibold text-stone-700 hover:bg-stone-50 transition"
                        >
                            <Clock class="h-3.5 w-3.5 text-stone-500" />
                            Re-open
                        </button>
                    </div>
                </div>

                <!-- Recent Order Context Strip (If customer has orders) -->
                <div
                    v-if="customerOrders.length > 0"
                    class="border-b border-stone-100 bg-stone-50/40 px-4 py-2 text-xs flex items-center justify-between"
                >
                    <div class="flex items-center gap-2">
                        <Package class="h-4 w-4 text-rose-700" />
                        <span class="font-bold text-stone-800">Recent Customer Order:</span>
                        <span class="font-mono text-stone-600">{{ customerOrders[0].order_number }}</span>
                        <span class="rounded bg-rose-50 px-1.5 py-0.2 text-[10px] font-semibold text-rose-800 uppercase">
                            {{ customerOrders[0].status }}
                        </span>
                    </div>
                    <span class="font-extrabold text-stone-900">
                        ₱{{ Number(customerOrders[0].total_amount).toFixed(2) }}
                    </span>
                </div>

                <!-- Message Stream -->
                <div class="flex-1 overflow-y-auto p-6 space-y-4 bg-stone-50/20">
                    <div
                        v-for="msg in selectedConversation.messages"
                        :key="msg.id"
                        :class="[
                            'flex flex-col max-w-xl rounded-2xl p-4 shadow-2xs',
                            msg.sender_type === 'staff'
                                ? 'ml-auto bg-rose-900 text-white rounded-br-xs'
                                : 'mr-auto bg-white border border-stone-200 text-stone-900 rounded-bl-xs'
                        ]"
                    >
                        <div class="flex items-center justify-between gap-4 text-[11px] mb-1 opacity-70">
                            <span class="font-bold">
                                {{ msg.sender_type === 'staff' ? (msg.user?.name || 'Butcher Staff') : selectedConversation.customer_name }}
                            </span>
                            <span>{{ new Date(msg.created_at).toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' }) }}</span>
                        </div>
                        <p class="text-xs sm:text-sm whitespace-pre-wrap leading-relaxed">
                            {{ msg.message }}
                        </p>
                    </div>
                </div>

                <!-- Reply Composer -->
                <form @submit.prevent="submitReply" class="border-t border-stone-200 bg-white p-4">
                    <div class="space-y-2">
                        <textarea
                            v-model="replyForm.message"
                            required
                            rows="3"
                            placeholder="Type official shop reply to customer (they will receive an automated email copy)..."
                            class="w-full rounded-xl border border-stone-300 p-3 text-xs sm:text-sm text-stone-800 outline-hidden transition focus:border-rose-600 focus:ring-1 focus:ring-rose-600"
                        ></textarea>

                        <div class="flex items-center justify-between">
                            <p class="text-[11px] text-stone-400">
                                Customer is notified instantly on storefront and at <strong class="text-stone-600">{{ selectedConversation.customer_email }}</strong>.
                            </p>
                            <button
                                type="submit"
                                :disabled="replyForm.processing"
                                class="cursor-pointer inline-flex items-center gap-1.5 rounded-xl bg-rose-700 px-4 py-2 text-xs font-bold text-white shadow-xs hover:bg-rose-800 transition disabled:opacity-50"
                            >
                                <Send class="h-3.5 w-3.5" />
                                <span>{{ replyForm.processing ? 'Sending...' : 'Send Reply & Email Customer' }}</span>
                            </button>
                        </div>
                    </div>
                </form>
            </div>

            <!-- Empty Selection State -->
            <div
                v-else
                class="flex-1 flex flex-col items-center justify-center rounded-2xl border border-dashed border-stone-200 bg-white p-12 text-center text-stone-400"
            >
                <MessageSquare class="h-10 w-10 text-stone-300 mb-2" />
                <p class="text-sm font-semibold text-stone-600">Select an inquiry to view conversation</p>
                <p class="text-xs text-stone-400 mt-1">Incoming chats from the storefront will appear here in real-time.</p>
            </div>
        </main>
    </div>
</template>
