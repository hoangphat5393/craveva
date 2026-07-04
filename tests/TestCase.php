<?php

namespace Tests;

use Illuminate\Foundation\Testing\TestCase as BaseTestCase;
use LogicException;

abstract class TestCase extends BaseTestCase
{
    use CreatesApplication;

    protected function setUp(): void
    {
        parent::setUp();

        $connection = (string) config('database.default');
        $database = (string) config("database.connections.{$connection}.database");

        if ($connection === 'sqlite' && $database === ':memory:') {
            return;
        }

        $databaseName = basename(str_replace('\\', '/', $database));
        if (! preg_match('/(?:^|_)(?:test|testing|audit)(?:_|$)/i', $databaseName)) {
            throw new LogicException(
                "Tests may only use SQLite :memory: or a database whose name contains test, testing, or audit. Current database: {$databaseName}"
            );
        }
    }
}
