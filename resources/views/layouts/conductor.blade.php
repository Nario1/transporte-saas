<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <title>@yield('title', 'TransJunín') — Mi Unidad</title>
    <link
        href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800&family=JetBrains+Mono:wght@400;600&display=swap"
        rel="stylesheet">
    <style>
        :root {
            --bg: #f0f2f7;
            --card: #ffffff;
            --border: #e2e6ef;
            --border2: #d0d6e3;
            --accent: #1d4ed8;
            --accent-l: #dbeafe;
            --gold: #d97706;
            --gold-l: #fef3c7;
            --green: #16a34a;
            --green-l: #dcfce7;
            --red: #dc2626;
            --red-l: #fee2e2;
            --orange: #ea580c;
            --orange-l: #ffedd5;
            --text: #0f172a;
            --text2: #475569;
            --text3: #94a3b8;
            --shadow: 0 1px 3px rgba(0, 0, 0, 0.07), 0 1px 2px rgba(0, 0, 0, 0.04);
            --shadow-m: 0 4px 12px rgba(0, 0, 0, 0.08), 0 2px 4px rgba(0, 0, 0, 0.04);
            --nav-h: 64px;
            --top-h: 56px;
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            -webkit-tap-highlight-color: transparent;
        }

        body {
            font-family: 'Plus Jakarta Sans', sans-serif;
            background: var(--bg);
            color: var(--text);
            font-size: 14px;
            min-height: 100vh;
            padding-bottom: var(--nav-h);
        }

        /* ── TOPBAR ── */
        .c-topbar {
            position: sticky;
            top: 0;
            z-index: 100;
            background: var(--card);
            border-bottom: 1px solid var(--border);
            height: var(--top-h);
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 0 18px;
            box-shadow: var(--shadow);
        }

        .c-topbar-left {
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .c-brand-icon {
            width: 32px;
            height: 32px;
            background: var(--accent);
            border-radius: 8px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 800;
            font-size: 13px;
            color: #fff;
        }

        .c-topbar-title {
            font-size: 15px;
            font-weight: 700;
        }

        .c-topbar-sub {
            font-size: 11px;
            color: var(--text3);
        }

        .c-topbar-right {
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .c-av {
            width: 34px;
            height: 34px;
            background: var(--accent);
            border-radius: 9px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 12px;
            font-weight: 700;
            color: #fff;
            text-decoration: none;
        }

        /* ── CONTENT ── */
        .c-content {
            padding: 16px 16px 8px;
        }

        /* ── NAV INFERIOR ── */
        .c-nav {
            position: fixed;
            bottom: 0;
            left: 0;
            right: 0;
            height: var(--nav-h);
            background: var(--card);
            border-top: 1px solid var(--border);
            display: flex;
            align-items: stretch;
            z-index: 100;
            box-shadow: 0 -2px 12px rgba(0, 0, 0, 0.06);
        }

        .c-nav-item {
            flex: 1;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            gap: 4px;
            text-decoration: none;
            color: var(--text3);
            font-size: 10px;
            font-weight: 600;
            transition: color .15s;
            position: relative;
            padding-bottom: 4px;
        }

        .c-nav-item.active {
            color: var(--accent);
        }

        .c-nav-item.active::before {
            content: '';
            position: absolute;
            top: 0;
            left: 20%;
            right: 20%;
            height: 3px;
            background: var(--accent);
            border-radius: 0 0 4px 4px;
        }

        .c-nav-icon {
            font-size: 20px;
            line-height: 1;
        }

        .c-nav-badge {
            position: absolute;
            top: 6px;
            right: calc(50% - 18px);
            background: var(--red);
            color: #fff;
            font-size: 9px;
            font-weight: 700;
            padding: 1px 5px;
            border-radius: 99px;
            min-width: 16px;
            text-align: center;
        }

        /* ── CARDS ── */
        .card {
            background: var(--card);
            border: 1px solid var(--border);
            border-radius: 14px;
            box-shadow: var(--shadow);
            margin-bottom: 14px;
            overflow: hidden;
        }

        .card-header {
            padding: 14px 16px;
            border-bottom: 1px solid var(--border);
            display: flex;
            align-items: center;
            justify-content: space-between;
        }

        .card-title {
            font-size: 14px;
            font-weight: 700;
        }

        .card-body {
            padding: 16px;
        }

        /* ── STATS ── */
        .stats-row {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 12px;
            margin-bottom: 14px;
        }

        .stat {
            background: var(--card);
            border: 1px solid var(--border);
            border-radius: 14px;
            padding: 16px 14px;
            box-shadow: var(--shadow);
            position: relative;
            overflow: hidden;
        }

        .stat-icon {
            font-size: 18px;
            opacity: .25;
            position: absolute;
            right: 12px;
            top: 12px;
        }

        .stat-label {
            font-size: 10px;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: .06em;
            color: var(--text2);
            margin-bottom: 6px;
        }

        .stat-val {
            font-size: 22px;
            font-weight: 800;
            line-height: 1;
            margin-bottom: 3px;
        }

        .stat-sub {
            font-size: 11px;
            color: var(--text3);
        }

        .stat::after {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 3px;
            border-radius: 14px 14px 0 0;
        }

        .stat.blue::after {
            background: var(--accent);
        }

        .stat.green::after {
            background: var(--green);
        }

        .stat.red::after {
            background: var(--red);
        }

        .stat.gold::after {
            background: var(--gold);
        }

        .stat.orange::after {
            background: var(--orange);
        }

        .stat.blue .stat-val {
            color: var(--accent);
        }

        .stat.green .stat-val {
            color: var(--green);
        }

        .stat.red .stat-val {
            color: var(--red);
        }

        .stat.gold .stat-val {
            color: var(--gold);
        }

        .stat.orange .stat-val {
            color: var(--orange);
        }

        /* ── PILLS ── */
        .pill {
            display: inline-flex;
            align-items: center;
            gap: 4px;
            padding: 3px 9px;
            border-radius: 99px;
            font-size: 11px;
            font-weight: 600;
        }

        .pill::before {
            content: '';
            width: 5px;
            height: 5px;
            border-radius: 50%;
            background: currentColor;
            display: inline-block;
        }

        .pill.green {
            background: var(--green-l);
            color: var(--green);
        }

        .pill.red {
            background: var(--red-l);
            color: var(--red);
        }

        .pill.orange {
            background: var(--orange-l);
            color: var(--orange);
        }

        .pill.blue {
            background: var(--accent-l);
            color: var(--accent);
        }

        .pill.gold {
            background: var(--gold-l);
            color: var(--gold);
        }

        /* ── BADGE ── */
        .badge {
            display: inline-block;
            font-size: 10px;
            font-weight: 700;
            padding: 2px 7px;
            border-radius: 99px;
            background: var(--accent-l);
            color: var(--accent);
        }

        /* ── ALERTS ── */
        .alert {
            border-radius: 10px;
            padding: 12px 14px;
            display: flex;
            align-items: flex-start;
            gap: 10px;
            font-size: 13px;
            margin-bottom: 14px;
            line-height: 1.5;
        }

        .alert.warning {
            background: var(--orange-l);
            color: var(--orange);
            border: 1px solid rgba(234, 88, 12, .2);
        }

        .alert.info {
            background: var(--accent-l);
            color: var(--accent);
            border: 1px solid rgba(29, 78, 216, .2);
        }

        .alert.success {
            background: var(--green-l);
            color: var(--green);
            border: 1px solid rgba(22, 163, 74, .2);
        }

        .alert.danger {
            background: var(--red-l);
            color: var(--red);
            border: 1px solid rgba(220, 38, 38, .2);
        }

        /* ── SUMMARY ROW ── */
        .summary-row {
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 11px 0;
            border-bottom: 1px solid var(--border);
        }

        .summary-row:last-child {
            border-bottom: none;
        }

        .summary-label {
            font-size: 12.5px;
            color: var(--text2);
        }

        .summary-val {
            font-weight: 700;
            font-size: 13px;
        }

        /* ── VUELTA CARD ── */
        .vuelta-card {
            background: var(--bg);
            border: 1px solid var(--border);
            border-radius: 10px;
            padding: 12px 14px;
            margin-bottom: 8px;
            display: flex;
            align-items: center;
            gap: 12px;
        }

        .vuelta-num {
            width: 36px;
            height: 36px;
            background: var(--accent);
            border-radius: 9px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 800;
            font-size: 15px;
            color: #fff;
            flex-shrink: 0;
        }

        .vuelta-info {
            flex: 1;
            min-width: 0;
        }

        .vuelta-name {
            font-size: 13px;
            font-weight: 600;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }

        .vuelta-sub {
            font-size: 11.5px;
            color: var(--text3);
            margin-top: 2px;
        }

        .vuelta-time {
            font-family: 'JetBrains Mono', monospace;
            font-size: 12px;
            color: var(--text2);
            flex-shrink: 0;
        }

        /* ── SANCION ROW ── */
        .sancion-row {
            display: flex;
            align-items: flex-start;
            gap: 12px;
            padding: 12px 16px;
            border-bottom: 1px solid var(--border);
        }

        .sancion-row:last-child {
            border-bottom: none;
        }

        .sancion-icon {
            width: 34px;
            height: 34px;
            border-radius: 8px;
            background: var(--red-l);
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 16px;
            flex-shrink: 0;
        }

        .sancion-info {
            flex: 1;
            min-width: 0;
        }

        .sancion-title {
            font-size: 13px;
            font-weight: 600;
        }

        .sancion-sub {
            font-size: 11.5px;
            color: var(--text3);
            margin-top: 2px;
        }

        /* ── HERO CONDUCTOR ── */
        .conductor-hero {
            background: linear-gradient(135deg, var(--accent) 0%, #1e3a8a 100%);
            border-radius: 14px;
            padding: 20px 16px;
            display: flex;
            align-items: center;
            gap: 14px;
            margin-bottom: 14px;
            color: #fff;
        }

        .conductor-av {
            width: 52px;
            height: 52px;
            background: rgba(255, 255, 255, 0.2);
            border-radius: 14px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 20px;
            font-weight: 800;
            flex-shrink: 0;
        }

        .conductor-hero-name {
            font-size: 16px;
            font-weight: 700;
        }

        .conductor-hero-sub {
            font-size: 12px;
            opacity: .75;
            margin-top: 3px;
        }

        /* ── FORMS ── */
        .field {
            display: flex;
            flex-direction: column;
            gap: 5px;
        }

        .field label {
            font-size: 11.5px;
            font-weight: 600;
            color: var(--text2);
            text-transform: uppercase;
            letter-spacing: .05em;
        }

        .form-control {
            background: var(--bg);
            border: 1px solid var(--border2);
            border-radius: 8px;
            padding: 10px 12px;
            font-size: 14px;
            color: var(--text);
            font-family: inherit;
            outline: none;
            width: 100%;
            transition: border .15s;
        }

        .form-control:focus {
            border-color: var(--accent);
            background: #fff;
        }

        /* ── BUTTONS ── */
        .btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 6px;
            border: none;
            border-radius: 10px;
            font-family: inherit;
            font-weight: 600;
            cursor: pointer;
            transition: opacity .15s;
            text-decoration: none;
            padding: 11px 18px;
            font-size: 14px;
        }

        .btn:hover {
            opacity: .87;
        }

        .btn-primary {
            background: var(--accent);
            color: #fff;
        }

        .btn-secondary {
            background: var(--card);
            color: var(--text2);
            border: 1px solid var(--border2);
        }

        .btn-danger {
            background: var(--red-l);
            color: var(--red);
            border: 1px solid rgba(220, 38, 38, .2);
        }

        .btn-block {
            width: 100%;
        }

        .btn-sm {
            padding: 6px 12px;
            font-size: 12px;
            border-radius: 8px;
        }

        /* ── MONO ── */
        .mono {
            font-family: 'JetBrains Mono', monospace;
            font-size: 12px;
            background: var(--bg);
            border: 1px solid var(--border);
            padding: 2px 7px;
            border-radius: 5px;
        }

        /* ── CHART ── */
        .chart-bars {
            display: flex;
            align-items: flex-end;
            gap: 5px;
            height: 60px;
        }

        .cb-wrap {
            flex: 1;
            display: flex;
            flex-direction: column;
            align-items: center;
            gap: 3px;
        }

        .cb {
            width: 100%;
            border-radius: 4px 4px 0 0;
            background: var(--accent-l);
            position: relative;
        }

        .cb-fill {
            position: absolute;
            bottom: 0;
            left: 0;
            right: 0;
            border-radius: 4px 4px 0 0;
            background: var(--accent);
        }

        .cb-label {
            font-size: 9px;
            color: var(--text3);
        }

        /* ── EMPTY STATE ── */
        .empty-state {
            text-align: center;
            padding: 28px 16px;
            color: var(--text3);
            font-size: 13px;
        }

        /* ── MISC ── */
        .mb14 {
            margin-bottom: 14px;
        }

        .mb16 {
            margin-bottom: 16px;
        }

        .text-orange {
            color: var(--orange);
        }

        /* ── FLASH ── */
        .flash-toast {
            position: fixed;
            top: calc(var(--top-h) + 10px);
            left: 16px;
            right: 16px;
            z-index: 999;
            border-radius: 10px;
            padding: 12px 16px;
            font-size: 13px;
            font-weight: 600;
            box-shadow: var(--shadow-m);
            animation: slideDown .25s ease;
        }

        .flash-toast.success {
            background: var(--green-l);
            color: var(--green);
            border: 1px solid rgba(22, 163, 74, .2);
        }

        .flash-toast.error {
            background: var(--red-l);
            color: var(--red);
            border: 1px solid rgba(220, 38, 38, .2);
        }

        @keyframes slideDown {
            from {
                opacity: 0;
                transform: translateY(-10px);
            }

            to {
                opacity: 1;
                transform: none;
            }
        }
    </style>
    @yield('extra_css')
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
</head>

<body>

    {{-- TOPBAR --}}
    <header class="c-topbar">
        <div class="c-topbar-left">
            @php
                $logo = Auth::check() && Auth::user()->empresa && Auth::user()->empresa->logo_path 
                        ? asset('storage/' . Auth::user()->empresa->logo_path) 
                        : null;
            @endphp
            @if($logo)
                <img src="{{ $logo }}" alt="Logo" style="width: 32px; height: 32px; object-fit: contain; border-radius: 8px; flex-shrink: 0; background: white; padding: 2px;">
            @else
                <div class="c-brand-icon">TJ</div>
            @endif
            <div>
                <div class="c-topbar-title">@yield('title', 'Mi Panel')</div>
                <div class="c-topbar-sub">{{ now()->locale('es')->isoFormat('ddd D MMM') }}</div>
            </div>
        </div>
        <div class="c-topbar-right">
            <a href="{{ route('conductor.perfil') }}" class="c-av" style="background: var(--gold);">
                <i class="fa-solid fa-bus"></i>
            </a>
        </div>
    </header>



    {{-- CONTENT --}}
    <main class="c-content">
        @yield('content')
    </main>

    {{-- NAV INFERIOR --}}
    <nav class="c-nav">
        <a href="{{ route('conductor.dashboard') }}"
            class="c-nav-item {{ request()->routeIs('conductor.dashboard') ? 'active' : '' }}">
            <span class="c-nav-icon">🏠</span>
            <span>Inicio</span>
        </a>

        <a href="{{ route('conductor.tributos') }}"
            class="c-nav-item {{ request()->routeIs('conductor.tributos') ? 'active' : '' }}">
            <span class="c-nav-icon">💰</span>
            @php
                $tribPendiente = Auth::user()?->conductor
                    ? \App\Models\Tributo::where('conductor_id', Auth::user()->conductor->id)
                        ->whereDate('fecha', today())
                        ->where('estado', 'pendiente')
                        ->exists()
                    : false;
            @endphp
            @if ($tribPendiente)
                <span class="c-nav-badge">!</span>
            @endif
            <span>Tributo</span>
        </a>

        <a href="{{ route('conductor.vueltas') }}"
            class="c-nav-item {{ request()->routeIs('conductor.vueltas') ? 'active' : '' }}">
            <span class="c-nav-icon">🔄</span>
            <span>Vueltas</span>
        </a>

        <a href="{{ route('conductor.sanciones') }}"
            class="c-nav-item {{ request()->routeIs('conductor.sanciones') ? 'active' : '' }}">
            <span class="c-nav-icon">⚠️</span>
            @php
                $sanPendientes = Auth::user()?->conductor
                    ? \App\Models\Sancion::where('conductor_id', Auth::user()->conductor->id)
                        ->where('estado', 'pendiente')
                        ->count()
                    : 0;
            @endphp
            @if ($sanPendientes > 0)
                <span class="c-nav-badge">{{ $sanPendientes }}</span>
            @endif
            <span>Sanciones</span>
        </a>

        <a href="{{ route('conductor.perfil') }}"
            class="c-nav-item {{ request()->routeIs('conductor.perfil') ? 'active' : '' }}">
            <span class="c-nav-icon">🚌</span>
            <span>Mi Unidad</span>
        </a>
    </nav>

    @stack('scripts')
    <script>
        @if(session('success'))
            Swal.fire({
                position: "center",
                icon: "success",
                title: "{{ session('success') }}",
                showConfirmButton: false,
                timer: 1500
            });
        @endif

        @if(session('error'))
            Swal.fire({
                position: "center",
                icon: "error",
                title: "{{ session('error') }}",
                showConfirmButton: true
            });
        @endif

        @if($errors->any())
            Swal.fire({
                position: "center",
                icon: "error",
                title: "Error de validación",
                text: "Por favor revisa que la información ingresada sea correcta.",
                showConfirmButton: true
            });
        @endif
    </script>
</body>

</html>
