<?php

namespace Database\Seeders;

use App\Models\Category;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $categoriesData = [
            [
                'name' => 'Fresh Pork',
                'slug' => 'fresh-pork',
                'description' => 'Locally sourced fresh prime pork cuts, delivered cold daily.',
                'products' => [
                    ['name' => 'Pork Belly (Liempo)', 'slug' => 'pork-belly-liempo', 'price_per_kg' => 380.00, 'description' => 'Juicy, marbled pork belly ideal for lechon kawali, grilling, or samgyupsal.'],
                    ['name' => 'Pork Chop (Bone-In)', 'slug' => 'pork-chop-bone-in', 'price_per_kg' => 340.00, 'description' => 'Tender center-cut chops perfect for pan-frying or roasting.'],
                    ['name' => 'Pork Kasim (Shoulder)', 'slug' => 'pork-kasim-shoulder', 'price_per_kg' => 330.00, 'description' => 'Lean and flavorful shoulder cut best for adobo, sinigang, and menudo.'],
                    ['name' => 'Ground Pork (Giniling)', 'slug' => 'ground-pork-giniling', 'price_per_kg' => 320.00, 'description' => 'Freshly ground 80/20 lean-to-fat ratio pork for meatballs and lumpia.'],
                ],
            ],
            [
                'name' => 'Fresh Beef',
                'slug' => 'fresh-beef',
                'description' => 'Grass-fed and grain-finished quality beef cuts.',
                'products' => [
                    ['name' => 'Beef Shank (Bulalo Cut)', 'slug' => 'beef-shank-bulalo', 'price_per_kg' => 450.00, 'description' => 'Bone-in beef shank with rich marrow for comforting soups and nilaga.'],
                    ['name' => 'Beef Sirloin Steak', 'slug' => 'beef-sirloin-steak', 'price_per_kg' => 520.00, 'description' => 'Prime cut sirloin steaks sliced to your preferred butcher thickness.'],
                    ['name' => 'Beef Brisket', 'slug' => 'beef-brisket', 'price_per_kg' => 420.00, 'description' => 'Well-marbled brisket great for slow-cooking, pot roast, or corned beef.'],
                    ['name' => 'Ground Beef (Lean)', 'slug' => 'ground-beef-lean', 'price_per_kg' => 410.00, 'description' => '90% lean freshly minced beef for burger patties and pasta sauces.'],
                ],
            ],
            [
                'name' => 'Poultry & Chicken',
                'slug' => 'poultry-chicken',
                'description' => 'Farm-fresh dressed chicken and poultry cuts.',
                'products' => [
                    ['name' => 'Whole Dressed Chicken', 'slug' => 'whole-dressed-chicken', 'price_per_kg' => 210.00, 'description' => 'Cleaned whole chicken ready for whole roasting or traditional tinola.'],
                    ['name' => 'Chicken Breast Fillet', 'slug' => 'chicken-breast-fillet', 'price_per_kg' => 280.00, 'description' => 'Boneless, skinless high-protein chicken breast for grilling and meal prep.'],
                    ['name' => 'Chicken Thighs & Drumsticks', 'slug' => 'chicken-thighs-drumsticks', 'price_per_kg' => 230.00, 'description' => 'Juicy dark meat ideal for crispy fried chicken or rich stews.'],
                    ['name' => 'Chicken Wings', 'slug' => 'chicken-wings', 'price_per_kg' => 240.00, 'description' => 'Fresh mid-joints and drumettes for buffalo and honey-garlic wings.'],
                ],
            ],
            [
                'name' => 'Specialty & Marinated',
                'slug' => 'specialty-marinated',
                'description' => 'Pre-seasoned, house-specialty meats ready to cook.',
                'products' => [
                    ['name' => 'Homemade Pork Tocino', 'slug' => 'homemade-pork-tocino', 'price_per_kg' => 360.00, 'description' => 'Sweet cured tender pork slices with no artificial preservatives.'],
                    ['name' => 'Skinless Pork Longganisa', 'slug' => 'skinless-pork-longganisa', 'price_per_kg' => 340.00, 'description' => 'Garlicky native sausage links for breakfast silog platters.'],
                ],
            ],
        ];

        foreach ($categoriesData as $catData) {
            $products = $catData['products'];
            unset($catData['products']);

            $category = Category::firstOrCreate(['slug' => $catData['slug']], $catData);

            foreach ($products as $prod) {
                $category->products()->firstOrCreate(
                    ['slug' => $prod['slug']],
                    array_merge($prod, ['is_available' => true])
                );
            }
        }
    }
}
