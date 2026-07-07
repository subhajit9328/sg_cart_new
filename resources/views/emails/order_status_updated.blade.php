<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Order Status Updated - #{{ $order->order_number }}</title>
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
            color: #4f46e5;
            /* Fallback */
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

        /* Status Badge Styling */
        .status-container {
            text-align: center;
            margin: 25px 0;
        }

        .status-badge {
            display: inline-block;
            padding: 10px 24px;
            font-size: 16px;
            font-weight: 700;
            border-radius: 30px;
            text-transform: uppercase;
            letter-spacing: 1px;
        }

        /* Dynamic Status Colors */
        @php
            $statusStr = $order->status instanceof \BackedEnum
                ? $order->status->value
                : (string) $order->status;

            switch (strtolower($statusStr)) {
                case 'delivered':
                    $badgeBg = '#d1fae5';
                    $badgeColor = '#065f46';
                    break;
                case 'shipped':
                    $badgeBg = '#dbeafe';
                    $badgeColor = '#1e40af';
                    break;
                case 'out for delivery':
                    $badgeBg = '#e0e7ff';
                    $badgeColor = '#3730a3';
                    break;
                case 'processed':
                case 'processing':
                    $badgeBg = '#fef3c7';
                    $badgeColor = '#92400e';
                    break;
                default:
                    $badgeBg = '#f3f4f6';
                    $badgeColor = '#374151';
                    break;
            }
        @endphp
        
        .badge-style {

               
                        backgr
             o  und-color: {{ $badgeBg }};
            color: {{ $badgeColor }};

                        border: 1px solid {{ $badgeBg }};
        }

        .tracking-box {
            background-color: #f8fafc;
            border-radius: 12px;
            padding: 24px;
            margin-top: 24px;
            margin-bottom: 24px;
            border: 1px solid #e2e8f0;

        }
        .tracking-title {
            font-size: 14px;
            font-weight: 700;
            color: #0f172a;
            text-transform: uppercase;
            margin-bottom: 12px;
            letter-spacing: 0.5px;
            border-bottom: 1px solid #e2e8f0;
            padding-bottom: 8px;

        }
        .tracking-row {
            margin-bottom: 8px;
            font-size: 15px;
            color: #334155;

        }
        .tracking-label {
            font-weight: 600;
            color: #64748b;
}
        
        .items-summary {
            background-color: #f9fafb;
            border-radius: 12px;
            padding: 20px;
            border: 1px solid #f3f4f6;
            margin-bottom: 24px;

        }
        .item-row {
            font-size: 14px;
            color: #4b5563;
            padding: 6px 0;
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
                <h1 class="title">Order Status Updated</h1>
                <p class="greeting">Hi {{ $order->first_name }},</p>
                <p class="body-text">
                    The status of your order <strong>#{{ $order->order_number }}</strong> has been updated.
                </p>

                <!-- Status Badge -->
                <div class="status-container">
                    <span class="status-badge badge-style">
                        Order {{ $statusStr }}
                    </span>
                </div>

                <!-- Tracking Information -->
                @if($order->tracking_number)
                    <div class="tracking-box">
                        <div class="tracking-title">Delivery & Tracking Information</div>
                        <table width="100%">
                            @if($order->shipping_carrier)
                                <tr>
                                    <td class="tracking-row" width="40%"><span class="tracking-label">Carrier:</span></td>
                                    <td class="tracking-row" width="60%">{{ $order->shipping_carrier }}</td>
                                </tr>
                            @endif
                            <tr>
                                <td class="tracking-row"><span class="tracking-label">Tracking Number:</spa
                                    n></td>
                                <td class="tracking-row" style="font-family: monospace; font-weight: 600;">{{ $order->tracking_number }}</td>
                            </tr>
                            @if($order->estimated_delivery_at)
                                <tr>
                                    <td class="tracking-row"><span class="tracking-label">Est. Delivery:</span></td>
                                    <td class="tracking-row" style="font-weight: 600; color: #0284c7;">
                                        {{ $order->estimated_delivery_at->format('M d, Y') }}
                                    </td>
                                </tr>
                            @endif
                        </table>
                    </div>
                @endif

                <!-- Items Summary -->
                <div cla
                       ss="items-summary">

                                            <div style="font-size: 13px; font-weight: 700; text-transform: uppercase; color: #6b7280; letter-spacing: 0.5px; border-bottom: 1px solid #f3f4f6; padding-bottom: 6px; margin-bottom: 8px;">Order Details</div>
                    <table width="100%">
                        @foreach($order->items as $item)
                            <tr>

                                 <td class="item-row">{{ $item->product_name }} <span style="color:
                                     #9ca3af;">x {{ $item->quantity }}</span></td>
                                <td class="item-row" style="text-align: right; font-weight: 600;">₹{{ number_format($item->price * $item->quantity, 2) }}</td>
                            </tr>
                        @endforeach
                        <tr>

                               
                                                            <td class="item-row"
                                style="font-weight: 700; border-top: 1px solid #f3f4f6; padding-top: 10px; color: #111827;">Total Paid</td>

                                                            <td class="item-row" style="text-align: right; font-weight: 700; border-top: 1px solid #f3f4f6; padding-top: 10px; color: #4f46e5; font-size: 16px;">₹{{ number_format($order->total, 2) }}</td>
                        </tr>
                    </table>
                </div>

                <!-- CTA -->
                <div class="cta-container">
                @if($order->tracking_url)
                    <a href="{{ $order->tracking_url }}" class="cta-button" style="color: #ffffff;">Track Package</a>
                @else

                       <a href="{{ route('store.account.order.view', $order->ulid) }}" class="cta-button" style="color: #ffffff;">View Order details</a>
                @endif
                </div>

                <p class="body-text" style="text-align: center; margin-top: 30px; font-size: 14px; color: #9ca3af;">
                    If you have any questions, feel free to contact our customer support.
                </p>
            </div>
        </div>
        <div class="footer">
            <p>&copy; {{ date('Y') }} SG CART. All rights reserved.</p>

                        <p>You received this email because of an order activity at <a href="{{ config('app.url') }}">sgcart.com</a>.</p>
        </div>
    </d
iv>
</body></html>
