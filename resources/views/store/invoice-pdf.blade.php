<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Invoice {{ $order->order_number }}</title>
    <style>
        body {
            font-family: 'DejaVu Sans', sans-serif;
            font-size: 13px;
            line-height: 1.6;
            color: #333333;
            margin: 0;
            padding: 0;
        }
        .invoice-box {
            max-width: 800px;
            margin: auto;
            padding: 20px;
        }
        .header-table, .details-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 30px;
        }
        .header-table td {
            vertical-align: top;
        }
        .logo {
            font-size: 24px;
            font-weight: 800;
            color: #0f172a;
            text-decoration: none;
            letter-spacing: -0.5px;
        }
        .logo span {
            color: #c8a97e;
        }
        .invoice-title {
            font-size: 22px;
            font-weight: bold;
            color: #0f172a;
            text-align: right;
            text-transform: uppercase;
        }
        .meta-text {
            font-size: 11px;
            color: #64748b;
            text-align: right;
        }
        .section-title {
            font-size: 11px;
            font-weight: bold;
            text-transform: uppercase;
            color: #64748b;
            border-bottom: 1px solid #e2e8f0;
            padding-bottom: 5px;
            margin-bottom: 10px;
        }
        .address-box {
            font-size: 12px;
            color: #334155;
        }
        .items-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
            margin-bottom: 30px;
        }
        .items-table th {
            background-color: #f8fafc;
            border-bottom: 2px solid #e2e8f0;
            color: #475569;
            font-weight: bold;
            font-size: 11px;
            text-transform: uppercase;
            padding: 10px;
            text-align: left;
        }
        .items-table td {
            padding: 12px 10px;
            border-bottom: 1px solid #e2e8f0;
            font-size: 12px;
        }
        .items-table tr.total-row td {
            border: none;
            font-size: 12px;
            padding: 6px 10px;
        }
        .items-table tr.grand-total-row td {
            border-top: 2px solid #e2e8f0;
            font-size: 14px;
            font-weight: bold;
            color: #0f172a;
            padding: 12px 10px;
        }
        .badge {
            display: inline-block;
            padding: 2px 6px;
            font-size: 9px;
            font-weight: bold;
            text-transform: uppercase;
            border-radius: 4px;
        }
        .badge-paid {
            background-color: #dcfce7;
            color: #15803d;
        }
        .badge-pending {
            background-color: #fef3c7;
            color: #b45309;
        }
        .footer {
            margin-top: 50px;
            border-top: 1px solid #e2e8f0;
            padding-top: 15px;
            text-align: center;
            font-size: 11px;
            color: #94a3b8;
        }
    </style>
</head>
<body>
    <div class="invoice-box">
        <!-- Header -->
        <table class="header-table">
            <tr>
                <td>
                    <div class="logo">sgcart<span>.</span></div>
                    <p style="font-size: 11px; color: #64748b; margin-top: 5px; line-height: 1.4;">
                        SGCart E-commerce Ltd.<br>
                        123 Fashion Ave, Suite 500<br>
                        Mumbai, MH 400001, India<br>
                        support@sgcart.com
                    </p>
                </td>
                <td style="text-align: right;">
                    <div class="invoice-title">Invoice</div>
                    <div class="meta-text" style="margin-top: 5px;">
                        <strong>Invoice No:</strong> #INV-{{ $order->order_number }}<br>
                        <strong>Order Date:</strong> {{ $order->created_at->format('M d, Y') }}<br>
                        <strong>Payment Status:</strong> 
                        <span style="color: {{ ($order->payment_status->value ?? $order->payment_status) === 'Paid' ? '#15803d' : '#b45309' }}; font-weight: bold; text-transform: uppercase;">
                            {{ $order->payment_status->value ?? $order->payment_status }}
                        </span>
                    </div>
                </td>
            </tr>
        </table>

        <!-- Billing/Shipping Details -->
        <table class="details-table" style="margin-bottom: 20px;">
            <tr>
                <td style="width: 50%; padding-right: 20px;">
                    <div class="section-title">Invoiced To</div>
                    <div class="address-box">
                        <strong>{{ $order->first_name }} {{ $order->last_name }}</strong><br>
                        {{ $order->address }}<br>
                        {{ $order->city }}, {{ $order->state }} {{ $order->zip }}<br>
                        {{ $order->country }}
                    </div>
                </td>
                <td style="width: 50%; padding-left: 20px;">
                    <div class="section-title">Payment Info</div>
                    <div class="address-box">
                        <strong>Method:</strong> {{ $order->payment_method ?? 'N/A' }}<br>
                        @if($order->card_number_masked)
                            <strong>Card:</strong> {{ $order->card_number_masked }}<br>
                        @endif
                        <strong>Reference Order ID:</strong> {{ $order->order_number }}
                    </div>
                </td>
            </tr>
        </table>

        <!-- Line Items -->
        <table class="items-table">
            <thead>
                <tr>
                    <th>Item Description</th>
                    <th style="text-align: right; width: 100px;">Price</th>
                    <th style="text-align: center; width: 60px;">Qty</th>
                    <th style="text-align: right; width: 120px;">Total</th>
                </tr>
            </thead>
            <tbody>
                @foreach($order->items as $item)
                    <tr>
                        <td>
                            <div style="font-weight: bold; color: #0f172a;">{{ $item->product_name }}</div>
                            @if($item->product_sku || $item->size || $item->color)
                                <div style="font-size: 10px; color: #64748b; margin-top: 2px;">
                                    @if($item->product_sku) SKU: {{ $item->product_sku }} @endif
                                    @if($item->size) &bull; Size: {{ $item->size }} @endif
                                    @if($item->color) &bull; Color: {{ $item->color }} @endif
                                </div>
                            @endif
                        </td>
                        <td style="text-align: right;">₹{{ number_format($item->price, 2) }}</td>
                        <td style="text-align: center;">{{ $item->quantity }}</td>
                        <td style="text-align: right;">₹{{ number_format($item->price * $item->quantity, 2) }}</td>
                    </tr>
                @endforeach

                <!-- Financials -->
                <tr class="total-row">
                    <td style="border: none;"></td>
                    <td colspan="2" style="text-align: right; color: #64748b;">Subtotal:</td>
                    <td style="text-align: right;">₹{{ number_format($order->subtotal, 2) }}</td>
                </tr>
                @if($order->discount > 0)
                    <tr class="total-row">
                        <td style="border: none;"></td>
                        <td colspan="2" style="text-align: right; color: #64748b;">Discount:</td>
                        <td style="text-align: right; color: #16a34a;">-₹{{ number_format($order->discount, 2) }}</td>
                    </tr>
                @endif
                <tr class="total-row">
                    <td style="border: none;"></td>
                    <td colspan="2" style="text-align: right; color: #64748b;">{{ $order->tax_method ?? 'Tax' }}:</td>
                    <td style="text-align: right;">₹{{ number_format($order->tax, 2) }}</td>
                </tr>
                <tr class="total-row">
                    <td style="border: none;"></td>
                    <td colspan="2" style="text-align: right; color: #64748b;">Shipping @if($order->shipping_method) ({{ $order->shipping_method }}) @endif:</td>
                    <td style="text-align: right;">{{ $order->shipping_charge > 0 ? '₹' . number_format($order->shipping_charge, 2) : 'Free' }}</td>
                </tr>
                <tr class="grand-total-row">
                    <td style="border: none;"></td>
                    <td colspan="2" style="text-align: right;">Total Amount:</td>
                    <td style="text-align: right;">₹{{ number_format($order->total, 2) }}</td>
                </tr>
            </tbody>
        </table>

        <!-- Footer -->
        <div class="footer">
            Thank you for shopping with SGCart! For inquiries, returns, or support, please contact us at support@sgcart.com.<br>
            This is a computer-generated invoice and does not require a physical signature.
        </div>
    </div>
</body>
</html>
