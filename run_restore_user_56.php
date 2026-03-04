<?php

use Illuminate\Support\Facades\DB;

require __DIR__ . '/vendor/autoload.php';
$app = require __DIR__ . '/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

echo "Restoring User 56 data...\n";

try {
    // Check if UserAuth 55 already exists to avoid duplicate entry error
    $exists = DB::table('user_auths')->where('id', 55)->exists();
    if (!$exists) {
        DB::unprepared("INSERT INTO `user_auths` VALUES (55,'toroyabe@gmail.com','\$2y\$10\$4iXv1vxm/mS9btlVu3Ke7u3GW7uzfKLTu5mzAyEgVOH/UEQjGTZCS',NULL,NULL,NULL,0,0,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'2026-01-27 09:09:15','2026-01-27 09:09:16');");
        echo "Inserted UserAuth 55.\n";
    } else {
        echo "UserAuth 55 already exists.\n";
    }

    // Check if User 56 exists
    $userExists = DB::table('users')->where('id', 56)->exists();
    if (!$userExists) {
        // We need to be careful with the JSON content in the SQL.
        // It's safer to use query builder for complex strings or ensure escaping is correct.
        // The SQL file has escaped quotes for JSON. DB::unprepared might handle it if the string is raw SQL.
        // Let's read the SQL file and execute the specific line for users if needed, 
        // OR better yet, just use Query Builder with the data we know.
        
        // Let's try executing the raw SQL from the file, but we need to handle the JSON escaping.
        // The file content: ... VALUES (56,28,55, ... '{\n \"userAgent\": ...
        // PHP string for DB::unprepared needs to be valid SQL.
        
        $sql = file_get_contents('restore_user_56.sql');
        
        // Split by semicolon to get individual statements
        $statements = array_filter(array_map('trim', explode(';', $sql)));
        
        foreach ($statements as $stmt) {
            if (empty($stmt)) continue;
            
            // We already handled UserAuth manually above (or checked it).
            if (strpos($stmt, 'INSERT INTO `user_auths`') !== false && $exists) continue;
            
            // Execute the statement
            try {
                DB::unprepared($stmt);
                echo "Executed statement: " . substr($stmt, 0, 50) . "...\n";
            } catch (\Exception $e) {
                // Ignore duplicate entry if we didn't check properly, but report others
                if (strpos($e->getMessage(), 'Duplicate entry') === false) {
                    throw $e;
                } else {
                    echo "Skipped duplicate entry.\n";
                }
            }
        }
    } else {
        echo "User 56 already exists.\n";
        
        // Ensure role exists
        $roleExists = DB::table('role_user')->where('user_id', 56)->where('role_id', 85)->exists();
        if (!$roleExists) {
            DB::table('role_user')->insert(['user_id' => 56, 'role_id' => 85]);
            echo "Assigned Role 85 to User 56.\n";
        } else {
            echo "Role 85 already assigned to User 56.\n";
        }
    }
    
    echo "Restore completed successfully.\n";

} catch (\Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
