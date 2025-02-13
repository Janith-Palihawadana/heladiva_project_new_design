<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>OTP Verification</title>
    <style>
        body {
            margin: 0;
            padding: 0;
            background-color: #f2f3f8;
            font-family: Arial, sans-serif;
        }

        .container {
            max-width: 670px;
            margin: 0 auto;
            background-color: #ffffff;
            padding: 20px;
            border-radius: 10px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        }

        .header {
            background-color: #007bff;
            text-align: center;
            padding: 10px 0;
            border-top-left-radius: 10px;
            border-top-right-radius: 10px;
        }

        .header h3 {
            color: white;
            font-size: 30px;
            margin: 0;
            text-align: center !important;
        }

        .content {
            padding: 20px 0;
            text-align: center;
        }

        .code {
            font-size: 36px;
            color: #007bff;
            margin: 20px 0;
            font-weight: 700;
        }

        .expiration {
            font-size: 14px;
            color: #8f8f8f;
        }

        .footer {
            text-align: center;
            margin-top: 20px;
            color: rgba(69, 80, 86, 0.7);
            width: 100% !important;
        }
    </style>
</head>

<body>
<div style="height: 50px"></div>
<div class="container">
    <div class="header">
     <h3>Authentication Code</h3>
    </div>
    <div class="content">
        <div style="height: 10px"></div>
        <p>Hi {{ $name }},</p>
        <p>There was recently a request to change the password on your account. If you requested this password change, please enter the code below on the two-factor verification screen, and you will be able to reset your password.</p>
        <div class="code">{{ $user_otp }}</div>
        <p class="expiration">Code expires: {{ $expire }} minutes</p>
        <p class="expiration" style="margin: 0">If you don’t want to change your password, just ignore this message.</p>
        <p class="expiration">Thank you for choosing KLMS.</p>
    </div>
    <div class="footer">
        &copy; <strong>{{ date('Y') }} KLMS. All rights reserved.</strong>
    </div>
</div>
<div style="height: 50px"></div>
</body>

</html>
