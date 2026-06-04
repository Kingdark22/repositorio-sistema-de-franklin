<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasColumn('comunidad_contactos', 'ccon_nombre')) {
            Schema::table('comunidad_contactos', function (Blueprint $table) {
                $table->renameColumn('comunidad_id', 'com_id');
                $table->renameColumn('nombre', 'ccon_nombre');
                $table->renameColumn('correo', 'ccon_correo');
                $table->renameColumn('telefono', 'ccon_telefono');
                $table->renameColumn('cargo', 'ccon_cargo');
                $table->string('ccon_apellido')->nullable()->after('ccon_nombre');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasColumn('comunidad_contactos', 'ccon_nombre')) {
            Schema::table('comunidad_contactos', function (Blueprint $table) {
                $table->renameColumn('com_id', 'comunidad_id');
                $table->renameColumn('ccon_nombre', 'nombre');
                $table->renameColumn('ccon_correo', 'correo');
                $table->renameColumn('ccon_telefono', 'telefono');
                $table->renameColumn('ccon_cargo', 'cargo');
                $table->dropColumn('ccon_apellido');
            });
        }
    }
};
