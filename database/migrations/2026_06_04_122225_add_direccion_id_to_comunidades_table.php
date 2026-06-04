<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('comunidades', function (Blueprint $table) {
            $table->dropColumn('com_direccion');
            $table->foreignId('dir_codigo')->nullable()->after('seccion')->constrained('direcciones')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('comunidades', function (Blueprint $table) {
            $table->dropConstrainedForeignId('dir_codigo');
            $table->text('com_direccion')->nullable()->after('seccion');
        });
    }
};
