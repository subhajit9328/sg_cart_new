# Logistic Tracking Package

The `sgcart/logistic-tracking` package provides decoupled, plug-and-play logistics and shipping tracking capabilities for orders in the SGCart application.

It stores order-specific logistics details (tracking number, carrier, URL, and estimated delivery date) in a separate `order_trackings` table and exposes them dynamically on the main application's `Order` model when the package is installed.

---

## Features

- **Decoupled Database Schema**: Moves logistics fields out of the main `orders` table into `order_trackings` with a foreign key to `orders`.
- **Dynamic Eloquent Relations**: Automatically injects the `tracking` relationship into the `App\Models\Order` model on boot.
- **DTO-Driven Updates**: Passes validated data to the package via the `LogisticTrackingData` DTO.
- **Action-Encapsulated Logic**: The `UpdateLogisticTrackingAction` class handles saving/updating and auto-generates tracking numbers and estimated delivery dates when an order is marked as `Shipped` without tracking details.
- **Activity Logging**: Captures changes to tracking details (old vs. new values) and logs them via the application's `LogActivity` action.
- **Seamless Uninstallation**: Automatically drops the table, cleans up migration records, and deletes published migration files on uninstallation.

---

## Installation

### 1. Install the Package via Composer
Run the following command in your DDEV environment to require and install the package:

```bash
ddev composer require sgcart/logistic-tracking:@dev
```

### 2. Run Migrations
Run the database migrations to create the `order_trackings` table:

```bash
ddev artisan migrate
```

### 3. (Optional) Publish Migrations
If you want to customize or publish the migration file into the main application's `database/migrations` directory, run:

```bash
ddev artisan vendor:publish --tag=logistic-tracking-migrations
```

---

## Usage

### Updating Order Status & Tracking
The update logic is performed inside the controller (e.g., `OrderController.php`) only if the package is installed.

```php
use SGCart\LogisticTracking\DTO\LogisticTrackingData;
use SGCart\LogisticTracking\Actions\UpdateLogisticTrackingAction;

if (class_exists(UpdateLogisticTrackingAction::class)) {
    // Construct the DTO using constructor parameters (PHP named arguments)
    $dto = new LogisticTrackingData(
        order_status: $data['status'],
        tracking_number: $data['tracking_number'] ?? null,
        shipping_carrier: $data['shipping_carrier'] ?? null,
        tracking_url: $data['tracking_url'] ?? null,
        estimated_delivery_at: $data['estimated_delivery_at'] ?? null
    );

    // Execute the update action
    app(UpdateLogisticTrackingAction::class)->execute($order, $dto);
}
```

### Accessing Tracking Details
When the package is installed, the tracking fields can be accessed directly on the `Order` model. Dynamic accessors proxy the calls to the `tracking` relationship:

```php
// Retrieve the tracking number
$trackingNumber = $order->tracking_number; // e.g., "SG-TRK-12345678"

// Retrieve the shipping carrier
$carrier = $order->shipping_carrier; // e.g., "Delhivery Express"

// Retrieve the tracking URL
$url = $order->tracking_url;

// Retrieve the estimated delivery date (Carbon instance)
$estDelivery = $order->estimated_delivery_at;
```

If the package is not installed, these accessors will safely return `null` without throwing any exceptions.

---

## Console Commands

The package registers the following Artisan command:

### `logistic-tracking:uninstall`
Used to clean up all database tables, migration records, and published files related to this package.

```bash
ddev artisan logistic-tracking:uninstall
```

---

## Uninstallation

To completely uninstall the package, run:

```bash
ddev composer remove sgcart/logistic-tracking
```

### Automatic Cleanup
When the package is removed via Composer, the `composer_package.sgcart/logistic-tracking:pre_uninstall` event listener automatically triggers the `logistic-tracking:uninstall` command. This will:
1. Drop the `order_trackings` table.
2. Remove all related migration records from the `migrations` table.
3. Delete any published migration files from the main application's `database/migrations` directory.

You can also run the command manually before removing the package if you want to clean up the database beforehand:
```bash
ddev artisan logistic-tracking:uninstall
```
