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
        Schema::table('comunidades', function (Blueprint $table) {
            $table->string('programa')->nullable()->after('anio');
            $table->string('seccion')->nullable()->after('programa');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('comunidades', function (Blueprint $table) {
            $table->dropColumn(['programa', 'seccion']);
        });
    }
};
