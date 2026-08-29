<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <title>@yield('title', 'TransJunín') — Mi Flota</title>
    @php
        $logoFavicon = Auth::check() && Auth::user()->empresa && Auth::user()->empresa->logo_path
            ? asset('storage/' . Auth::user()->empresa->logo_path)
            : null;
    @endphp
    @if ($logoFavicon)
        <link rel="icon" href="{{ $logoFavicon }}">
    @endif
    <link
        href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800&family=JetBrains+Mono:wght@400;600&display=swap"
        rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
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

        /* Botón de pago MercadoPago / Yape */
        .btn-mp {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            border: none;
            border-radius: 10px;
            font-family: inherit;
            font-weight: 800;
            font-size: 13px;
            padding: 10px 16px;
            cursor: pointer;
            transition: all 0.2s ease;
            text-decoration: none;
            background: #009ee3;
            color: #ffffff;
            box-shadow: 0 4px 10px rgba(0, 158, 227, 0.2);
        }
        .btn-mp:hover {
            opacity: 0.9;
            transform: translateY(-1px);
            box-shadow: 0 6px 14px rgba(0, 158, 227, 0.3);
        }
        .btn-mp:active {
            transform: translateY(0);
        }
        .btn-mp.btn-mp-danger {
            background: #ef4444;
            box-shadow: 0 4px 10px rgba(239, 68, 68, 0.2);
        }
        .btn-mp.btn-mp-danger:hover {
            box-shadow: 0 6px 14px rgba(239, 68, 68, 0.3);
        }
        .btn-mp.btn-mp-sm {
            padding: 6px 12px;
            font-size: 11px;
            border-radius: 8px;
        }

        /* Cajón de pago */
        .payment-box {
            display: flex;
            align-items: center;
            justify-content: space-between;
            background: #f0f7ff;
            padding: 12px 16px;
            border-radius: 14px;
            border: 1px solid #bae6fd;
            gap: 12px;
        }
        .payment-label {
            font-size: 13px;
            color: #0369a1;
            font-weight: 600;
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
        @keyframes pulse-icon {
            0% { transform: scale(1); box-shadow: 0 0 0 0 rgba(254, 240, 21, 0.4); }
            70% { transform: scale(1.15); box-shadow: 0 0 0 8px rgba(254, 240, 21, 0); }
            100% { transform: scale(1); box-shadow: 0 0 0 0 rgba(254, 240, 21, 0); }
        }
        .alert-pulse-red {
            animation: pulse-bg 2s infinite;
        }
        @keyframes pulse-bg {
            0% { border-color: #ef4444; }
            50% { border-color: #facc15; }
            100% { border-color: #ef4444; }
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
                <i class="fa-solid fa-car"></i>
            </a>
        </div>
    </header>



    {{-- CONTENT --}}
    <main class="c-content">
        @if (auth()->check() && auth()->user()->empresa_id)
            @php
                $layoutAlertas = \App\Models\AlertaOperativo::where('empresa_id', auth()->user()->empresa_id)
                    ->where('estado', 'activo')
                    ->where('expires_at', '>', now())
                    ->with(['conductor', 'user'])
                    ->get();
            @endphp
            <div id="global-operativos-container" style="display: flex; flex-direction: column; gap: 10px; width: 100%; box-sizing: border-box; margin-bottom: 14px;">
                @if ($layoutAlertas->count() > 0)
                    @foreach ($layoutAlertas as $opAlerta)
                        <div id="operativo-card-{{ $opAlerta->id }}" class="alert-pulse-red" style="display: flex; justify-content: space-between; align-items: center; background: linear-gradient(135deg, #dc2626 0%, #991b1b 100%); color: white; padding: 20px 16px; border-radius: 14px; box-shadow: 0 4px 15px rgba(220, 38, 38, 0.4); border: 2px solid #ef4444; position: relative; overflow: hidden; width: 100%; box-sizing: border-box; gap: 14px;">
                            <div style="display: flex; align-items: center; gap: 12px; z-index: 2;">
                                <div style="width: 38px; height: 38px; background: rgba(255,255,255,0.2); border-radius: 50%; display: flex; align-items: center; justify-content: center; animation: pulse-icon 1.2s infinite; flex-shrink: 0;">
                                    <i class="fa-solid fa-triangle-exclamation" style="font-size: 18px; color: #facc15;"></i>
                                </div>
                                <div style="text-align: left;">
                                    <div style="font-weight: 900; font-size: 13.5px; letter-spacing: 0.5px; text-transform: uppercase; color: #ffffff;">⚠️ Control / Operativo</div>
                                    <div style="font-size: 12px; font-weight: 700; opacity: 0.95; margin-top: 2px; color: #fef08a;">
                                        Ubicación: <strong style="font-size: 14px; text-decoration: underline;">{{ $opAlerta->punto }}</strong>
                                        <span style="font-size: 10px; display: block; opacity: 0.8; font-weight: normal; margin-top: 1px;">Reportado a las {{ $opAlerta->created_at->format('h:i A') }}</span>
                                    </div>
                                </div>
                            </div>
                            @if (auth()->user()->conductor && $opAlerta->conductor_id === auth()->user()->conductor->id)
                                <button onclick="finalizarOperativo({{ $opAlerta->id }})" style="background: #22c55e; color: white; border: none; padding: 8px 14px; font-size: 11px; font-weight: 900; border-radius: 8px; cursor: pointer; text-transform: uppercase; display: flex; align-items: center; gap: 4px; box-shadow: 0 2px 6px rgba(34,197,94,0.4); z-index: 2; flex-shrink: 0; transition: transform 0.15s ease;">
                                    <i class="fa-solid fa-circle-check"></i> Retirado
                                </button>
                            @endif
                        </div>
                    @endforeach
                @endif
            </div>
        @endif

        @yield('content')
    </main>

    {{-- NAV INFERIOR --}}
    <nav class="c-nav">
        <a href="{{ route('conductor.dashboard') }}"
            class="c-nav-item {{ request()->routeIs('conductor.dashboard') ? 'active' : '' }}">
            <span class="c-nav-icon"><i class="fa-solid fa-house"></i></span>
            <span>Inicio</span>
        </a>

        <a href="{{ route('conductor.tributos') }}"
            class="c-nav-item {{ request()->routeIs('conductor.tributos') ? 'active' : '' }}">
            <span class="c-nav-icon"><i class="fa-solid fa-sack-dollar"></i></span>
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
            <span class="c-nav-icon"><i class="fa-solid fa-arrows-rotate"></i></span>
            <span>Vueltas</span>
        </a>

        <a href="{{ route('conductor.sanciones') }}"
            class="c-nav-item {{ request()->routeIs('conductor.sanciones') ? 'active' : '' }}">
            <span class="c-nav-icon"><i class="fa-solid fa-triangle-exclamation"></i></span>
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
            <span class="c-nav-icon"><i class="fa-solid fa-car"></i></span>
            <span>Mi Flota</span>
        </a>
    </nav>

    @if(auth()->check() && auth()->user()->empresa_id)
        @vite(['resources/js/app.js'])
        <script>
            const layoutEmpresaId = '{{ auth()->user()->empresa_id }}';
            const currentConductorId = '{{ auth()->user()->conductor?->id ?? "" }}';
            
            window.addEventListener('DOMContentLoaded', () => {
                if (window.Echo) {
                    window.Echo.private(`empresa.${layoutEmpresaId}.operativos`)
                        .listen('.operativo.creado', (e) => {
                            // Validar que no exista ya en el DOM
                            if (document.getElementById(`operativo-card-${e.alerta.id}`)) {
                                return;
                            }

                            const container = document.getElementById('global-operativos-container');
                            if (container) {
                                const showButton = currentConductorId && e.alerta.conductor_id == currentConductorId;
                                const buttonHtml = showButton ? `
                                    <button onclick="finalizarOperativo(${e.alerta.id})" style="background: #22c55e; color: white; border: none; padding: 8px 14px; font-size: 11px; font-weight: 900; border-radius: 8px; cursor: pointer; text-transform: uppercase; display: flex; align-items: center; gap: 4px; box-shadow: 0 2px 6px rgba(34,197,94,0.4); z-index: 2; flex-shrink: 0; transition: transform 0.15s ease;">
                                        <i class="fa-solid fa-circle-check"></i> Retirado
                                    </button>
                                ` : '';

                                const timeStr = new Date(e.alerta.created_at).toLocaleTimeString('es-ES', { hour: '2-digit', minute: '2-digit', hour12: true });

                                const cardHtml = `
                                    <div id="operativo-card-${e.alerta.id}" class="alert-pulse-red" style="display: flex; justify-content: space-between; align-items: center; background: linear-gradient(135deg, #dc2626 0%, #991b1b 100%); color: white; padding: 20px 16px; border-radius: 14px; box-shadow: 0 4px 15px rgba(220, 38, 38, 0.4); border: 2px solid #ef4444; position: relative; overflow: hidden; width: 100%; box-sizing: border-box; gap: 14px;">
                                        <div style="display: flex; align-items: center; gap: 12px; z-index: 2;">
                                            <div style="width: 38px; height: 38px; background: rgba(255,255,255,0.2); border-radius: 50%; display: flex; align-items: center; justify-content: center; animation: pulse-icon 1.2s infinite; flex-shrink: 0;">
                                                <i class="fa-solid fa-triangle-exclamation" style="font-size: 18px; color: #facc15;"></i>
                                            </div>
                                            <div style="text-align: left;">
                                                <div style="font-weight: 900; font-size: 13.5px; letter-spacing: 0.5px; text-transform: uppercase; color: #ffffff;">⚠️ Control / Operativo</div>
                                                <div style="font-size: 12px; font-weight: 700; opacity: 0.95; margin-top: 2px; color: #fef08a;">
                                                    Ubicación: <strong style="font-size: 14px; text-decoration: underline;">${e.alerta.punto}</strong>
                                                    <span style="font-size: 10px; display: block; opacity: 0.8; font-weight: normal; margin-top: 1px;">Reportado a las ${timeStr}</span>
                                                </div>
                                            </div>
                                        </div>
                                        ${buttonHtml}
                                    </div>
                                `;
                                container.insertAdjacentHTML('beforeend', cardHtml);
                            }

                            Swal.fire({
                                title: '🚨 ¡OPERATIVO DETECTADO!',
                                html: `Se reportó control municipal en el <strong>${e.alerta.punto}</strong>.<br><br><span style="color:#ef4444;font-weight:700;">¡Conduce con cuidado!</span>`,
                                icon: 'warning',
                                confirmButtonText: 'Entendido',
                                confirmButtonColor: '#dc2626',
                                allowOutsideClick: true
                            });
                        })
                        .listen('.operativo.finalizado', (e) => {
                            const card = document.getElementById(`operativo-card-${e.alerta.id}`);
                            if (card) {
                                card.remove();
                            }

                            Swal.fire({
                                title: '🛡️ Punto Liberado',
                                text: `El operativo en el ${e.alerta.punto} ha finalizado.`,
                                icon: 'success',
                                timer: 3000,
                                showConfirmButton: false
                            });
                        });
                }
            });

            function finalizarOperativo(alertaId) {
                Swal.fire({
                    title: '¿Operativo Retirado?',
                    text: '¿Confirmas que los inspectores ya se retiraron de este punto?',
                    icon: 'question',
                    showCancelButton: true,
                    confirmButtonText: 'Sí, ya se retiraron',
                    cancelButtonText: 'Cancelar',
                    confirmButtonColor: '#22c55e'
                }).then((result) => {
                    if (result.isConfirmed) {
                        Swal.showLoading();
                        fetch(`/conductor/operativos/${alertaId}/finalizar`, {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'X-CSRF-TOKEN': '{{ csrf_token() }}'
                            }
                        })
                        .then(response => response.json())
                        .then(data => {
                            if (data.success) {
                                const card = document.getElementById(`operativo-card-${alertaId}`);
                                if (card) {
                                    card.remove();
                                }
                                Swal.fire({
                                    title: 'Alerta Cancelada',
                                    text: 'Se notificó a tus compañeros que el punto está libre.',
                                    icon: 'success',
                                    timer: 2000,
                                    showConfirmButton: false
                                });
                            } else {
                                Swal.fire('Error', data.error || 'No se pudo cancelar la alerta.', 'error');
                            }
                        })
                        .catch(error => {
                            console.error('Error:', error);
                            Swal.fire('Error', 'No se pudo finalizar la alerta.', 'error');
                        });
                    }
                });
            }
        </script>
    @endif

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
