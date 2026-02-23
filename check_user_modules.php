$u = App\Models\User::where('email', 'hoangphat5393@gmail.com')->first();
auth()->login($u);
echo 'User ID: ' . $u->id . PHP_EOL;
echo 'Modules: ' . json_encode(user_modules()) . PHP_EOL;
exit;
