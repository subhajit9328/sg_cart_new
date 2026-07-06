<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Payout Disbursed - SGCart Seller Portal</title>
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@500;600;700;800&family=Inter:wght@400;500;600&display=swap');
        
        body {
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif;
            margin: 0;
            padding: 0;
            width: 100% !important;
            background-color: #f8fafc;
            -webkit-font-smoothing: antialiased;
        }
        table {
            border-spacing: 0;
            border-collapse: collapse;
        }
        td {
            padding: 0;
        }
        img {
            border: 0;
        }
        .wrapper {
            width: 100%;
            table-layout: fixed;
            background-color: #f8fafc;
            padding: 40px 20px;
        }
        .main-card {
            background-color: #ffffff;
            margin: 0 auto;
            width: 100%;
            max-width: 580px;
            border-radius: 16px;
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.05), 0 2px 4px -1px rgba(0, 0, 0, 0.03), 0 20px 25px -5px rgba(0, 0, 0, 0.05);
            overflow: hidden;
            border: 1px solid #e2e8f0;
        }
        .gradient-bar {
            height: 6px;
            background: linear-gradient(90deg, #10b981 0%, #3b82f6 100%);
        }
        .content {
            padding: 40px 35px;
        }
        .header {
            text-align: center;
            margin-bottom: 30px;
        }
        .logo-text {
            font-family: 'Plus Jakarta Sans', sans-serif;
            font-size: 26px;
            font-weight: 800;
            color: #3b82f6;
            letter-spacing: -0.5px;
        }
        .logo-text span {
            color: #6366f1;
        }
        .logo-badge {
            font-size: 10px;
            font-weight: 700;
            text-transform: uppercase;
            color: #94a3b8;
            letter-spacing: 1px;
            margin-left: 4px;
            vertical-align: middle;
            border: 1px solid #e2e8f0;
            padding: 2px 6px;
            border-radius: 4px;
            background-color: #f8fafc;
        }
        .title {
            font-family: 'Plus Jakarta Sans', sans-serif;
            font-size: 22px;
            font-weight: 750;
            color: #0f172a;
            margin-top: 0;
            margin-bottom: 20px;
            text-align: center;
            letter-spacing: -0.3px;
        }
        .greeting {
            font-size: 16px;
            font-weight: 600;
            color: #1e293b;
            margin-bottom: 12px;
        }
        .body-text {
            font-size: 15px;
            line-height: 1.6;
            color: #475569;
            margin-bottom: 24px;
        }
        .details-box {
            background-color: #f1f5f9;
            border-radius: 12px;
            padding: 20px;
            margin-bottom: 25px;
            border: 1px solid #e2e8f0;
        }
        .details-title {
            font-size: 11px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 1.2px;
            color: #64748b;
            margin-bottom: 15px;
            border-bottom: 1px solid #cbd5e1;
            padding-bottom: 6px;
        }
        .detail-row {
            display: flex;
            justify-content: space-between;
            margin-bottom: 8px;
            font-size: 14px;
        }
        .detail-row:last-child {
            margin-bottom: 0;
        }
        .detail-label {
            color: #64748b;
            font-weight: 500;
        }
        .detail-value {
            color: #0f172a;
            font-weight: 600;
            text-align: right;
        }
        .detail-value.amount {
            color: #10b981;
            font-size: 16px;
            font-weight: 800;
        }
        .detail-value.mono {
            font-family: monospace;
        }
        .button-container {
            text-align: center;
            margin: 30px 0 10px 0;
        }
        .btn {
            display: inline-block;
            background-color: #3b82f6;
            color: #ffffff !important;
            text-decoration: none;
            padding: 12px 30px;
            font-size: 14px;
            font-weight: 600;
            border-radius: 8px;
            box-shadow: 0 4px 6px -1px rgba(59, 130, 246, 0.2), 0 2px 4px -1px rgba(59, 130, 246, 0.1);
            transition: all 0.2s ease-in-out;
        }
        .btn:hover {
            background-color: #2563eb;
        }
        .footer {
            text-align: center;
            padding: 25px 0 0 0;
            border-top: 1px solid #e2e8f0;
            margin-top: 35px;
        }
        .footer-text {
            font-size: 12px;
            color: #94a3b8;
            line-height: 1.5;
        }
    </style>
</head>
<body>
    <div class="wrapper">
        <div class="main-card">
            <div class="gradient-bar"></div>
            <div class="content">
                <div class="header">
                    <div class="logo-text">SG<span>CART</span><span class="logo-badge">Seller</span></div>
                </div>
                
                <h1 class="title">Payout Disbursed successfully!</h1>
                
                <div class="greeting">Hello {{ $seller->name }},</div>
                
                <p class="body-text">
                    We are pleased to inform you that a payout has been successfully recorded and processed for your shop <strong>{{ $seller->shop_name }}</strong>. The settled funds should reflect in your registered payment account shortly.
                </p>
                
                <div class="details-box">
                    <div class="details-title">Transaction Details</div>
                    <div class="detail-row">
                        <span class="detail-label">Disbursed Amount</span>
                        <span class="detail-value amount">₹{{ number_format($payout->amount, 2) }}</span>
                    </div>
                    <div class="detail-row">
                        <span class="detail-label">Settlement Date</span>
                        <span class="detail-value">{{ $payout->payout_date->format('d M Y, h:i A') }}</span>
                    </div>
                    <div class="detail-row">
                        <span class="detail-label">Payment Method</span>
                        <span class="detail-value">{{ ucwords(str_replace('_', ' ', $payout->payment_method)) }}</span>
                    </div>
                    @if($payout->transaction_reference)
                        <div class="detail-row">
                            <span class="detail-label">UTR / Txn Ref ID</span>
                            <span class="detail-value mono">{{ $payout->transaction_reference }}</span>
                        </div>
                    @endif
                    <div class="detail-row">
                        <span class="detail-label">Payout ID</span>
                        <span class="detail-value mono">#{{ $payout->ulid }}</span>
                    </div>
                </div>

                <p class="body-text">
                    To view the detailed break-up of order commissions settled under this payout, please log into your Seller Portal and navigate to your Payout History.
                </p>
                
                <div class="button-container">
                    <a href="{{ route('seller.payouts') }}" class="btn">View Payout History</a>
                </div>
                
                <div class="footer">
                    <p class="footer-text">
                        This is an automated transaction confirmation message. Please do not reply directly to this email.<br>
                        &copy; {{ date('Y') }} SGCart Marketplace. All Rights Reserved.
                    </p>
                </div>
            </div>
        </div>
    </div>
</body>
</html>
