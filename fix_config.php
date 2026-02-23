<?php
$file = '/var/www/craveva-staging/current/craveva/vendor/froiden/envato/src/Config/froiden_envato.php';
$content = file_get_contents($file);
$newContent = str_replace('\App\Setting::class', '\App\Models\GlobalSetting::class', $content);
if ($content !== $newContent) {
    file_put_contents($file, $newContent);
    echo "File updated successfully.\n";
} else {
    echo "No changes needed or pattern not found.\n";
}
