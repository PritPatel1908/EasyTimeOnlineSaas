<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Domain unavailable | EasyTime Online SaaS</title>
    <style>
        body { align-items: center; background: #f1f2f7; color: #17233c; display: flex; font-family: Arial, sans-serif; justify-content: center; margin: 0; min-height: 100vh; }
        .message { background: #fff; border-top: 4px solid #7367f0; box-shadow: 0 8px 30px rgba(23, 35, 60, .12); max-width: 520px; padding: 42px; text-align: center; width: calc(100% - 48px); }
        h1 { font-size: 28px; font-weight: 500; margin: 0 0 14px; }
        p { color: #67748e; line-height: 1.6; margin: 0; }
    </style>
</head>
<body>
    <main class="message">
        <h1>This domain is currently unavailable</h1>
        <p>Your workspace cannot be opened because this domain is {{ strtolower($domainStatus ?? 'not active') }}. Please contact your administrator for assistance.</p>
    </main>
</body>
</html>
