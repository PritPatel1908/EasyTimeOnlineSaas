<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Verify sign in | EasyTime Online SaaS</title>
    <link rel="stylesheet" href="{{ asset('admin-dist/vendors/bootstrap/css/bootstrap.min.css') }}">
    <link rel="stylesheet" href="{{ asset('admin-dist/css/main.css') }}">
    <style>
        html, body { min-height: 100%; margin: 0; }
        main.auth-shell { min-height: 100vh; width: 100%; max-width: none; margin: 0; box-sizing: border-box; }
    </style>
</head>
<body class="bg-light">
    <main class="container auth-shell d-flex align-items-center justify-content-center py-5">
        <div class="card shadow-sm w-100" style="max-width: 430px;">
            <div class="card-body p-4 p-md-5">
                <h1 class="h3 mb-2">Check your email</h1>
                <p class="text-muted mb-4">Enter the 6-digit code sent to {{ $email }}.</p>
                @include('partials.flash-alerts')
                <form method="POST" action="{{ route('auth.otp.verify', absolute: false) }}">
                    @csrf
                    <div class="form-group"><label for="otp">Verification code</label><input id="otp" name="otp" type="text" inputmode="numeric" autocomplete="one-time-code" class="form-control text-center" maxlength="6" required autofocus></div>
                    <button type="submit" class="btn btn-primary btn-block">Verify and sign in</button>
                </form>
                <form method="POST" action="{{ route('auth.otp.resend', absolute: false) }}" class="text-center mt-3">@csrf<button type="submit" class="btn btn-link">Send a new code</button></form>
                <a href="{{ route('login', absolute: false) }}" class="d-block text-center small">Use a different account</a>
            </div>
        </div>
    </main>
    @stack('scripts')
</body>
</html>
