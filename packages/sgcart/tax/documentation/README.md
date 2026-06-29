# SGCart Tax Documentation

This guide details how to install, configure, use, and completely remove the Tax package from the e-commerce skeleton.

---

## 1. How to Use

### Step 1: Install the Package
Run composer to pull the package dependency into your Laravel project:
```bash
composer require sgcart/tax:@dev
```
*(Once the package is installed, the database migrations run automatically to create `tax_rates` and `tax_settings` tables, and seed the default configuration.)*

### Step 2: Calculate Taxes
The package registers a singleton service bind `tax.calculator` which handles calculating tax amount on order subtotals:
```php
$subtotal = 1000.00;
$taxResult = app('tax.calculator')->calculate($subtotal);
// Returns an array: ['amount' => X.XX, 'label' => 'GST (18%)']
```

### Step 3: Manage Tax Rates and Settings
The package registers administration routes and permissions to manage tax configuration dynamically.
- **Routes**: Access the tax rates panel in the administration panel at `/admin/tax`.
- **Permissions**: Users with the `manage tax` permission can configure tax options and CRUD tax rates.
- **Dynamic Settings**: Use `SGCart\Tax\Models\TaxSetting` to store custom configuration flags like calculation modes.

---

## 2. How to Remove

To completely uninstall the package, rollback all database migrations, drop tables, and clean up permissions:

### Step 1: Run the Uninstall Command
Run the uninstall Artisan command to rollback database schema changes:
```bash
php artisan sgcart:tax-uninstall
```

### Step 2: Remove the Package Dependency
Remove the package from your Composer dependencies:
```bash
composer remove sgcart/tax
```
