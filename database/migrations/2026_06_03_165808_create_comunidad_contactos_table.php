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
        Schema::create('comunidad_contactos', function (Blueprint $table) {
            $table->bigIncrements('ccon_codigo');
            $table->bigInteger('com_codigo');
            $table->string('ccon_nombre');
            $table->string('ccon_apellido')->nullable();
            $table->string('ccon_correo')->nullable();
            $table->string('ccon_telefono')->nullable();
            $table->string('ccon_cargo')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('comunidad_contactos');
    }
};
