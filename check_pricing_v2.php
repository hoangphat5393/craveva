<?php
// check_pricing_v2.php
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo "--- START CHECK ---\n";
$controller = 'Modules\Pricing\Http\Controllers\CompanyPricingController';
echo "Checking Class: $controller\n";

if (class_exists($controller)) {
    echo "[OK] Class exists!\n";
    $ref = new ReflectionClass($controller);
    echo "File: " . $ref->getFileName() . "\n";
} else {
    echo "[FAIL] Class does NOT exist.\n";
    
    $path = base_path('Modules/Pricing/Http/Controllers/CompanyPricingController.php');
    echo "Checking File: $path\n";
    
    if (file_exists($path)) {
        echo "[OK] File exists.\n";
        echo "File Perms: " . substr(sprintf('%o', fileperms($path)), -4) . "\n";
        echo "File Owner: " . posix_getpwuid(fileowner($path))['name'] . "\n";
    } else {
        echo "[FAIL] File NOT found.\n";
        $dir = dirname($path);
        if (is_dir($dir)) {
            echo "Directory contents of $dir:\n";
            $files = scandir($dir);
            foreach ($files as $file) {
                echo " - $file\n";
            }
        } else {
            echo "Directory $dir NOT found.\n";
        }
    }
}
echo "--- END CHECK ---\n";
