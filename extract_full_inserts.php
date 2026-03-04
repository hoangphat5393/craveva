<?php

$file = 'hub_db_backup.sql';
$handle = fopen($file, "r");

if ($handle) {
    while (($line = fgets($handle)) !== false) {
        // User 56
        if (strpos($line, '(56,28,') !== false) {
             // We need to extract just the (56,28,...) part.
             // It might be in a list of values.
             // Regex: /\(56,28,.*?\),/ or similar.
             // But values can contain ) inside strings.
             // Assuming no ) inside strings for this user (seems simple data).
             // Let's try to match until `),` or `);`
             if (preg_match('/(\(56,28,.*?\)(?:,|;))/', $line, $matches)) {
                 echo "User 56 INSERT: " . $matches[1] . "\n";
             }
        }
        
        // UserAuth 55
        if (strpos($line, '(55,\'toroyabe@gmail.com\'') !== false) {
             if (preg_match('/(\(55,\'toroyabe@gmail.com\'.*?\)(?:,|;))/', $line, $matches)) {
                 echo "UserAuth 55 INSERT: " . $matches[1] . "\n";
             }
        }
    }
    fclose($handle);
}
