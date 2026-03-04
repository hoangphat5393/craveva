<?php

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

require __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo "Resetting encrypted fields in global_settings table...\n";

if (Schema::hasTable('global_settings')) {
    try {
        DB::table('global_settings')->update([
            'google_map_key' => null,
            'google_client_secret' => null,
            'google_recaptcha_v2_secret_key' => null,
            'google_recaptcha_v3_secret_key' => null,
        ]);
        echo "Reset 'google_map_key', 'google_client_secret', 'google_recaptcha_v2_secret_key', 'google_recaptcha_v3_secret_key' in 'global_settings' table to NULL.\n";
    } catch (\Exception $e) {
        echo "Error updating global_settings table: " . $e->getMessage() . "\n";
    }
} else {
    echo "Table 'global_settings' does not exist.\n";
}

if (Schema::hasTable('companies')) {
    try {
        // Check if columns exist before updating
        $columns = Schema::getColumnListing('companies');
        $updateData = [];

        if (in_array('google_map_key', $columns)) {
            $updateData['google_map_key'] = null;
        }
        if (in_array('google_recaptcha_v2_secret_key', $columns)) {
            $updateData['google_recaptcha_v2_secret_key'] = null;
        }
        if (in_array('google_recaptcha_v3_secret_key', $columns)) {
            $updateData['google_recaptcha_v3_secret_key'] = null;
        }
         if (in_array('google_client_secret', $columns)) {
            $updateData['google_client_secret'] = null;
        }


        if (!empty($updateData)) {
            DB::table('companies')->update($updateData);
            echo "Reset encrypted fields in 'companies' table to NULL.\n";
        } else {
             echo "No encrypted columns found in 'companies' table to reset.\n";
        }

    } catch (\Exception $e) {
        echo "Error updating companies table: " . $e->getMessage() . "\n";
    }
} else {
    echo "Table 'companies' does not exist.\n";
}

echo "Done.\n";
