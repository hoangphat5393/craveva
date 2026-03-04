<?php

use App\Models\SmtpSetting;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

require __DIR__ . '/vendor/autoload.php';
$app = require __DIR__ . '/bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

echo "--- CHECKING SMTP SETTINGS ---\n";

$count = SmtpSetting::count();
echo "Current SmtpSetting count: {$count}\n";

if ($count == 0) {
    echo "SmtpSetting is empty. Creating default record...\n";
    
    $smtp = new SmtpSetting();
    $smtp->mail_driver = 'log';
    $smtp->mail_host = '127.0.0.1';
    $smtp->mail_port = '2525';
    $smtp->mail_username = 'user';
    $smtp->mail_password = 'password';
    $smtp->mail_from_email = 'admin@example.com';
    $smtp->mail_from_name = 'Craveva';
    $smtp->mail_encryption = 'null'; // nullable string or 'tls'/'ssl'
    $smtp->verified = 0;
    $smtp->save();
    
    echo "Created SmtpSetting with ID: {$smtp->id}\n";
} else {
    echo "SmtpSetting already exists.\n";
    $smtp = SmtpSetting::first();
    echo "ID: {$smtp->id}, Driver: {$smtp->mail_driver}, From: {$smtp->mail_from_email}\n";
    
    if (empty($smtp->mail_from_email)) {
        echo "mail_from_email is empty. Updating...\n";
        $smtp->mail_from_email = 'admin@example.com';
        $smtp->save();
        echo "Updated mail_from_email to admin@example.com\n";
    }
}

echo "--- DONE ---\n";
