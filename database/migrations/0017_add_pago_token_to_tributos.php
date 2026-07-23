<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('tributos', function (Blueprint $table) {
            $table->string('token_pago', 64)->nullable()->unique()->after('observaciones');
        });

        DB::statement("ALTER TABLE tributos MODIFY COLUMN metodo_pago ENUM('efectivo','yape','plin','transferencia','mercadopago') NULL");
    }

    public function down(): void
    {
        Schema::table('tributos', function (Blueprint $table) {
            $table->dropColumn('token_pago');
        });
        DB::statement("ALTER TABLE tributos MODIFY COLUMN metodo_pago ENUM('efectivo','yape','plin','transferencia') NULL");
    }
};
