<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Ya Pagado — TransJunín</title>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;600;700;800&display=swap" rel="stylesheet">
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body {
            font-family: 'Plus Jakarta Sans', sans-serif;
            background: #f0f2f7;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 24px 16px;
        }
        .container { width: 100%; max-width: 400px; text-align: center; }
        .result-icon { font-size: 72px; margin-bottom: 16px; animation: pop .4s ease; }
        @keyframes pop {
            from { transform: scale(0.5); opacity: 0; }
            to   { transform: scale(1);   opacity: 1; }
        }
        .result-title { font-size: 26px; font-weight: 800; margin-bottom: 8px; color: #16a34a; }
        .result-sub   { font-size: 14px; color: #64748b; margin-bottom: 28px; line-height: 1.6; }
        .card {
            background: #fff; border-radius: 18px;
            box-shadow: 0 4px 24px rgba(0,0,0,0.08);
            padding: 24px; margin-bottom: 20px;
            text-align: left;
        }
        .info-row {
            display: flex; align-items: center; justify-content: space-between;
            padding: 8px 0; border-bottom: 1px solid #f1f5f9;
            font-size: 13.5px;
        }
        .info-row:last-child { border-bottom: none; }
        .info-label { color: #64748b; }
        .info-val   { font-weight: 600; }
        .btn-back {
            display: inline-flex; align-items: center; justify-content: center; gap: 10px;
            background: #1d4ed8; color: #fff;
            border: none; border-radius: 14px;
            padding: 16px 32px; font-size: 16px; font-weight: 800;
            cursor: pointer; font-family: inherit;
            text-decoration: none; width: 100%;
            transition: all 0.2s;
            box-shadow: 0 4px 12px rgba(29, 78, 216, 0.25);
        }
        .btn-back:hover { background: #1e3a8a; transform: translateY(-2px); box-shadow: 0 6px 16px rgba(29, 78, 216, 0.35); }
        .btn-back i { font-size: 18px; }
    </style>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body>
    <div class="container">
        <div class="result-icon" style="color: #16a34a; background: #dcfce7; width: 80px; height: 80px; border-radius: 50%; display: flex; align-items: center; justify-content: center; margin: 0 auto 20px; font-size: 40px; box-shadow: 0 4px 12px rgba(22,163,74,0.2);">✓</div>
        <div class="result-title">¡Ya Pagado!</div>
        <div class="result-sub">Este {{ $tipo === 'sancion' ? 'sanción' : 'tributo' }} ya ha sido registrado como pagado anteriormente.</div>

        <div class="card">
            <div class="info-row">
                <span class="info-label">Conductor</span>
                <span class="info-val">{{ $tributo->conductor?->nombre ?? '—' }}</span>
            </div>
            <div class="info-row">
                <span class="info-label">Fecha Pago</span>
                <span class="info-val">{{ $tributo->cobrado_at ? $tributo->cobrado_at->format('d/m/Y h:i A') : '—' }}</span>
            </div>
            <div class="info-row">
                <span class="info-label">Monto</span>
                <span class="info-val">S/ {{ number_format($tributo->monto, 2) }}</span>
            </div>
        </div>

        <a href="{{ $tipo === 'sancion' ? route('conductor.sanciones') : route('conductor.tributos') }}" class="btn-back">
            <i class="fa-solid fa-arrow-left"></i> Volver a mi Panel
        </a>
    </div>
</body>
</html>
