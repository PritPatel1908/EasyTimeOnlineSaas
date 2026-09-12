<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $tenantId }} | EasyTime Online SaaS</title>
    <style>
        :root {
            --ink: #18223b;
            --muted: #71809b;
            --line: #e7eaf1;
            --paper: #ffffff;
            --wash: #f5f7fb;
            --violet: #6f61e8;
            --violet-dark: #5145c7;
            --mint: #e4f6ef;
            --mint-ink: #14805b;
            --shadow: 0 18px 45px rgba(32, 43, 77, .08);
        }

        * { box-sizing: border-box; }
        body { background: var(--wash); color: var(--ink); font-family: "Trebuchet MS", "Segoe UI", sans-serif; margin: 0; }
        a { color: inherit; text-decoration: none; }
        .shell { display: flex; min-height: 100vh; }
        .sidebar { background: var(--paper); border-right: 1px solid var(--line); display: flex; flex-direction: column; padding: 28px 20px; width: 248px; }
        .brand { align-items: center; display: flex; font-size: 20px; font-weight: 700; gap: 10px; letter-spacing: .02em; }
        .brand img { height: 34px; object-fit: contain; width: 34px; }
        .brand span { color: var(--violet); }
        .eyebrow { color: var(--muted); font-size: 11px; letter-spacing: .13em; margin: 48px 12px 14px; text-transform: uppercase; }
        .nav-link { align-items: center; border-radius: 10px; color: var(--muted); display: flex; font-size: 14px; gap: 12px; margin: 4px 0; padding: 12px; }
        .nav-link.active, .nav-link:hover { background: #efedff; color: var(--violet-dark); }
        .nav-icon { align-items: center; background: #f1f3f8; border-radius: 8px; display: inline-flex; font-size: 15px; height: 30px; justify-content: center; width: 30px; }
        .nav-link.active .nav-icon { background: var(--violet); color: white; }
        .sidebar-foot { border-top: 1px solid var(--line); color: var(--muted); font-size: 12px; line-height: 1.5; margin-top: auto; padding: 18px 12px 0; }
        .main { flex: 1; min-width: 0; }
        .topbar { align-items: center; background: var(--paper); border-bottom: 1px solid var(--line); display: flex; justify-content: space-between; min-height: 82px; padding: 18px 5%; }
        .crumb { color: var(--muted); font-size: 13px; }
        .crumb strong { color: var(--ink); font-weight: 600; }
        .profile { align-items: center; display: flex; gap: 11px; }
        .avatar { align-items: center; background: #e9e6ff; border-radius: 50%; color: var(--violet-dark); display: flex; font-size: 13px; font-weight: 700; height: 38px; justify-content: center; width: 38px; }
        .profile small { color: var(--muted); display: block; font-size: 11px; margin-top: 3px; }
        .content { margin: 0 auto; max-width: 1180px; padding: 46px 5%; }
        .hero { align-items: flex-end; display: flex; gap: 30px; justify-content: space-between; margin-bottom: 32px; }
        h1 { font-size: clamp(28px, 4vw, 42px); letter-spacing: -.03em; line-height: 1.1; margin: 8px 0 12px; }
        .intro { color: var(--muted); font-size: 15px; line-height: 1.7; margin: 0; max-width: 590px; }
        .status { align-items: center; background: var(--mint); border-radius: 30px; color: var(--mint-ink); display: inline-flex; font-size: 13px; font-weight: 700; gap: 8px; padding: 10px 16px; white-space: nowrap; }
        .status i { background: #19a875; border-radius: 50%; height: 8px; width: 8px; }
        .grid { display: grid; gap: 18px; grid-template-columns: repeat(3, minmax(0, 1fr)); }
        .card { background: var(--paper); border: 1px solid var(--line); border-radius: 12px; box-shadow: var(--shadow); padding: 24px; }
        .card-label { color: var(--muted); font-size: 12px; letter-spacing: .08em; text-transform: uppercase; }
        .card-value { font-size: 23px; font-weight: 700; margin-top: 12px; overflow-wrap: anywhere; }
        .card-note { color: var(--muted); font-size: 13px; line-height: 1.5; margin-top: 8px; }
        .section { margin-top: 28px; }
        .section-head { align-items: center; display: flex; justify-content: space-between; margin-bottom: 14px; }
        h2 { font-size: 18px; margin: 0; }
        .section-head span { color: var(--muted); font-size: 13px; }
        .quick { align-items: center; display: flex; gap: 16px; }
        .quick-icon { align-items: center; background: #efedff; border-radius: 10px; color: var(--violet-dark); display: flex; font-size: 20px; height: 44px; justify-content: center; width: 44px; }
        .quick strong { display: block; font-size: 15px; }
        .quick p { color: var(--muted); font-size: 13px; margin: 5px 0 0; }
        .users { overflow-x: auto; }
        .users table { border-collapse: collapse; min-width: 520px; width: 100%; }
        .users th, .users td { border-bottom: 1px solid var(--line); padding: 14px 0; text-align: left; }
        .users th { color: var(--muted); font-size: 11px; letter-spacing: .08em; text-transform: uppercase; }
        .users td { font-size: 14px; }
        .users td:last-child, .users th:last-child { text-align: right; }
        @media (max-width: 760px) {
            .shell { display: block; }
            .sidebar { border-bottom: 1px solid var(--line); border-right: 0; padding: 18px 20px; width: auto; }
            .sidebar .eyebrow, .sidebar-foot { display: none; }
            .nav { display: flex; gap: 5px; margin-top: 16px; overflow-x: auto; }
            .nav-link { flex: 0 0 auto; margin: 0; padding: 8px 10px; }
            .nav-link:not(.active) { display: none; }
            .topbar { min-height: 68px; padding: 14px 20px; }
            .content { padding: 32px 20px; }
            .hero { align-items: flex-start; display: block; }
            .status { margin-top: 22px; }
            .grid { grid-template-columns: 1fr; }
        }
    </style>
</head>
<body>
    <div class="shell">
        <aside class="sidebar">
            <a class="brand" href="{{ url('/') }}"><img src="{{ asset('admin-dist/images/logo.png') }}" alt="EasyTime"><span>EASYTIME</span></a>
            <div class="eyebrow">Workspace</div>
            <nav class="nav" aria-label="Workspace navigation">
                <a class="nav-link active" href="{{ url('/') }}"><span class="nav-icon">&#8962;</span>Overview</a>
                <a class="nav-link" href="#"><span class="nav-icon">&#9638;</span>Employees</a>
                <a class="nav-link" href="#"><span class="nav-icon">&#9687;</span>Attendance</a>
                <a class="nav-link" href="#"><span class="nav-icon">&#9647;</span>Reports</a>
            </nav>
            <div class="sidebar-foot">EasyTime Online SaaS<br>Workspace portal</div>
        </aside>
        <main class="main">
            <header class="topbar">
                <div class="crumb">Workspace / <strong>Overview</strong></div>
                <div class="profile"><div class="avatar">{{ strtoupper(substr((string) $tenantId, 0, 2)) }}</div><div><strong>{{ $tenantId }}</strong><small>Tenant workspace</small></div></div>
            </header>
            <section class="content">
                <div class="hero">
                    <div><div class="card-label">Good to see you</div><h1>Welcome to your workspace.</h1><p class="intro">Manage your team's time, attendance, and daily operations from one clear and focused place.</p></div>
                    <div class="status"><i></i>Domain active</div>
                </div>
                <div class="grid">
                    <article class="card"><div class="card-label">Workspace</div><div class="card-value">{{ $tenantId }}</div><div class="card-note">Your active tenant workspace</div></article>
                    <article class="card"><div class="card-label">Current domain</div><div class="card-value">{{ $domain }}</div><div class="card-note">Secure access point for this workspace</div></article>
                    <article class="card"><div class="card-label">Employees</div><div class="card-value">{{ $userCount }}</div><div class="card-note">Users stored in this tenant database</div></article>
                </div>
                <div class="section"><div class="section-head"><h2>Quick access</h2><span>Start with a workspace area</span></div><article class="card quick"><div class="quick-icon">&#9687;</div><div><strong>Attendance overview</strong><p>Review attendance activity and keep your team on schedule.</p></div></article></div>
                <div class="section"><div class="section-head"><h2>Recent employees</h2><span>{{ $userCount }} total in this workspace</span></div><article class="card users"><table><thead><tr><th>Name</th><th>Email</th><th>ID</th></tr></thead><tbody>@forelse ($recentUsers as $user)<tr><td>{{ $user->name ?: 'Unnamed user' }}</td><td>{{ $user->email ?: 'No email' }}</td><td>#{{ $user->id }}</td></tr>@empty<tr><td colspan="3">No employees found in this tenant database.</td></tr>@endforelse</tbody></table></article></div>
            </section>
        </main>
    </div>
</body>
</html>
