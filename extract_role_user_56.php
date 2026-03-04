<?php

$file = 'hub_db_backup.sql';
$handle = fopen($file, "r");

if ($handle) {
    while (($line = fgets($handle)) !== false) {
        if (strpos($line, 'INSERT INTO `role_user`') !== false) {
            $inRoleUser = true;
        }
        
        if (isset($inRoleUser) && $inRoleUser) {
            // Check for user_id = 56 and role_id = 85.
            // Format usually (user_id, role_id) or (role_id, user_id)?
            // Entrust usually uses (user_id, role_id).
            // Let's check for (56,85) or (85,56).
            // Actually, let's just find anything with 56.
            
            if (strpos($line, '56') !== false) {
                // Check if it matches pattern (56,85 or (85,56
                // Or maybe other columns exist.
                // Let's print the line snippet around 56.
                $pos = strpos($line, '56');
                echo "Found 56 in role_user insert:\n";
                echo substr($line, max(0, $pos - 20), 100) . "\n";
            }
            
            if (substr(trim($line), -1) == ';') {
                $inRoleUser = false;
            }
        }
    }
    fclose($handle);
}
