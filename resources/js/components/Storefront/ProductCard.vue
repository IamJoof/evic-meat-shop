<script setup lang="ts">
import { router } from '@inertiajs/vue3';
import { computed, ref } from 'vue';
import WeightSelector from '@/components/Storefront/WeightSelector.vue';
import { Scissors, Check } from 'lucide-vue-next';

interface Product {
    id: number;
    name: string;
    slug: string;
    price_per_kg: string | number;
    is_available: boolean;
    description: string | null;
    category?: {
        id: number;
        name: string;
    };
    primary_image?: {
        image_path: string;
    } | null;
}

const props = defineProps<{
    product: Product;
}>();

const emit = defineEmits<{
    (e: 'added'): void;
}>();

const selectedWeight = ref<number>(1.0);
const cuttingNotes = ref<string>('');
const isSubmitting = ref<boolean>(false);
const showCustomInput = ref<boolean>(false);

const prepPresets = [
    { label: 'Whole Cut', value: 'Whole Cut / No slicing' },
    { label: 'Thin Slice', value: 'Thin Slice (Samgyupsal / Sukiyaki)' },
    { label: 'Adobo Cubes', value: 'Adobo / Menudo 1-inch Cubes' },
    { label: 'Deboned', value: 'Deboned fillet' },
];

function selectPreset(val: string) {
    if (cuttingNotes.value === val) {
        cuttingNotes.value = '';
    } else {
        cuttingNotes.value = val;
        showCustomInput.value = false;
    }
}

function toggleCustom() {
    showCustomInput.value = !showCustomInput.value;
    if (showCustomInput.value) {
        cuttingNotes.value = '';
    }
}

const lineTotal = computed(() => {
    return (selectedWeight.value * Number(props.product.price_per_kg)).toFixed(2);
});

function addToCart() {
    if (!props.product.is_available) {
        return;
    }

    isSubmitting.value = true;
    router.post(
        '/cart/items',
        {
            product_id: props.product.id,
            weight_kg: selectedWeight.value,
            cutting_notes: cuttingNotes.value || null,
        },
        {
            preserveScroll: true,
            onSuccess: () => {
                emit('added');
            },
            onFinish: () => {
                isSubmitting.value = false;
                cuttingNotes.value = '';
                showCustomInput.value = false;
            },
        },
    );
}
</script>

<template>
    <article
        class="flex flex-col justify-between rounded-2xl border border-stone-200/90 bg-white p-5 shadow-xs transition hover:border-rose-300 hover:shadow-md"
    >
        <div>
            <!-- Header Badges -->
            <div class="flex items-center justify-between text-xs">
                <span
                    class="rounded-full bg-rose-50 px-2.5 py-0.5 font-semibold text-rose-800 border border-rose-100"
                >
                    {{ product.category?.name || 'Fresh Meat' }}
                </span>
                <span
                    :class="[
                        'rounded-full px-2.5 py-0.5 text-xs font-semibold',
                        product.is_available
                            ? 'bg-emerald-50 text-emerald-700 border border-emerald-100'
                            : 'bg-stone-100 text-stone-500',
                    ]"
                >
                    {{ product.is_available ? 'Fresh & In Stock' : 'Sold Out Today' }}
                </span>
            </div>

            <!-- Title & Price -->
            <div class="mt-4">
                <h3 class="text-lg font-bold text-stone-900 tracking-tight">
                    {{ product.name }}
                </h3>
                <div class="mt-1 flex items-baseline gap-2">
                    <span class="text-2xl font-black text-rose-950">
                        ₱{{ Number(product.price_per_kg).toFixed(2) }}
                    </span>
                    <span class="text-xs font-medium text-stone-500">per kilogram</span>
                </div>
                <p
                    v-if="product.description"
                    class="mt-1.5 line-clamp-2 text-xs text-stone-600 leading-relaxed"
                >
                    {{ product.description }}
                </p>
            </div>
        </div>

        <!-- Butcher & Weight Ordering Controls -->
        <div class="mt-5 space-y-3.5 border-t border-stone-100 pt-4">
            <div v-if="product.is_available" class="space-y-3">
                <WeightSelector
                    v-model="selectedWeight"
                    :price-per-kg="Number(product.price_per_kg)"
                />

                <!-- Butchery Prep Chips -->
                <div class="space-y-1.5">
                    <div class="flex items-center justify-between text-xs font-semibold text-stone-700">
                        <span class="inline-flex items-center gap-1">
                            <Scissors class="h-3.5 w-3.5 text-rose-600" />
                            Butcher Preparation
                        </span>
                        <span class="text-[11px] font-normal text-emerald-700">Free of charge</span>
                    </div>

                    <div class="flex flex-wrap gap-1.5">
                        <button
                            v-for="preset in prepPresets"
                            :key="preset.label"
                            type="button"
                            @click="selectPreset(preset.value)"
                            :class="[
                                'inline-flex items-center gap-1 rounded-lg px-2.5 py-1 text-xs font-medium transition cursor-pointer',
                                cuttingNotes === preset.value
                                    ? 'bg-rose-900 text-white shadow-xs'
                                    : 'bg-stone-100 text-stone-700 hover:bg-stone-200',
                            ]"
                        >
                            <Check v-if="cuttingNotes === preset.value" class="h-3 w-3" />
                            {{ preset.label }}
                        </button>

                        <button
                            type="button"
                            @click="toggleCustom"
                            :class="[
                                'rounded-lg px-2.5 py-1 text-xs font-medium transition cursor-pointer',
                                showCustomInput
                                    ? 'bg-stone-900 text-white'
                                    : 'bg-stone-100 text-stone-600 hover:bg-stone-200',
                            ]"
                        >
                            Custom...
                        </button>
                    </div>

                    <!-- Custom instruction input -->
                    <div v-if="showCustomInput" class="pt-1">
                        <input
                            v-model="cuttingNotes"
                            type="text"
                            placeholder="e.g. Skin-on, slice into 2-inch steaks, bone-in..."
                            class="w-full rounded-lg border border-stone-300 px-3 py-1.5 text-xs text-stone-800 outline-hidden focus:border-rose-500 focus:ring-1 focus:ring-rose-500"
                        />
                    </div>
                </div>
            </div>

            <!-- Action Button with Estimated Total -->
            <button
                type="button"
                @click="addToCart"
                :disabled="!product.is_available || isSubmitting"
                class="flex w-full cursor-pointer items-center justify-between rounded-xl bg-rose-700 px-4 py-3 text-sm font-semibold text-white shadow-xs transition hover:bg-rose-800 active:scale-[0.99] disabled:cursor-not-allowed disabled:bg-stone-200 disabled:text-stone-400"
            >
                <span v-if="isSubmitting">Adding to counter...</span>
                <span v-else-if="product.is_available" class="inline-flex items-center gap-2">
                    Add to Order
                </span>
                <span v-else>Currently Sold Out</span>

                <span
                    v-if="product.is_available && !isSubmitting"
                    class="rounded-lg bg-rose-900/40 px-2 py-0.5 text-xs font-bold text-rose-100"
                >
                    ₱{{ lineTotal }}
                </span>
            </button>
        </div>
    </article>
</template>
