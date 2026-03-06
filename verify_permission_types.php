<?php

use App\Models\PermissionType;

require __DIR__.'/bootstrap/autoload.php';
$app = require __DIR__.'/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

$types = PermissionType::all();
foreach ($types as $type) {
    echo 'ID: '.$type->id.' - Name: '.$type->name."\n";
}
