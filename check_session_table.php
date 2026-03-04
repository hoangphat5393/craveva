<?php

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

require __DIR__ . '/vendor/autoload.php';
$app = require __DIR__ . '/bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

echo "Checking sessions table...\n";
if (Schema::hasTable('sessions')) {
    echo "sessions table EXISTS.\n";
} else {
    echo "sessions table DOES NOT EXIST.\n";
}

echo "Session Driver: " . config('session.driver') . "\n";
