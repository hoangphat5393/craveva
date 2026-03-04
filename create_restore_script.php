<?php

$inputFile = 'hub_db_backup.sql';
$outputFile = 'restore_user_56.sql';

$input = fopen($inputFile, "r");
$output = fopen($outputFile, "w");

if ($input && $output) {
    fwrite($output, "INSERT INTO `user_auths` VALUES ");
    $userAuthFound = false;
    
    // Scan for UserAuth
    while (($line = fgets($input)) !== false) {
        if (strpos($line, "(55,'toroyabe@gmail.com'") !== false) {
            // Extract the value group
            if (preg_match('/(\(55,\'toroyabe@gmail.com\'.*?\)(?:,|;))/', $line, $matches)) {
                $val = trim($matches[1]);
                if (substr($val, -1) == ',') {
                    $val = substr($val, 0, -1); // Remove trailing comma
                }
                if (substr($val, -1) == ';') {
                    $val = substr($val, 0, -1); // Remove trailing semicolon
                }
                fwrite($output, $val . ";\n");
                $userAuthFound = true;
                break;
            }
        }
    }
    
    if (!$userAuthFound) {
        echo "UserAuth 55 not found!\n";
    }

    // Rewind file to search for User
    rewind($input);
    
    fwrite($output, "INSERT INTO `users` VALUES ");
    $userFound = false;
    
    while (($line = fgets($input)) !== false) {
        if (strpos($line, "(56,28,55,0,'Yadah Wang'") !== false) {
            // Extract the value group
            // Note: This line might contain JSON with newlines, but usually mysqldump escapes newlines as \n
            // So it should be on one line.
            if (preg_match('/(\(56,28,55,0,\'Yadah Wang\'.*?\)(?:,|;))/', $line, $matches)) {
                $val = trim($matches[1]);
                if (substr($val, -1) == ',') {
                    $val = substr($val, 0, -1);
                }
                if (substr($val, -1) == ';') {
                    $val = substr($val, 0, -1);
                }
                fwrite($output, $val . ";\n");
                $userFound = true;
                break;
            }
        }
    }
    
    if (!$userFound) {
        echo "User 56 not found!\n";
    }
    
    // Add role_user insert manually since it's simple
    fwrite($output, "INSERT INTO `role_user` (`user_id`, `role_id`) VALUES (56, 85);\n");
    
    fclose($input);
    fclose($output);
    echo "Script generated: $outputFile\n";
    
} else {
    echo "Error opening files.\n";
}
