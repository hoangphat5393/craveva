<?php

namespace Modules\Policy\Providers;

use App\Events\NewCompanyCreatedEvent;
use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;
use Modules\Policy\Events\PolicyAcknowledgedEvent;
use Modules\Policy\Events\PolicyPublishedEvent;
use Modules\Policy\Events\SendReminderEvent;
use Modules\Policy\Listeners\CompanyCreatedListener;
use Modules\Policy\Listeners\PolicyAcknowledgedListener;
use Modules\Policy\Listeners\PolicyPublishedListener;
use Modules\Policy\Listeners\SendReminderListener;

class EventServiceProvider extends ServiceProvider
{
    protected $listen = [
        NewCompanyCreatedEvent::class => [CompanyCreatedListener::class],
        SendReminderEvent::class => [SendReminderListener::class],
        PolicyAcknowledgedEvent::class => [PolicyAcknowledgedListener::class],
        PolicyPublishedEvent::class => [PolicyPublishedListener::class],
    ];
}
