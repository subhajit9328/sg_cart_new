# Simple & Integration-Ready SGCart Inventory Management Plan

This document outlines the architecture, database layout, integration hooks, and UI screens for a **simple, entry-level Inventory Management System (IMS)** package (`sgcart/inventory`) for SGCart. 

It is designed to check if the Product Variants package is installed and adapt dynamically, while disabling direct stock editing on base products and variants, forcing all stock adjustments to go through the inventory ledger for full auditing.

---

## 1. Package Directory Layout
```
packages/sgcart/inventory/
├── composer.json
├── database/
│   └── migrations/
│       └── 2026_06_29_000001_create_inventory_logs_table.php
├── resources/
│   └── views/
│       ├── admin-menu.blade.php                   # Sidebar navigation link
│       ├── index.blade.php                        # Master inventory grid (reads products & variants)
│       ├── adjust.blade.php                       # Manual Add/Deduct Stock entry form section
│       └── logs.blade.php                         # Simple history audit trail view
├── routes/
│   └── web.php                                    # Resource routes
└── src/
    ├── Http/
    │   └── Controllers/
    │       └── InventoryController.php            # Core UI Actions (index, show adjustment form, post adjustment, logs)
    ├── Listeners/
    │   ├── DeductStockOnOrder.php                 # Listens to order checkouts to deduct stock
    │   └── RestoreStockOnCancel.php               # Listens to order cancellations to restore stock
    ├── Models/
    │   └── InventoryLog.php                       # Keeps track of all stock adjustments
    └── Providers/
        ├── InventoryServiceProvider.php           # Registers migrations, routes, views
        └── EventServiceProvider.php               # Registers order events and listeners
```

---

## 2. Minimal Database Schema
We leverage the existing `stock` columns on the `products` and `product_variants` tables. We introduce only a single database table to audit stock history.

### Table: `inventory_logs` (Stock Movement History)
Tracks every manual or automated addition or reduction of stock.
- `id` (PK, BigInt, AutoIncrement)
- `product_id` (FK to `products.id`, Cascade on delete)
- `product_variant_id` (FK to `product_variants.id`, Nullable, Cascade on delete)
- `quantity` (Integer - e.g., `+10` for stock in, `-3` for sales or damage)
- `action` (String - e.g., `manual_adjustment`, `order_sale`, `order_refund`)
- `reason` (String, Nullable - e.g., "Received shipment", "Damaged unit", "Order Checkout #1005")
- `user_id` (FK to `users.id`, Nullable - Admin user who made the manual adjustment)
- `before_stock` (Integer)
- `after_stock` (Integer)
- `created_at` (Timestamp)

---

## 3. Dynamic Variant Package Integration Check
The package adapts its behaviors depending on whether the `sgcart/product-variants` package is active:

1. **Class Existence Helper**:
   At runtime, we check `class_exists(\SGCart\ProductVariants\Models\ProductVariant::class)`.
2. **If Variant Package IS Installed**:
   - The **Master Inventory Grid** shows each product variant as its own line item (e.g., "iPhone 15 Pro (Red / 256GB)").
   - The **Manual Add Stock Section** allows selecting a product, then dynamically loading its active variants in a dropdown so stock can be added to the specific combination.
   - The variant service provider or controllers automatically skip direct stock saving from the product/variant editor (since it's managed by Inventory).
3. **If Variant Package IS NOT Installed**:
   - The **Master Inventory Grid** only shows standard base products.
   - The **Manual Add Stock Section** only displays base product selection.
   - Variants are completely ignored, preventing php class not found or database column exceptions.

---

## 4. Disabling Direct Stock Editing
When the Inventory package is active, the admin must not edit stock fields directly inside the product edit forms. We enforce this in two layers:

### Layer A: UI Restriction (Readonly Fields)
1. **Product Edit Page (`resources/views/admin/products/edit.blade.php`)**:
   We check if the Inventory model class exists:
   ```html
   @if(class_exists(\SGCart\Inventory\Models\InventoryLog::class))
       <input type="number" name="stock" id="stock" value="{{ old('stock', $product->stock) }}" readonly
           class="w-full bg-slate-100 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-lg py-2.5 px-3.5 text-sm text-slate-500 cursor-not-allowed" style="pointer-events: none;">
       <p class="text-xs text-blue-600 dark:text-blue-400 mt-1 font-medium">
           <i class="fa-solid fa-circle-info"></i> Stock is managed via the <a href="{{ route('admin.inventory.index') }}" class="underline hover:text-blue-700">Inventory Management System</a>.
       </p>
   @else
       <input type="number" name="stock" id="stock" value="{{ old('stock', $product->stock) }}" required min="0" ...>
   @endif
   ```
2. **Variant Edit Table Grid (`admin-product-variants-partial.blade.php`)**:
   Similarly, we disable the stock input field in the variant row:
   ```html
   @if(class_exists(\SGCart\Inventory\Models\InventoryLog::class))
       <input type="number" name="variants[{{ $index }}][stock]" value="{{ $v->stock ?? 0 }}" readonly
           class="w-full bg-slate-100 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-lg py-1.5 px-3 text-sm text-slate-500 cursor-not-allowed font-mono" style="pointer-events: none;">
   @else
       <input type="number" name="variants[{{ $index }}][stock]" value="{{ $v->stock ?? 0 }}" class="w-full bg-slate-50 dark:bg-slate-800 ...">
   @endif
   ```

### Layer B: Backend Interceptor Protection
In `VariantController::saveGrid()`, if the `InventoryLog` class exists, we bypass updating the database `stock` column to prevent manual variant request tampering from altering inventory quantities.

---

## 5. UI/UX: Manual Add Stock Section
A dedicated screen (`admin/inventory/adjust`) allows quick additions to stock levels:

- **Form Fields**:
  - **Product** (Select Dropdown search filter).
  - **Variant** (Select Dropdown: visible and populated dynamically via JavaScript if variants exist, otherwise hidden).
  - **Adjustment Type**: [ `Add Stock` (Positive) | `Deduct Stock` (Negative) ]
  - **Quantity**: [ Number input ]
  - **Reason**: [ Text or pre-defined dropdown: "Received Shipment", "Returned Item", "Damaged", "Loss/Theft", "Audit Adjustment" ]

Submitting this form:
1. Calculates the adjusted amount.
2. Updates the `products` or `product_variants` stock column in the database.
3. If a variant's stock is updated, automatically sums up the active variant stocks and updates the base product's `stock` column to match.
4. Generates an `inventory_logs` record with action = `manual_adjustment`, referencing the user and the exact reason.
