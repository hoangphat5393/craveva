<?php

$file = 'hub_db_backup.sql';
$handle = fopen($file, "r");

if ($handle) {
    while (($line = fgets($handle)) !== false) {
        // Search for (55, ... in user_auths table
        // But (55, could be in any table.
        // We need to check if it's in INSERT INTO `user_auths`
        // But since we are reading line by line, context is lost if we don't track it.
        // However, usually INSERTs are grouped.
        // Let's just grep for `(55,` and print context if `user_auths` is nearby.
        
        if (strpos($line, 'INSERT INTO `user_auths`') !== false) {
             $inUserAuths = true;
        }
        
        if (isset($inUserAuths) && $inUserAuths) {
            $pos = strpos($line, '(55,');
            if ($pos !== false) {
                echo "Found (55, in user_auths insert:\n";
                echo substr($line, $pos, 200) . "\n";
            }
            if (substr(trim($line), -1) == ';') {
                $inUserAuths = false;
            }
        }
    }
    fclose($handle);
}
