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
        Schema::table('vueltas', function (Blueprint $table) {
            $table->decimal('lat_actual', 10, 7)->nullable()->after('longitud');
            $table->decimal('lng_actual', 10, 7)->nullable()->after('lat_actual');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('vueltas', function (Blueprint $table) {
            $table->dropColumn(['lat_actual', 'lng_actual']);
        });
    }
};
