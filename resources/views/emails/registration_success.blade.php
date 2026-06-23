<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Welcome to {{ config('app.name') }}</title>
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
        .features {
            background-color: #f9fafb;
            border-radius: 12px;
            padding: 24px;
            margin-top: 32px;
            border: 1px solid #f3f4f6;
        }
        .feature-title {
            font-weight: 600;
            color: #1f2937;
            font-size: 15px;
        }
        .feature-desc {
            color: #6b7280;
            font-size: 14px;
            margin-top: 2px;
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
                <h1 class="title">Welcome Aboard!</h1>
                <p class="greeting">Hi {{ $customer->name }},</p>
                <p class="body-text">
                    Thank you for registering at <strong>SG CART</strong>! We are thrilled to have you join our community. Your account has been successfully created, and you are now ready to explore our wide selection of premium products.
                </p>
                
                <div class="cta-container">
                    <a href="{{ $shopUrl }}" class="cta-button" style="color: #ffffff;">Start Exploring</a>
                </div>

                <p class="body-text">
                    If you have any questions or need assistance, feel free to contact our support team at any time.
                </p>

                <div class="features">
                    <table width="100%">
                        <tr>
                            <td width="32" valign="top" style="padding-top: 4px;">
                                <span style="font-size: 20px;">⚡</span>
                            </td>
                            <td>
                                <div class="feature-title">Lightning Fast Delivery</div>
                                <div class="feature-desc">Get your items delivered right to your doorstep in record time.</div>
                            </td>
                        </tr>
                        <tr><td height="16"></td></tr>
                        <tr>
                            <td width="32" valign="top" style="padding-top: 4px;">
                                <span style="font-size: 20px;">🔒</span>
                            </td>
                            <td>
                                <div class="feature-title">100% Secure Checkout</div>
                                <div class="feature-desc">Shop with peace of mind. Your personal data is completely safe.</div>
                            </td>
                        </tr>
                        <tr><td height="16"></td></tr>
                        <tr>
                            <td width="32" valign="top" style="padding-top: 4px;">
                                <span style="font-size: 20px;">🎉</span>
                            </td>
                            <td>
                                <div class="feature-title">Exclusive Member Deals</div>
                                <div class="feature-desc">Unlock special discounts, early access to sales, and unique offers.</div>
                            </td>
                        </tr>
                    </table>
                </div>
            </div>
        </div>
        <div class="footer">
            <p>&copy; {{ date('Y') }} SG CART. All rights reserved.</p>
            <p>You received this email because you registered at <a href="{{ config('app.url') }}">sgcart.com</a>.</p>
        </div>
    </div>
</body>
</html>
