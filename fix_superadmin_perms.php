<?php

use App\Models\User;
use App\Models\Permission;
use App\Models\UserPermission;
use Illuminate\Support\Facades\DB;

require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo "Starting Super Admin Permission Fix...\n";

// 1. Find Super Admin User
$user = User::where('email', 'superadmin@example.com')->first();

if (!$user) {
    echo "Error: User 'superadmin@example.com' not found.\n";
    exit(1);
}

echo "Found User: {$user->name} (ID: {$user->id})\n";

// 2. Get Super Admin Permissions
// Using logic from sidebar_superadmin_perms in app/Helper/start.php as a base,
// but we want to grant ALL superadmin module permissions, not just the sidebar ones.
$superadminPermissions = Permission::whereHas('module', function ($query) {
    $query->withoutGlobalScopes()->where('is_superadmin', '1');
})->get();

if ($superadminPermissions->isEmpty()) {
    echo "Warning: No superadmin permissions found.\n";
} else {
    echo "Found " . $superadminPermissions->count() . " superadmin permissions.\n";
}

// 3. Assign Permissions
DB::beginTransaction();
try {
    $count = 0;
    foreach ($superadminPermissions as $permission) {
        $userPermission = UserPermission::firstOrNew([
            'user_id' => $user->id,
            'permission_id' => $permission->id,
        ]);

        // If permission is not set or not 'All' (4), update it.
        if ($userPermission->permission_type_id != 4) {
            $userPermission->permission_type_id = 4; // 4 = All/Allow
            $userPermission->save();
            $count++;
            echo "Granted permission: {$permission->name}\n";
        }
    }

    DB::commit();
    echo "Successfully updated {$count} permissions.\n";
} catch (\Exception $e) {
    DB::rollBack();
    echo "Error updating permissions: " . $e->getMessage() . "\n";
    exit(1);
}

// 4. Clear Cache
echo "Clearing cache...\n";
\Illuminate\Support\Facades\Artisan::call('cache:clear');
\Illuminate\Support\Facades\Artisan::call('config:clear');
\Illuminate\Support\Facades\Artisan::call('view:clear');
\Illuminate\Support\Facades\Artisan::call('route:clear');

echo "Done. Please check the Super Admin dashboard.\n";
