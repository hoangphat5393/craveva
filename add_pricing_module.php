<?php

require __DIR__ . '/vendor/autoload.php';
$app = require __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\DB;

echo "Adding 'pricing' module if missing...\n";

$module = DB::table('modules')->where('module_name', 'pricing')->first();

if (!$module) {
    $moduleId = DB::table('modules')->insertGetId([
        'module_name' => 'pricing',
        'description' => 'Pricing',
        'is_superadmin' => 0,
        'created_at' => now(),
        'updated_at' => now(),
    ]);

    echo "Created module 'pricing' with ID {$moduleId}.\n";

    $perms = [
        ['name' => 'add_pricing',   'display_name' => 'Add Pricing'],
        ['name' => 'view_pricing',  'display_name' => 'View Pricing'],
        ['name' => 'edit_pricing',  'display_name' => 'Edit Pricing'],
        ['name' => 'delete_pricing', 'display_name' => 'Delete Pricing'],
    ];

    foreach ($perms as $p) {
        DB::table('permissions')->insert([
            'name' => $p['name'],
            'display_name' => $p['display_name'],
            'description' => null,
            'module_id' => $moduleId,
            'is_custom' => 0,
            'allowed_permissions' => '{"all":4, "none":5}',
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    echo "Inserted basic permissions for 'pricing'.\n";
} else {
    echo "Module 'pricing' already exists.\n";
}

use Illuminate\Support\Facades\Artisan;

echo "Enabling 'pricing' module...\n";
try {
    Artisan::call('module:enable', ['module' => 'Pricing']);
    echo "Module 'Pricing' enabled.\n";
} catch (\Exception $e) {
    echo "Error enabling module: " . $e->getMessage() . "\n";
}

echo "Running migrations for Pricing module...\n";
try {
    Artisan::call('migrate', ['--force' => true]);
    echo "Migrations completed.\n";
} catch (\Exception $e) {
    echo "Error running migrations: " . $e->getMessage() . "\n";
}

echo "Clearing cache...\n";
Artisan::call('optimize:clear');
echo "Cache cleared.\n";

echo "Done.\n";
