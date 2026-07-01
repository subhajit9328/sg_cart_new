# SGCart Blog Documentation

This guide details how to install, configure, seed, use, and completely remove the Blog package from the e-commerce skeleton.

---

## 1. How to Use

### Step 1: Install the Package
Run composer to pull the package dependency into your Laravel project:
```bash
composer require sgcart/blog:@dev
```
*(Once the package is installed, the configuration file `config/blog.php` is automatically created, and database migrations are run automatically.)*

### Step 2: Seed Blog Data (Optional)
To seed the database with e-commerce-focused blog categories, posts, and related collection matches:
```bash
php artisan db:seed --class="SGCart\Blog\Database\Seeders\BlogDatabaseSeeder"
```

### Step 3: Manage Blog via Admin Panel
Access the Admin Sidebar and click on the **Marketing** section. You will find a collapsible **Blog** menu containing:
- **Blog Posts**: Create, edit, publish, and delete blog articles.
- **Blog Categories**: Organize articles into categories.

---

## 2. How to Remove

To completely uninstall the package, rollback all database migrations, drop tables, delete the published configuration, and remove permissions:

### Step 1: Run the Uninstall Command
Run the uninstall Artisan command to rollback database schema changes, delete permission, and delete the published configuration file (`config/blog.php`):
```bash
php artisan sgcart:blog-uninstall
```

### Step 2: Remove the Package Dependency
Remove the package from your Composer dependencies:
```bash
composer remove sgcart/blog
```
*(Alternatively, simply running `composer remove sgcart/blog` triggers the pre-uninstall lifecycle event hook, which automatically executes `sgcart:blog-uninstall` for you.)*
