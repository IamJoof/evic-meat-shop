# Evic Meat & Poultry Shop Implementation Plan

> **For agentic workers:** REQUIRED SUB-SKILL: Use superpowers:subagent-driven-development (recommended) or superpowers:executing-plans to implement this plan task-by-task. Steps use checkbox (`- [ ]`) syntax for tracking.

**Goal:** Build a complete, weight-based e-commerce storefront and butcher order fulfillment management system for Evic Meat & Poultry Shop using Laravel 12/13, Inertia.js v3, Vue 3, and Tailwind CSS v4.

**Architecture:** A monolithic Inertia.js v3 application pairing Laravel back-end models, services, and policies with Vue 3 single-page front-end views via Wayfinder typed routing. A dedicated `CartService` and weight-calculation engine ensures precision for decimal quantities (kg) and custom butchery cutting notes, with snapshot pricing on orders and a merchant fulfillment board.

**Tech Stack:** PHP 8.3, Laravel 13, Inertia.js v3, Vue 3, Tailwind CSS v4, Wayfinder, Fortify, Pest 4.

**Spec:** [`docs/superpowers/specs/2026-10-17-meat-shop-prd.md`](file:///c:/Users/techd/.gemini/antigravity/scratch/evic-meat-shop/docs/superpowers/specs/2026-10-17-meat-shop-prd.md)

---

## Global Constraints

- **Language & Framework Versions:** PHP 8.3+, Laravel 13, Inertia.js v3, Vue 3 with Composition API `<script setup>`.
- **Styling:** Tailwind CSS v4 utility classes; no custom vanilla CSS when Tailwind utilities exist.
- **Coding Standards:** Follow Laravel Pint (`vendor/bin/pint --dirty --format agent`). Always use curly braces on control structures, constructor property promotion, explicit return types and type hints.
- **Route Functions:** Use Wayfinder generated functions (`@/actions/` or `@/routes/`) instead of hardcoded route strings in frontend code.
- **Testing Standard:** All tests written in Pest (`php artisan test --compact`). TDD cycle (write failing test &rarr; verify fail &rarr; implement &rarr; verify pass &rarr; commit) for every task.
- **Pricing & Quantities:** All weights must be represented in kilograms as decimals (e.g., `0.50`, `1.25`, `2.00`). Prices stored as decimals with 2 decimal places (`8, 2`).

---

## Review Focus

1. **Zero or Negative Weight in Cart / Order:** A user inputs `0 kg` or `-1.5 kg` in the cart quantity selector. Expected: Rejected with validation error, minimum quantity is `0.1 kg`.
2. **Product Marked Out of Stock Mid-Checkout:** Item becomes `is_available = false` between adding to cart and clicking "Place Order". Expected: Checkout halts gracefully, highlights unavailable items, and blocks order creation.
3. **Invalid Historical Price Mutation:** A shop manager changes product price per kg after an order was already placed. Expected: The historical `order_items.price_per_kg` and `order_items.line_total` remain locked to the purchase-time value.
4. **Order Cancellation While Already in Preparation:** Customer attempts to cancel an order after status has transitioned to `preparing`. Expected: 403 Forbidden with user-facing message to contact shop directly to avoid meat spoilage.
5. **Delivery Date in the Past or Exceeding Allowed Horizon:** Customer submits checkout with an expected delivery date that is in the past or beyond the 7-day fresh booking horizon. Expected: Validation error rejecting the schedule date.

---

## Task Decomposition

### Task 1: Environment Setup & Core Dependency Scaffolding

**Files:**
- Modify: `.env.example`
- Create: `.env`
- Test: Artisan CLI verification

**Interfaces:**
- Consumes: System PHP 8.3, Composer, Node.js
- Produces: Working vendor autoload, SQLite database config, app key, and test environment

- [ ] **Step 1: Install Composer and NPM dependencies**
Run: `composer install --no-interaction` and `npm install`
Expected: Dependencies installed and `vendor/autoload.php` generated.

- [ ] **Step 2: Setup environment and database**
Create `.env` from `.env.example`, configure `DB_CONNECTION=sqlite` and `DB_DATABASE=database/database.sqlite`, touch `database/database.sqlite`.
Run: `php artisan key:generate --no-interaction`
Expected: `.env` populated with `APP_KEY`.

- [ ] **Step 3: Run existing baseline test suite**
Run: `php artisan test --compact`
Expected: Baseline starter tests run and pass.

- [ ] **Step 4: Commit**
```bash
git add .env.example composer.lock package.json package-lock.json
git commit -m "chore: initialize dependencies and environment configuration"
```

---

### Task 2: Database Schema & Migration Overhaul

**Files:**
- Modify: `database/migrations/2026_05_27_074630_create_categories_table.php`
- Modify: `database/migrations/2026_05_27_074638_create_products_table.php`
- Modify: `database/migrations/2026_06_08_121644_create_product_images_table.php`
- Modify: `database/migrations/2026_06_12_151201_create_carts_table.php`
- Modify: `database/migrations/2026_06_12_151211_create_orders_table.php`
- Create: `database/migrations/2026_06_12_151220_create_order_items_table.php`
- Test: `tests/Feature/DatabaseMigrationTest.php`

**Interfaces:**
- Consumes: Blueprint schema definitions
- Produces: Correct table structures for `categories`, `products`, `product_images`, `carts`, `cart_items`, `orders`, `order_items`, `payments`

- [ ] **Step 1: Write the failing migration test**
```php
// tests/Feature/DatabaseMigrationTest.php
test('all meat shop tables and columns migrate successfully', function () {
    expect(Schema::hasColumns('categories', ['id', 'name', 'slug', 'description']))->toBeTrue();
    expect(Schema::hasColumns('products', ['id', 'category_id', 'name', 'slug', 'price_per_kg', 'is_available', 'description']))->toBeTrue();
    expect(Schema::hasColumns('cart_items', ['id', 'cart_id', 'product_id', 'weight_kg', 'cutting_notes']))->toBeTrue();
    expect(Schema::hasColumns('orders', ['id', 'user_id', 'order_number', 'customer_name', 'customer_phone', 'customer_address', 'fulfillment_type', 'expected_delivery_date', 'subtotal', 'delivery_fee', 'total_amount', 'status']))->toBeTrue();
    expect(Schema::hasColumns('order_items', ['id', 'order_id', 'product_id', 'product_name', 'price_per_kg', 'weight_kg', 'line_total', 'cutting_notes']))->toBeTrue();
    expect(Schema::hasColumns('payments', ['id', 'order_id', 'amount', 'option', 'status', 'transaction_reference']))->toBeTrue();
});
```

- [ ] **Step 2: Run test to verify it fails**
Run: `php artisan test --compact --filter=DatabaseMigrationTest`
Expected: FAIL (missing columns and tables).

- [ ] **Step 3: Update migrations to match specification**
  - Fix `create_carts_table.php`: create `carts` first, then `cart_items` with `cart_id` foreign key, `decimal('weight_kg', 8, 3)`, and nullable `string('cutting_notes')`.
  - Fix `create_orders_table.php`: add `user_id` (nullable), `order_number`, customer contact/address columns, `fulfillment_type`, decimal financial totals, and proper status enums (`pending`, `preparing`, `shipped`, `received`, `cancelled`).
  - Create `create_order_items_table.php`: `order_id`, `product_id`, `product_name`, `price_per_kg`, `weight_kg`, `line_total`, `cutting_notes`.
  - Fix `payments`: add `order_id` foreign key, `amount`, `transaction_reference`.

- [ ] **Step 4: Run migration and verify test passes**
Run: `php artisan migrate:fresh --compact`
Run: `php artisan test --compact --filter=DatabaseMigrationTest`
Expected: PASS.

- [ ] **Step 5: Commit**
```bash
git add database/migrations/ tests/Feature/DatabaseMigrationTest.php
git commit -m "feat(database): overhaul schema for meat shop orders, carts, and products"
```

---

### Task 3: Eloquent Models, Relationships, and Factories

**Files:**
- Modify: `app/Models/Category.php`
- Modify: `app/Models/Product.php`
- Modify: `app/Models/Cart.php`
- Modify: `app/Models/Order.php`
- Create: `app/Models/ProductImage.php`
- Create: `app/Models/CartItem.php`
- Create: `app/Models/OrderItem.php`
- Create: `app/Models/Payment.php`
- Create/Modify: Factories for all models in `database/factories/`
- Test: `tests/Unit/ModelsTest.php`

**Interfaces:**
- Consumes: Database schema from Task 2
- Produces: Complete Eloquent relationships (`Product->category()`, `Product->images()`, `Cart->items()`, `Order->items()`, `Order->payment()`) and model factories for test setup

- [ ] **Step 1: Write failing models and relationships unit test**
```php
// tests/Unit/ModelsTest.php
test('models associate correctly through eloquent relationships', function () {
    $category = Category::factory()->create();
    $product = Product::factory()->for($category)->create(['price_per_kg' => 350.00]);
    $cart = Cart::factory()->create();
    $cartItem = CartItem::factory()->create([
        'cart_id' => $cart->id,
        'product_id' => $product->id,
        'weight_kg' => 1.5,
        'cutting_notes' => 'Chop into 8 pieces',
    ]);
    $order = Order::factory()->create(['user_id' => null]);
    $orderItem = OrderItem::factory()->create([
        'order_id' => $order->id,
        'product_id' => $product->id,
        'price_per_kg' => 350.00,
        'weight_kg' => 1.5,
        'line_total' => 525.00,
    ]);
    $payment = Payment::factory()->create(['order_id' => $order->id]);

    expect($product->category->id)->toBe($category->id);
    expect($cart->items)->toHaveCount(1);
    expect($cart->items->first()->product->id)->toBe($product->id);
    expect($order->items)->toHaveCount(1);
    expect($order->payment->id)->toBe($payment->id);
});
```

- [ ] **Step 2: Run test to verify it fails**
Run: `php artisan test --compact --filter=ModelsTest`
Expected: FAIL.

- [ ] **Step 3: Implement Eloquent Models, casts, fillables, and Factories**
  - Add `hasMany(CartItem::class)`, `belongsTo(Cart::class)`, `hasMany(OrderItem::class)`, `belongsTo(Order::class)`, `hasOne(Payment::class)`.
  - Add casting for `price_per_kg => 'decimal:2'`, `weight_kg => 'decimal:3'`, `line_total => 'decimal:2'`, `is_available => 'boolean'`.
  - Implement factories with sensible fake data (`fake()->randomFloat(2, 100, 800)`, etc.).

- [ ] **Step 4: Run test to verify it passes**
Run: `php artisan test --compact --filter=ModelsTest`
Expected: PASS.

- [ ] **Step 5: Commit**
```bash
git add app/Models/ database/factories/ tests/Unit/ModelsTest.php
git commit -m "feat(models): implement meat shop models, relationships, and factories"
```

---

### Task 4: Weight-Based Pricing & CartService

**Files:**
- Create: `app/Services/CartService.php`
- Create: `app/Http/Controllers/CartController.php`
- Modify: `routes/web.php`
- Test: `tests/Feature/CartServiceTest.php`

**Interfaces:**
- Consumes: `Product`, `Cart`, `CartItem`
- Produces:
  - `CartService::getOrCreateCart(?User $user, ?string $sessionId): Cart`
  - `CartService::addItem(Cart $cart, int $productId, float $weightKg, ?string $cuttingNotes): CartItem`
  - `CartService::updateItemWeight(CartItem $item, float $weightKg): CartItem`
  - `CartService::calculateSubtotal(Cart $cart): float`

- [ ] **Step 1: Write failing cart calculation and service tests**
```php
// tests/Feature/CartServiceTest.php
test('cart calculates accurate subtotal for decimal weights', function () {
    $cart = Cart::factory()->create();
    $beef = Product::factory()->create(['price_per_kg' => 400.00, 'is_available' => true]);
    $chicken = Product::factory()->create(['price_per_kg' => 220.00, 'is_available' => true]);

    $service = app(CartService::class);
    $service->addItem($cart, $beef->id, 1.5, 'Thinly sliced for yakiniku'); // 400 * 1.5 = 600
    $service->addItem($cart, $chicken->id, 0.75, 'Adobo cuts'); // 220 * 0.75 = 165

    expect($service->calculateSubtotal($cart))->toEqual(765.00);
});

test('cart rejects non-positive weights', function () {
    $cart = Cart::factory()->create();
    $product = Product::factory()->create(['price_per_kg' => 200.00]);
    $service = app(CartService::class);

    $this->expectException(InvalidArgumentException::class);
    $service->addItem($cart, $product->id, 0.0);
});
```

- [ ] **Step 2: Run test to verify it fails**
Run: `php artisan test --compact --filter=CartServiceTest`
Expected: FAIL.

- [ ] **Step 3: Implement `CartService` and `CartController`**
  - Implement decimal rounding `round($item->weight_kg * $item->product->price_per_kg, 2)`.
  - Add validation: `weight_kg` must be `>= 0.1` and `<= 50.0`.
  - Add CartController endpoints: `GET /cart`, `POST /cart/items`, `PATCH /cart/items/{id}`, `DELETE /cart/items/{id}`.

- [ ] **Step 4: Run tests to verify they pass**
Run: `php artisan test --compact --filter=CartServiceTest`
Expected: PASS.

- [ ] **Step 5: Commit**
```bash
git add app/Services/CartService.php app/Http/Controllers/CartController.php routes/web.php tests/Feature/CartServiceTest.php
git commit -m "feat(cart): implement decimal weight calculation engine and cart service"
```

---

### Task 5: Storefront Catalog Browsing & Availability

**Files:**
- Create: `app/Http/Controllers/StorefrontController.php`
- Modify: `routes/web.php`
- Test: `tests/Feature/StorefrontTest.php`

**Interfaces:**
- Consumes: `Category`, `Product` with scope `is_available`
- Produces: Inertia props: `categories`, `products` (paginated, filtered by category and search keyword)

- [ ] **Step 1: Write failing storefront browsing tests**
```php
// tests/Feature/StorefrontTest.php
test('storefront lists available products and filters by category', function () {
    $poultry = Category::factory()->create(['name' => 'Poultry', 'slug' => 'poultry']);
    $pork = Category::factory()->create(['name' => 'Pork', 'slug' => 'pork']);

    $chicken = Product::factory()->for($poultry)->create(['name' => 'Whole Dressed Chicken', 'is_available' => true]);
    $ribs = Product::factory()->for($pork)->create(['name' => 'Pork Spareribs', 'is_available' => true]);

    $response = $this->get(route('storefront.index', ['category' => 'poultry']));

    $response->assertOk();
    $response->assertInertia(fn ($page) => $page
        ->component('Storefront/Index')
        ->has('products.data', 1)
        ->where('products.data.0.name', 'Whole Dressed Chicken')
    );
});
```

- [ ] **Step 2: Run test to verify it fails**
Run: `php artisan test --compact --filter=StorefrontTest`
Expected: FAIL.

- [ ] **Step 3: Implement `StorefrontController` and route**
  - Query products eager-loading `category` and `images`.
  - Support query params: `category` (slug filter), `search` (name search).
  - Return `Inertia::render('Storefront/Index', [...])`.

- [ ] **Step 4: Run test to verify it passes**
Run: `php artisan test --compact --filter=StorefrontTest`
Expected: PASS.

- [ ] **Step 5: Commit**
```bash
git add app/Http/Controllers/StorefrontController.php routes/web.php tests/Feature/StorefrontTest.php
git commit -m "feat(storefront): add categorized catalog browsing and search"
```

---

### Task 6: Checkout Flow & Snapshot Order Creation

**Files:**
- Create: `app/Http/Controllers/CheckoutController.php`
- Create: `app/Http/Requests/StoreOrderRequest.php`
- Modify: `routes/web.php`
- Test: `tests/Feature/CheckoutTest.php`

**Interfaces:**
- Consumes: Active `Cart` with items, customer details, delivery schedule, payment option
- Produces: `Order` with `order_items` snapshot and initial `Payment` record

- [ ] **Step 1: Write failing checkout and order snapshot test**
```php
// tests/Feature/CheckoutTest.php
test('checkout converts cart items into orders with immutable price snapshots', function () {
    $cart = Cart::factory()->create();
    $product = Product::factory()->create(['name' => 'Pork Belly', 'price_per_kg' => 380.00, 'is_available' => true]);
    $cartItem = CartItem::factory()->create([
        'cart_id' => $cart->id,
        'product_id' => $product->id,
        'weight_kg' => 2.0,
        'cutting_notes' => 'Sliced for samgyupsal',
    ]);

    $payload = [
        'customer_name' => 'Juan Dela Cruz',
        'customer_phone' => '09171234567',
        'customer_address' => '123 Meat St, Brgy Fresh',
        'fulfillment_type' => 'delivery',
        'expected_delivery_date' => now()->addDay()->format('Y-m-d H:i:s'),
        'payment_option' => 'COD',
        'notes' => 'Leave at front gate',
    ];

    $response = $this->withSession(['cart_id' => $cart->id])->post(route('checkout.store'), $payload);

    $response->assertRedirect();
    $order = Order::latest()->first();
    expect($order)->not->toBeNull();
    expect($order->total_amount)->toEqual(760.00);
    expect($order->status)->toBe('pending');
    expect($order->items)->toHaveCount(1);
    expect($order->items->first()->price_per_kg)->toEqual(380.00);

    // Verify mutating the original product price does not alter historical order line total
    $product->update(['price_per_kg' => 450.00]);
    expect($order->items->first()->fresh()->price_per_kg)->toEqual(380.00);
});

test('checkout fails if an item in the cart is sold out', function () {
    $cart = Cart::factory()->create();
    $unavailable = Product::factory()->create(['is_available' => false]);
    CartItem::factory()->create(['cart_id' => $cart->id, 'product_id' => $unavailable->id, 'weight_kg' => 1.0]);

    $response = $this->withSession(['cart_id' => $cart->id])->post(route('checkout.store'), [
        'customer_name' => 'Maria',
        'customer_phone' => '09170000000',
        'customer_address' => 'Address',
        'fulfillment_type' => 'pickup',
        'expected_delivery_date' => now()->addDay()->format('Y-m-d H:i:s'),
        'payment_option' => 'COD',
    ]);

    $response->assertSessionHasErrors('cart');
});
```

- [ ] **Step 2: Run test to verify it fails**
Run: `php artisan test --compact --filter=CheckoutTest`
Expected: FAIL.

- [ ] **Step 3: Implement `StoreOrderRequest` and `CheckoutController`**
  - Validate required customer fields and ensure `expected_delivery_date` is after `now()`.
  - Check that all items in cart are `is_available = true`.
  - Wrap order creation in DB transaction:
    1. Create `Order` with generated `order_number` (e.g. `EVIC-YYYYMMDD-XXXX`).
    2. Snapshot each `cart_item` into `order_items` with current `price_per_kg` and calculated `line_total`.
    3. Create initial `Payment` record with status `pending`.
    4. Clear the cart.
  - Redirect to `orders.show` (Order Confirmation / Tracking).

- [ ] **Step 4: Run test to verify it passes**
Run: `php artisan test --compact --filter=CheckoutTest`
Expected: PASS.

- [ ] **Step 5: Commit**
```bash
git add app/Http/Controllers/CheckoutController.php app/Http/Requests/StoreOrderRequest.php routes/web.php tests/Feature/CheckoutTest.php
git commit -m "feat(checkout): implement order creation with price snapshotting and inventory checks"
```

---

### Task 7: Customer Order Tracking & Cancellation Rules

**Files:**
- Create: `app/Http/Controllers/CustomerOrderController.php`
- Modify: `routes/web.php`
- Test: `tests/Feature/CustomerOrderTest.php`

**Interfaces:**
- Consumes: `order_number` or authenticated `User`
- Produces: Order status view and cancellation handler

- [ ] **Step 1: Write failing customer order tracking and cancellation test**
```php
// tests/Feature/CustomerOrderTest.php
test('customer can view order status timeline', function () {
    $order = Order::factory()->create(['status' => 'preparing']);

    $response = $this->get(route('orders.show', $order->order_number));

    $response->assertOk();
    $response->assertInertia(fn ($page) => $page
        ->component('Storefront/OrderSuccess')
        ->where('order.order_number', $order->order_number)
        ->where('order.status', 'preparing')
    );
});

test('customer can cancel pending order but cannot cancel preparing order', function () {
    $pendingOrder = Order::factory()->create(['status' => 'pending']);
    $preparingOrder = Order::factory()->create(['status' => 'preparing']);

    $this->post(route('orders.cancel', $pendingOrder->order_number))
        ->assertRedirect();
    expect($pendingOrder->fresh()->status)->toBe('cancelled');

    $this->post(route('orders.cancel', $preparingOrder->order_number))
        ->assertForbidden();
    expect($preparingOrder->fresh()->status)->toBe('preparing');
});
```

- [ ] **Step 2: Run test to verify it fails**
Run: `php artisan test --compact --filter=CustomerOrderTest`
Expected: FAIL.

- [ ] **Step 3: Implement `CustomerOrderController`**
  - Implement `show(string $orderNumber)` rendering Inertia view with timeline.
  - Implement `cancel(string $orderNumber)`: abort 403 if `status !== 'pending'`, else set `status = 'cancelled'` and update payment status to `cancelled`.

- [ ] **Step 4: Run test to verify it passes**
Run: `php artisan test --compact --filter=CustomerOrderTest`
Expected: PASS.

- [ ] **Step 5: Commit**
```bash
git add app/Http/Controllers/CustomerOrderController.php routes/web.php tests/Feature/CustomerOrderTest.php
git commit -m "feat(orders): implement customer order tracking and status cancellation guard"
```

---

### Task 8: Admin Product Catalog & Inventory Management

**Files:**
- Create: `app/Http/Controllers/Admin/ProductController.php`
- Create: `app/Http/Requests/Admin/StoreProductRequest.php`
- Modify: `routes/web.php`
- Test: `tests/Feature/Admin/ProductManagementTest.php`

**Interfaces:**
- Consumes: Authenticated admin request
- Produces: Product CRUD, image uploads, one-click `is_available` toggle

- [ ] **Step 1: Write failing admin product management tests**
```php
// tests/Feature/Admin/ProductManagementTest.php
test('admin can create product and toggle daily stock availability', function () {
    $admin = User::factory()->create(['role' => 'admin']);
    $category = Category::factory()->create();

    $this->actingAs($admin)->post(route('admin.products.store'), [
        'category_id' => $category->id,
        'name' => 'Pork Chops',
        'price_per_kg' => 320.00,
        'description' => 'Bone-in loin chops',
        'is_available' => true,
    ])->assertRedirect();

    $product = Product::where('name', 'Pork Chops')->first();
    expect($product)->not->toBeNull();

    // Toggle out-of-stock
    $this->actingAs($admin)->patch(route('admin.products.toggle-availability', $product->id))
        ->assertOk();
    expect($product->fresh()->is_available)->toBeFalse();
});
```

- [ ] **Step 2: Run test to verify it fails**
Run: `php artisan test --compact --filter=ProductManagementTest`
Expected: FAIL.

- [ ] **Step 3: Implement admin ProductController and routes**
  - Add middleware protecting `/admin/*`.
  - Implement CRUD: `index`, `store`, `update`, `destroy`, `toggleAvailability`.

- [ ] **Step 4: Run test to verify it passes**
Run: `php artisan test --compact --filter=ProductManagementTest`
Expected: PASS.

- [ ] **Step 5: Commit**
```bash
git add app/Http/Controllers/Admin/ProductController.php app/Http/Requests/Admin/ routes/web.php tests/Feature/Admin/ProductManagementTest.php
git commit -m "feat(admin): implement product catalog management and stock availability toggle"
```

---

### Task 9: Admin Order Processing Board & Butcher Fulfillment

**Files:**
- Create: `app/Http/Controllers/Admin/OrderController.php`
- Modify: `routes/web.php`
- Test: `tests/Feature/Admin/OrderFulfillmentTest.php`

**Interfaces:**
- Consumes: Authenticated admin request, order status transitions
- Produces: Order fulfillment dashboard, status progression (`pending` &rarr; `preparing` &rarr; `shipped` &rarr; `received`), butcher prep slip endpoint

- [ ] **Step 1: Write failing admin order fulfillment tests**
```php
// tests/Feature/Admin/OrderFulfillmentTest.php
test('admin can transition order through fulfillment lifecycle and mark payment paid', function () {
    $admin = User::factory()->create(['role' => 'admin']);
    $order = Order::factory()->create(['status' => 'pending']);
    $payment = Payment::factory()->create(['order_id' => $order->id, 'status' => 'pending']);

    // Butcher starts preparing
    $this->actingAs($admin)->patch(route('admin.orders.update-status', $order->id), [
        'status' => 'preparing',
    ])->assertOk();
    expect($order->fresh()->status)->toBe('preparing');

    // Handed over to courier
    $this->actingAs($admin)->patch(route('admin.orders.update-status', $order->id), [
        'status' => 'shipped',
    ])->assertOk();
    expect($order->fresh()->status)->toBe('shipped');

    // Delivered & paid
    $this->actingAs($admin)->patch(route('admin.orders.update-status', $order->id), [
        'status' => 'received',
    ])->assertOk();
    expect($order->fresh()->status)->toBe('received');
    expect($payment->fresh()->status)->toBe('paid');
});
```

- [ ] **Step 2: Run test to verify it fails**
Run: `php artisan test --compact --filter=OrderFulfillmentTest`
Expected: FAIL.

- [ ] **Step 3: Implement admin OrderController and status lifecycle**
  - Implement status update transitions with validation.
  - When status transitions to `received`, automatically mark linked COD payment as `paid` and record `actual_delivery_date = now()`.
  - Add butcher prep slip view (`/admin/orders/{id}/slip`) showing items, exact weights, customer cutting notes, and delivery time slot.

- [ ] **Step 4: Run test to verify it passes**
Run: `php artisan test --compact --filter=OrderFulfillmentTest`
Expected: PASS.

- [ ] **Step 5: Commit**
```bash
git add app/Http/Controllers/Admin/OrderController.php routes/web.php tests/Feature/Admin/OrderFulfillmentTest.php
git commit -m "feat(admin): implement order fulfillment lifecycle and butcher prep slips"
```

---

### Task 10: Storefront Vue UI (Catalog, Weight Selector, Cart Drawer, Checkout)

**Files:**
- Create: `resources/js/components/Storefront/WeightSelector.vue`
- Create: `resources/js/components/Storefront/ProductCard.vue`
- Create: `resources/js/components/Storefront/CartDrawer.vue`
- Create: `resources/js/pages/Storefront/Index.vue`
- Create: `resources/js/pages/Storefront/Checkout.vue`
- Create: `resources/js/pages/Storefront/OrderSuccess.vue`

**Interfaces:**
- Consumes: Inertia props from `StorefrontController`, `CartController`, `CheckoutController`
- Produces: Responsive client-side shopping experience with Wayfinder action calls (`storefront.index`, `cart.store`, `checkout.store`)

- [ ] **Step 1: Create `WeightSelector.vue` component**
  - Decimal quantity buttons: `-`, `+` in `0.5 kg` or `0.25 kg` increments, or manual numeric typing.
  - Dynamic display: computed price based on `price_per_kg * selectedWeight`.

- [ ] **Step 2: Create `ProductCard.vue` with cut customization notes**
  - Freshness/Availability badge (`Fresh Arrival`, `Available`, `Sold Out Today`).
  - Modal or accordion for cutting notes (e.g. "Adobo size", "Deboned", "Skinless", "Thin sliced").

- [ ] **Step 3: Create `CartDrawer.vue`**
  - Slide-out drawer with list of items, weights, cutting notes, and subtotal.
  - Direct checkout button.

- [ ] **Step 4: Create `Checkout.vue` and `OrderSuccess.vue`**
  - Date & delivery time slot picker (restricting past dates).
  - COD vs Online payment radio toggles.
  - Live timeline component on `OrderSuccess.vue` reflecting `order.status`.

- [ ] **Step 5: Verify build succeeds**
Run: `npm run build`
Expected: Vite builds assets with 0 errors.

- [ ] **Step 6: Commit**
```bash
git add resources/js/
git commit -m "feat(ui): build storefront catalog, weight selector, and checkout Vue pages"
```

---

### Task 11: Admin Back-Office Vue UI (Fulfillment Board & Product Management)

**Files:**
- Create: `resources/js/pages/Admin/Dashboard.vue`
- Create: `resources/js/pages/Admin/Orders/Index.vue`
- Create: `resources/js/pages/Admin/Orders/Show.vue`
- Create: `resources/js/pages/Admin/Products/Index.vue`
- Create: `resources/js/pages/Admin/Products/Form.vue`

**Interfaces:**
- Consumes: Admin controller props
- Produces: Back-office management views for staff and butchers

- [ ] **Step 1: Create Admin Orders Fulfillment Board**
  - Filterable order list by status (`Pending`, `Preparing`, `Shipped`, `Received`, `Cancelled`).
  - Quick action buttons to advance status with single click.
  - Printable butcher prep slip view with print CSS styling.

- [ ] **Step 2: Create Admin Product & Stock Inventory Management**
  - Product catalog table with quick toggle switch for `is_available`.
  - Form to add new cuts, assign categories, and update price per kg.

- [ ] **Step 3: Run full style formatter and tests**
Run: `vendor/bin/pint --dirty --format agent`
Run: `php artisan test --compact`
Run: `npm run build`
Expected: All tests pass, styling passes, Vite build succeeds.

- [ ] **Step 4: Commit**
```bash
git add resources/js/pages/Admin/
git commit -m "feat(admin-ui): create admin order fulfillment board and product management views"
```

---

## Self-Review Checklist

- **Spec Coverage:**
  - Weight-based pricing & decimal quantities: Handled in Task 2, Task 3, Task 4, Task 10.
  - Custom butchery & cutting notes: Handled in Task 2, Task 3, Task 4, Task 10, Task 11.
  - Delivery scheduling & time windows: Handled in Task 2, Task 6, Task 10.
  - Catch-weight and price snapshotting: Handled in Task 2, Task 3, Task 6.
  - Order cancellation guard (forbidden after `pending`): Handled in Task 7.
  - Admin stock availability toggle: Handled in Task 8, Task 11.
  - Admin fulfillment lifecycle & butcher slip: Handled in Task 9, Task 11.
- **Review Focus Coverage:**
  - Non-positive weights: Tested in Task 4 (`CartServiceTest`).
  - Sold-out mid-checkout: Tested in Task 6 (`CheckoutTest`).
  - Historical price lock: Tested in Task 6 (`CheckoutTest`).
  - Cancellation past pending: Tested in Task 7 (`CustomerOrderTest`).
  - Scheduled date horizon: Tested in Task 6 (`CheckoutTest`).
- **Type & Interface Consistency:** All methods, models, and routes adhere to consistent naming (`price_per_kg`, `weight_kg`, `cutting_notes`, `order_number`, `is_available`).
