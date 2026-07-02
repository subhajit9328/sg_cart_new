# SGCart Product Reviews Package (`sgcart/reviews`)

A Laravel package providing a complete product reviews system for the SGCart platform. Customers who have purchased and received products can write, view, and edit reviews with rating stars and multiple photos. Administrators can moderate reviews (approve, reject, delete) individually or via bulk actions.

---

## Features

### 🌟 Customer Storefront Reviews
- **Purchase & Delivery Validation**: Reviews can only be submitted for products that the customer has purchased and have a status of `Delivered`.
- **Review Editing**: Customers can update their reviews. If admin approval is enabled, editing resets approval status and timestamps.
- **Multi-Photo Attachments**: Supports uploading multiple images per review, complete with selection accumulation, capping limit verification, drag/upload previews, and individual photo deletions.
- **Storefront Display Widgets**:
  - Overall rating score card with star count.
  - Ratings allocation breakdown graph (percentage progress bars for 5★ down to 1★).
  - Interactive AJAX filtering (Most Helpful, Latest, Positive, Negative) and pagination with shimmer loading placeholders.

### 🛡️ Administrative Moderation Dashboard
- **Review Moderation**: Quick actions to approve, reject, or delete individual reviews. Approved reviews are immediately published to the storefront.
- **Dynamic Search & Filtering**: Search reviews by customer name, email, product name, or comment, and filter by status.
- **Bulk Actions**: Select multiple reviews to bulk-approve, bulk-reject, or bulk-delete with action confirmation dialogs.
- **Automated Disk Cleanup**: Deleting a review automatically deletes all its image files from the public storage disk.

---

## Installation

### Step 1: Register the Package Dependency
In the SGCart root `composer.json` file, register the local repository path and require the package:

```json
"repositories": [
    {
        "type": "path",
        "url": "packages/sgcart/reviews",
        "options": {
            "symlink": true
        }
    }
],
"require": {
    "sgcart/reviews": "@dev"
}
```

Run Composer to install and link the package:
```bash
composer require sgcart/reviews:@dev
```

### Step 2: Run Database Migrations
Create the `reviews`, `review_images`, and `review_statuses` tables in the database:
```bash
php artisan migrate
```

### Step 3: Seed Default Moderation Statuses
Seed the default status states (`Pending`, `Approved`, `Rejected`) derived from the PHP Enum cases:
```bash
php artisan db:seed --class="SGCart\Reviews\Database\Seeders\ReviewStatusSeeder"
```

---

## Configuration

Publish the package configuration file to your host application config folder:
```bash
php artisan vendor:publish --tag=reviews-config
```

The published file `config/reviews.php` supports the following settings:

| Option | Type | Default | Description |
| :--- | :--- | :--- | :--- |
| `admin_approval` | `bool` | `true` | If true, newly submitted or edited reviews are set to `Pending` and require admin approval. If false, reviews are auto-approved. |
| `max_no_image` | `int` | `5` | Defines the maximum number of images a customer can attach to a review. Set to `0` to disable photo uploads. |

---

## Database Architecture

### 1. `review_statuses` Table
Tracks status categories synced from the `ReviewStatus` Enum.
- `id` (Primary Key)
- `name` (string: `Pending`, `Approved`, `Rejected`)
- `timestamps`

### 2. `reviews` Table
Stores review contents, ratings, and moderation timestamps.
- `id` (Primary Key)
- `customer_id` (foreignId to `customers`)
- `product_id` (foreignId to `products`)
- `rating` (integer: `1` to `5`)
- `comment` (text, nullable)
- `status_id` (foreignId to `review_statuses`, defaults to pending)
- `approved_at` (timestamp, nullable)
- `timestamps`

### 3. `review_images` Table
Handles the one-to-many relationship mapping images to a review.
- `id` (Primary Key)
- `review_id` (foreignId to `reviews`, cascade on delete)
- `image_path` (string)
- `timestamps`

---

## Uninstallation

If you wish to uninstall and remove the reviews package, follow these steps:

### Step 1: Run the Uninstall Command
Run the package uninstall command to roll back database migrations, clean up migration registry entries, and delete published migration files:
```bash
php artisan sgcart:reviews-uninstall
```

### Step 2: Remove the Package Dependency
Uninstall the package dependency using Composer:
```bash
composer remove sgcart/reviews
```

*Note: SGCart storefront integration uses a graceful fallback (`class_exists`) to automatically hide review links, modals, ratings summary widgets, and buttons when the package is uninstalled.*
