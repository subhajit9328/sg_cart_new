<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Order Cancelled - #{{ $order->order_number }}</title>
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
            background: linear-gradient(95deg, #ef4444 0%, #f43f5e 100%);
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
            background: linear-gradient(95deg, #ef4444 0%, #f43f5e 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            display: inline-block;
            letter-spacing: -0.5px;
            color: #ef4444; /* Fallback */
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
        .info-box {
            background-color: #fef2f2;
            border-radius: 12px;
            padding: 20px;
            margin-bottom: 24px;
            border: 1px solid #fee2e2;
            color: #b91c1c;
            font-size: 15px;
            line-height: 1.5;
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
        .cta-container {
            text-align: center;
            margin: 35px 0;
        }
        .cta-button {
            display: inline-block;
            padding: 14px 32px;
            background-color: #ef4444;
            background: linear-gradient(95deg, #ef4444 0%, #f43f5e 100%);
            color: #ffffff !important;
            font-size: 16px;
            font-weight: 600;
            text-decoration: none;
            border-radius: 8px;
            box-shadow: 0 4px 6px -1px rgba(239, 68, 68, 0.2), 0 2px 4px -1px rgba(239, 68, 68, 0.1);
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
            color: #ef4444;
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
                <h1 class="title" style="color: #ef4444;">Order Cancelled</h1>
                <p class="greeting">Hi {{ $order->first_name }},</p>
                <p class="body-text">
                    We regret to inform you that your order <strong>#{{ $order->order_number }}</strong> has been cancelled.
                </p>

                <div class="info-box">
                    <strong>Refund Information:</strong><br>
                    If you have already made a payment for this order, the refund process will be initiated automatically. The amount will be credited back to your original payment method within 5-7 business days.
                </div>

                <h3 style="margin-top: 32px; font-weight: 600; color: #111827; border-bottom: 2px solid #f3f4f6; padding-bottom: 8px; font-size: 16px; text-transform: uppercase; letter-spacing: 0.5px;">Cancelled Items</h3>
                <!-- Items list -->
                <table class="items-table">
                    <thead>
                        <tr>
                            <th width="60%">Item</th>
                            <th width="20%" style="text-align: center;">Qty</th>
                            <th width="20%" style="text-align: right;">Price</th>
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
                            </td>
                            <td style="text-align: center; font-weight: 500;">{{ $item->quantity }}</td>
                            <td style="text-align: right; font-weight: 600; color: #111827;">₹{{ number_format($item->price, 2) }}</td>
                        </tr>
                        @endforeach

                        <tr class="summary-row total-row">
                            <td></td>
                            <td style="text-align: right; color: #111827; font-weight: 700;">Total Cancelled</td>
                            <td style="text-align: right; color: #ef4444; font-weight: 700; font-size: 19px;">₹{{ number_format($order->total, 2) }}</td>
                        </tr>
                    </tbody>
                </table>

                <!-- CTA -->
                <div class="cta-container">
                    <a href="{{ config('app.url') }}" class="cta-button" style="color: #ffffff;">Return to Shop</a>
                </div>

                <p class="body-text" style="text-align: center; margin-top: 30px; font-size: 14px; color: #9ca3af;">
                    If you did not request this cancellation or have any questions, please contact our support team.
                </p>
            </div>
        </div>
        <div class="footer">
            <p>&copy; {{ date('Y') }} SG CART. All rights reserved.</p>
            <p>You received this email because of an order activity at <a href="{{ config('app.url') }}">sgcart.com</a>.</p>
        </div>
    </div>
</body>
</html>
