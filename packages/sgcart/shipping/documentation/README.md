# SGCart Shipping Documentation

This guide details how to install, configure, use, and completely remove the Shipping package from the e-commerce skeleton.

---

## 1. How to Use

### Step 1: Install the Package
Run composer to pull the package dependency into your Laravel project:
```bash
composer require sgcart/shipping:@dev
```
*(Once the package is installed, the database migrations run automatically to create `shipping_rates` and `shipping_settings` tables, and seed the default configuration.)*

### Step 2: Manage Shipping Rates and Settings
The package registers administration routes and permissions to manage shipping configuration dynamically.
- **Routes**: Access the shipping management panel in the administration panel at `/admin/shipping`.
- **Permissions**: Users with the `manage shipping` permission can configure shipping options and CRUD shipping rates.
- **Dynamic Settings**: Use `SGCart\Shipping\Models\ShippingSetting` to store custom configuration flags like selection modes.

---

## 2. How to Remove

To completely uninstall the package, rollback all database migrations, drop tables, and clean up permissions:

### Step 1: Run the Uninstall Command
Run the uninstall Artisan command to rollback database schema changes:
```bash
php artisan sgcart:shipping-uninstall
```

### Step 2: Remove the Package Dependency
Remove the package from your Composer dependencies:
```bash
composer remove sgcart/shipping
```
