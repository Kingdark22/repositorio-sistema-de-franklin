<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasColumn('estados', 'id')) {
            Schema::table('estados', function (Blueprint $table) {
                $table->renameColumn('id', 'est_codigo');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasColumn('estados', 'est_codigo')) {
            Schema::table('estados', function (Blueprint $table) {
                $table->renameColumn('est_codigo', 'id');
            });
        }
    }
};
