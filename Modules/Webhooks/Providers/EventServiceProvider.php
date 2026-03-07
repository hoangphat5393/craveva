<?php

namespace Modules\Webhooks\Providers;

use App\Events\NewCompanyCreatedEvent;
use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;
use Modules\Webhooks\Listeners\CompanyCreatedListener;

class EventServiceProvider extends ServiceProvider
{
    protected $listen = [
        NewCompanyCreatedEvent::class => [CompanyCreatedListener::class],
    ];

    protected $observers = [
        // Observers are now registered dynamically in WebhooksServiceProvider::registerObservers()
        // based on WebhooksSetting::WEBHOOK_FOR configuration.
        // This prevents crashes when modules/models are missing and avoids duplicate observer registration.
    ];
}
