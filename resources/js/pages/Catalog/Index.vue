<script setup lang="ts">
import { Head } from '@inertiajs/vue3';

interface Product {
    id: number;
    name: string;
    image_path: string | null;
    price_per_kg: number | string;
    is_available: boolean;
    category_name: string;
}

interface Props {
    products?: Product[];
    categories?: { name: string, products: Product[] }[];
}

const props = defineProps<Props>();

// If categories are not provided, group products into a default category to maintain layout
const displayCategories = props.categories ?? [{ name: 'All Products', products: props.products ?? [] }];
</script>

<template>
    <Head title="Fresh Cuts Catalog" />

    <div class="fresh-cuts-wrapper">
        <div class="fresh-cuts-container">
            <h1 class="page-title">Fresh Cuts</h1>

            <div v-for="category in displayCategories" :key="category.name" class="category-section">
                <!-- Category Heading -->
                <h2 class="category-title">{{ category.name }}</h2>

                <!-- Grid of Products for this Category -->
                <div class="product-grid">
                    <div v-for="product in category.products" :key="product.id" class="product-card">
                        
                        <!-- Product Image (Add your img tag here later) -->
                        <div class="product-image-placeholder">
                            Image Placeholder
                        </div>

                        <div class="product-details">
                            <h3 class="product-name">{{ product.name }}</h3>
                            
                            <p class="product-price">
                                ₱{{ Number(product.price_per_kg).toFixed(2) }} 
                                <span class="price-unit">/ kg</span>
                            </p>
                            
                            <!-- Optional call to action -->
                            <button class="btn-add-cart">Add to Cart</button>
                        </div>
                        
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<style>
@import '../../../css/styles.css';
</style>
