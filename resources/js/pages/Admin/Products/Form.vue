<script setup lang="ts">
import { Head, useForm } from '@inertiajs/vue3';

interface Category {
    id: number;
    name: string;
}

interface Product {
    id: number;
    category_id: number;
    name: string;
    price_per_kg: string | number;
    is_available: boolean;
    description: string | null;
}

const props = defineProps<{
    categories: Category[];
    product?: Product | null;
}>();

const isEditing = !!props.product;

const form = useForm({
    category_id: props.product?.category_id || (props.categories[0]?.id ?? ''),
    name: props.product?.name || '',
    price_per_kg: props.product?.price_per_kg || '',
    description: props.product?.description || '',
    is_available: props.product ? props.product.is_available : true,
});

function submitForm() {
    if (isEditing && props.product) {
        form.put(`/admin/products/${props.product.id}`);
    } else {
        form.post('/admin/products');
    }
}
</script>

<template>
    <div class="min-h-screen bg-neutral-100 text-neutral-900 pb-16">
        <Head :title="isEditing ? `Edit ${product?.name} - Admin` : 'Add New Meat Cut - Admin'" />

        <header class="border-b border-neutral-200 bg-white py-4 shadow-xs">
            <div class="mx-auto flex max-w-2xl items-center justify-between px-4 sm:px-6">
                <a href="/admin/products" class="text-xs font-bold text-neutral-600 hover:text-rose-600">
                    ← Back to Meat Catalog
                </a>
                <span class="text-xs font-bold text-rose-600 uppercase">{{ isEditing ? 'Edit Product' : 'New Cut' }}</span>
            </div>
        </header>

        <main class="mx-auto mt-6 max-w-2xl px-4 sm:px-6">
            <form @submit.prevent="submitForm" class="rounded-2xl border border-neutral-200 bg-white p-6 sm:p-8 shadow-xs space-y-5">
                <h1 class="text-lg font-black text-neutral-900">
                    {{ isEditing ? `Edit Cut: ${product?.name}` : 'Add New Fresh Meat Cut' }}
                </h1>

                <div>
                    <label class="block text-xs font-semibold text-neutral-700">Category</label>
                    <select
                        v-model="form.category_id"
                        required
                        class="mt-1 w-full rounded-xl border border-neutral-300 px-3 py-2 text-sm outline-hidden focus:border-rose-500"
                    >
                        <option v-for="cat in categories" :key="cat.id" :value="cat.id">
                            {{ cat.name }}
                        </option>
                    </select>
                    <span v-if="form.errors.category_id" class="text-xs text-red-600">{{ form.errors.category_id }}</span>
                </div>

                <div>
                    <label class="block text-xs font-semibold text-neutral-700">Cut Name</label>
                    <input
                        v-model="form.name"
                        type="text"
                        required
                        placeholder="e.g. Pork Liempo / Belly"
                        class="mt-1 w-full rounded-xl border border-neutral-300 px-3 py-2 text-sm outline-hidden focus:border-rose-500"
                    />
                    <span v-if="form.errors.name" class="text-xs text-red-600">{{ form.errors.name }}</span>
                </div>

                <div>
                    <label class="block text-xs font-semibold text-neutral-700">Price per Kilogram (₱)</label>
                    <input
                        v-model="form.price_per_kg"
                        type="number"
                        step="0.01"
                        min="0.01"
                        required
                        placeholder="e.g. 380.00"
                        class="mt-1 w-full rounded-xl border border-neutral-300 px-3 py-2 text-sm outline-hidden focus:border-rose-500"
                    />
                    <span v-if="form.errors.price_per_kg" class="text-xs text-red-600">{{ form.errors.price_per_kg }}</span>
                </div>

                <div>
                    <label class="block text-xs font-semibold text-neutral-700">Description / Butchery Note</label>
                    <textarea
                        v-model="form.description"
                        rows="3"
                        placeholder="e.g. Tender pork cut ideal for roasting, grilling, or stewing."
                        class="mt-1 w-full rounded-xl border border-neutral-300 px-3 py-2 text-sm outline-hidden focus:border-rose-500"
                    ></textarea>
                </div>

                <div class="flex items-center gap-2 pt-2">
                    <input
                        id="is_available"
                        type="checkbox"
                        v-model="form.is_available"
                        class="h-4 w-4 rounded text-rose-600 focus:ring-rose-500"
                    />
                    <label for="is_available" class="text-xs font-semibold text-neutral-700 cursor-pointer">
                        Currently in stock & available today
                    </label>
                </div>

                <div class="pt-4 border-t border-neutral-100 flex justify-end gap-3">
                    <a
                        href="/admin/products"
                        class="rounded-xl border border-neutral-300 px-4 py-2 text-xs font-semibold text-neutral-700 hover:bg-neutral-50"
                    >
                        Cancel
                    </a>
                    <button
                        type="submit"
                        :disabled="form.processing"
                        class="rounded-xl bg-rose-600 px-5 py-2 text-xs font-bold text-white shadow-xs hover:bg-rose-700 disabled:opacity-50 cursor-pointer"
                    >
                        {{ form.processing ? 'Saving...' : (isEditing ? 'Update Cut' : 'Create Cut') }}
                    </button>
                </div>
            </form>
        </main>
    </div>
</template>
