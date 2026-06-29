# SGCart Coupons Documentation

This guide details how to install, configure, use, and completely remove the Coupons package from the e-commerce skeleton.

---

## 1. How to Use

### Step 1: Install the Package
Run composer to pull the package dependency into your Laravel project:
```bash
composer require sgcart/coupons:@dev
```
*(Once the package is installed, the configuration file `config/coupons.php` is automatically created with all options enabled by default.)*

### Step 2: Configure the Package Features (Optional)
If you would like to run the interactive console configurator to choose which coupon features to enable (Minimum Cart Subtotals and/or Expiration Dates):
```bash
php artisan sgcart:coupons-config
```

### Step 3: Manage Options Dynamically
If you ever need to change the enabled coupon validation features later, edit the settings in the published config file:
```php
// config/coupons.php
return [
    'features' => [
        'min_cart_total' => true,
        'expires_at' => false, // Set to false to disable and drop expires columns
    ],
];
```
Then, execute the schema sync command to update the database tables and UI forms:
```bash
php artisan sgcart:coupons-sync
```

---

## 2. How to Remove

To completely uninstall the package, rollback all database migrations, drop columns, and clean up the published configuration files:

### Step 1: Run the Uninstall Command
Run the uninstall Artisan command to rollback database schema changes and delete the published configuration file (`config/coupons.php`):
```bash
php artisan sgcart:coupons-uninstall
```

### Step 2: Remove the Package Dependency
Remove the package from your Composer dependencies:
```bash
composer remove sgcart/coupons
```
