<script setup lang="ts">
import { ref, onMounted, onUnmounted } from 'vue';
import {
    MessageSquare,
    X,
    Send,
    CreditCard,
    Clock,
    MapPin,
    Package,
    ChevronDown,
    ChevronUp,
    RefreshCw,
    CheckCircle
} from 'lucide-vue-next';

interface Faq {
    id: string;
    title: string;
    summary: string;
    details: string;
}

interface Message {
    id: number;
    sender_type: 'customer' | 'staff';
    message: string;
    created_at: string;
    user?: {
        name: string;
    } | null;
}

interface Conversation {
    id: number;
    access_token: string;
    customer_name: string;
    customer_email: string;
    status: string;
    messages: Message[];
}

interface OrderLookupResult {
    order_number: string;
    status: string;
    customer_name: string;
    expected_delivery_date?: string | null;
    fulfillment_type: string;
    total_amount: number | string;
    items: Array<{
        id: number;
        product_name: string;
        weight_kg: number;
        line_total: number;
        cutting_notes?: string | null;
    }>;
}

const isOpen = ref(false);
const activeTab = ref<'faq' | 'chat' | 'tracker'>('faq');
const expandedFaq = ref<string | null>('payments');

// State
const faqs = ref<Faq[]>([]);
const conversation = ref<Conversation | null>(null);
const isLoadingConversation = ref(false);
const isSending = ref(false);

// Chat Form
const messageText = ref('');
const guestName = ref('');
const guestEmail = ref('');
const guestPhone = ref('');
const formError = ref('');

// Order Lookup Form
const lookupOrderNumber = ref('');
const lookupEmail = ref('');
const isLookingUpOrder = ref(false);
const lookupError = ref('');
const orderResult = ref<OrderLookupResult | null>(null);

let pollTimer: any = null;

// CSRF helper
function getCsrfToken(): string {
    const match = document.cookie.match(/XSRF-TOKEN=([^;]+)/);
    return match ? decodeURIComponent(match[1]) : '';
}

async function fetchFaqs() {
    try {
        const res = await fetch('/support/faqs', {
            headers: { Accept: 'application/json' },
        });
        if (res.ok) {
            const data = await res.json();
            faqs.value = data.faqs;
        }
    } catch {
        // Fallback default FAQs
    }
}

async function fetchConversation() {
    if (!isOpen.value) return;

    try {
        const url = new URL('/support/conversation', window.location.origin);
        // Check URL for support_token parameter if present
        const params = new URLSearchParams(window.location.search);
        const supportToken = params.get('support_token');
        if (supportToken) {
            url.searchParams.set('token', supportToken);
        }

        const res = await fetch(url.toString(), {
            headers: { Accept: 'application/json' },
        });
        if (res.ok) {
            const data = await res.json();
            if (data.conversation) {
                conversation.value = data.conversation;
            }
        }
    } catch {
        // Ignore polling errors
    }
}

async function sendMessage() {
    if (!messageText.value.trim()) return;

    formError.value = '';

    // If no existing conversation, name & email required
    if (!conversation.value && (!guestName.value.trim() || !guestEmail.value.trim())) {
        formError.value = 'Please provide your name and email so we can reach you.';
        return;
    }

    isSending.value = true;

    try {
        const res = await fetch('/support/messages', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                Accept: 'application/json',
                'X-XSRF-TOKEN': getCsrfToken(),
            },
            body: JSON.stringify({
                message: messageText.value,
                customer_name: guestName.value || undefined,
                customer_email: guestEmail.value || undefined,
                customer_phone: guestPhone.value || undefined,
            }),
        });

        if (res.ok) {
            const data = await res.json();
            conversation.value = data.conversation;
            messageText.value = '';
        } else {
            const errData = await res.json();
            formError.value = errData.message || 'Unable to send message. Please try again.';
        }
    } catch {
        formError.value = 'Network error. Please try again.';
    } finally {
        isSending.value = false;
    }
}

async function lookupOrder() {
    if (!lookupOrderNumber.value.trim() || !lookupEmail.value.trim()) {
        lookupError.value = 'Please enter both the order reference number and your email.';
        return;
    }

    lookupError.value = '';
    orderResult.value = null;
    isLookingUpOrder.value = true;

    try {
        const res = await fetch('/support/order-lookup', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                Accept: 'application/json',
                'X-XSRF-TOKEN': getCsrfToken(),
            },
            body: JSON.stringify({
                order_number: lookupOrderNumber.value.trim(),
                customer_email: lookupEmail.value.trim(),
            }),
        });

        if (res.ok) {
            const data = await res.json();
            orderResult.value = data.order;
        } else {
            const err = await res.json();
            lookupError.value = err.message || 'No order found matching this reference and email.';
        }
    } catch {
        lookupError.value = 'Error looking up order. Please check your connection.';
    } finally {
        isLookingUpOrder.value = false;
    }
}

function toggleFaq(id: string) {
    expandedFaq.value = expandedFaq.value === id ? null : id;
}

function openWidget(tab: 'faq' | 'chat' | 'tracker' = 'faq') {
    isOpen.value = true;
    activeTab.value = tab;
    fetchConversation();

    // Start 5s polling when open
    if (!pollTimer) {
        pollTimer = setInterval(fetchConversation, 5000);
    }
}

function closeWidget() {
    isOpen.value = false;
    if (pollTimer) {
        clearInterval(pollTimer);
        pollTimer = null;
    }
}

onMounted(() => {
    fetchFaqs();

    // If URL has ?support_token, auto-open the chat
    const params = new URLSearchParams(window.location.search);
    if (params.get('support_token')) {
        openWidget('chat');
    }
});

onUnmounted(() => {
    if (pollTimer) {
        clearInterval(pollTimer);
    }
});
</script>

<template>
    <!-- Floating Trigger Bubble -->
    <div class="fixed bottom-6 right-6 z-40">
        <button
            type="button"
            @click="isOpen ? closeWidget() : openWidget('faq')"
            class="group relative flex h-14 w-14 cursor-pointer items-center justify-center rounded-2xl bg-rose-900 text-white shadow-xl transition-all duration-300 hover:scale-105 hover:bg-rose-950 active:scale-95"
            aria-label="Open butcher support chat"
        >
            <span class="absolute -top-1 -right-1 flex h-3.5 w-3.5">
                <span class="absolute inline-flex h-full w-full animate-ping rounded-full bg-emerald-400 opacity-75"></span>
                <span class="relative inline-flex h-3.5 w-3.5 rounded-full border-2 border-white bg-emerald-500"></span>
            </span>

            <MessageSquare v-if="!isOpen" class="h-6 w-6 text-rose-200 transition group-hover:scale-110" />
            <X v-else class="h-6 w-6 text-rose-200" />
        </button>
    </div>

    <!-- Floating Chat Modal / Drawer -->
    <div
        v-if="isOpen"
        class="fixed bottom-22 right-4 z-50 flex h-[580px] w-96 max-w-[calc(100vw-2rem)] flex-col overflow-hidden rounded-2xl border border-stone-200 bg-white shadow-2xl transition-all sm:right-6"
    >
        <!-- Widget Header -->
        <div class="bg-gradient-to-r from-rose-950 via-stone-900 to-rose-950 p-4 text-white">
            <div class="flex items-center justify-between">
                <div class="flex items-center gap-2.5">
                    <span class="flex h-9 w-9 items-center justify-center rounded-xl bg-rose-800 font-serif text-sm font-black text-rose-100 shadow-xs">
                        E
                    </span>
                    <div>
                        <h3 class="font-serif text-sm font-black tracking-tight text-white">
                            Evic Butcher Counter
                        </h3>
                        <p class="text-[11px] text-rose-200">
                            🟢 Online • Slicing & Dispatch Desk
                        </p>
                    </div>
                </div>

                <button
                    type="button"
                    @click="closeWidget"
                    class="cursor-pointer rounded-lg p-1 text-rose-300 hover:bg-white/10 hover:text-white transition"
                    aria-label="Close"
                >
                    <X class="h-4 w-4" />
                </button>
            </div>

            <!-- Tabs Bar -->
            <div class="mt-3.5 flex gap-1 rounded-xl bg-white/10 p-1 backdrop-blur-xs text-xs font-semibold">
                <button
                    type="button"
                    @click="activeTab = 'faq'"
                    :class="[
                        'flex-1 rounded-lg py-1.5 transition cursor-pointer text-center',
                        activeTab === 'faq' ? 'bg-white text-stone-900 shadow-xs font-bold' : 'text-rose-200 hover:text-white'
                    ]"
                >
                    Quick FAQ
                </button>
                <button
                    type="button"
                    @click="activeTab = 'chat'"
                    :class="[
                        'flex-1 rounded-lg py-1.5 transition cursor-pointer text-center',
                        activeTab === 'chat' ? 'bg-white text-stone-900 shadow-xs font-bold' : 'text-rose-200 hover:text-white'
                    ]"
                >
                    Live Chat
                </button>
                <button
                    type="button"
                    @click="activeTab = 'tracker'"
                    :class="[
                        'flex-1 rounded-lg py-1.5 transition cursor-pointer text-center',
                        activeTab === 'tracker' ? 'bg-white text-stone-900 shadow-xs font-bold' : 'text-rose-200 hover:text-white'
                    ]"
                >
                    Track Order
                </button>
            </div>
        </div>

        <!-- Tab 1: Quick FAQ Accordion -->
        <div v-if="activeTab === 'faq'" class="flex-1 overflow-y-auto p-4 space-y-3 bg-stone-50/50">
            <div class="rounded-xl border border-rose-100 bg-rose-50/70 p-3 text-xs text-rose-900">
                <strong class="font-bold">Instant Shop Information:</strong>
                <p class="mt-0.5 text-[11px] text-rose-800">
                    Tap any topic below for our official store policies. Need something custom? Switch to the <strong>Live Chat</strong> tab.
                </p>
            </div>

            <div class="space-y-2">
                <div
                    v-for="faq in faqs"
                    :key="faq.id"
                    class="rounded-xl border border-stone-200 bg-white transition shadow-2xs overflow-hidden"
                >
                    <button
                        type="button"
                        @click="toggleFaq(faq.id)"
                        class="w-full p-3.5 text-left flex items-center justify-between cursor-pointer hover:bg-stone-50 transition"
                    >
                        <div class="flex items-center gap-2">
                            <CreditCard v-if="faq.id === 'payments'" class="h-4 w-4 text-rose-700 shrink-0" />
                            <Clock v-else-if="faq.id === 'restock'" class="h-4 w-4 text-amber-600 shrink-0" />
                            <MapPin v-else-if="faq.id === 'location'" class="h-4 w-4 text-emerald-600 shrink-0" />
                            <Package v-else class="h-4 w-4 text-indigo-600 shrink-0" />
                            <span class="text-xs font-bold text-stone-800">{{ faq.title }}</span>
                        </div>
                        <ChevronUp v-if="expandedFaq === faq.id" class="h-3.5 w-3.5 text-stone-400" />
                        <ChevronDown v-else class="h-3.5 w-3.5 text-stone-400" />
                    </button>

                    <div v-if="expandedFaq === faq.id" class="px-3.5 pb-3.5 pt-1 text-xs text-stone-600 leading-relaxed border-t border-stone-100 bg-stone-50/40">
                        {{ faq.details }}
                    </div>
                </div>
            </div>

            <div class="pt-2 text-center">
                <button
                    type="button"
                    @click="activeTab = 'chat'"
                    class="text-xs font-bold text-rose-700 hover:text-rose-900 underline cursor-pointer"
                >
                    Have a specific question? Ask our butcher desk →
                </button>
            </div>
        </div>

        <!-- Tab 2: Live Chat Message Stream -->
        <div v-else-if="activeTab === 'chat'" class="flex-1 flex flex-col justify-between overflow-hidden bg-stone-50/30">
            <!-- Messages Stream -->
            <div class="flex-1 overflow-y-auto p-4 space-y-3">
                <!-- Welcome greeting -->
                <div class="flex flex-col rounded-2xl bg-white border border-stone-200 p-3 shadow-2xs text-xs text-stone-700 space-y-1">
                    <p class="font-bold text-rose-950 flex items-center gap-1.5">
                        <span>🥩</span> Evic Butcher Concierge
                    </p>
                    <p class="text-[11px] text-stone-500 leading-relaxed">
                        Welcome to Evic! Our counter butchers are here to assist with custom slicing requests, wholesale bulk pricing, or schedule updates. Send us a message below.
                    </p>
                </div>

                <!-- Existing messages -->
                <template v-if="conversation && conversation.messages?.length">
                    <div
                        v-for="msg in conversation.messages"
                        :key="msg.id"
                        :class="[
                            'flex flex-col max-w-[85%] rounded-2xl p-3 shadow-2xs text-xs',
                            msg.sender_type === 'staff'
                                ? 'mr-auto bg-white border border-rose-100 text-stone-900 rounded-bl-xs'
                                : 'ml-auto bg-rose-900 text-white rounded-br-xs'
                        ]"
                    >
                        <div class="flex items-center justify-between text-[10px] mb-1 opacity-75 gap-3">
                            <span class="font-bold">
                                {{ msg.sender_type === 'staff' ? 'Butcher Staff' : 'You' }}
                            </span>
                            <span>{{ new Date(msg.created_at).toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' }) }}</span>
                        </div>
                        <p class="whitespace-pre-wrap leading-relaxed">{{ msg.message }}</p>
                    </div>
                </template>
            </div>

            <!-- Composer & Guest details input -->
            <div class="border-t border-stone-200 bg-white p-3 space-y-2">
                <!-- Guest details prompt if no conversation exists yet -->
                <div v-if="!conversation" class="space-y-1.5 pb-2 border-b border-stone-100">
                    <div class="grid grid-cols-2 gap-2">
                        <input
                            v-model="guestName"
                            type="text"
                            placeholder="Your Name *"
                            class="rounded-lg border border-stone-300 px-2.5 py-1 text-xs outline-hidden focus:border-rose-600"
                        />
                        <input
                            v-model="guestEmail"
                            type="email"
                            placeholder="Your Email *"
                            class="rounded-lg border border-stone-300 px-2.5 py-1 text-xs outline-hidden focus:border-rose-600"
                        />
                    </div>
                    <p class="text-[10px] text-stone-400">
                        We'll email you a copy as soon as our butcher desk replies.
                    </p>
                </div>

                <div v-if="formError" class="text-[11px] text-rose-600 font-semibold">
                    {{ formError }}
                </div>

                <!-- Message composer -->
                <form @submit.prevent="sendMessage" class="flex gap-1.5">
                    <input
                        v-model="messageText"
                        type="text"
                        placeholder="Type message to butcher..."
                        class="flex-1 rounded-xl border border-stone-300 px-3 py-2 text-xs text-stone-800 outline-hidden transition focus:border-rose-600 focus:ring-1 focus:ring-rose-600"
                    />
                    <button
                        type="submit"
                        :disabled="isSending || !messageText.trim()"
                        class="cursor-pointer rounded-xl bg-rose-700 px-3 py-2 text-white hover:bg-rose-800 transition disabled:opacity-40"
                    >
                        <Send class="h-4 w-4" />
                    </button>
                </form>
            </div>
        </div>

        <!-- Tab 3: Interactive Order Tracker -->
        <div v-else-if="activeTab === 'tracker'" class="flex-1 overflow-y-auto p-4 space-y-4 bg-stone-50/50">
            <div class="space-y-2 rounded-xl bg-white p-4 border border-stone-200 shadow-2xs">
                <h4 class="text-xs font-bold text-stone-900 font-serif">
                    Self-Service Meat Order Lookup
                </h4>
                <p class="text-[11px] text-stone-500">
                    Enter the order reference number from your receipt to see real-time cutting and delivery status.
                </p>

                <form @submit.prevent="lookupOrder" class="space-y-2 pt-1">
                    <div>
                        <input
                            v-model="lookupOrderNumber"
                            type="text"
                            placeholder="e.g. EVIC-20261017-XXXXX"
                            class="w-full rounded-lg border border-stone-300 px-3 py-1.5 text-xs font-mono uppercase outline-hidden focus:border-rose-600"
                        />
                    </div>
                    <div>
                        <input
                            v-model="lookupEmail"
                            type="email"
                            placeholder="Order Email Address"
                            class="w-full rounded-lg border border-stone-300 px-3 py-1.5 text-xs outline-hidden focus:border-rose-600"
                        />
                    </div>

                    <div v-if="lookupError" class="text-[11px] text-rose-600 font-medium">
                        {{ lookupError }}
                    </div>

                    <button
                        type="submit"
                        :disabled="isLookingUpOrder"
                        class="w-full cursor-pointer rounded-xl bg-rose-700 py-2 text-xs font-bold text-white transition hover:bg-rose-800 disabled:opacity-50"
                    >
                        <span v-if="isLookingUpOrder">Checking counter queue...</span>
                        <span v-else>Check Live Status</span>
                    </button>
                </form>
            </div>

            <!-- Order Lookup Result -->
            <div
                v-if="orderResult"
                class="rounded-xl border border-stone-200 bg-white p-4 space-y-3 shadow-2xs"
            >
                <div class="flex items-center justify-between border-b border-stone-100 pb-2">
                    <div>
                        <span class="text-xs font-bold text-stone-900 font-mono">{{ orderResult.order_number }}</span>
                        <p class="text-[10px] text-stone-500 capitalize">{{ orderResult.fulfillment_type }}</p>
                    </div>
                    <span class="rounded-full bg-rose-100 px-2 py-0.5 text-[10px] font-bold text-rose-800 uppercase">
                        {{ orderResult.status }}
                    </span>
                </div>

                <!-- Items list -->
                <div class="space-y-1.5 text-xs">
                    <div
                        v-for="item in orderResult.items"
                        :key="item.id"
                        class="flex justify-between border-b border-stone-50 pb-1"
                    >
                        <div>
                            <p class="font-semibold text-stone-800">{{ item.product_name }}</p>
                            <p class="text-[10px] text-stone-400">
                                {{ item.weight_kg }} kg <span v-if="item.cutting_notes">• {{ item.cutting_notes }}</span>
                            </p>
                        </div>
                        <span class="font-bold text-stone-800">₱{{ Number(item.line_total).toFixed(2) }}</span>
                    </div>
                </div>

                <div class="flex justify-between pt-1 text-xs font-black text-stone-900 border-t border-stone-100">
                    <span>Total Amount</span>
                    <span class="text-rose-700">₱{{ Number(orderResult.total_amount).toFixed(2) }}</span>
                </div>
            </div>
        </div>
    </div>
</template>
