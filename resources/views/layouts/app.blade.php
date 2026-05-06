<!DOCTYPE html>
<html lang="en" data-theme="{{ auth()->user()->theme ?? 'light' }}">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ config('app.name') }}</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <style>
        :root {
            --bg: #f5f7fb;
            --card-bg: #ffffff;
            --text: #1a1a2e;
            --primary: #0000CC;
            --primary-light: #4d4dff;
            --wallet-gradient: linear-gradient(135deg, #0000CC 0%, #8e2de2 100%);
            --shadow-sm: 0 2px 8px rgba(0,0,0,0.05);
            --shadow-md: 0 4px 16px rgba(0,0,0,0.08);
            --shadow-lg: 0 8px 30px rgba(0,0,0,0.12);
            --header-height: 56px;
            --header-margin: 8px;
            --footer-height: 70px;
            --radius: 16px;
            --close-btn-filter: none;
        }
        [data-theme="dark"] {
            --bg: #0d0d19;
            --card-bg: #1a1a2e;
            --text: #e0e0ff;
            --primary: #7c73ff;
            --primary-light: #9f99ff;
            --wallet-gradient: linear-gradient(135deg, #6c63ff 0%, #3f2b96 100%);
            --shadow-sm: 0 2px 8px rgba(0,0,0,0.4);
            --shadow-md: 0 4px 16px rgba(0,0,0,0.6);
            --shadow-lg: 0 8px 30px rgba(0,0,0,0.8);
            --close-btn-filter: invert(1);
        }

        * { box-sizing: border-box; margin: 0; padding: 0; }
        body {
            background: var(--bg);
            color: var(--text);
            font-family: 'Inter', 'Segoe UI', system-ui, sans-serif;
            padding-top: calc(var(--header-height) + var(--header-margin) * 2);
            padding-bottom: var(--footer-height);
            transition: background 0.3s, color 0.3s;
        }

        .card {
            background: var(--card-bg);
            border: none;
            border-radius: var(--radius);
            box-shadow: var(--shadow-md);
            transition: transform 0.2s ease, box-shadow 0.2s ease;
            color: var(--text);
        }
        .card:active { transform: scale(0.98); box-shadow: var(--shadow-lg); }

        .wallet-card {
            background: var(--wallet-gradient);
            color: white;
            border-radius: var(--radius);
            box-shadow: var(--shadow-lg);
        }

        .quick-action {
            background: var(--card-bg);
            border-radius: var(--radius);
            padding: 20px 10px;
            text-align: center;
            cursor: pointer;
            box-shadow: var(--shadow-sm);
            transition: all 0.2s ease;
            border: 1px solid rgba(0,0,0,0.03);
            color: var(--text);
        }
        .quick-action:active { transform: scale(0.95); box-shadow: var(--shadow-lg); }
        .quick-action i { font-size: 28px; color: var(--primary); transition: transform 0.2s; }
        .quick-action:active i { transform: scale(1.2); }

        /* ===== FLOATING HEADER ===== */
        .app-header {
            position: fixed;
            top: var(--header-margin);
            left: var(--header-margin);
            right: var(--header-margin);
            height: var(--header-height);
            background: var(--card-bg);
            color: var(--text);
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 0 20px;
            border-radius: 10px;
            box-shadow: var(--shadow-lg);
            z-index: 1000;
            transition: background 0.3s, color 0.3s;
        }
        .app-header .greeting {
            font-size: 16px;
            font-weight: 600;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
            margin-right: 10px;
        }

        /* Theme Switch */
        .theme-switch-wrapper {
            display: flex;
            align-items: center;
        }
        .theme-switch {
            position: relative;
            width: 52px;
            height: 28px;
            background: var(--bg);
            border: 1px solid rgba(128,128,128,0.3);
            border-radius: 28px;
            cursor: pointer;
            transition: background 0.3s, border-color 0.3s;
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 0 4px;
            box-shadow: inset 0 0 4px rgba(0,0,0,0.08);
        }
        .theme-switch i {
            font-size: 14px;
            color: var(--text);
            z-index: 1;
            pointer-events: none;
        }
        .theme-switch .slider {
            position: absolute;
            top: 2px;
            left: 2px;
            width: 24px;
            height: 24px;
            background: var(--primary);
            border-radius: 50%;
            transition: transform 0.3s, background 0.3s;
            box-shadow: 0 1px 4px rgba(0,0,0,0.2);
        }
        [data-theme="dark"] .theme-switch .slider {
            transform: translateX(24px);
        }

        /* Bottom Navigation (common) */
        .bottom-nav {
            position: fixed;
            bottom: 0; left: 0; right: 0;
            height: var(--footer-height);
            background: var(--card-bg);
            border-top: 1px solid rgba(0,0,0,0.08);
            display: flex;
            align-items: center;
            justify-content: space-around;
            z-index: 1000;
            box-shadow: 0 -4px 16px rgba(0,0,0,0.08);
            padding: 0 5px;
        }
        .bottom-nav a {
            display: flex;
            flex-direction: column;
            align-items: center;
            text-decoration: none;
            color: var(--text);
            opacity: 0.7;
            font-size: 11px;
            transition: 0.2s;
        }
        .bottom-nav a.active { opacity: 1; color: var(--primary); font-weight: 600; }
        .bottom-nav a i { font-size: 22px; margin-bottom: 2px; }

        .center-btn {
            width: 58px;
            height: 58px;
            border-radius: 50%;
            background: var(--primary);
            color: white !important;
            display: flex;
            align-items: center;
            justify-content: center;
            box-shadow: 0 6px 20px rgba(0,0,204,0.4);
            margin-top: -25px;
            opacity: 1 !important;
            transition: transform 0.2s;
        }
        .center-btn i { font-size: 28px; margin-bottom: 0; }
        .center-btn:active { transform: scale(0.9); }

        /* Toast container */
        .toast-container {
            position: fixed;
            top: calc(var(--header-height) + var(--header-margin) * 2);
            right: 10px;
            z-index: 9999;
        }
        .toast {
            background: var(--card-bg);
            color: var(--text);
            border-radius: var(--radius);
        }

        /* Forms */
        .form-control, .form-select {
            background: var(--bg);
            color: var(--text);
            border: 1px solid rgba(128,128,128,0.3);
            border-radius: 10px;
            transition: 0.2s;
        }
        .form-control:focus, .form-select:focus {
            border-color: var(--primary);
            box-shadow: 0 0 0 0.2rem rgba(0,0,204,0.15);
            background: var(--bg);
            color: var(--text);
        }

        .btn-primary {
            background: var(--primary);
            border: none;
            border-radius: 10px;
            font-weight: 600;
            transition: 0.2s;
        }
        .btn-primary:active { transform: scale(0.97); }

        /* ========== MODAL ========== */
        .modal-content {
            background-color: var(--card-bg) !important;
            color: var(--text) !important;
            border: none;
            border-radius: var(--radius);
            box-shadow: var(--shadow-lg);
        }
        .modal-header, .modal-body, .modal-footer {
            border-color: rgba(128,128,128,0.2);
            color: var(--text);
        }
        .modal-header .btn-close {
            filter: var(--close-btn-filter);
        }
        .modal-body .form-control,
        .modal-body .form-select {
            background: var(--bg);
            color: var(--text);
        }
        .modal-body .form-control:focus,
        .modal-body .form-select:focus {
            background: var(--bg);
            color: var(--text);
        }
        .modal {
            backdrop-filter: blur(4px);
        }
        .modal-dialog {
            margin-top: calc(var(--header-height) + var(--header-margin) * 2);
            margin-bottom: var(--footer-height);
            height: calc(100% - var(--header-height) - var(--footer-height) - var(--header-margin)*2);
            display: flex;
            align-items: center;
        }
        .modal-content {
            animation: slideUp 0.3s ease;
        }
        @keyframes slideUp {
            from { transform: translateY(50px); opacity: 0; }
            to { transform: translateY(0); opacity: 1; }
        }

            @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }
    </style>
</head>
<body>
<div id="globalLoader" style="display:none; position:fixed; top:0; left:0; width:100vw; height:100vh; background: var(--bg); z-index:9999; align-items:center; justify-content:center;">
    <div style="position:relative; width:100px; height:100px;">
        <img src="{{ asset('images/logo.png') }}" alt="Logo" style="width:80px; height:80px; position:absolute; top:50%; left:50%; transform:translate(-50%,-50%); z-index:2; border-radius:50%;">
        <div style="position:absolute; top:0; left:0; width:100%; height:100%; border:4px solid transparent; border-top-color: var(--primary); border-radius:50%; animation: spin 1s linear infinite;"></div>
    </div>
</div>
    @auth
    @php
        $hour = now()->hour;
        if($hour < 12) $greet = 'Good morning';
        elseif($hour < 17) $greet = 'Good afternoon';
        else $greet = 'Good evening';
    @endphp
    <div class="app-header">
        <span class="greeting">{{ $greet }}, {{ auth()->user()->full_name }}</span>
        <div class="theme-switch-wrapper">
            <div class="theme-switch" id="themeSwitch">
                <i class="bi bi-sun-fill"></i>
                <i class="bi bi-moon-fill"></i>
                <span class="slider"></span>
            </div>
        </div>
    </div>
    @endauth

    <main class="px-3 py-3">
        @yield('content')
    </main>

    <div class="toast-container" id="toastContainer"></div>

    {{-- ========== BOTTOM NAVIGATION ========== --}}
    @auth
        @if(request()->is('admin*'))
            {{-- Admin Bottom Bar --}}
            <div class="bottom-nav">
                <a href="{{ route('admin.dashboard') }}" id="admin-dashboard">
                    <i class="bi bi-speedometer2"></i><span>Dashboard</span>
                </a>
                <a href="{{ route('admin.users') }}" id="admin-users">
                    <i class="bi bi-people"></i><span>Users</span>
                </a>
                <a href="{{ route('admin.transactions') }}" id="admin-transactions">
                    <i class="bi bi-list-ul"></i><span>Logs</span>
                </a>
                <a href="{{ route('dashboard') }}" class="text-danger">
                    <i class="bi bi-house-door"></i><span>App</span>
                </a>
            </div>
        @else
            {{-- User Bottom Bar --}}
            <div class="bottom-nav">
                <a href="{{ route('transactions') }}" id="nav-history">
                    <i class="bi bi-clock-history"></i><span>History</span>
                </a>
                <a href="{{ route('support') }}" id="nav-support">
                    <i class="bi bi-headset"></i><span>Support</span>
                </a>
                <a href="{{ route('dashboard') }}" class="center-btn" id="nav-dashboard">
                    <i class="bi bi-house-door-fill"></i>
                </a>
                <a href="{{ route('profile') }}" id="nav-profile">
                    <i class="bi bi-person-circle"></i><span>Profile</span>
                </a>
                <a href="{{ route('logout') }}">
                    <i class="bi bi-box-arrow-right"></i><span>Logout</span>
                </a>
            </div>
        @endif
    @endauth

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://js.paystack.co/v1/inline.js"></script>
    <script>
        const csrfToken = '{{ csrf_token() }}';

        // Theme Switch
        const themeSwitch = document.getElementById('themeSwitch');
        const html = document.documentElement;
        if(themeSwitch) {
            themeSwitch.addEventListener('click', function() {
                let current = html.getAttribute('data-theme');
                let next = (current === 'light') ? 'dark' : 'light';
                html.setAttribute('data-theme', next);
                fetch('{{ route("theme.update") }}', {
                    method:'POST',
                    headers: {'Content-Type':'application/json','X-CSRF-TOKEN':csrfToken},
                    body: JSON.stringify({theme: next})
                });
            });
        }

        // Active bottom nav
        const currentPath = window.location.pathname;
        document.querySelectorAll('.bottom-nav a').forEach(el => el.classList.remove('active'));

        @if(request()->is('admin*'))
            if(currentPath === '/admin' || currentPath === '/admin/dashboard') document.getElementById('admin-dashboard').classList.add('active');
            else if(currentPath.startsWith('/admin/users')) document.getElementById('admin-users').classList.add('active');
            else if(currentPath.startsWith('/admin/transactions')) document.getElementById('admin-transactions').classList.add('active');
        @else
            if(currentPath === '/' || currentPath === '/dashboard') document.getElementById('nav-dashboard').classList.add('active');
            else if(currentPath.startsWith('/transactions')) document.getElementById('nav-history').classList.add('active');
            else if(currentPath.startsWith('/support')) document.getElementById('nav-support').classList.add('active');
            else if(currentPath.startsWith('/profile')) document.getElementById('nav-profile').classList.add('active');
        @endif

        // Toast function
        function showToast(message, type = 'info') {
            const container = document.getElementById('toastContainer');
            const colors = {
                info: '#0d6efd',
                success: '#198754',
                warning: '#ffc107',
                danger: '#dc3545'
            };
            const toastEl = document.createElement('div');
            toastEl.className = 'toast show align-items-center text-white border-0 mb-2';
            toastEl.style.backgroundColor = colors[type] || colors.info;
            toastEl.style.minWidth = '200px';
            toastEl.setAttribute('role', 'alert');
            toastEl.innerHTML = `
                <div class="d-flex">
                    <div class="toast-body">${message}</div>
                    <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast"></button>
                </div>`;
            container.appendChild(toastEl);
            setTimeout(() => {
                toastEl.classList.remove('show');
                setTimeout(() => toastEl.remove(), 300);
            }, 4000);
        }
    </script>
    @stack('scripts')
<script>
    // Global loader logic
    const loader = document.getElementById('globalLoader');
    function showLoader() { loader.style.display = 'flex'; }
    function hideLoader() { loader.style.display = 'none'; }

    window.addEventListener('beforeunload', showLoader);
    window.addEventListener('load', hideLoader);

    document.addEventListener('click', function(e) {
        let el = e.target.closest('a');
        if(el && el.getAttribute('href') && !el.getAttribute('href').startsWith('#') && !el.getAttribute('target')) {
            showLoader();
        }
    });

    document.addEventListener('submit', function(e) {
        if(!e.target.hasAttribute('data-no-loader')) {
            showLoader();
        }
    });
</script>
</body>
</html>
