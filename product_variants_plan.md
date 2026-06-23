# Final Action Plan: SGCart Product Variant Package

This document outlines the final architecture, database schema, RESTful routes, tab-based UI, storefront lookup flow, and installation operations for the plug-and-play **Product Variant Package** (`sgcart/variants`) for SGCart.

---

## 1. Package Structure & Directory Layout
The package is a modular standalone plugin located under `packages/sgcart/variants` and integrated using Laravel's autoloading capabilities.

```
packages/sgcart/variants/
├── composer.json
├── database/
│   └── migrations/
│       ├── 2026_06_22_000001_create_colors_and_sizes_tables.php
│       └── 2026_06_22_000002_create_product_variants_table.php
├── resources/
│   └── views/
│       ├── admin-menu.blade.php                 # Sidebar navigation hooks
│       ├── admin-product-variants-page.blade.php# Wrapper page for standalone variants view
│       ├── admin-product-variants-partial.blade.php # Reusable AJAX-loaded grid template
│       ├── colors/                              # Full Color CRUD views (index, create, edit)
│       ├── sizes/                               # Full Size CRUD views (index, create, edit)
│       └── storefront-variant-script.blade.php  # Storefront dependent filtering and JSON lookup
├── routes/
│   └── web.php                                  # Nested RESTful routes and quick CRUD endpoints
└── src/
    ├── Http/
    │   └── Controllers/
    │       ├── ColorController.php              # Full resource management for colors
    │       ├── SizeController.php               # Full resource management for sizes
    │       └── VariantController.php            # AJAX lookups, quick-stores, and product variant CRUD
    ├── Models/
    │   ├── Color.php                            # Color swatch codes and metadata
    │   ├── Size.php                             # Size labels and codes
    │   └── ProductVariant.php                   # Combinations, pricing, stock, overrides, and images
    └── Providers/
        └── VariantServiceProvider.php           # Service provider registering views, routes, and assets
```

---

## 2. Normalized Database Schema
No global state or type columns are stored on the parent `products` table. The configuration state is determined dynamically at runtime by checking associated variant records.

### Table A: `colors`
- `id` (Primary Key)
- `name` (Unique, e.g., `Burgundy`, `Mint Green`)
- `hex_code` (e.g., `#800020`, `#3EB489`)
- `timestamps`

### Table B: `sizes`
- `id` (Primary Key)
- `name` (e.g., `Small`, `Double Extra Large`)
- `code` (Unique display tag, e.g., `S`, `XXL`)
- `timestamps`

### Table C: `product_variants`
- `id` (Primary Key)
- `product_id` (Foreign Key linked to `products.id`, cascade on delete)
- `color_id` (Nullable Foreign Key linked to `colors.id`, set null or cascade)
- `size_id` (Nullable Foreign Key linked to `sizes.id`, set null or cascade)
- `sku` (Unique string, nullable override)
- `price` (Decimal 10,2, nullable override)
- `stock` (Integer, default `0`)
- `is_active` (Boolean, default `true`)
- `timestamps`

### Table D: `variant_images`
- `id` (Primary Key)
- `product_variant_id` (Foreign Key linked to `product_variants.id`, cascade on delete)
- `image_path` (String path for variant-specific uploads)
- `is_default` (Boolean, default `false` - indicates which variant photo is the default lookup display)
- `timestamps`

---

## 3. Industry-Standard Nested Routing (RESTful)
All variant management endpoints are nested under the parent product resources:

- **JSON Lookup API**: `GET /api/variants/lookup` (returns details like stock, formatted price, SKU, and default variant image URL by resolving the linked `variant_images` records).
- **Manage Views (GET)**: `GET /admin/products/{product_id}/variants` (resolves as AJAX request, returning the variants form fragment, or redirects standard browser GET requests to the unified tabbed page at `/admin/products/{product_id}/edit?tab=variants`).
- **Update Persistence (POST)**: `POST /admin/products/{product_id}/variants` (persists changes, deletes removed rows, uploads files, sums active stock, and redirects back to the active tab page).

---

## 4. Single-Page Tabbed Editor (Decoupled & Fast)
To prevent slow page loads and handle variant image galleries efficiently, the variant configuration grid is detached from the base product form:

### Add Product Page (`create.blade.php`)
- Displays unified navigation tabs at the top.
- The **Product Variants** tab is locked (disabled with a padlock icon and tooltip) because a product record must exist in the database before variations can be attached.
- The bottom of the form has no extra cards, keeping it clean and light.

### Edit Product Page (`edit.blade.php`)
- Displays navigation tabs: **Basic Details** and **Product Variants**.
- Clicking **Product Variants** toggles the details form hidden, displays a pulsing skeleton table loader mock, and triggers a fetch request to load the variants grid asynchronously.
- The state parameters (`?tab=variants`) are preserved in the URL history bar to persist the tab position across reloads or form submissions.

### The Variants Configuration Grid (`admin-product-variants-partial.blade.php`)
- **Grid Spreadsheet**: Configure SKU, custom pricing overrides, stock level, and status toggle for each variant.
- **Variant-Specific Gallery Modal**: Each variant row has a gallery icon button. Clicking it opens a modal containing a card-based multi-image manager (similar to the parent product's gallery) where the admin can drag-drop files, upload multiple variant-specific images, view existing uploads, delete individual items, and select a default display photo using radio buttons.
- **Client-Side Validation**: Grid validation prevents saving if duplicate combinations (e.g. two `Red + M` rows) are detected.
- **Quick Attribute Modals**: Modals for quick-adding new Colors and Sizes. Submitting adds them to database via AJAX and appends them to all active select dropdowns in the grid instantly.
- **Automatic Summation**: Saving variants automatically calculates total active inventory and updates the `products.stock` column in the database.

---

## 5. Storefront Selection Logic
- Uses `storefront-variant-script.blade.php` to fetch active options mapping.
- **Dependent Filtering**: Toggling a color dynamically hides invalid sizes from the DOM or switches options so that shoppers can only select active, matching size overrides. If color-only options have no size, the size selector fades out completely.
- Automatically handles fallback searches (`color_id IS NULL` or `size_id IS NULL`) for mixed configs.

---

## 6. Preserved CRUD Systems
- Dedicated resource controllers and views for **Color Swatches** and **Size Labels** are registered in the main admin dashboard sidebar, allowing full CRUD management for global swatches.

---

## 7. Package Autoloading & Automatic Installation (Mirroring Coupons Package)
To match the architecture pattern seen in `packages/sgcart/coupons`, the Variants package must run programmatically and register itself dynamically during bootstrap phase without requiring manual user intervention:

### Namespacing & PSR-4 Autoloading
The package is registered with `SGCart\Variants\` namespace in the parent `composer.json` or autoloaded as a vendor dependency.

### Automatic Service Provider Setup (`VariantServiceProvider.php`)
The service provider boots routes, migrations, views, and automated installers:
```php
<?php

namespace SGCart\Variants\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Artisan;

class VariantServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        // Register any package-specific services or helpers here
    }

    public function boot(): void
    {
        $this->loadRoutesFrom(__DIR__.'/../../routes/web.php');
        $this->loadMigrationsFrom(__DIR__.'/../../database/migrations');
        $this->loadViewsFrom(__DIR__.'/../../resources/views', 'variants');

        $this->autoInstall();
    }

    protected function autoInstall(): void
    {
        try {
            // Check connection and run package migrations dynamically if table is missing
            if (Schema::connection(null)->getConnection()->getPdo() && !Schema::hasTable('product_variants')) {
                Artisan::call('migrate', [
                    '--path' => 'packages/sgcart/variants/database/migrations',
                    '--force' => true
                ]);
            }

            // Seed Spatie manage permission for admin panel
            if (class_exists(\Spatie\Permission\Models\Permission::class)) {
                $permission = \Spatie\Permission\Models\Permission::firstOrCreate(['name' => 'manage variants', 'guard_name' => 'web']);
                $role = \Spatie\Permission\Models\Role::where('name', 'Super Admin')->first();
                if ($role && !$role->hasPermissionTo($permission)) {
                    $role->givePermissionTo($permission);
                }
            }
        } catch (\Exception $e) {
            // Silence exceptions during early boot phase
        }
    }
}
```
