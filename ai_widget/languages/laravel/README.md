# Laravel/PHP Integration

## Installation

No additional packages required. Uses Laravel's built-in HTTP client.

## Usage

```php
use App\Services\CravevaAgentService;

$agent = new CravevaAgentService();
$response = $agent->chat("Hello, how can you help me?");
echo $response['output'];
```

## Service Registration

Add to `app/Providers/AppServiceProvider.php`:

```php
use App\Services\CravevaAgentService;

public function register()
{
    $this->app->singleton(CravevaAgentService::class);
}
```

## Features

- Laravel service class
- HTTP client with timeout handling
- Error logging included
- Ready for dependency injection
