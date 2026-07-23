<?php
require __DIR__ . '/../vendor/autoload.php';
$app = require __DIR__ . '/../bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use Illuminate\Support\Facades\DB;

// Limpiar preference_id viejo para que se cree uno nuevo con localhost back_urls
$updated = DB::table('pagos_mp')
    ->where('estado', 'pendiente')
    ->update(['preference_id' => null]);

echo "preference_id limpiados: {$updated}\n";

// Ver todos los pagos_mp
$pagos = DB::table('pagos_mp')->get();
foreach ($pagos as $p) {
    echo "PagoMp #{$p->id} | tributo_id={$p->tributo_id} | estado={$p->estado} | payment_id={$p->payment_id} | pref_id=" . substr($p->preference_id ?? 'null', 0, 20) . "\n";
}
