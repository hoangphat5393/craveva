<?php

echo "Starting script...\n";

require __DIR__.'/vendor/autoload.php';
echo "Autoload loaded.\n";

$app = require __DIR__.'/bootstrap/app.php';
echo "App required.\n";

$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();
echo "App bootstrapped.\n";

use App\Models\Product;
use App\Models\User;
use Modules\Webhooks\Entities\WebhooksLog;
use Modules\Webhooks\Entities\WebhooksSetting;
use Illuminate\Support\Facades\Auth;
use Modules\Webhooks\Jobs\SendWebhook;

// Set up the environment
$userId = 25; // Yadah Wang
$companyId = 20;

// Login as the user
// $user = User::find($userId);
// if (!$user) {
//     echo "User not found!\n";
//     exit;
// }
// Auth::login($user);
// echo "Logged in as User ID: " . $user->id . " (Company: " . $user->company_id . ")\n";

// Check Webhook Settings
$webhook = WebhooksSetting::where('company_id', $companyId)
    ->where('webhook_for', 'Product')
    ->where('status', 'active')
    ->first();

if (!$webhook) {
    echo "No active webhook found for Product in Company $companyId\n";
    exit;
}
echo "Webhook found: " . $webhook->url . "\n";

// Count logs before
$logsBefore = WebhooksLog::count();
echo "Logs before: $logsBefore\n";

// Create a Product
echo "Creating product...\n";
$product = new Product();
$product->name = 'Debug Webhook Product ' . time();
$product->price = 100;
$product->default_image = 'default.png'; // required by some logic?
$product->company_id = $companyId; // Manually set company_id
$product->save();

echo "Product created. ID: " . $product->id . ", Company ID: " . ($product->company_id ?? 'NULL') . "\n";

if (!$product->company_id) {
    echo "WARNING: Product created with NULL company_id. This is likely the issue.\n";
} else {
    echo "Product has company_id: " . $product->company_id . "\n";
}

// Check logs after (wait a bit for queue if async, but it's sync)
// If queue is sync, job should have run.
$logsAfter = WebhooksLog::count();
echo "Logs after: $logsAfter\n";

if ($logsAfter > $logsBefore) {
    echo "SUCCESS: Log count increased.\n";
    $latestLog = WebhooksLog::latest()->first();
    echo "Latest Log ID: " . $latestLog->id . "\n";
    echo "Response Code: " . $latestLog->response_code . "\n";
} else {
    echo "FAILURE: Log count did NOT increase.\n";
    
    // Manually trigger the job to see if it works with explicit company ID
    echo "Attempting manual job dispatch...\n";
    $data = $product->toArray();
    $data['event_action'] = 'created';
    
    // Instantiate job directly to check handle method logic if possible, or just dispatch
    // We will dispatch it and see.
    try {
        SendWebhook::dispatch($data, 'Product', $product->company_id)->onQueue('default');
        echo "Manual dispatch called.\n";
        
        $logsAfterManual = WebhooksLog::count();
        echo "Logs after manual dispatch: $logsAfterManual\n";
    } catch (\Exception $e) {
        echo "Exception during manual dispatch: " . $e->getMessage() . "\n";
    }
}
