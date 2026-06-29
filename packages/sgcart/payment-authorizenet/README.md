# SGCart Authorize.Net Payment Gateway

An Authorize.Net payment gateway integration plugin for **SGCart**, providing credit card processing capabilities.

## Features

- **Credit Card Payments**: Secure card processing directly on the checkout page.
- **Sandbox Mode**: Easy testing with Authorize.Net merchant sandbox credentials.
- **Dynamic Settings Interface**: Automatically registers its configuration schema with the SGCart administration panel.
- **Automatic Seeding**: Self-registers as a payment method in the database upon booting.

---

## Installation

The plugin is designed to be installed as a local package within the SGCart ecosystem.

1. **Register the Path Repository** in the root `composer.json` (already configured in SGCart):
   ```json
   "repositories": [
       {
           "type": "path",
           "url": "packages/sgcart/payment-authorizenet",
           "options": {
               "symlink": true
           }
       }
   ]
   ```

2. **Require the Package**:
   ```json
   "require": {
       "sgcart/payment-authorizenet": "@dev"
   }
   ```

3. **Install/Update Dependencies**:
   Run the following command in the project root:
   ```bash
   composer update sgcart/payment-authorizenet
   ```

---

## Configuration & Credentials

Once enabled in the SGCart admin panel, navigate to **Settings > Payments** to configure the Authorize.Net credentials:

| Config Key | Label | Type | Description |
|---|---|---|---|
| `merchant_login_id` | **Merchant Login ID** | `text` | The API Login ID generated from the Authorize.Net Merchant Interface. |
| `merchant_transaction_key` | **Merchant Transaction Key** | `password` | The Transaction Key generated from the Authorize.Net Merchant Interface. |
| `sandbox` | **Test Mode** | `select` | Set to `Yes` (default) for testing, or `No` for live transaction processing. |

---

## Architecture & Integration

### 1. Service Provider
`SGCart\AuthorizeNet\Providers\AuthorizeNetServiceProvider`
- Loads views from `resources/views` with the `authorizenet` namespace.
- Hooks into the core `payment.manager` service container binding.
- Registers `AuthorizeNetGateway` inside the payment registry.
- Performs automatic database seeding by checking the `payment_methods` database table.

### 2. Gateway Implementation
`SGCart\AuthorizeNet\Gateways\AuthorizeNetGateway`
- Implements `App\Payments\Contracts\PaymentGatewayInterface`.
- Returns package metadata:
  - **ID**: `authorizenet`
  - **Name**: `Authorize.Net`
  - **Description**: `Secure credit card payments via Authorize.Net.`

---

## Frontend Checkout Integration

The gateway renders its custom card entry inputs on the checkout screen via the `authorizenet::frontend.fields` Blade template:

- **Cardholder Name** (`authorizenet_card_name` - required)
- **Credit Card Number** (`authorizenet_card_num` - required)
- **Expiry Date** (`authorizenet_card_expiry` - MM/YY, required)
- **CVV Code** (`authorizenet_card_cvv` - required)

### Payment Processing Workflow

When an order is submitted:
1. `processPayment` validates the required card fields.
2. The card number is stripped of spaces.
3. The order payment status is set to `PAID`.
4. The database record is populated with:
   - `payment_method` set to `Authorize.Net`.
   - `transaction_id` generated with the prefix `AUTH-`.
   - `card_name` with the customer-supplied name.
   - `card_number_masked` showing only the last 4 digits (e.g. `**** **** **** 1111`).
5. Returns a standard confirmation response:
   ```php
   [
       'success' => true,
       'message' => 'Payment processed successfully via Authorize.Net.'
   ]
   ```

---

## License

This package is open-source software licensed under the [MIT license](LICENSE).
