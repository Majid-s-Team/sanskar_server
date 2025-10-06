<!DOCTYPE html>
<html>
<head>
    <title>Password Reset OTP</title>
</head>
<body>
    <h2>Hello {{ $name }},</h2>
    <p>You requested to reset your password. Please use the OTP below:</p>

    <h1 style="color:#2c3e50;">{{ $otp }}</h1>

    <p>This OTP will expire in 10 minutes.</p>
    <p>If you did not request this, please ignore this email.</p>
    <br>
    <p>Thanks,<br>Sanskar Academy Team</p>
</body>
</html>
