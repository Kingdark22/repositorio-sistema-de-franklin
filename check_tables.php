<?php
require __DIR__ . '/vendor/autoload.php';
$app = require __DIR__ . '/bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

foreach (['departamento', 'organizacion'] as $table) {
    echo "=== $table ===\n";
    $cols = DB::connection('mysql')->select('SHOW FULL COLUMNS FROM `' . $table . '`');
    foreach ($cols as $c) {
        $default = $c->Default === null ? 'NULL' : $c->Default;
        echo "  {$c->Field} ({$c->Type}) Null:{$c->Null} Default:{$default}\n";
    }
    echo "\n";
}
