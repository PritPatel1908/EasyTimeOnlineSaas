<!DOCTYPE html>
<html lang="en">
<body>
    <p>Hello {{ $userName ?: 'there' }},</p>
    <p>Click the link below to verify your new email address:</p>
    <p><a href="{{ $verificationUrl }}">Verify email address</a></p>
    <p>This link expires in one hour. If you did not request this change, you can ignore this email.</p>
</body>
</html>
