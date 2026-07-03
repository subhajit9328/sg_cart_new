<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Shop Profile Submitted - {{ config('app.name') }} SELLER</title>
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
            background: linear-gradient(90deg, #3b82f6 0%, #6366f1 100%);
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
            background-color: #f8fafc;
            border: 1px solid #e2e8f0;
            border-radius: 12px;
            padding: 20px 24px;
            margin: 25px 0;
        }
        .detail-row {
            margin-bottom: 12px;
            font-size: 14px;
            color: #475569;
            line-height: 1.5;
        }
        .detail-row:last-child {
            margin-bottom: 0;
        }
        .detail-label {
            font-weight: 600;
            color: #1e293b;
            display: inline-block;
            width: 110px;
        }
        .status-badge {
            display: inline-block;
            background-color: #fffbeb;
            border: 1px solid #fcd34d;
            color: #d97706;
            font-size: 11px;
            font-weight: 700;
            padding: 6px 16px;
            border-radius: 20px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            margin-top: 15px;
        }
        .footer {
            text-align: center;
            margin-top: 30px;
            font-size: 13px;
            color: #64748b;
            line-height: 1.5;
        }
        .footer a {
            color: #3b82f6;
            text-decoration: none;
            font-weight: 500;
        }
    </style>
</head>
<body>
    <table width="100%" class="wrapper">
        <tr>
            <td align="center">
                <table width="100%" max-width="580px" class="main-card">
                    <tr>
                        <td>
                            <div class="gradient-bar"></div>
                            <div class="content">
                                <div class="header">
                                    <span class="logo-text">SG<span>CART</span></span>
                                    <span class="logo-badge">Seller</span>
                                </div>
                                <h1 class="title">Shop Profile Submitted</h1>
                                <p class="greeting">Hi {{ $seller->name }},</p>
                                <p class="body-text">
                                    Thank you for completing your shop onboarding! We have received your business profile and it is now queued for administrator review.
                                </p>
                                
                                <div class="details-box">
                                    <div class="detail-row">
                                        <span class="detail-label">Shop Name:</span>
                                        <span>{{ $seller->shop_name }}</span>
                                    </div>
                                    <div class="detail-row">
                                        <span class="detail-label">Contact No:</span>
                                        <span>{{ $seller->phone_no }}</span>
                                    </div>
                                    <div class="detail-row">
                                        <span class="detail-label">Address:</span>
                                        <span>{{ $seller->address }}</span>
                                    </div>
                                </div>

                                <p class="body-text">
                                    Our team is currently reviewing your submission to verify registration details. You will receive an automated email notification once your shop has been approved.
                                </p>

                                <div style="text-align: center;">
                                    <span class="status-badge">Under Review</span>
                                </div>
                            </div>
                        </td>
                    </tr>
                </table>
                <div class="footer">
                    <p style="margin: 0 0 4px 0;">&copy; {{ date('Y') }} SG CART. All rights reserved.</p>
                    <p style="margin: 0;">You received this email because you registered as a seller at <a href="{{ config('app.url') }}">sgcart.com</a>.</p>
                </div>
            </td>
        </tr>
    </table>
</body>
</html>
