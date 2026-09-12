@php
    $filename = Route::currentRouteName(); // use route name instead of PHP_SELF

    $acronyms = ['ui', 'ai', 'api', 'it', 'hr', 'sla', 'seo', 'ip', 'faq', 'pos', 'rtl', 'css', 'js', 'gdpr', 'otp', 'sms'];

    if ($filename === 'index') {
        $title = 'Admin Dashboard';
    } else {
        $parts = explode('-', str_replace('ui-', '', strtolower($filename)));

        $hasIcon = false;
        $hasChart = false;
        $cleaned_parts = [];

        foreach ($parts as $part) {
            if ($part === 'icon') {
                $hasIcon = true;
                continue;
            }
            if ($part === 'chart') {
                $hasChart = true;
                continue;
            }
            if ($part === 'all') {
                continue;
            }
            $cleaned_parts[] = $part;
        }

        $formatted_parts = array_map(function ($word) use ($acronyms) {
            return in_array($word, $acronyms) ? strtoupper($word) : ucfirst($word);
        }, $cleaned_parts);

        if ($hasIcon) {
            $formatted_parts[] = 'Icons';
        }
        if ($hasChart) {
            $formatted_parts[] = 'Charts';
        }

        $title = implode(' ', $formatted_parts);
    }
@endphp

<!-- Meta Tags -->
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title> {{ $title }} | SmartHR - Advanced Bootstrap 5 Multipurpose Admin Dashboard Template for HRM, Payroll & CRM</title>
<meta name="description" content="SmartHR - An advanced Bootstrap 5 admin dashboard template for HRM and CRM. Ideal for managing employee records, payroll, attendance, recruitment, and team performance with an intuitive and responsive design. Perfect for HR teams and business managers looking to streamline workforce management.">
<meta name="keywords" content="HR dashboard template, HRM admin template, Bootstrap 5 HR dashboard, workforce management dashboard, employee management system, payroll dashboard, HR analytics, admin dashboard, CRM admin template, human resources management, HR admin template, team management dashboard, recruitment dashboard, employee attendance system, performance management, HR CRM, HR dashboard HTML, Bootstrap HR template, employee engagement, HR software, project management dashboard">
<meta name="author" content="Dreams Technologies">
<meta name="robots" content="noindex, nofollow" />
