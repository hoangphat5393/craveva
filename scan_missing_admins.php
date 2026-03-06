<?php

$file = 'hub_db_backup.sql';
$handle = fopen($file, 'r');

$targetCompanies = [1, 20, 27, 30]; // Legit looking companies

echo 'Scanning for users in companies: '.implode(', ', $targetCompanies)."\n";

if ($handle) {
    while (($line = fgets($handle)) !== false) {
        // Look for users belonging to these companies
        // Pattern: (id, company_id, ...)
        // e.g. (..., 20, ...)

        foreach ($targetCompanies as $companyId) {
            // Pattern: (AnyID, CompanyID, ...
            // Users table structure: `id`, `company_id`, ...
            // Insert usually looks like: VALUES (..., ..., ...), (..., ..., ...)
            // We need to match `,CompanyID,` carefully or just use loose matching for now.
            // The dump format for `users` table:
            // INSERT INTO `users` VALUES (56,28,...)

            // Regex to find user entries for specific company
            // Matches: `(UserID, CompanyID,`
            if (preg_match("/\((\d+),$companyId,/", $line, $matches)) {
                echo "Found User ID {$matches[1]} for Company $companyId\n";
                // echo substr($line, 0, 100) . "...\n";
            }
        }
    }
    fclose($handle);
}
