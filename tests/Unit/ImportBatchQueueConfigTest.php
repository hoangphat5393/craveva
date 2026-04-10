<?php

namespace Tests\Unit;

use PHPUnit\Framework\TestCase;

class ImportBatchQueueConfigTest extends TestCase
{
    public function test_import_batch_connection_defaults_to_database_when_env_unset(): void
    {
        $path = dirname(__DIR__, 2).'/config/queue.php';

        $keys = ['IMPORT_BATCH_QUEUE_CONNECTION'];
        $saved = [];
        foreach ($keys as $key) {
            $saved[$key] = getenv($key);
            putenv($key);
            unset($_ENV[$key], $_SERVER[$key]);
        }

        try {
            $config = require $path;
            $this->assertSame('database', $config['import_batch_connection']);
        } finally {
            foreach ($keys as $key) {
                if ($saved[$key] === false || $saved[$key] === '') {
                    putenv($key);
                    unset($_ENV[$key], $_SERVER[$key]);
                } else {
                    putenv($key.'='.$saved[$key]);
                    $_ENV[$key] = $saved[$key];
                    $_SERVER[$key] = $saved[$key];
                }
            }
        }
    }
}
