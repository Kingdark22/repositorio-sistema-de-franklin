<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::connection('mysql')->table('comentarios_proyecto', function (Blueprint $table) {
            $table->integer('uex_codigo')->nullable()->change();
            $table->string('cop_nombre_contacto', 255)->nullable()->after('uex_codigo');
        });
    }

    public function down(): void
    {
        Schema::connection('mysql')->table('comentarios_proyecto', function (Blueprint $table) {
            $table->dropColumn('cop_nombre_contacto');
            $table->integer('uex_codigo')->nullable(false)->change();
        });
    }
};
