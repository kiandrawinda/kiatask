<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ config('app.name', 'between Us') }} – @yield('title', 'Dashboard')</title>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800&family=Fraunces:ital,opsz,wght@0,9..144,300;1,9..144,400&display=swap" rel="stylesheet">
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://unpkg.com/alpinejs@3.x.x/dist/cdn.min.js" defer></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

    <style>
        :root {
            --lavender: #b8a9e3;
            --lavender-light: #e8e3f5;
            --lavender-soft: #f2effe;
            --baby-blue: #a8c8f0;
            --blue-soft: #e8f2fd;
            --blush: #f5c2d4;
            --mint: #a8e6cf;
            --peach: #ffd5b8;
            --bg: #f8f6ff;
            --card: #ffffff;
            --text-primary: #2d2640;
            --text-secondary: #7b6fa0;
            --text-muted: #b0a8cc;
            --border: #ede9f8;
            --shadow: 0 4px 24px rgba(184, 169, 227, 0.18);
            --shadow-hover: 0 8px 32px rgba(184, 169, 227, 0.28);
        }

        * { box-sizing: border-box; }

        body {
            font-family: 'Plus Jakarta Sans', sans-serif;
            background: var(--bg);
            color: var(--text-primary);
            min-height: 100vh;
        }

        .font-display { font-family: 'Fraunces', serif; }

        /* Cards */
        .card {
            background: var(--card);
            border-radius: 20px;
            padding: 24px;
            box-shadow: var(--shadow);
            border: 1px solid var(--border);
            transition: box-shadow 0.2s;
        }
        .card:hover { box-shadow: var(--shadow-hover); }

        /* Sidebar */
        .sidebar {
            width: 260px;
            background: white;
            border-right: 1px solid var(--border);
            height: 100vh;
            overflow: hidden;
            position: fixed;
            left: 0; top: 0;
            display: flex;
            flex-direction: column;
            padding: 28px 16px;
            z-index: 40;
        }

        .sidebar nav {
            overflow-y: auto;
            overflow-x: hidden;
            min-height: 0;
            padding-right: 4px;
        }

        .sidebar-logo {
            font-family: 'Fraunces', serif;
            font-size: 1.5rem;
            color: var(--text-primary);
            font-weight: 400;
            padding: 0 8px 28px;
            border-bottom: 1px solid var(--border);
            margin-bottom: 20px;
        }

        .nav-item {
            display: flex;
            align-items: center;
            gap: 12px;
            padding: 11px 14px;
            border-radius: 12px;
            color: var(--text-secondary);
            font-size: 0.875rem;
            font-weight: 500;
            transition: all 0.15s;
            margin-bottom: 2px;
            text-decoration: none;
        }
        .nav-item:hover, .nav-item.active {
            background: var(--lavender-soft);
            color: #5b42a0;
        }
        .nav-item.active { font-weight: 600; }

        .nav-icon { width: 20px; text-align: center; font-size: 1rem; }

        /* Main content */
        .main-content {
            margin-left: 260px;
            padding: 32px;
            min-height: 100vh;
        }

        /* Top bar */
        .topbar {
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin-bottom: 32px;
        }

        /* Buttons */
        .btn {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            padding: 10px 20px;
            border-radius: 12px;
            font-size: 0.875rem;
            font-weight: 600;
            border: none;
            cursor: pointer;
            transition: all 0.15s;
            text-decoration: none;
        }
        .btn-primary {
            background: linear-gradient(135deg, #8b5cf6, #a78bfa);
            color: white;
        }
        .btn-primary:hover { opacity: 0.92; transform: translateY(-1px); }
        .btn-secondary {
            background: var(--lavender-soft);
            color: #5b42a0;
        }
        .btn-secondary:hover { background: var(--lavender-light); }
        .btn-danger { background: #fee2e2; color: #dc2626; }
        .btn-danger:hover { background: #fecaca; }
        .btn-sm { padding: 7px 14px; font-size: 0.8rem; }

        /* Badges */
        .badge {
            display: inline-flex;
            align-items: center;
            gap: 4px;
            padding: 4px 10px;
            border-radius: 999px;
            font-size: 0.75rem;
            font-weight: 600;
        }
        .badge-low { background: #ecfdf5; color: #059669; }
        .badge-medium { background: #fffbeb; color: #d97706; }
        .badge-high { background: #fef2f2; color: #dc2626; }
        .badge-pending { background: #f5f3ff; color: #7c3aed; }
        .badge-on_progress { background: #eff6ff; color: #2563eb; }
        .badge-done { background: #ecfdf5; color: #059669; }
        .badge-love { background: #fdf2f8; color: #db2777; }

        /* Progress bar */
        .progress-bar {
            height: 8px;
            background: var(--lavender-light);
            border-radius: 999px;
            overflow: hidden;
        }
        .progress-fill {
            height: 100%;
            background: linear-gradient(90deg, #8b5cf6, #a78bfa);
            border-radius: 999px;
            transition: width 0.5s ease;
        }

        /* Mood emojis */
        .mood-btn {
            width: 52px; height: 52px;
            border-radius: 50%;
            border: 2px solid var(--border);
            background: white;
            font-size: 1.5rem;
            cursor: pointer;
            transition: all 0.15s;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .mood-btn:hover, .mood-btn.selected {
            border-color: #8b5cf6;
            background: var(--lavender-soft);
            transform: scale(1.1);
        }

        /* Notification dot */
        .notif-dot {
            position: absolute;
            top: -2px; right: -2px;
            width: 8px; height: 8px;
            background: #f43f5e;
            border-radius: 50%;
            border: 2px solid white;
        }

        /* Avatar */
        .avatar {
            width: 38px; height: 38px;
            border-radius: 50%;
            background: linear-gradient(135deg, var(--lavender), var(--baby-blue));
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 700;
            font-size: 0.875rem;
            color: white;
        }

        /* Stats card gradient */
        .stat-card-violet { background: linear-gradient(135deg, #f3f0ff, #e9e4ff); }
        .stat-card-blue { background: linear-gradient(135deg, #eff6ff, #dbeafe); }
        .stat-card-rose { background: linear-gradient(135deg, #fff1f2, #ffe4e6); }
        .stat-card-emerald { background: linear-gradient(135deg, #f0fdf4, #dcfce7); }

        /* Countdown */
        .countdown-unit {
            background: var(--lavender-soft);
            border-radius: 12px;
            padding: 12px 16px;
            text-align: center;
            min-width: 60px;
        }

        /* Flash messages */
        .flash {
            padding: 14px 20px;
            border-radius: 14px;
            margin-bottom: 20px;
            font-size: 0.875rem;
            font-weight: 500;
        }
        .flash-success { background: #ecfdf5; color: #059669; border: 1px solid #a7f3d0; }
        .flash-error { background: #fef2f2; color: #dc2626; border: 1px solid #fecaca; }

        /* Animations */
        @keyframes fadeIn { from { opacity: 0; transform: translateY(8px); } to { opacity: 1; transform: none; } }
        .fade-in { animation: fadeIn 0.3s ease forwards; }

        @keyframes pulse-soft {
            0%, 100% { opacity: 1; }
            50% { opacity: 0.7; }
        }
        .pulse-soft { animation: pulse-soft 2s ease infinite; }

        /* ← FIX: .gradient-text ditutup duluu sebelum media queries */
        .gradient-text {
            background: linear-gradient(135deg, #7c3aed, #db2777);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }

        /* ============================================
           RESPONSIVE — Tablet (max 1024px)
           ← dipindah ke BAWAH setelah .mobile-nav didefinisikan
           ============================================ */

        /* Mobile (max 768px) */
        @media (max-width: 768px) {
            .main-content { margin-left: 0 !important; padding: 16px !important; padding-bottom: 80px !important; }

            /* Topbar */
            .topbar {
                flex-direction: column;
                align-items: flex-start !important;
                gap: 12px;
                margin-bottom: 20px !important;
            }
            .topbar > div:last-child {
                width: 100%;
                display: flex;
                gap: 8px;
            }
            .topbar .btn { flex: 1; justify-content: center; }
            .topbar h1 { font-size: 1.4rem !important; }

            /* Stat cards — 4 kolom → 2 kolom */
            [style*="grid-template-columns:repeat(4"],
            [style*="grid-template-columns: repeat(4"] {
                display: grid !important;
                grid-template-columns: 1fr 1fr !important;
                gap: 12px !important;
            }

            /* 3 kolom → 1 kolom */
            [style*="grid-template-columns:1fr 1fr 1fr"],
            [style*="grid-template-columns: 1fr 1fr 1fr"] {
                display: grid !important;
                grid-template-columns: 1fr !important;
                gap: 12px !important;
            }

            /* 2 kolom → 1 kolom */
            [style*="grid-template-columns:1fr 1fr"],
            [style*="grid-template-columns: 1fr 1fr"] {
                display: grid !important;
                grid-template-columns: 1fr !important;
                gap: 12px !important;
            }

            .card { border-radius: 16px !important; padding: 16px !important; }
            .hero-stat-num { font-size: 1.75rem !important; }
            .countdown-unit { min-width: 48px !important; padding: 8px 12px !important; }

            .connect-banner { padding: 20px !important; }
            .connect-banner form { flex-direction: column !important; }
            .connect-banner form input,
            .connect-banner form button { width: 100% !important; }

            #weeklyChart { max-height: 200px; }
            .flash { font-size: 0.8rem; padding: 10px 14px; }

            #mood-modal .card { margin: 16px !important; }
            .mood-btn { width: 44px !important; height: 44px !important; font-size: 1.25rem !important; }
        }

        /* Small mobile (max 400px) */
        @media (max-width: 400px) {
            .main-content { padding: 12px !important; padding-bottom: 80px !important; }
            .topbar h1 { font-size: 1.2rem !important; }
            .hero-stat-num { font-size: 1.5rem !important; }

            [style*="grid-template-columns:repeat(4"],
            [style*="grid-template-columns: repeat(4"] {
                grid-template-columns: 1fr 1fr !important;
            }
        }

        /* ============================================
           MOBILE BOTTOM NAV
           ============================================ */
        .mobile-nav {
            display: none;
            position: fixed;
            bottom: 0; left: 0; right: 0;
            background: white;
            border-top: 1px solid var(--border);
            padding: 8px 0 calc(8px + env(safe-area-inset-bottom));
            z-index: 50;
            box-shadow: 0 -4px 20px rgba(184,169,227,0.15);
        }

        .mobile-nav-items {
            display: flex;
            justify-content: space-around;
            align-items: center;
        }

        .mobile-nav-item {
            display: flex;
            flex-direction: column;
            align-items: center;
            gap: 2px;
            padding: 6px 12px;
            border-radius: 12px;
            text-decoration: none;
            color: var(--text-muted);
            font-size: 0.65rem;
            font-weight: 500;
            transition: all 0.15s;
            background: none;
            border: none;
            cursor: pointer;
            font-family: 'Plus Jakarta Sans', sans-serif;
        }

        .mobile-nav-item .nav-emoji { font-size: 1.3rem; }

        .mobile-nav-item.active,
        .mobile-nav-item:hover {
            color: #7c3aed;
            background: var(--lavender-soft);
        }

        /* ← media query ini HARUS setelah .mobile-nav { display:none }
           supaya display:block bisa override */
        @media (max-width: 1024px) {
            .sidebar { display: none; }
            .main-content { margin-left: 0 !important; padding: 20px !important; padding-bottom: 80px !important; }
            .mobile-nav { display: block; }
        }
    </style>

    @yield('styles')
</head>
<body>

@auth
    <!-- Sidebar -->
    <aside class="sidebar">
        <div class="sidebar-logo">
            ✨ Between Us
        </div>

        <nav class="flex-1">
            <a href="{{ route('dashboard') }}" class="nav-item {{ request()->routeIs('dashboard') ? 'active' : '' }}">
                <span class="nav-icon">🏠</span> Dashboard
            </a>
            <a href="{{ route('tasks.index') }}" class="nav-item {{ request()->routeIs('tasks.*') ? 'active' : '' }}">
                <span class="nav-icon">📋</span> My Tasks
            </a>
            @if(auth()->user()->hasPartner())
            <a href="{{ route('tasks.index', ['type' => 'shared']) }}" class="nav-item">
                <span class="nav-icon">🤝</span> Shared Tasks
            </a>
            <a href="{{ route('goals.index') }}" class="nav-item {{ request()->routeIs('goals.*') ? 'active' : '' }}">
                <span class="nav-icon">🎯</span> Couple Goals
            </a>
            <a href="{{ route('focus.index') }}" class="nav-item {{ request()->routeIs('focus.*') ? 'active' : '' }}">
                <span class="nav-icon">🍅</span> Focus Timer
            </a>
            <a href="{{ route('letters.index') }}" class="nav-item {{ request()->routeIs('letters.*') ? 'active' : '' }}">
                <span class="nav-icon">💌</span> Secret Letters
            </a>
            <a href="{{ route('dates.index') }}" class="nav-item {{ request()->routeIs('dates.*') ? 'active' : '' }}">
                <span class="nav-icon">📅</span> Special Dates
            </a>
            <a href="{{ route('analytics.index') }}" class="nav-item {{ request()->routeIs('analytics.*') ? 'active' : '' }}">
                <span class="nav-icon">📊</span> Analytics
            </a>
            @endif
            <a href="{{ route('notifications.index') }}" class="nav-item {{ request()->routeIs('notifications.*') ? 'active' : '' }}" style="position:relative">
                <span class="nav-icon">🔔</span> Notifications
                @php $unread = \App\Models\AppNotification::where('user_id', auth()->id())->where('read_status', false)->count() @endphp
                @if($unread > 0)
                <span style="margin-left:auto;background:#f43f5e;color:white;border-radius:999px;padding:2px 7px;font-size:0.7rem;font-weight:700">{{ $unread }}</span>
                @endif
            </a>
        </nav>

        <!-- User info -->
        <div style="border-top: 1px solid var(--border); padding-top: 16px; margin-top: 16px;">
            @if(auth()->user()->hasPartner())
            <div style="background:var(--lavender-soft);border-radius:12px;padding:12px;margin-bottom:12px;font-size:0.8rem;color:var(--text-secondary)">
                <div style="font-weight:600;margin-bottom:4px;">💑 Connected with</div>
                <div style="color:var(--text-primary);font-weight:700">{{ auth()->user()->partner->name }}</div>
            </div>
            @endif
            <a href="{{ route('profile.edit') }}" class="nav-item">
                <div class="avatar">{{ auth()->user()->initials }}</div>
                <div>
                    <div style="font-size:0.8rem;font-weight:600;color:var(--text-primary)">{{ Str::limit(auth()->user()->name, 16) }}</div>
                    <div style="font-size:0.7rem;color:var(--text-muted)">View Profile</div>
                </div>
            </a>
            <form method="POST" action="{{ route('logout') }}">
                @csrf
                <button type="submit" class="nav-item" style="width:100%;border:none;background:none;cursor:pointer;color:#ef4444">
                    <span class="nav-icon">🚪</span> Sign Out
                </button>
            </form>
        </div>
    </aside>

    <!-- Main content -->
    <main class="main-content">
        @if(session('success'))
        <div class="flash flash-success fade-in">{{ session('success') }}</div>
        @endif
        @if(session('error') || $errors->any())
        <div class="flash flash-error fade-in">
            {{ session('error') ?? $errors->first() }}
        </div>
        @endif

        @yield('content')
    </main>

    <!-- Mobile Bottom Nav -->
    <nav class="mobile-nav">
        <div class="mobile-nav-items">
            <a href="{{ route('dashboard') }}"
               class="mobile-nav-item {{ request()->routeIs('dashboard') ? 'active' : '' }}">
                <span class="nav-emoji">🏠</span>
                <span>Home</span>
            </a>

            <a href="{{ route('tasks.index') }}"
               class="mobile-nav-item {{ request()->routeIs('tasks.*') ? 'active' : '' }}">
                <span class="nav-emoji">📋</span>
                <span>Tasks</span>
            </a>

            @if(auth()->user()->hasPartner())
            <a href="{{ route('goals.index') }}"
               class="mobile-nav-item {{ request()->routeIs('goals.*') ? 'active' : '' }}">
                <span class="nav-emoji">🎯</span>
                <span>Goals</span>
            </a>
            @endif

            <a href="{{ route('notifications.index') }}"
               class="mobile-nav-item {{ request()->routeIs('notifications.*') ? 'active' : '' }}"
               style="position:relative">
                <span class="nav-emoji">🔔</span>
                <span>Notif</span>
                @if($unread > 0)
                <span style="position:absolute;top:2px;right:6px;background:#f43f5e;color:white;border-radius:999px;padding:1px 5px;font-size:0.6rem;font-weight:700">{{ $unread }}</span>
                @endif
            </a>

            <button class="mobile-nav-item" onclick="toggleMoreDrawer()">
                <span class="nav-emoji">☰</span>
                <span>More</span>
            </button>
        </div>
    </nav>

    <!-- Backdrop -->
    <div id="more-backdrop"
         onclick="toggleMoreDrawer()"
         style="display:none;position:fixed;inset:0;background:rgba(0,0,0,0.3);z-index:55;"></div>

    <!-- More Drawer -->
    <div id="more-drawer"
         style="display:none;position:fixed;bottom:0;left:0;right:0;background:white;border-radius:24px 24px 0 0;padding:20px 16px calc(20px + env(safe-area-inset-bottom));z-index:60;box-shadow:0 -8px 32px rgba(184,169,227,0.25);transform:translateY(100%);transition:transform 0.3s ease;">
        <div style="width:40px;height:4px;background:var(--border);border-radius:999px;margin:0 auto 20px;"></div>
        <div style="font-family:'Fraunces',serif;font-size:1.1rem;color:var(--text-primary);margin-bottom:16px;padding:0 4px;">More</div>
        <div style="display:grid;grid-template-columns:1fr 1fr;gap:10px;">
            @if(auth()->user()->hasPartner())
            <a href="{{ route('focus.index') }}"
               style="display:flex;align-items:center;gap:10px;padding:14px;border-radius:14px;background:var(--lavender-soft);text-decoration:none;color:var(--text-primary);">
                <span style="font-size:1.4rem;">🍅</span>
                <span style="font-size:0.85rem;font-weight:600;">Focus Timer</span>
            </a>
            <a href="{{ route('letters.index') }}"
               style="display:flex;align-items:center;gap:10px;padding:14px;border-radius:14px;background:var(--lavender-soft);text-decoration:none;color:var(--text-primary);">
                <span style="font-size:1.4rem;">💌</span>
                <span style="font-size:0.85rem;font-weight:600;">Letters</span>
            </a>
            <a href="{{ route('dates.index') }}"
               style="display:flex;align-items:center;gap:10px;padding:14px;border-radius:14px;background:var(--lavender-soft);text-decoration:none;color:var(--text-primary);">
                <span style="font-size:1.4rem;">📅</span>
                <span style="font-size:0.85rem;font-weight:600;">Special Dates</span>
            </a>
            <a href="{{ route('tasks.index', ['type' => 'shared']) }}"
               style="display:flex;align-items:center;gap:10px;padding:14px;border-radius:14px;background:var(--lavender-soft);text-decoration:none;color:var(--text-primary);">
                <span style="font-size:1.4rem;">🤝</span>
                <span style="font-size:0.85rem;font-weight:600;">Shared Tasks</span>
            </a>
            <a href="{{ route('analytics.index') }}"
               style="display:flex;align-items:center;gap:10px;padding:14px;border-radius:14px;background:var(--lavender-soft);text-decoration:none;color:var(--text-primary);">
                <span style="font-size:1.4rem;">📊</span>
                <span style="font-size:0.85rem;font-weight:600;">Analytics</span>
            </a>
            @endif
            <a href="{{ route('profile.edit') }}"
               style="display:flex;align-items:center;gap:10px;padding:14px;border-radius:14px;background:var(--lavender-soft);text-decoration:none;color:var(--text-primary);">
                <span style="font-size:1.4rem;">👤</span>
                <span style="font-size:0.85rem;font-weight:600;">Profile</span>
            </a>
            <form method="POST" action="{{ route('logout') }}" style="margin:0;">
                @csrf
                <button type="submit"
                        style="display:flex;align-items:center;gap:10px;padding:14px;border-radius:14px;background:#fff1f2;border:none;cursor:pointer;width:100%;color:#dc2626;font-family:'Plus Jakarta Sans',sans-serif;">
                    <span style="font-size:1.4rem;">🚪</span>
                    <span style="font-size:0.85rem;font-weight:600;">Sign Out</span>
                </button>
            </form>
        </div>
    </div>

    <script>
        function toggleMoreDrawer() {
            const drawer = document.getElementById('more-drawer');
            const backdrop = document.getElementById('more-backdrop');
            const isOpen = drawer.style.transform === 'translateY(0%)';
            if (isOpen) {
                drawer.style.transform = 'translateY(100%)';
                setTimeout(() => {
                    drawer.style.display = 'none';
                    backdrop.style.display = 'none';
                }, 300);
            } else {
                drawer.style.display = 'block';
                backdrop.style.display = 'block';
                requestAnimationFrame(() => { drawer.style.transform = 'translateY(0%)'; });
            }
        }
    </script>

@else
    {{-- Guest: render content tanpa layout --}}
    @yield('content')
@endauth

    <script>
        setTimeout(() => {
            document.querySelectorAll('.flash').forEach(el => {
                el.style.opacity = '0';
                el.style.transition = 'opacity 0.5s';
                setTimeout(() => el.remove(), 500);
            });
        }, 4000);

        const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
    </script>

    @yield('scripts')
</body>
</html>