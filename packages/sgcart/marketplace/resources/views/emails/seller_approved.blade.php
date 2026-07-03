<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Welcome to SG CART SELLER - Your Shop is Approved!</title>
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
        .success-box {
            background-color: #ecfdf5;
            border: 1px solid #a7f3d0;
            border-radius: 12px;
            padding: 20px 24px;
            margin: 25px 0;
            text-align: center;
        }
        .success-label {
            margin: 0 0 6px 0;
            font-size: 11px;
            color: #059669;
            text-transform: uppercase;
            font-weight: 700;
            letter-spacing: 1px;
        }
        .shop-title {
            font-family: 'Plus Jakarta Sans', sans-serif;
            font-size: 20px;
            font-weight: 800;
            color: #047857;
            margin: 0;
        }
        .btn-container {
            text-align: center;
            margin: 30px 0 10px 0;
        }
        .cta-button {
            display: inline-block;
            background-color: #3b82f6;
            color: #ffffff !important;
            font-size: 15px;
            font-weight: 600;
            padding: 14px 30px;
            border-radius: 8px;
            text-decoration: none;
            box-shadow: 0 4px 12px 0 rgba(59, 130, 246, 0.2);
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
                                <h1 class="title">Your Shop is Approved!</h1>
                                <p class="greeting">Congratulations {{ $seller->name }},</p>
                                
                                <div class="success-box">
                                    <p class="success-label">Active Storefront</p>
                                    <h2 class="shop-title">{{ $seller->shop_name }}</h2>
                                </div>

                                <p class="body-text">
                                    We are thrilled to inform you that your seller profile has been approved! Your store is now active and you have full access to the SG CART SELLER portal.
                                </p>

                                <p class="body-text">
                                    You can now log in to upload your catalog, track incoming customer orders, manage shipments, and view your earnings.
                                </p>

                                <div class="btn-container">
                                    <a href="{{ route('seller.login') }}" class="cta-button">Access Seller Portal</a>
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
