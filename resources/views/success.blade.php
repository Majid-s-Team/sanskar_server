<!DOCTYPE html>
<html>
<head><title>Payment Success</title></head>
<body style="font-family: Arial; text-align:center; margin-top:50px;">
    <h1>Payment Successful</h1>
    <p>Thank you for your payment.</p>
    <p><strong>Amount:</strong> {{ $amount ?? 'N/A' }} {{ $currency ?? '' }}</p>
    <p><small>Session: {{ $session->id ?? 'N/A' }}</small></p>
    <a href="{{ url('/') }}">Go Home</a>
</body>
</html>