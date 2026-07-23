<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('pagos_mp', function (Blueprint $table) {
            $table->foreignId('sancion_id')->nullable()->after('tributo_id')->constrained('sanciones')->nullOnDelete();
            $table->foreignId('tributo_id')->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('pagos_mp', function (Blueprint $table) {
            $table->dropForeign(['sancion_id']);
            $table->dropColumn('sancion_id');
            $table->foreignId('tributo_id')->nullable(false)->change();
        });
    }
};
