<?php

$file = 'hub_db_backup.sql';
$handle = fopen($file, "r");

if ($handle) {
    $inUsersInsert = false;
    $buffer = '';
    
    while (($line = fgets($handle)) !== false) {
        if (strpos($line, 'INSERT INTO `users`') !== false) {
            $inUsersInsert = true;
            $buffer = $line;
        } else if ($inUsersInsert) {
            $buffer .= $line;
        }

        if ($inUsersInsert && substr(trim($line), -1) == ';') {
            // End of insert statement
            // Process buffer
            // Matches (<id>, <company_id>, ...
            // We look for ,28, as second parameter.
            // Pattern: start of value group '(', then digits, then comma, then 28, then comma.
            
            if (preg_match_all('/\((\d+),28,/', $buffer, $matches)) {
                echo "Found users for company 28:\n";
                print_r($matches[1]); // IDs
            }
            
            $inUsersInsert = false;
            $buffer = '';
        }
    }
    fclose($handle);
} else {
    echo "Error opening file.";
}

echo "Done scanning.\n";
