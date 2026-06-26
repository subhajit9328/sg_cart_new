# SGCart Razorpay Payment Gateway

A Razorpay payment gateway integration plugin for **SGCart**, supporting Cards, UPI, Netbanking, and Wallets.

## Features

- **Redirect/Hosted Checkout Integration**: Seamless redirection to Razorpay's checkout platform to securely complete payments.
- **Multiple Payment Modes**: Supports Credit/Debit Cards, UPI, Netbanking, and digital wallets.
- **Sandbox Mode**: Simple testing toggle utilizing Razorpay's API keys in test mode.
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
           "url": "packages/sgcart/payment-razorpay",
           "options": {
               "symlink": true
           }
       }
   ]
   ```

2. **Require the Package**:
   ```json
   "require": {
       "sgcart/payment-razorpay": "@dev"
   }
   ```

3. **Install/Update Dependencies**:
   Run the following command in the project root:
   ```bash
   composer update sgcart/payment-razorpay
   ```

---

## Configuration & Credentials

Once enabled in the SGCart admin panel, navigate to **Settings > Payments** to configure the Razorpay credentials:

| Config Key | Label | Type | Description |
|---|---|---|---|
| `key_id` | **Razorpay Key ID** | `text` | The API Key ID obtained from the API Keys section of the Razorpay Dashboard. |
| `key_secret` | **Razorpay Key Secret** | `password` | The API Key Secret obtained alongside the Key ID from the Razorpay Dashboard. |
| `sandbox` | **Test Mode** | `select` | Set to `Yes` (default) for testing, or `No` for live transaction processing. |

---

## Architecture & Integration

### 1. Service Provider
`SGCart\Razorpay\Providers\RazorpayServiceProvider`
- Loads views from `resources/views` with the `razorpay` namespace.
- Hooks into the core `payment.manager` service container binding.
- Registers `RazorpayGateway` inside the payment registry.
- Performs automatic database seeding by checking the `payment_methods` database table.

### 2. Gateway Implementation
`SGCart\Razorpay\Gateways\RazorpayGateway`
- Implements `App\Payments\Contracts\PaymentGatewayInterface`.
- Returns package metadata:
  - **ID**: `razorpay`
  - **Name**: `Razorpay`
  - **Description**: `Pay securely using Razorpay (Cards, UPI, Netbanking).`

---

## Frontend Checkout Integration

The gateway renders an information tile on the checkout screen via the `razorpay::frontend.fields` Blade template, indicating that the client will be redirected to the secure Razorpay payment wizard.

### Payment Processing Workflow

When an order is submitted:
1. `processPayment` processes the order details.
2. The order payment status is set to `PAID`.
3. The database record is populated with:
   - `payment_method` set to `Razorpay`.
   - `transaction_id` generated with the prefix `RZP-`.
4. Returns a standard confirmation response:
   ```php
   [
       'success' => true,
       'message' => 'Payment processed successfully via Razorpay.'
   ]
   ```

---

## License

This package is open-source software licensed under the [MIT license](LICENSE).
