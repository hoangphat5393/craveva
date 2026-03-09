<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Cache;

class ClearDecryptRelatedCache extends Command
{
    protected $signature = 'cache:clear-decrypt-related
                            {--all : Clear entire application cache instead of only decrypt-related keys}';

    protected $description = 'Clear caches that may hold encrypted data (e.g. after fixing DecryptException / APP_KEY mismatch)';

    public function handle(): int
    {
        if ($this->option('all')) {
            Cache::flush();
            $this->info('Application cache cleared.');

            return Command::SUCCESS;
        }

        $keys = ['global_setting', 'push_setting'];
        foreach ($keys as $key) {
            Cache::forget($key);
        }
        $this->info('Cleared: ' . implode(', ', $keys) . '. Run "php artisan cache:clear" if issue persists.');

        return Command::SUCCESS;
    }
}
