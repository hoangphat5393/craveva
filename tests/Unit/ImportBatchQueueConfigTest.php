<?php

namespace Tests\Unit;

use PHPUnit\Framework\TestCase;

class ImportBatchQueueConfigTest extends TestCase
{
    public function test_import_batch_connection_defaults_to_database(): void
    {
        $path = dirname(__DIR__, 2).'/config/queue.php';
        $config = require $path;

        $this->assertSame('database', $config['import_batch_connection']);
    }
}
