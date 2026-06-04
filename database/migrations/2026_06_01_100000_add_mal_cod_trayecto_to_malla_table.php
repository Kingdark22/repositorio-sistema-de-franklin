<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        $connection = 'simulacion';
        if (Schema::connection($connection)->hasTable('malla')
            && ! Schema::connection($connection)->hasColumn('malla', 'mal_cod_trayecto')) {
            Schema::connection($connection)->table('malla', function (Blueprint $table) {
                $table->integer('mal_cod_trayecto')->nullable()->after('mal_cod_programa');
            });

            DB::connection($connection)->table('malla')
                ->where('mal_codigo', 25)
                ->update(['mal_cod_trayecto' => 5]);
        }
    }

    public function down(): void
    {
        Schema::connection('simulacion')->table('malla', function (Blueprint $table) {
            $table->dropColumn('mal_cod_trayecto');
        });
    }
};
