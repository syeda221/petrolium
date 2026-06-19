<?php
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$tables = DB::select('SHOW TABLES');
$tableNames = [];
foreach ($tables as $t) {
    $tableNames[] = array_values((array)$t)[0];
}
sort($tableNames);
echo "=== TABLES ===\n";
print_r($tableNames);

echo "\n=== customers ===\n";
$cols = DB::select('SHOW COLUMNS FROM customers');
foreach ($cols as $c) {
    echo "$c->Field: $c->Type Null=$c->Null Key=$c->Key Default=$c->Default Extra=$c->Extra\n";
}

echo "\n=== customer_ledgers ===\n";
$cols = DB::select('SHOW COLUMNS FROM customer_ledgers');
foreach ($cols as $c) {
    echo "$c->Field: $c->Type Null=$c->Null Key=$c->Key Default=$c->Default Extra=$c->Extra\n";
}

echo "\n=== vendor_payments ===\n";
$cols = DB::select('SHOW COLUMNS FROM vendor_payments');
foreach ($cols as $c) {
    echo "$c->Field: $c->Type Null=$c->Null Key=$c->Key Default=$c->Default Extra=$c->Extra\n";
}

echo "\n=== vendor_ledgers ===\n";
$cols = DB::select('SHOW COLUMNS FROM vendor_ledgers');
foreach ($cols as $c) {
    echo "$c->Field: $c->Type Null=$c->Null Key=$c->Key Default=$c->Default Extra=$c->Extra\n";
}
