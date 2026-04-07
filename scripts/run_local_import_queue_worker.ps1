# Local: run a worker that processes Client import chunk jobs on the Redis connection.
# Prerequisites: Redis on REDIS_HOST:REDIS_PORT, phpredis enabled, IMPORT_BATCH_QUEUE_CONNECTION=redis in .env
# Usage: .\scripts\run_local_import_queue_worker.ps1
# Then trigger Import Client in the browser; keep this window open.

$ErrorActionPreference = "Stop"
Set-Location (Split-Path -Parent $PSScriptRoot)

Write-Host "Starting queue:work redis --queue=ClientImport (Ctrl+C to stop)..." -ForegroundColor Cyan
php artisan queue:work redis --queue=ClientImport --tries=3
