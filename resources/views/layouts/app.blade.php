<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'TransJunín — SaaS')</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800&family=JetBrains+Mono:wght@400;600&display=swap" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script>
        (function() {
            const theme = localStorage.getItem('theme') || 'light';
            document.documentElement.setAttribute('data-theme', theme);
        })();
    </script>
    <style>
        :root {
            /* Light Mode (No Pure White) */
            --bg: #f1f5f9;      /* Slate 100 */
            --card: #f8fafc;    /* Slate 50 - Stronger than White but safe */
            --sidebar: #0f172a; /* Slate 900 */
            --sidebar2: #1e293b;/* Slate 800 */
            --border: #e2e8f0;  /* Slate 200 */
            --border2: #cbd5e1; /* Slate 300 */
            --accent: #2563eb;  /* Blue 600 */
            --accent-l: #dbeafe;/* Blue 100 */
            --accent-t: rgba(37, 99, 235, 0.08);
            --gold: #d97706;
            --gold-l: #fef3c7;
            --green: #16a34a;
            --green-l: #dcfce7;
            --red: #dc2626;
            --red-l: #fee2e2;
            --orange: #ea580c;
            --orange-l: #ffedd5;
            --text: #0f172a;    /* Slate 900 */
            --text2: #475569;   /* Slate 600 */
            --text3: #94a3b8;   /* Slate 400 */
            --text-inv: #f8fafc;
            --shadow: 0 1px 3px rgba(0, 0, 0, 0.07), 0 1px 2px rgba(0, 0, 0, 0.04);
            --shadow-m: 0 4px 12px rgba(0, 0, 0, 0.08), 0 2px 4px rgba(0, 0, 0, 0.04);
            --input-bg: #f8fafc;
        }

        [data-theme="dark"] {
            --bg: #0f172a;      /* Slate 900 */
            --card: #1e293b;    /* Slate 800 */
            --sidebar: #020617; /* Slate 950 */
            --sidebar2: #0f172a;/* Slate 900 */
            --border: #334155;  /* Slate 700 */
            --border2: #475569; /* Slate 600 */
            --accent: #3b82f6;  /* Blue 500 */
            --accent-l: #1e3a8a;/* Blue 900 */
            --accent-t: rgba(59, 130, 246, 0.15);
            --text: #f1f5f9;    /* Slate 100 */
            --text2: #94a3b8;   /* Slate 400 */
            --text3: #64748b;   /* Slate 500 */
            --text-inv: #f8fafc;
            --shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.3), 0 2px 4px -2px rgba(0, 0, 0, 0.2);
            --shadow-m: 0 10px 15px -3px rgba(0, 0, 0, 0.4), 0 4px 6px -4px rgba(0, 0, 0, 0.3);
            --input-bg: #0f172a;
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Plus Jakarta Sans', sans-serif;
            background: var(--bg);
            color: var(--text);
            display: flex;
            min-height: 100vh;
            font-size: 16px;
        }

        /* ── SIDEBAR ── */
        .sb {
            width: 232px;
            min-height: 100vh;
            background: var(--sidebar);
            position: fixed;
            top: 0;
            left: 0;
            bottom: 0;
            display: flex;
            flex-direction: column;
            z-index: 200;
        }

        .sb-brand {
            padding: 20px 18px 16px;
            border-bottom: 1px solid rgba(255, 255, 255, 0.07);
        }

        .brand-logo {
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .brand-icon {
            width: 36px;
            height: 36px;
            background: var(--accent);
            border-radius: 9px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 800;
            font-size: 18px;
            color: var(--text-inv);
            flex-shrink: 0;
        }

        .brand-name {
            font-size: 16px;
            font-weight: 700;
            color: var(--text-inv);
            line-height: 1.2;
        }

        .brand-sub {
            font-size: 12.5px;
            color: rgba(248, 250, 252, 0.4);
            margin-top: 1px;
        }

        .sb-company {
            margin: 12px 10px;
            background: var(--sidebar2);
            border: 1px solid rgba(255, 255, 255, 0.07);
            border-radius: 10px;
            padding: 10px 12px;
            display: flex;
            align-items: center;
            gap: 9px;
            cursor: pointer;
        }

        .company-dot {
            width: 8px;
            height: 8px;
            border-radius: 50%;
            background: #22c55e;
            flex-shrink: 0;
        }

        .company-name {
            font-size: 14.5px;
            font-weight: 600;
            color: #e2e8f0;
            flex: 1;
        }

        .company-caret {
            color: rgba(255, 255, 255, 0.3);
            font-size: 13px;
        }

        .sb-nav {
            flex: 1;
            padding: 8px 10px;
            overflow-y: auto;
        }

        .nav-group {
            margin-bottom: 20px;
        }

        .nav-group-label {
            font-size: 12px;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.09em;
            color: rgba(255, 255, 255, 0.3);
            padding: 0 8px;
            margin-bottom: 4px;
        }

        .nav-item {
            display: flex;
            align-items: center;
            gap: 9px;
            padding: 8px 10px;
            border-radius: 8px;
            cursor: pointer;
            color: rgba(255, 255, 255, 0.55);
            font-size: 15px;
            font-weight: 500;
            transition: all .15s;
            margin-bottom: 1px;
            position: relative;
            text-decoration: none;
        }

        .nav-item:hover {
            background: rgba(255, 255, 255, 0.06);
            color: rgba(255, 255, 255, 0.9);
        }

        .nav-item.active {
            background: var(--accent);
            color: var(--text-inv);
        }

        .nav-item .ni {
            font-size: 17px;
            width: 18px;
            text-align: center;
            flex-shrink: 0;
        }

        .nav-badge {
            margin-left: auto;
            background: var(--red);
            color: var(--text-inv);
            font-size: 12px;
            padding: 1px 7px;
            border-radius: 99px;
            font-weight: 700;
        }

        .sb-footer {
            padding: 14px 10px;
            border-top: 1px solid rgba(255, 255, 255, 0.07);
        }

        .user-row {
            display: flex;
            align-items: center;
            gap: 9px;
            padding: 8px 10px;
            border-radius: 8px;
            text-decoration: none;
        }

        .user-av {
            width: 32px;
            height: 32px;
            border-radius: 8px;
            background: var(--accent);
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 14px;
            font-weight: 700;
            color: var(--text-inv);
            flex-shrink: 0;
        }

        .user-name {
            font-size: 14.5px;
            font-weight: 600;
            color: #e2e8f0;
        }

        .user-role {
            font-size: 13px;
            color: rgba(255, 255, 255, 0.4);
        }

        /* ── MAIN ── */
        .main {
            margin-left: 232px;
            flex: 1;
            display: flex;
            flex-direction: column;
            min-height: 100vh;
        }

        .topbar {
            background: var(--card);
            border-bottom: 1px solid var(--border);
            padding: 0 28px;
            height: 56px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            position: sticky;
            top: 0;
            z-index: 100;
            box-shadow: var(--shadow);
        }

        .tb-left {
            display: flex;
            flex-direction: column;
        }

        .tb-title {
            font-size: 18px;
            font-weight: 700;
            color: var(--text);
        }

        .tb-crumb {
            font-size: 13.5px;
            color: var(--text3);
        }

        .tb-right {
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .tb-date {
            background: var(--bg);
            border: 1px solid var(--border);
            border-radius: 7px;
            padding: 6px 12px;
            font-size: 14px;
            color: var(--text2);
        }

        .btn-primary {
            background: var(--accent);
            color: var(--text-inv);
            border: none;
            border-radius: 8px;
            padding: 8px 16px;
            font-size: 15px;
            font-weight: 600;
            cursor: pointer;
            font-family: inherit;
            transition: opacity .15s;
            display: flex;
            align-items: center;
            gap: 6px;
            text-decoration: none;
        }

        .btn-primary:hover {
            opacity: .87;
        }

        .btn-secondary {
            background: var(--card);
            color: var(--text2);
            border: 1px solid var(--border2);
            border-radius: 8px;
            padding: 7px 14px;
            font-size: 15px;
            font-weight: 500;
            cursor: pointer;
            font-family: inherit;
            transition: all .15s;
            text-decoration: none;
        }

        .btn-secondary:hover {
            border-color: var(--accent);
            color: var(--accent);
        }

        .btn-danger {
            background: var(--red-l);
            color: var(--red);
            border: 1px solid rgba(220, 38, 38, .2);
            border-radius: 7px;
            padding: 5px 12px;
            font-size: 14px;
            font-weight: 600;
            cursor: pointer;
            font-family: inherit;
        }

        .btn-sm {
            padding: 5px 12px;
            font-size: 14px;
        }

        /* ── VOLVER (NUEVA UBICACIÓN) ── */
        .btn-back-content {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            color: var(--text2);
            font-size: 14px;
            font-weight: 700;
            text-decoration: none;
            text-transform: uppercase;
            letter-spacing: 0.05em;
            transition: all 0.2s ease;
            margin-bottom: 20px;
            padding: 8px 0;
        }

        .btn-back-content i {
            width: 32px;
            height: 32px;
            background: var(--card);
            border: 1px solid var(--border);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 12px;
            color: var(--text2);
            box-shadow: var(--shadow);
            transition: all 0.2s ease;
        }

        .btn-back-content:hover {
            color: var(--accent);
        }

        .btn-back-content:hover i {
            background: var(--accent);
            color: var(--text-inv);
            border-color: var(--accent);
            transform: translateX(-3px);
            box-shadow: 0 4px 12px rgba(29, 78, 216, 0.25);
        }

        /* ── CONTENT ── */
        .content {
            padding: 24px 28px;
            flex: 1;
        }

        .panel {
            animation: fadeIn .2s ease;
            display: flex;
            flex-direction: column;
            gap: 24px;
        }

        /* ── CARDS ── */
        .card {
            background: var(--card);
            border: 1px solid var(--border);
            border-radius: 14px;
            box-shadow: var(--shadow);
            margin-bottom: 20px;
        }

        .card-header {
            padding: 16px 20px;
            border-bottom: 1px solid var(--border);
            display: flex;
            align-items: center;
            justify-content: space-between;
        }

        .card-title {
            font-size: 16px;
            font-weight: 700;
        }

        .card-body {
            padding: 20px;
        }

        /* ── STAT CARDS ── */
        .stats-row {
            display: grid;
            grid-template-columns: repeat(6, 1fr);
            gap: 14px;
            margin-bottom: 22px;
        }

        .stat {
            background: var(--card);
            border: 1px solid var(--border);
            border-radius: 20px;
            padding: 24px 20px;
            box-shadow: var(--shadow);
            position: relative;
            overflow: hidden;
            transition: transform 0.2s ease, box-shadow 0.2s ease;
        }

        .stat:hover {
            transform: translateY(-4px);
            box-shadow: var(--shadow-m);
        }

        .stat-label {
            font-size: 13px;
            font-weight: 700;
            color: var(--text3);
            text-transform: uppercase;
            letter-spacing: .08em;
            margin-bottom: 12px;
        }

        .stat-val {
            font-size: 34px;
            font-weight: 800;
            line-height: 1.1;
            margin-bottom: 6px;
            letter-spacing: -0.02em;
        }

        .stat-sub {
            font-size: 14px;
            font-weight: 600;
            color: var(--text2);
        }

        .stat-icon {
            position: absolute;
            right: 14px;
            top: 14px;
            font-size: 22px;
            opacity: .25;
        }

        .stat.blue .stat-val {
            color: var(--accent);
        }

        .stat.green .stat-val {
            color: var(--green);
        }

        .stat.gold .stat-val {
            color: var(--gold);
        }

        .stat.red .stat-val {
            color: var(--red);
        }

        .stat.orange .stat-val {
            color: var(--orange);
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

        .stat.gold::after {
            background: var(--gold);
        }

        .stat.red::after {
            background: var(--red);
        }

        .stat.orange::after {
            background: var(--orange);
        }

        /* ── TABLES ── */
        .tbl {
            width: 100%;
            border-collapse: collapse;
        }

        .tbl th {
            text-align: left;
            padding: 10px 16px;
            font-size: 13px;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: .07em;
            color: var(--text3);
            background: var(--bg);
            border-bottom: 1px solid var(--border);
        }

        .tbl td {
            padding: 12px 16px;
            font-size: 15px;
            border-bottom: 1px solid var(--border);
            vertical-align: middle;
        }

        /* ── TABLE UTILITIES ── */
        .col-id { width: 80px; }
        .col-status { width: 130px; }
        .col-actions { width: 110px; text-align: right !important; }
        
        .text-main { 
            display: block;
            font-weight: 600; 
            color: var(--text);
            line-height: 1.2;
        }

        .text-sub {
            display: block;
            font-size: 11px;
            color: var(--text3);
            margin-top: 2px;
            font-weight: 400;
        }

        .tbl-modern td {
            padding: 14px 16px;
        }

        .tbl-modern thead th {
            font-size: 11px;
            letter-spacing: 0.1em;
            padding: 12px 16px;
        }

        .tbl tbody tr:hover td {
            background: rgba(29, 78, 216, 0.02);
        }

        /* ── PILLS ── */
        .pill {
            display: inline-flex;
            align-items: center;
            gap: 5px;
            padding: 3px 10px;
            border-radius: 99px;
            font-size: 13.5px;
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

        /* ── FORMS ── */
        .form-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 14px;
        }

        .field {
            display: flex;
            flex-direction: column;
            gap: 5px;
        }

        .field label {
            font-size: 13.5px;
            font-weight: 600;
            color: var(--text2);
            text-transform: uppercase;
            letter-spacing: .05em;
        }

        .field input,
        .field select,
        .field textarea {
            background: var(--bg);
            border: 1px solid var(--border2);
            border-radius: 8px;
            padding: 9px 12px;
            font-size: 15.5px;
            color: var(--text);
            font-family: inherit;
            outline: none;
        }

        .field input:focus,
        .field select:focus,
        .field textarea:focus {
            border-color: var(--accent);
            background: var(--card);
        }

        .field-full {
            grid-column: 1/-1;
        }

        /* ── MODAL ── */
        .modal-overlay {
            display: none;
            position: fixed;
            inset: 0;
            background: rgba(15, 22, 35, 0.5);
            z-index: 500;
            align-items: center;
            justify-content: center;
        }

        .modal-overlay.open {
            display: flex;
        }

        .modal {
            background: var(--card);
            border-radius: 16px;
            width: 620px;
            max-height: 90vh;
            overflow-y: auto;
            box-shadow: 0 20px 60px rgba(0, 0, 0, 0.2);
        }

        .modal-header {
            padding: 20px 24px 16px;
            border-bottom: 1px solid var(--border);
            display: flex;
            align-items: center;
            justify-content: space-between;
        }

        .modal-body {
            padding: 20px 24px;
        }

        .modal-footer {
            padding: 16px 24px;
            border-top: 1px solid var(--border);
            display: flex;
            gap: 10px;
            justify-content: flex-end;
        }

        /* ── MINI CHART ── */
        .chart-bars {
            display: flex;
            align-items: flex-end;
            gap: 5px;
            height: 70px;
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
            height: 70px;
        }

        .cb-fill {
            position: absolute;
            bottom: 0;
            left: 0;
            right: 0;
            border-radius: 4px 4px 0 0;
            background: var(--accent);
        }

        /* ── OTHERS ── */
        .mono {
            font-family: 'JetBrains Mono', monospace;
            font-size: 14px;
            background: var(--bg);
            border: 1px solid var(--border);
            padding: 2px 7px;
            border-radius: 5px;
        }

        .alert {
            border-radius: 10px;
            padding: 12px 16px;
            display: flex;
            align-items: center;
            gap: 10px;
            font-size: 15px;
            margin-bottom: 18px;
        }

        .alert.warning {
            background: var(--orange-l);
            color: var(--orange);
            border: 1px solid rgba(234, 88, 12, .2);
        }

        .g2-1 {
            display: grid;
            grid-template-columns: 2fr 1fr;
            gap: 16px;
            margin-bottom: 20px;
        }

        .progress-wrap {
            margin-bottom: 10px;
        }

        .progress-label {
            display: flex;
            justify-content: space-between;
            font-size: 14px;
            margin-bottom: 4px;
        }

        .progress-track {
            height: 7px;
            background: var(--bg);
            border-radius: 4px;
            overflow: hidden;
        }

        .progress-fill {
            height: 100%;
            border-radius: 4px;
            background: var(--green);
        }

        .theme-toggle {
            width: 40px;
            height: 40px;
            border-radius: 10px;
            background: var(--bg);
            border: 1px solid var(--border);
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            color: var(--text2);
            transition: all 0.2s;
        }

        .theme-toggle:hover {
            border-color: var(--accent);
            color: var(--accent);
            background: var(--accent-t);
        }

        [data-theme="dark"] .theme-toggle .fa-sun {
            display: block;
        }

        [data-theme="dark"] .theme-toggle .fa-moon {
            display: none;
        }

        .theme-toggle .fa-sun {
            display: none;
        }

        .theme-toggle .fa-moon {
            display: block;
        }

        @keyframes fadeIn {
            from {
                opacity: 0;
                transform: translateY(6px)
            }

            to {
                opacity: 1;
                transform: none
            }
        }

        /* ── ICONOS DE ACCIÓN (ESTILO APPLE) ── */
        .action-icon {
            font-size: 18px;
            margin-left: 12px;
            transition: all 0.2s ease;
            display: inline-block;
            text-decoration: none;
            cursor: pointer;
        }

        .action-icon:hover {
            transform: scale(1.2);
            filter: brightness(1.1);
        }

        /* Colores específicos de Apple */
        .show-icon {
            color: #5856d6;
        }

        /* Apple Purple para el ojo */
        .edit-icon {
            color: #0071e3;
        }

        /* Apple Blue para el lápiz */
        .delete-icon {
            color: #ff3b30;
        }

        /* Apple Red para el tacho */

        /* Estilo para que el botón de formulario se comporte como link */
        .btn-icon-submit {
            border: none;
            background: none;
            padding: 0;
            font-family: inherit;
        }
        /* ── ACTION BAR ── */
        .action-bar {
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin-bottom: 16px;
            gap: 10px;
        }

        .action-bar-left {
            display: flex;
            align-items: center;
            gap: 8px;
            flex-wrap: wrap;
        }

        .action-bar-right {
            display: flex;
            align-items: center;
            gap: 8px;
            flex-shrink: 0;
        }

        /* ── SEARCH BOX (estilo Apple) ── */
        .search-box {
            background: var(--card);
            border: 1px solid var(--border2);
            border-radius: 10px;
            padding: 8px 14px 8px 36px;
            font-size: 15px;
            font-family: inherit;
            color: var(--text);
            outline: none;
            width: 240px;
            background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='14' height='14' viewBox='0 0 24 24' fill='none' stroke='%2394a3b8' stroke-width='2'%3E%3Ccircle cx='11' cy='11' r='8'%3E%3C/circle%3E%3Cline x1='21' y1='21' x2='16.65' y2='16.65'%3E%3C/line%3E%3C/svg%3E");
            background-repeat: no-repeat;
            background-position: 12px center;
            transition: border .15s, box-shadow .15s;
        }

        .search-box:focus {
            border-color: var(--accent);
            box-shadow: 0 0 0 3px rgba(37, 99, 235, 0.1);
            background-color: var(--card);
        }

        /* ── FILTER SELECT (estilo Apple) ── */
        .filter-select {
            background: var(--card);
            border: 1px solid var(--border2);
            border-radius: 10px;
            padding: 8px 32px 8px 12px;
            font-size: 15px;
            font-family: inherit;
            color: var(--text2);
            outline: none;
            cursor: pointer;
            appearance: none;
            background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='12' height='12' viewBox='0 0 24 24' fill='none' stroke='%2394a3b8' stroke-width='2'%3E%3Cpolyline points='6 9 12 15 18 9'%3E%3C/polyline%3E%3C/svg%3E");
            background-repeat: no-repeat;
            background-position: right 10px center;
            transition: border .15s, box-shadow .15s;
        }

        .filter-select:focus {
            border-color: var(--accent);
            box-shadow: 0 0 0 3px rgba(29, 78, 216, 0.1);
        }

        /* Chrome, Safari and Opera */
        *::-webkit-scrollbar {
            display: none;
        }

        /* IE, Edge and Firefox */
        * {
            -ms-overflow-style: none;
            /* IE and Edge */
            scrollbar-width: none;
            /* Firefox */
        }
        /* ==========================================================================
           MODERN ADMIN THEME (SPACE GRAY)
           ========================================================================== */

        /* ── SIDEBAR ── */
        .sb-modern {
            background: var(--sidebar) !important;
            border-right: 1px solid var(--border) !important;
        }

        .sb-brand-modern {
            padding: 24px 18px;
            border-bottom: 1px solid rgba(255, 255, 255, 0.05);
        }

        .brand-logo-modern {
            display: flex;
            align-items: center;
            justify-content: space-between;
            width: 100%;
        }

        .brand-icon-modern {
            width: 36px;
            height: 36px;
            border-radius: 9px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 800;
            font-size: 16px;
            color: var(--text-inv);
            flex-shrink: 0;
        }

        .brand-icon-sa { background: linear-gradient(135deg, #FFD60A, #FF9F0A); }
        .brand-icon-tj { background: linear-gradient(135deg, #0A84FF, #0040DD); }

        .brand-name-modern {
            color: var(--text-inv);
            font-size: 16.5px;
            font-weight: 700;
            letter-spacing: -0.02em;
            line-height: 1.2;
        }

        .brand-sub-modern {
            color: #8e8e93;
            font-size: 12px;
            margin-top: 1px;
        }

        /* ── NAVIGATION ── */
        .nav-group-label-modern {
            font-size: 12px;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.09em;
            color: #636366;
            padding: 0 8px;
            margin-bottom: 4px;
        }

        .nav-item-modern {
            color: #ebebf5 !important;
            display: flex;
            align-items: center;
            gap: 9px;
            padding: 8px 10px;
            border-radius: 8px;
            font-size: 15px;
            font-weight: 500;
            transition: all .15s;
            text-decoration: none;
            margin-bottom: 1px;
        }

        .nav-item-modern:hover {
            background: rgba(255, 255, 255, 0.06);
            color: var(--text-inv) !important;
        }

        .nav-item-modern.active {
            background: var(--accent);
            color: var(--text-inv) !important;
        }

        /* ── FOOTER SIDEBAR ── */
        .sb-footer-modern {
            border-top: 1px solid rgba(255, 255, 255, 0.05);
            background: rgba(0, 0, 0, 0.2);
            padding: 14px 10px;
        }

        .user-av-modern {
            width: 32px;
            height: 32px;
            border-radius: 8px;
            background: var(--sidebar2);
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 14px;
            font-weight: 700;
            color: var(--text-inv);
            flex-shrink: 0;
        }

        .user-name-modern {
            font-size: 14.5px;
            font-weight: 600;
            color: var(--text-inv);
        }

        .user-role-modern {
            font-size: 13px;
            color: #8e8e93;
        }

        .logout-btn-modern {
            color: #ff453a;
            opacity: 0.8;
            transition: all 0.2s;
            text-decoration: none;
        }

        .logout-btn-modern:hover {
            opacity: 1;
            transform: scale(1.1);
        }

        /* ── MAIN & TOPBAR ── */
        .main-modern {
            background: var(--bg);
            margin-left: 232px;
            flex: 1;
            display: flex;
            flex-direction: column;
            min-height: 100vh;
        }

        .topbar-modern {
            height: 64px;
            background: var(--card);
            backdrop-filter: blur(20px) saturate(190%);
            -webkit-backdrop-filter: blur(20px) saturate(190%);
            border-bottom: 1px solid var(--border);
            padding: 0 28px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            position: sticky;
            top: 0;
            z-index: 100;
        }

        .tb-title-modern {
            font-size: 23px;
            font-weight: 800;
            color: var(--text);
            letter-spacing: -0.015em;
        }

        .tb-crumb-modern {
            font-size: 15.5px;
            color: #6e6e73;
        }

        .tb-right-section {
            display: flex;
            align-items: center;
            gap: 20px;
        }

        .tb-date-modern {
            font-weight: 600;
            color: #424245;
            font-size: 14px;
        }

        .tb-user-wrap {
            display: flex;
            align-items: center;
            gap: 12px;
            padding: 4px;
            padding-left: 12px;
            border-radius: 99px;
            background: rgba(0, 0, 0, 0.03);
            border: 1px solid rgba(0, 0, 0, 0.04);
            transition: all 0.2s ease;
        }

        .tb-user-wrap:hover {
            background: rgba(0, 0, 0, 0.05);
            border-color: rgba(0, 0, 0, 0.08);
        }

        .tb-user-name {
            font-size: 14px;
            font-weight: 600;
            color: var(--text);
        }

        .tb-avatar-btn {
            width: 34px;
            height: 34px;
            border-radius: 50%;
            background: var(--sidebar);
            color: var(--text-inv);
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 13px;
            font-weight: 700;
            cursor: pointer;
            transition: all 0.2s cubic-bezier(0.4, 0, 0.2, 1);
            position: relative;
            overflow: hidden;
            border: 2px solid var(--card);
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }

        .tb-avatar-btn:hover {
            transform: scale(1.05);
            background: #ff3b30; /* Apple Red on hover for logout hint */
        }

        .tb-avatar-btn::after {
            content: "\f08b"; /* FontAwesome logout icon */
            font-family: "Font Awesome 6 Free";
            position: absolute;
            inset: 0;
            display: flex;
            align-items: center;
            justify-content: center;
            background: #ff3b30;
            opacity: 0;
            transition: opacity 0.2s ease;
        }

        .tb-avatar-btn:hover::after {
            opacity: 1;
        }

        .content-modern {
            padding: 24px 28px;
            flex: 1;
        }

        /* ── UTILITIES (RESPONSIVE GRID SYSTEM) ── */
        .g-3 { display: grid; grid-template-columns: repeat(3, 1fr); gap: 24px; }
        .g-4 { display: grid; grid-template-columns: repeat(4, 1fr); gap: 24px; }
        .g-2-1 { display: grid; grid-template-columns: 1fr 350px; gap: 24px; }
        
        @media (max-width: 1400px) {
            .g-2-1 { grid-template-columns: 1fr; }
            .g-4 { grid-template-columns: repeat(3, 1fr); }
        }

        @media (max-width: 1100px) {
            .g-4 { grid-template-columns: repeat(2, 1fr); }
            .g-3 { grid-template-columns: repeat(2, 1fr); }
            .stats-row { grid-template-columns: repeat(3, 1fr) !important; }
        }

        @media (max-width: 768px) {
            .main-modern { margin-left: 0; }
            .sb-modern { transform: translateX(-100%); transition: transform 0.3s ease; }
            .g-4, .g-3, .stats-row { grid-template-columns: 1fr !important; }
            .content-modern { padding: 15px; }
            .topbar-modern { padding: 0 15px; }
            .tb-title-modern { font-size: 18px; }
        }

        .g-filters { display: flex; align-items: flex-end; gap: 16px; flex-wrap: wrap; }
        
        .flex-h { display: flex; align-items: center; gap: 12px; }
        .flex-v { display: flex; flex-direction: column; gap: 12px; }
        .flex-between { display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 10px; }
        .gap-24 { gap: 24px; }
        .mt-25 { margin-top: 25px; }

        /* Estados de Fechas */
        .date-expired { color: var(--red) !important; font-weight: 700; }
        .date-warning { color: var(--orange) !important; font-weight: 700; }
        .date-valid { color: var(--green) !important; font-weight: 700; }

        /* Secciones de Formulario */
        .form-section { margin-bottom: 30px; }
        .form-section-title { 
            font-size: 14px; 
            font-weight: 800; 
            color: var(--accent); 
            text-transform: uppercase; 
            letter-spacing: 0.1em;
            margin-bottom: 15px;
            display: flex;
            align-items: center;
            gap: 10px;
        }
        .form-section-title::after {
            content: '';
            flex: 1;
            height: 1px;
            background: var(--border);
        }

        .form-actions { 
            display: flex; 
            justify-content: flex-end; 
            gap: 12px; 
            margin-top: 40px;
            padding-top: 20px;
            border-top: 1px solid var(--border);
        }

        /* Responsive Table fix */
        .tbl-wrap { overflow-x: auto; border-radius: 0 0 14px 14px; -webkit-overflow-scrolling: touch; }
        .tbl-modern { min-width: 800px; width: 100%; border-bottom: none; }
    </style>

    @yield('extra_css')
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
</head>

<body>
    @yield('body_content')
    @stack('scripts')
    <script>
        @if (session('success'))
            Swal.fire({
                position: "center",
                icon: "success",
                title: "{{ session('success') }}",
                showConfirmButton: false,
                timer: 1500
            });
        @endif

        @if (session('error'))
            Swal.fire({
                position: "center",
                icon: "error",
                title: "{{ session('error') }}",
                showConfirmButton: true
            });
        @endif

        @if ($errors->any())
            Swal.fire({
                position: "center",
                icon: "error",
                title: "Error de validación",
                text: "Por favor revisa los campos en rojo o corrige la información ingresada.",
                showConfirmButton: true
            });
        @endif
    </script>
    <script>
        function toggleTheme() {
            const html = document.documentElement;
            const currentTheme = html.getAttribute('data-theme') || 'light';
            const newTheme = currentTheme === 'light' ? 'dark' : 'light';
            
            html.setAttribute('data-theme', newTheme);
            localStorage.setItem('theme', newTheme);
        }
    </script>
</body>

</html>
