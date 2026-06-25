# SGCart Product Variants Documentation

This guide details how to install, configure, use, and completely remove the Product Variants package from the e-commerce skeleton.

---

## 1. How to Use

### Step 1: Install the Package
Run composer to pull the package dependency into your Laravel project:
```bash
composer require sgcart/product-variants:@dev
```
*(Once the package is installed, the configuration file `config/product-variants.php` is automatically created with all options enabled by default.)*

### Step 2: Configure the Variant Dimensions (Optional)
If you would like to run the interactive console configurator to choose which variant dimensions to enable (Color only, Size only, or Both):
```bash
php artisan sgcart:variants-config
```

### Step 3: Manage Options Dynamically
If you ever need to change the enabled dimensions later, edit the settings in the published config file:
```php
// config/product-variants.php
return [
    'features' => [
        'color' => true,
        'size' => false, // Set to false to disable and drop sizes
    ],
];
```
Then, execute the schema sync command to update the database tables and UI grids:
```bash
php artisan sgcart:variants-sync
```

---

## 2. How to Remove

To completely uninstall the package, rollback all database migrations, drop tables/columns, and clean up the published configuration files:

### Step 1: Run the Uninstall Command
Run the uninstall Artisan command to rollback database schema changes and delete the published configuration file (`config/product-variants.php`):
```bash
php artisan sgcart:variants-uninstall
```

### Step 2: Remove the Package Dependency
Remove the package from your Composer dependencies:
```bash
composer remove sgcart/product-variants
```
