<script setup lang="ts">
import { ref } from 'vue';
import { router } from '@inertiajs/vue3';
import WeightSelector from '@/components/Storefront/WeightSelector.vue';

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

const selectedWeight = ref<number>(1.0);
const cuttingNotes = ref<string>('');
const isSubmitting = ref<boolean>(false);
const showNotesInput = ref<boolean>(false);

function addToCart() {
    if (!props.product.is_available) return;

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
            onFinish: () => {
                isSubmitting.value = false;
                cuttingNotes.value = '';
                showNotesInput.value = false;
            },
        }
    );
}
</script>

<template>
    <article
        class="flex flex-col justify-between rounded-2xl border border-neutral-200 bg-white p-5 shadow-sm transition hover:shadow-md"
    >
        <div>
            <!-- Header Badges -->
            <div class="flex items-center justify-between text-xs">
                <span class="font-medium text-rose-700 bg-rose-50 px-2 py-0.5 rounded-full">
                    {{ product.category?.name || 'Fresh Meat' }}
                </span>
                <span
                    :class="[
                        'rounded-full px-2 py-0.5 font-semibold text-xs',
                        product.is_available
                            ? 'bg-emerald-50 text-emerald-700'
                            : 'bg-neutral-100 text-neutral-500'
                    ]"
                >
                    {{ product.is_available ? 'In Stock' : 'Sold Out Today' }}
                </span>
            </div>

            <!-- Title & Price -->
            <div class="mt-4">
                <h3 class="text-lg font-bold text-neutral-900">{{ product.name }}</h3>
                <p class="mt-1 text-xl font-extrabold text-neutral-900">
                    ₱{{ Number(product.price_per_kg).toFixed(2) }}
                    <span class="text-xs font-normal text-neutral-500">/ kg</span>
                </p>
                <p v-if="product.description" class="mt-1.5 text-xs text-neutral-500 line-clamp-2">
                    {{ product.description }}
                </p>
            </div>
        </div>

        <!-- Controls -->
        <div class="mt-5 space-y-3 border-t border-neutral-100 pt-4">
            <div v-if="product.is_available">
                <WeightSelector
                    v-model="selectedWeight"
                    :price-per-kg="Number(product.price_per_kg)"
                />

                <!-- Butchery Cutting Instructions Toggle -->
                <div class="mt-2.5">
                    <button
                        type="button"
                        @click="showNotesInput = !showNotesInput"
                        class="text-xs font-medium text-rose-600 hover:text-rose-700 underline cursor-pointer"
                    >
                        {{ showNotesInput ? '- Hide cut instructions' : '+ Add custom cut/prep note' }}
                    </button>
                    <input
                        v-if="showNotesInput"
                        v-model="cuttingNotes"
                        type="text"
                        placeholder="e.g. Debone, adobo cut, slice thinly..."
                        class="mt-1.5 w-full rounded-md border border-neutral-300 px-2.5 py-1 text-xs outline-hidden focus:border-rose-500"
                    />
                </div>
            </div>

            <!-- Action Button -->
            <button
                type="button"
                @click="addToCart"
                :disabled="!product.is_available || isSubmitting"
                class="w-full rounded-xl bg-rose-600 py-2.5 text-sm font-semibold text-white shadow-xs transition hover:bg-rose-700 disabled:cursor-not-allowed disabled:bg-neutral-200 disabled:text-neutral-500"
            >
                <span v-if="isSubmitting">Adding...</span>
                <span v-else-if="product.is_available">Add to Order</span>
                <span v-else>Sold Out</span>
            </button>
        </div>
    </article>
</template>
