<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasColumn('comunidades', 'direccion_id')) {
            DB::statement('ALTER TABLE comunidades DROP FOREIGN KEY comunidades_direccion_id_foreign');
            DB::statement('ALTER TABLE comunidades CHANGE COLUMN direccion_id dir_codigo BIGINT UNSIGNED NULL');
            DB::statement('ALTER TABLE comunidades ADD CONSTRAINT comunidades_dir_codigo_foreign FOREIGN KEY (dir_codigo) REFERENCES direcciones(dir_codigo) ON DELETE SET NULL');
        }
    }

    public function down(): void
    {
        if (Schema::hasColumn('comunidades', 'dir_codigo')) {
            DB::statement('ALTER TABLE comunidades DROP FOREIGN KEY comunidades_dir_codigo_foreign');
            DB::statement('ALTER TABLE comunidades CHANGE COLUMN dir_codigo direccion_id BIGINT UNSIGNED NULL');
            DB::statement('ALTER TABLE comunidades ADD CONSTRAINT comunidades_direccion_id_foreign FOREIGN KEY (direccion_id) REFERENCES direcciones(dir_codigo) ON DELETE SET NULL');
        }
    }
};
