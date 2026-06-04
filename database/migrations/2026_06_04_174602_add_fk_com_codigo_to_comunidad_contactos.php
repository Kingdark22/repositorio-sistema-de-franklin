<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasColumn('comunidad_contactos', 'id')) {
            DB::statement('ALTER TABLE comunidad_contactos CHANGE COLUMN id ccon_codigo BIGINT UNSIGNED NOT NULL AUTO_INCREMENT');
        }
        if (Schema::hasColumn('comunidad_contactos', 'com_id')) {
            DB::statement('ALTER TABLE comunidad_contactos CHANGE COLUMN com_id com_codigo BIGINT NOT NULL');
        }
        if (!Schema::hasColumn('comunidad_contactos', 'ccon_codigo')) {
            return;
        }
        DB::statement('ALTER TABLE comunidad_contactos ADD CONSTRAINT comunidad_contactos_com_codigo_foreign FOREIGN KEY (com_codigo) REFERENCES comunidades(com_codigo) ON DELETE CASCADE');
    }

    public function down(): void
    {
        DB::statement('ALTER TABLE comunidad_contactos DROP FOREIGN KEY comunidad_contactos_com_codigo_foreign');
        if (Schema::hasColumn('comunidad_contactos', 'ccon_codigo')) {
            DB::statement('ALTER TABLE comunidad_contactos CHANGE COLUMN ccon_codigo id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT');
        }
        if (Schema::hasColumn('comunidad_contactos', 'com_codigo')) {
            DB::statement('ALTER TABLE comunidad_contactos CHANGE COLUMN com_codigo com_id BIGINT NOT NULL');
        }
    }
};
