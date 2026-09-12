<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title', 'Pollo Admin')</title>
    <link rel="shortcut icon" href="{{ asset('admin-dist/images/favicon.ico') }}">
    <link rel="stylesheet" href="{{ asset('admin-dist/vendors/bootstrap/css/bootstrap.min.css') }}">
    <link rel="stylesheet" href="{{ asset('admin-dist/vendors/jquery-ui/jquery-ui.min.css') }}">
    <link rel="stylesheet" href="{{ asset('admin-dist/vendors/jquery-ui/jquery-ui.theme.min.css') }}">
    <link rel="stylesheet" href="{{ asset('admin-dist/vendors/simple-line-icons/css/simple-line-icons.css') }}">
    <link rel="stylesheet" href="{{ asset('admin-dist/vendors/flags-icon/css/flag-icon.min.css') }}">
    <link rel="stylesheet" href="{{ asset('admin-dist/vendors/flag-select/css/flags.css') }}">
    <link rel="stylesheet" href="{{ asset('admin-dist/vendors/morris/morris.css') }}">
    <link rel="stylesheet" href="{{ asset('admin-dist/vendors/weather-icons/css/pe-icon-set-weather.min.css') }}">
    <link rel="stylesheet" href="{{ asset('admin-dist/vendors/chartjs/Chart.min.css') }}">
    <link rel="stylesheet" href="{{ asset('admin-dist/vendors/starrr/starrr.css') }}">
    <link rel="stylesheet" href="{{ asset('admin-dist/vendors/bootstrap-tour/css/bootstrap-tour-standalone.min.css') }}">
    <link rel="stylesheet" href="{{ asset('admin-dist/vendors/fontawesome/css/all.min.css') }}">
    <link rel="stylesheet" href="{{ asset('admin-dist/vendors/ionicons/css/ionicons.min.css') }}">
    <link rel="stylesheet" href="{{ asset('admin-dist/css/main.css') }}">
    @vite(['resources/css/admin.css', 'resources/js/admin.js'])
    @stack('styles')
</head>
<body id="main-container" class="default">
    <div class="se-pre-con"><img src="{{ asset('admin-dist/images/logo.png') }}" alt="Pollo" width="23" class="img-fluid"></div>
    @include('layouts.partials.header')
    @include('layouts.partials.sidebar')
    <main>
        @yield('content')
    </main>
    @include('layouts.partials.footer')
    <script src="{{ asset('admin-dist/vendors/jquery/jquery-3.3.1.min.js') }}"></script>
    <script src="{{ asset('admin-dist/vendors/jquery-ui/jquery-ui.min.js') }}"></script>
    <script src="{{ asset('admin-dist/vendors/moment/moment.js') }}"></script>
    <script src="{{ asset('admin-dist/vendors/bootstrap/js/bootstrap.bundle.min.js') }}"></script>
    <script src="{{ asset('admin-dist/vendors/slimscroll/jquery.slimscroll.min.js') }}"></script>
    <script src="{{ asset('admin-dist/vendors/flag-select/js/jquery.flagstrap.min.js') }}"></script>
    <script src="{{ asset('admin-dist/js/app.js') }}"></script>
    @stack('scripts')
</body>
</html>
