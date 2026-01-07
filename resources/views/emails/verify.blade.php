<!DOCTYPE html>
<html>
<head>
    <title>Verify Your AuraNexus Account</title>
</head>
<body>
    <h1>Hello {{ $username }},</h1>
    <p>Thanks for registering at AuraNexus!</p>
    <p>Please click the link below to verify your email and activate your account:</p>
    <p><a href="{{ $verificationUrl }}">{{ $verificationUrl }}</a></p>
    <p>This link will expire in 24 hours.</p>
    <p>— AuraNexus Team</p>
</body>
</html>
