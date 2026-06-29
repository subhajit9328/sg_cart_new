<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Verify Your Email Address - {{ config('app.name') }}</title>
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
        .otp-container {
            text-align: center;
            margin: 35px 0;
        }
        .otp-code {
            display: inline-block;
            font-family: 'Courier New', Courier, monospace;
            font-size: 38px;
            font-weight: 700;
            letter-spacing: 8px;
            padding: 16px 32px;
            background-color: #f9fafb;
            border-radius: 12px;
            border: 2px dashed #e5e7eb;
            color: #4f46e5;
            box-shadow: inset 0 2px 4px rgba(0, 0, 0, 0.02);
        }
        .info-box {
            background-color: #fef3c7;
            border-left: 4px solid #f59e0b;
            color: #92400e;
            border-radius: 6px;
            padding: 16px;
            margin-top: 32px;
            font-size: 14px;
            line-height: 1.5;
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
                <h1 class="title">Verify Your Email Address</h1>
                <p class="greeting">Hi {{ $customer->name }},</p>
                <p class="body-text">
                    Thank you for registering at <strong>SG CART</strong>! To complete your registration and activate your account, please enter the following 6-digit verification code:
                </p>
                
                <div class="otp-container">
                    <div class="otp-code">{{ $otp }}</div>
                </div>

                <div class="info-box">
                    <strong>Please note:</strong> This code is valid for <strong>5 minutes</strong>. If the code expires, you can generate a new one on the verification screen.
                </div>

                <p class="body-text" style="margin-top: 24px;">
                    If you did not create an account with us, you can safely ignore this email.
                </p>
            </div>
        </div>
        <div class="footer">
            <p>&copy; {{ date('Y') }} SG CART. All rights reserved.</p>
            <p>You received this email because you registered at <a href="{{ config('app.url') }}">sgcart.com</a>.</p>
        </div>
    </div>
</body>
</html>
