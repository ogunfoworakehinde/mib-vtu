<!DOCTYPE html>
<html lang="en" data-theme="light">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', config('app.name'))</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <style>
        :root {
            --bg: #f5f7fb;
            --card-bg: #ffffff;
            --text: #1a1a2e;
            --primary: #0000CC;
            --primary-light: #4d4dff;
            --input-bg: #f8f9fa;
            --shadow: 0 8px 30px rgba(0,0,0,0.08);
            --radius: 16px;
        }
        [data-theme="dark"] {
            --bg: #0d0d19;
            --card-bg: #1a1a2e;
            --text: #e0e0ff;
            --primary: #7c73ff;
            --primary-light: #9f99ff;
            --input-bg: #2c2c3e;
            --shadow: 0 8px 30px rgba(0,0,0,0.6);
        }

        * { box-sizing: border-box; margin: 0; padding: 0; }
        body {
            background: var(--bg);
            color: var(--text);
            font-family: 'Inter', 'Segoe UI', system-ui, sans-serif;
            display: flex;
            align-items: center;
            justify-content: center;
            min-height: 100vh;
            transition: background 0.3s, color 0.3s;
            padding: 16px;
        }

        .auth-container {
            width: 100%;
            max-width: 420px;
            background: var(--card-bg);
            border-radius: var(--radius);
            padding: 2.5rem 2rem;
            box-shadow: var(--shadow);
            position: relative;
        }

        .logo-area {
            text-align: center;
            margin-bottom: 2rem;
        }
        .logo-area .logo-icon {
            font-size: 48px;
            color: var(--primary);
            display: block;
        }
        .logo-area h4 {
            color: var(--text);
            font-weight: 700;
            letter-spacing: -0.5px;
            margin-top: 0.5rem;
        }

        .form-label {
            color: var(--text);
            font-weight: 500;
            font-size: 14px;
            margin-bottom: 2px;
        }
        .form-control {
            background: var(--input-bg);
            color: var(--text);
            border: 1px solid rgba(128,128,128,0.25);
            border-radius: 10px;
            padding: 12px 16px;
            font-size: 15px;
            transition: 0.2s;
        }
        .form-control:focus {
            border-color: var(--primary);
            box-shadow: 0 0 0 0.2rem rgba(0,0,204,0.15);
            background: var(--input-bg);
        }
        .form-control::placeholder {
            color: rgba(128,128,128,0.6);
        }

        .btn-primary {
            background: var(--primary);
            border: none;
            border-radius: 10px;
            padding: 12px;
            font-weight: 600;
            transition: 0.2s;
        }
        .btn-primary:active { transform: scale(0.98); }

        .password-toggle {
            position: absolute;
            right: 14px;
            top: 38px;
            cursor: pointer;
            color: var(--text);
            opacity: 0.6;
            z-index: 10;
        }
        .password-toggle:hover { opacity: 1; }

        .theme-fab {
            position: absolute;
            top: 16px;
            right: 16px;
            width: 40px;
            height: 40px;
            background: var(--card-bg);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            box-shadow: 0 2px 8px rgba(0,0,0,0.1);
            cursor: pointer;
            z-index: 10;
            transition: background 0.3s;
        }
        .theme-fab i {
            font-size: 20px;
            color: var(--text);
        }

        .form-check-label {
            color: var(--text);
            font-size: 14px;
        }
        .form-check-input {
            border-color: rgba(128,128,128,0.4);
        }
    </style>
</head>
<body>
    <div class="auth-container">
        <!-- Theme toggle (floating button) -->
        <div class="theme-fab" id="themeFab">
            <i class="bi bi-moon-fill" id="themeIcon"></i>
        </div>

        <!-- Logo area -->
<div class="logo-area">
    <img src="{{ asset('images/logo.png') }}" alt="Logo" style="height: 60px; width: auto;" class="mb-2 rounded">
    <h4>{{ config('app.name', 'MIB GROUP') }}</h4>
</div>


        @yield('content')
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // CSRF token for AJAX
        window.csrfToken = '{{ csrf_token() }}';

        // Theme toggle (light ↔ dark)
        (function() {
            const saved = localStorage.getItem('vtu-theme') || 'light';
            document.documentElement.setAttribute('data-theme', saved);
            updateIcon(saved);
        })();
        function updateIcon(theme) {
            const icon = document.getElementById('themeIcon');
            icon.className = theme === 'dark' ? 'bi bi-sun-fill' : 'bi bi-moon-fill';
        }
        document.getElementById('themeFab').addEventListener('click', function() {
            let current = document.documentElement.getAttribute('data-theme');
            let next = current === 'dark' ? 'light' : 'dark';
            document.documentElement.setAttribute('data-theme', next);
            localStorage.setItem('vtu-theme', next);
            updateIcon(next);
        });

        // Password toggle helper
        function togglePassword(inputId, iconId) {
            const input = document.getElementById(inputId);
            const icon = document.getElementById(iconId);
            if (input.type === 'password') {
                input.type = 'text';
                icon.className = 'bi bi-eye password-toggle';
            } else {
                input.type = 'password';
                icon.className = 'bi bi-eye-slash password-toggle';
            }
            // Keep the absolute positioning
        }
    </script>
    @stack('scripts')
</body>
</html>
