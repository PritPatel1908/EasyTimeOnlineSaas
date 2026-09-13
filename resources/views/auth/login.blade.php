<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Sign in | EasyTime Online SaaS</title>
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
                <h1 class="h3 mb-2">Central administration</h1>
                <p class="text-muted mb-4">Sign in with your account to continue.</p>
                @include('partials.flash-alerts')
                <form method="POST" action="{{ route('login', absolute: false) }}">
                    @csrf
                    <div class="form-group"><label for="email">Email address</label><input id="email" name="email" type="email" class="form-control" value="{{ old('email') }}" required autofocus></div>
                    <div class="form-group"><label for="password">Password</label><input id="password" name="password" type="password" class="form-control" required></div>
                    <button type="submit" class="btn btn-primary btn-block">Continue</button>
                </form>
            </div>
        </div>
    </main>
    @stack('scripts')
</body>
</html>
