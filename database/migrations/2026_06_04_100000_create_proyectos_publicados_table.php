<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        $connection = (string) config('dual_database.repositorio_connection', 'mysql');

        if (Schema::connection($connection)->hasTable('proyectos_publicados')) {
            return;
        }

        Schema::connection($connection)->create('proyectos_publicados', function (Blueprint $table) {
            $table->id('pub_codigo');
            $table->unsignedBigInteger('pry_codigo');
            $table->string('pub_archivo_path', 500)->nullable();
            $table->string('pub_estado', 20)->default('publicado');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        $connection = (string) config('dual_database.repositorio_connection', 'mysql');
        Schema::connection($connection)->dropIfExists('proyectos_publicados');
    }
};
