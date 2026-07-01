# SGCart Dashboard Analytics Documentation

This guide details how to install, use, and completely remove the Dashboard Analytics package from the SGCart e-commerce skeleton.

---

## 1. How to Use

### Step 1: Install the Package
To install and register the package, run composer to pull the package dependency into your Laravel project:
```bash
composer require sgcart/dashboard-analytics:@dev
```
*(Once composer completes installation, the package's Service Provider is auto-discovered, migrations are automatically run to add the `search_logs` table and `coupon_code` column, Spatie permissions are registered, and high-quality mock sales/search data is seeded to the database if it is empty.)*

### Step 2: Accessing the Dashboard
1. Sign in to your store administration panel as a `Super Admin` or `Manager`.
2. Locate the **Analytics** sidebar menu item in the left-hand navigation pane, or directly navigate to:
   `http://localhost/admin/analytics`
3. If your role does not have the `view analytics` permission, access will be restricted with a Spatie Authorization Exception.

### Step 3: Date Filtering & Validation
* **Presets**: Quickly filter the entire page using the preset buttons: **1 Week**, **15 Days**, **1 Month**, or **6 Months**.
* **Custom Range**: Select custom start and end date pickers.
* **Validation Rules**:
  * Date selections in the future are disabled.
  * Start Date must be before or equal to End Date.
  * Custom ranges cannot span more than **6 months (180 days)**. If a violation occurs, a verification alert is shown, and the chart rolls back to the default 1-week preset.

### Step 4: Search & Coupon Logging (Automated)
* **Search Logging**: A built-in route middleware automatically captures all customer searches on the storefront shop route (`?search=term`) and the live search API (`?q=term`) into the database.
* **Coupon Tracking**: When a customer checkout is completed, the applied session coupon is saved into the order's `coupon_code` database column for historical attribution.

---

## 2. How to Remove

To completely uninstall the package, rollback all database migrations, drop search log tables, drop order coupon columns, and remove Spatie permissions:

### Step 1: Run the Uninstall Command
Run the uninstall Artisan command to rollback database schema changes and clean up custom Spatie permission sets:
```bash
php artisan sgcart:dashboard-analytics-uninstall
```

### Step 2: Remove the Package Dependency
Remove the package from your Composer dependencies and update the autoloader:
```bash
composer remove sgcart/dashboard-analytics
```
*(During composer removal, the pre-uninstall scripts will trigger cleanup functions automatically to prevent database or route errors.)*
