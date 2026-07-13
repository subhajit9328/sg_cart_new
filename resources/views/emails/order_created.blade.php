<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Order Confirmation - #{{ $order->order_number }}</title>
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;600;700&display=swap');
        body {
            font-family: 'Outfit', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif;
            margin: 0;
            padding: 0;
            width: 100% !important;
            background-color: #f3f4f6;
            -webkit-font-smoothing: antialiased;
        }
        table {
            border-spacing: 0;
            width: 100%;
        }
        img {
            border: 0;
        }
        .wrapper {
            width: 100%;
            table-layout: fixed;
            background-color: #f3f4f6;
            padding-bottom: 60px;
            padding-top: 40px;
        }
        .main-card {
            background-color: #ffffff;
            margin: 0 auto;
            width: 100%;
            max-width: 600px;
            border-radius: 16px;
            box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.05), 0 4px 6px -4px rgba(0, 0, 0, 0.05);
            overflow: hidden;
            border: 1px solid #e5e7eb;
        }
        .gradient-bar {
            height: 6px;
            background: linear-gradient(95deg, #4f46e5 0%, #06b6d4 100%);
        }
        .content {
            padding: 40px 30px;
            color: #1f2937;
        }
        .header {
            text-align: center;
            margin-bottom: 30px;
        }
        .logo-text {
            font-size: 28px;
            font-weight: 700;
            background: linear-gradient(95deg, #4f46e5 0%, #06b6d4 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            display: inline-block;
            letter-spacing: -0.5px;
            color: #4f46e5; /* Fallback */
        }
        .title {
            font-size: 24px;
            font-weight: 700;
            color: #111827;
            margin-top: 0;
            margin-bottom: 16px;
            text-align: center;
            letter-spacing: -0.3px;
        }
        .greeting {
            font-size: 18px;
            font-weight: 600;
            color: #374151;
            margin-bottom: 12px;
        }
        .body-text {
            font-size: 16px;
            line-height: 1.6;
            color: #4b5563;
            margin-bottom: 24px;
        }
        .order-meta-box {
            background-color: #f9fafb;
            border-radius: 12px;
            padding: 20px;
            margin-bottom: 24px;
            border: 1px solid #f3f4f6;
        }
        .meta-grid {
            width: 100%;
        }
        .meta-label {
            font-size: 13px;
            color: #9ca3af;
            text-transform: uppercase;
            font-weight: 600;
            letter-spacing: 0.5px;
        }
        .meta-val {
            font-size: 15px;
            color: #111827;
            font-weight: 600;
            margin-top: 2px;
        }
        .items-table {
            width: 100%;
            margin-top: 24px;
            border-collapse: collapse;
        }
        .items-table th {
            text-align: left;
            padding: 12px 8px;
            border-bottom: 2px solid #f3f4f6;
            font-size: 13px;
            text-transform: uppercase;
            color: #6b7280;
            letter-spacing: 0.5px;
        }
        .items-table td {
            padding: 16px 8px;
            border-bottom: 1px solid #f3f4f6;
            font-size: 15px;
            color: #374151;
        }
        .summary-row td {
            padding: 8px 8px;
            border-bottom: none;
            font-size: 15px;
        }
        .summary-label {
            text-align: right;
            color: #6b7280;
            font-weight: 500;
        }
        .summary-value {
            text-align: right;
            font-weight: 600;
            color: #111827;
        }
        .total-row td {
            padding-top: 16px;
            border-top: 2px solid #e5e7eb;
            font-size: 18px;
            font-weight: 700;
        }
        .address-section {
            margin-top: 32px;
            width: 100%;
        }
        .address-box {
            background-color: #f9fafb;
            border-radius: 12px;
            padding: 20px;
            border: 1px solid #f3f4f6;
        }
        .address-title {
            font-size: 14px;
            font-weight: 700;
            color: #111827;
            text-transform: uppercase;
            margin-bottom: 8px;
            letter-spacing: 0.5px;
        }
        .address-content {
            font-size: 14px;
            line-height: 1.5;
            color: #4b5563;
        }
        .cta-container {
            text-align: center;
            margin: 35px 0;
        }
        .cta-button {
            display: inline-block;
            padding: 14px 32px;
            background-color: #4f46e5;
            background: linear-gradient(95deg, #4f46e5 0%, #06b6d4 100%);
            color: #ffffff !important;
            font-size: 16px;
            font-weight: 600;
            text-decoration: none;
            border-radius: 8px;
            box-shadow: 0 4px 6px -1px rgba(79, 70, 229, 0.2), 0 2px 4px -1px rgba(79, 70, 229, 0.1);
            transition: all 0.2s ease;
        }
        .footer {
            text-align: center;
            padding-top: 30px;
            font-size: 13px;
            color: #9ca3af;
            line-height: 1.5;
        }
        .footer a {
            color: #4f46e5;
            text-decoration: none;
        }
    </style>
</head>
<body>
    <div class="wrapper">
        <div class="main-card">
            <div class="gradient-bar"></div>
            <div class="content">
                <div class="header">
                    <span class="logo-text">SG CART</span>
                </div>
                <h1 class="title">Thank You For Your Order!</h1>
                <p class="greeting">Hi {{ $order->first_name }},</p>
                <p class="body-text">
                    Your order has been placed successfully. We are now preparing your items for packaging. Below is your order confirmation details.
                </p>

                <!-- Order Meta -->
                <div class="order-meta-box">
                    <table class="meta-grid">
                        <tr>
                            <td width="50%" style="padding-bottom: 12px;">
                                <div class="meta-label">Order Number</div>
                                <div class="meta-val">#{{ $order->order_number }}</div>
                            </td>
                            <td width="50%" style="padding-bottom: 12px;">
                                <div class="meta-label">Date Placed</div>
                                <div class="meta-val">{{ $order->created_at->format('M d, Y') }}</div>
                            </td>
                        </tr>
                        <tr>
                            <td>
                                <div class="meta-label">Payment Method</div>
                                <div class="meta-val">{{ $order->payment_method }}</div>
                            </td>
                            <td>
                                <div class="meta-label">Payment Status</div>
                                <div class="meta-val">{{ $order->payment_status instanceof \BackedEnum ? $order->payment_status->value : (string) $order->payment_status }}</div>
                            </td>
                        </tr>
                    </table>
                </div>

                <!-- Items list -->
                <table class="items-table">
                    <thead>
                        <tr>
                            <th width="50%">Item</th>
                            <th width="15%" style="text-align: center;">Qty</th>
                            <th width="15%" style="text-align: right;">Price</th>
                            <th width="20%" style="text-align: right;">Total</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($order->items as $item)
                        <tr>
                            <td>
                                <div style="font-weight: 600; color: #111827;">{{ $item->product_name }}</div>
                                @if($item->product_sku)
                                    <div style="font-size: 12px; color: #9ca3af; margin-top: 2px;">SKU: {{ $item->product_sku }}</div>
                                @endif
                                @if($item->size || $item->color)
                                    <div style="font-size: 12px; color: #6b7280; margin-top: 2px;">
                                        @if($item->size) Size: {{ $item->size }} @endif
                                        @if($item->color) @if($item->size) | @endif Color: {{ $item->color }} @endif
                                    </div>
                                @endif
                            </td>
                            <td style="text-align: center; font-weight: 500;">{{ $item->quantity }}</td>
                            <td style="text-align: right; color: #4b5563;">₹{{ number_format($item->price, 2) }}</td>
                            <td style="text-align: right; font-weight: 600; color: #111827;">₹{{ number_format($item->price * $item->quantity, 2) }}</td>
                        </tr>
                        @endforeach

                        <!-- Calculations -->
                        <tr class="summary-row">
                            <td colspan="2"></td>
                            <td class="summary-label">Subtotal</td>
                            <td class="summary-value">₹{{ number_format($order->subtotal, 2) }}</td>
                        </tr>
                        @if($order->discount > 0)
                        <tr class="summary-row">
                            <td colspan="2"></td>
                            <td class="summary-label" style="color: #10b981;">Discount</td>
                            <td class="summary-value" style="color: #10b981;">-₹{{ number_format($order->discount, 2) }}</td>
                        </tr>
                        @endif
                        <tr class="summary-row">
                            <td colspan="2"></td>
                            <td class="summary-label">Tax</td>
                            <td class="summary-value">₹{{ number_format($order->tax, 2) }}</td>
                        </tr>
                        <tr class="summary-row">
                            <td colspan="2"></td>
                            <td class="summary-label">Shipping</td>
                            <td class="summary-value">
                                {{ $order->shipping_charge > 0 ? '₹' . number_format($order->shipping_charge, 2) : 'Free' }}
                            </td>
                        </tr>
                        <tr class="summary-row total-row">
                            <td colspan="2"></td>
                            <td style="text-align: right; color: #111827; font-weight: 700;">Grand Total</td>
                            <td style="text-align: right; color: #4f46e5; font-weight: 700; font-size: 19px;">₹{{ number_format($order->total, 2) }}</td>
                        </tr>
                    </tbody>
                </table>

                <!-- Addresses section -->
                <table class="address-section">
                    <tr>
                        <td width="48%" valign="top">
                            <div class="address-box">
                                <div class="address-title">Shipping Address</div>
                                <div class="address-content">
                                    <strong>{{ $order->first_name }} {{ $order->last_name }}</strong><br>
                                    {{ $order->address }}<br>
                                    @if($order->landmark) Landmark: {{ $order->landmark }}<br> @endif
                                    {{ $order->city }}, {{ $order->state }} {{ $order->zip }}<br>
                                    {{ $order->country }}<br>
                                    Phone: {{ $order->phone }}
                                </div>
                            </div>
                        </td>
                        <td width="4%"></td>
                        <td width="48%" valign="top">
                            <div class="address-box">
                                <div class="address-title">Billing Address</div>
                                <div class="address-content">
                                    @if($order->shipping_and_billing_same)
                                        <em>Same as shipping address</em>
                                    @else
                                        <strong>{{ $order->billing_first_name }} {{ $order->billing_last_name }}</strong><br>
                                        {{ $order->billing_address }}<br>
                                        {{ $order->billing_city }}, {{ $order->billing_state }} {{ $order->billing_zip }}<br>
                                        {{ $order->billing_country }}<br>
                                        Phone: {{ $order->billing_phone }}
                                    @endif
                                </div>
                            </div>
                        </td>
                    </tr>
                </table>

                <!-- CTA -->
                <div class="cta-container">
                    <a href="{{ route('store.account.order.view', $order->ulid) }}" class="cta-button" style="color: #ffffff;">View Order Details</a>
                </div>

                <p class="body-text" style="text-align: center; margin-top: 30px; font-size: 14px; color: #9ca3af;">
                    If you have any questions or concerns regarding this order, please contact our support team.
                </p>
            </div>
        </div>
        <div class="footer">
            <p>&copy; {{ date('Y') }} SG CART. All rights reserved.</p>
            <p>You received this email because you placed an order at <a href="{{ config('app.url') }}">sgcart.com</a>.</p>
        </div>
    </div>
</body>
</html>
