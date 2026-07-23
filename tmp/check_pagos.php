<?php
require __DIR__ . '/../vendor/autoload.php';
$app = require __DIR__ . '/../bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\Tributo;
use App\Models\PagoMp;

echo "=== Tributos pagados ===\n";
Tributo::where('estado','pagado')->get(['id','monto','estado','metodo_pago','cobrado_at'])
    ->each(fn($t) => printf("  Tributo #%d | S/ %.2f | %s | Cobrado: %s\n",
        $t->id, $t->monto, $t->metodo_pago, $t->cobrado_at));

echo "\n=== Pagos MP aprobados ===\n";
PagoMp::where('estado','aprobado')->get(['id','tributo_id','payment_id','estado','monto','metodo'])
    ->each(fn($p) => printf("  PagoMp #%d | Tributo #%d | PaymentID: %s | S/ %.2f | Método: %s\n",
        $p->id, $p->tributo_id, $p->payment_id, $p->monto, $p->metodo));
