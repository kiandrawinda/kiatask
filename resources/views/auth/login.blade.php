<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Login — Between Us</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=DM+Sans:wght@400;500;600;700;800&family=DM+Serif+Display:ital@0;1&display=swap" rel="stylesheet">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <style>
        *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }

        body {
            font-family: 'DM Sans', sans-serif;
            min-height: 100vh;
            background: #0f0a1e;
            display: flex;
            overflow: hidden;
        }

        /* ── Loading Overlay ── */
        #loading-overlay {
            position: fixed;
            inset: 0;
            background: #0f0a1e;
            z-index: 9999;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            transition: opacity 0.5s ease, visibility 0.5s ease;
        }

        #loading-overlay.hide {
            opacity: 0;
            visibility: hidden;
        }

        .loader-logo {
            font-family: 'DM Serif Display', serif;
            font-size: 2rem;
            color: white;
            margin-bottom: 2rem;
            animation: fadeUp 0.6s ease forwards;
        }

        .loader-logo span { color: #ec4899; }

        .loader-hearts {
            display: flex;
            gap: 0.6rem;
            animation: fadeUp 0.6s 0.1s ease forwards;
            opacity: 0;
        }

        .loader-heart {
            width: 12px; height: 12px;
            background: #7c3aed;
            border-radius: 50%;
            animation: bounce 1.2s ease-in-out infinite;
        }

        .loader-heart:nth-child(1) { animation-delay: 0s; background: #7c3aed; }
        .loader-heart:nth-child(2) { animation-delay: 0.2s; background: #a855f7; }
        .loader-heart:nth-child(3) { animation-delay: 0.4s; background: #ec4899; }

        @keyframes bounce {
            0%, 100% { transform: translateY(0); opacity: 0.5; }
            50% { transform: translateY(-12px); opacity: 1; }
        }

        @keyframes fadeUp {
            from { opacity: 0; transform: translateY(16px); }
            to { opacity: 1; transform: translateY(0); }
        }

        /* ── Left Panel ── */
        .left-panel {
            flex: 1;
            position: relative;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 3rem;
            overflow: hidden;
        }

        .left-bg {
            position: absolute;
            inset: 0;
            background: linear-gradient(135deg, #1a0533 0%, #0f0a1e 50%, #1a0a2e 100%);
        }

        /* Floating orbs */
        .orb {
            position: absolute;
            border-radius: 50%;
            filter: blur(60px);
            opacity: 0.35;
            animation: drift 8s ease-in-out infinite alternate;
        }

        .orb-1 { width: 400px; height: 400px; background: #7c3aed; top: -100px; left: -100px; animation-delay: 0s; }
        .orb-2 { width: 300px; height: 300px; background: #ec4899; bottom: -50px; right: -50px; animation-delay: 2s; }
        .orb-3 { width: 200px; height: 200px; background: #a855f7; top: 50%; left: 60%; animation-delay: 4s; }

        @keyframes drift {
            from { transform: translate(0, 0) scale(1); }
            to { transform: translate(30px, 20px) scale(1.1); }
        }

        /* Floating particles */
        .particle {
            position: absolute;
            font-size: 1.2rem;
            opacity: 0;
            animation: float-up 6s ease-in infinite;
        }

        @keyframes float-up {
            0% { transform: translateY(100vh) rotate(0deg); opacity: 0; }
            10% { opacity: 0.6; }
            90% { opacity: 0.4; }
            100% { transform: translateY(-100px) rotate(360deg); opacity: 0; }
        }

        .left-content {
            position: relative;
            z-index: 1;
            text-align: center;
            color: white;
            max-width: 420px;
        }

        .brand-logo {
            font-family: 'DM Serif Display', serif;
            font-size: 3rem;
            line-height: 1;
            margin-bottom: 0.5rem;
            animation: fadeUp 0.8s ease forwards;
        }

        .brand-logo span { color: #ec4899; }

        .brand-tagline {
            font-size: 1rem;
            color: rgba(255,255,255,0.55);
            margin-bottom: 3rem;
            font-weight: 400;
            animation: fadeUp 0.8s 0.1s ease forwards;
            opacity: 0;
        }

        .feature-list {
            display: flex;
            flex-direction: column;
            gap: 1rem;
            animation: fadeUp 0.8s 0.2s ease forwards;
            opacity: 0;
        }

        .feature-item {
            display: flex;
            align-items: center;
            gap: 1rem;
            background: rgba(255,255,255,0.06);
            border: 1px solid rgba(255,255,255,0.1);
            border-radius: 14px;
            padding: 1rem 1.25rem;
            text-align: left;
            backdrop-filter: blur(10px);
            transition: all 0.3s;
        }

        .feature-item:hover {
            background: rgba(255,255,255,0.1);
            transform: translateX(4px);
        }

        .feature-icon {
            font-size: 1.5rem;
            width: 44px; height: 44px;
            background: rgba(124,58,237,0.3);
            border-radius: 12px;
            display: flex; align-items: center; justify-content: center;
            flex-shrink: 0;
        }

        .feature-text strong {
            display: block;
            font-size: 0.9rem;
            font-weight: 700;
            color: white;
            margin-bottom: 0.1rem;
        }

        .feature-text span {
            font-size: 0.78rem;
            color: rgba(255,255,255,0.5);
        }

        /* ── Right Panel (Form) ── */
        .right-panel {
            width: 480px;
            flex-shrink: 0;
            background: white;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 3rem 2.5rem;
            position: relative;
            animation: slideLeft 0.6s 0.4s ease forwards;
            opacity: 0;
            transform: translateX(30px);
        }

        @keyframes slideLeft {
            to { opacity: 1; transform: translateX(0); }
        }

        .right-panel::before {
            content: '';
            position: absolute;
            top: 0; left: -1px; bottom: 0;
            width: 1px;
            background: linear-gradient(to bottom, transparent, #7c3aed, #ec4899, transparent);
        }

        .form-container { width: 100%; max-width: 380px; }

        .form-header { margin-bottom: 2rem; }

        .form-title {
            font-family: 'DM Serif Display', serif;
            font-size: 2rem;
            color: #1a1a2e;
            line-height: 1.2;
            margin-bottom: 0.4rem;
        }

        .form-title em { color: #7c3aed; font-style: italic; }

        .form-subtitle {
            font-size: 0.88rem;
            color: #9ca3af;
            font-weight: 400;
        }

        /* Session Status */
        .session-status {
            background: #ecfdf5;
            color: #065f46;
            font-size: 0.85rem;
            font-weight: 500;
            padding: 0.75rem 1rem;
            border-radius: 12px;
            margin-bottom: 1.25rem;
            border: 1px solid #a7f3d0;
        }

        /* Input Group */
        .input-group {
            margin-bottom: 1.1rem;
        }

        .input-label {
            display: block;
            font-size: 0.83rem;
            font-weight: 700;
            color: #374151;
            margin-bottom: 0.45rem;
            letter-spacing: 0.2px;
        }

        .input-wrap {
            position: relative;
        }

        .input-icon {
            position: absolute;
            left: 1rem;
            top: 50%;
            transform: translateY(-50%);
            font-size: 1rem;
            pointer-events: none;
        }

        .form-input {
            width: 100%;
            padding: 0.75rem 1rem 0.75rem 2.75rem;
            border: 1.5px solid #e5e7eb;
            border-radius: 14px;
            font-size: 0.92rem;
            font-family: 'DM Sans', sans-serif;
            color: #1f2937;
            background: #fafafa;
            transition: all 0.2s;
            outline: none;
        }

        .form-input:focus {
            border-color: #7c3aed;
            background: white;
            box-shadow: 0 0 0 4px rgba(124,58,237,0.08);
        }

        .form-input.error {
            border-color: #ef4444;
            background: #fef2f2;
        }

        .error-msg {
            font-size: 0.75rem;
            color: #ef4444;
            margin-top: 0.35rem;
            font-weight: 500;
        }

        /* Row (remember + forgot) */
        .form-row {
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin-bottom: 1.5rem;
        }

        .remember-label {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            font-size: 0.83rem;
            color: #6b7280;
            cursor: pointer;
            font-weight: 500;
        }

        .remember-label input[type="checkbox"] {
            width: 15px; height: 15px;
            accent-color: #7c3aed;
            cursor: pointer;
        }

        .forgot-link {
            font-size: 0.83rem;
            color: #7c3aed;
            text-decoration: none;
            font-weight: 600;
            transition: color 0.2s;
        }

        .forgot-link:hover { color: #6d28d9; text-decoration: underline; }

        /* Submit Button */
        .btn-submit {
            width: 100%;
            padding: 0.9rem;
            background: linear-gradient(135deg, #7c3aed, #ec4899);
            color: white;
            border: none;
            border-radius: 14px;
            font-size: 0.97rem;
            font-weight: 700;
            font-family: 'DM Sans', sans-serif;
            cursor: pointer;
            box-shadow: 0 6px 20px rgba(124,58,237,0.35);
            transition: all 0.2s;
            position: relative;
            overflow: hidden;
        }

        .btn-submit::after {
            content: '';
            position: absolute;
            inset: 0;
            background: rgba(255,255,255,0.1);
            opacity: 0;
            transition: opacity 0.2s;
        }

        .btn-submit:hover::after { opacity: 1; }
        .btn-submit:hover { transform: translateY(-2px); box-shadow: 0 8px 28px rgba(124,58,237,0.45); }
        .btn-submit:active { transform: translateY(0); }

        .btn-submit.loading {
            pointer-events: none;
            opacity: 0.8;
        }

        /* Divider */
        .divider {
            display: flex;
            align-items: center;
            gap: 0.75rem;
            margin: 1.25rem 0;
            color: #d1d5db;
            font-size: 0.8rem;
        }

        .divider::before, .divider::after {
            content: '';
            flex: 1;
            height: 1px;
            background: #e5e7eb;
        }

        /* Footer link */
        .form-footer {
            text-align: center;
            font-size: 0.85rem;
            color: #9ca3af;
            font-weight: 400;
        }

        .form-footer a {
            color: #7c3aed;
            font-weight: 700;
            text-decoration: none;
            transition: color 0.2s;
        }

        .form-footer a:hover { color: #6d28d9; text-decoration: underline; }

        /* Submit loading spinner */
        .btn-spinner {
            display: none;
            width: 18px; height: 18px;
            border: 2.5px solid rgba(255,255,255,0.4);
            border-top-color: white;
            border-radius: 50%;
            animation: spin 0.7s linear infinite;
            margin: 0 auto;
        }

        @keyframes spin { to { transform: rotate(360deg); } }

        /* Responsive */
        @media (max-width: 768px) {
            body { flex-direction: column; overflow: auto; }
            .left-panel { min-height: 280px; padding: 2rem; }
            .right-panel { width: 100%; padding: 2rem 1.5rem; }
            .feature-list { display: none; }
            .brand-tagline { margin-bottom: 0; }
        }
    </style>
</head>
<body>

    {{-- Loading Overlay --}}
    <div id="loading-overlay">
        <div class="loader-logo">Between<span>Us</span> 💑</div>
        <div class="loader-hearts">
            <div class="loader-heart"></div>
            <div class="loader-heart"></div>
            <div class="loader-heart"></div>
        </div>
    </div>

    {{-- Left Panel --}}
    <div class="left-panel">
        <div class="left-bg"></div>
        <div class="orb orb-1"></div>
        <div class="orb orb-2"></div>
        <div class="orb orb-3"></div>

        {{-- Floating particles --}}
        <div class="particle" style="left:10%; animation-delay:0s; animation-duration:7s">💜</div>
        <div class="particle" style="left:30%; animation-delay:1.5s; animation-duration:8s">🌸</div>
        <div class="particle" style="left:55%; animation-delay:3s; animation-duration:6s">✨</div>
        <div class="particle" style="left:75%; animation-delay:0.8s; animation-duration:9s">💖</div>
        <div class="particle" style="left:85%; animation-delay:2.3s; animation-duration:7.5s">🍅</div>

        <div class="left-content">
            <div class="brand-logo">Between<span>Us</span> 💑</div>
            <p class="brand-tagline">Your couple productivity companion</p>

            <div class="feature-list">
                <div class="feature-item">
                    <div class="feature-icon">🎯</div>
                    <div class="feature-text">
                        <strong>Couple Goals</strong>
                        <span>Dream it, set it, crush it together</span>
                    </div>
                </div>
                <div class="feature-item">
                    <div class="feature-icon">💌</div>
                    <div class="feature-text">
                        <strong>Secret Letters</strong>
                        <span>Love notes that unlock on milestones</span>
                    </div>
                </div>
                <div class="feature-item">
                    <div class="feature-icon">🍅</div>
                    <div class="feature-text">
                        <strong>Focus Sessions</strong>
                        <span>Stay in the zone, side by side</span>
                    </div>
                </div>
                <div class="feature-item">
                    <div class="feature-icon">📅</div>
                    <div class="feature-text">
                        <strong>Special Dates</strong>
                        <span>Never miss what matters most</span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Right Panel --}}
    <div class="right-panel">
        <div class="form-container">
            <div class="form-header">
                <h1 class="form-title">Welcome <em>back</em> 👋</h1>
                <p class="form-subtitle">Sign in to continue your journey together</p>
            </div>

            {{-- Session Status (e.g. password reset link sent) --}}
            @if (session('status'))
                <div class="session-status">{{ session('status') }}</div>
            @endif

            <form method="POST" action="{{ route('login') }}" id="login-form">
                @csrf

                {{-- Email --}}
                <div class="input-group">
                    <label class="input-label" for="email">Email Address</label>
                    <div class="input-wrap">
                        <span class="input-icon">✉️</span>
                        <input
                            id="email"
                            type="email"
                            name="email"
                            class="form-input {{ $errors->has('email') ? 'error' : '' }}"
                            value="{{ old('email') }}"
                            required
                            autofocus
                            autocomplete="username"
                            placeholder="you@example.com"
                        >
                    </div>
                    @error('email')
                        <div class="error-msg">{{ $message }}</div>
                    @enderror
                </div>

                {{-- Password --}}
                <div class="input-group">
                    <label class="input-label" for="password">Password</label>
                    <div class="input-wrap">
                        <span class="input-icon">🔒</span>
                        <input
                            id="password"
                            type="password"
                            name="password"
                            class="form-input {{ $errors->has('password') ? 'error' : '' }}"
                            required
                            autocomplete="current-password"
                            placeholder="Your password"
                        >
                    </div>
                    @error('password')
                        <div class="error-msg">{{ $message }}</div>
                    @enderror
                </div>

                {{-- Remember + Forgot --}}
                <div class="form-row">
                    <label class="remember-label">
                        <input type="checkbox" name="remember" {{ old('remember') ? 'checked' : '' }}>
                        Remember me
                    </label>
                    @if (Route::has('password.request'))
                        <a href="{{ route('password.request') }}" class="forgot-link">Forgot password?</a>
                    @endif
                </div>

                {{-- Submit --}}
                <button type="submit" class="btn-submit" id="btn-login">
                    <span id="btn-text">Sign In 💑</span>
                    <div class="btn-spinner" id="btn-spinner"></div>
                </button>
            </form>

            <div class="divider">or</div>

            <div class="form-footer">
                Don't have an account?
                <a href="{{ route('register') }}">Create one →</a>
            </div>
        </div>
    </div>

    <script>
        // Hide loading overlay after page load
        window.addEventListener('load', () => {
            setTimeout(() => {
                document.getElementById('loading-overlay').classList.add('hide');
            }, 900);
        });

        // Show spinner on form submit
        document.getElementById('login-form').addEventListener('submit', function() {
            const btn = document.getElementById('btn-login');
            const text = document.getElementById('btn-text');
            const spinner = document.getElementById('btn-spinner');
            btn.classList.add('loading');
            text.style.display = 'none';
            spinner.style.display = 'block';
        });
    </script>
</body>
</html>