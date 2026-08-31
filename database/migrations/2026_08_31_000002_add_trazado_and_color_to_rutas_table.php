<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('rutas', function (Blueprint $table) {
            $table->json('trazado')->nullable();
            $table->string('color', 10)->nullable();
        });
    }

    public function down()
    {
        Schema::table('rutas', function (Blueprint $table) {
            $table->dropColumn(['trazado', 'color']);
        });
    }
};
