<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasColumn('direcciones', 'id')) {
            DB::statement('ALTER TABLE direcciones DROP FOREIGN KEY direcciones_mun_id_foreign');
            DB::statement('ALTER TABLE direcciones CHANGE COLUMN id dir_codigo BIGINT UNSIGNED NOT NULL AUTO_INCREMENT');
            DB::statement('ALTER TABLE direcciones CHANGE COLUMN mun_id mun_codigo BIGINT UNSIGNED NOT NULL');
            DB::statement('ALTER TABLE direcciones ADD CONSTRAINT direcciones_mun_codigo_foreign FOREIGN KEY (mun_codigo) REFERENCES municipios(mun_codigo) ON DELETE CASCADE');
        }
    }

    public function down(): void
    {
        if (Schema::hasColumn('direcciones', 'dir_codigo') && Schema::hasColumn('direcciones', 'dir_codigo')) {
            DB::statement('ALTER TABLE direcciones DROP FOREIGN KEY direcciones_mun_codigo_foreign');
            DB::statement('ALTER TABLE direcciones CHANGE COLUMN dir_codigo id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT');
            DB::statement('ALTER TABLE direcciones CHANGE COLUMN mun_codigo mun_id BIGINT UNSIGNED NOT NULL');
            DB::statement('ALTER TABLE direcciones ADD CONSTRAINT direcciones_mun_id_foreign FOREIGN KEY (mun_id) REFERENCES municipios(mun_codigo) ON DELETE CASCADE');
        }
    }
};
