<?php

$file = 'hub_db_backup.sql';
$handle = fopen($file, "r");

if ($handle) {
    while (($line = fgets($handle)) !== false) {
        $pos = strpos($line, '(56,28');
        if ($pos !== false) {
            echo "Found (56,28 at position $pos\n";
            echo "Snippet: " . substr($line, $pos, 100) . "\n";
        }
    }
    fclose($handle);
}
