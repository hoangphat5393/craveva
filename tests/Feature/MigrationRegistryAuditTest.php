<?php

use Symfony\Component\Process\Process;

it('passes migration registry audit with no duplicate basenames and critical tables present', function () {
    $script = base_path('database/scripts/audit_migrations_registry.php');
    $process = new Process([PHP_BINARY, $script], base_path());
    $process->run();

    expect($process->getExitCode())->toBe(0, $process->getOutput() . $process->getErrorOutput());
});
