<?php

namespace App\Providers;

use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;

class EventServiceProvider extends ServiceProvider
{
    protected $listen = [
        \App\Events\RequestCreated::class => [
            \App\Listeners\SendNewRequestNotifications::class,
        ],
        \App\Events\RequestAdvanced::class => [
            \App\Listeners\SendRequestStatusNotifications::class,
        ],
        \App\Events\RequestRejected::class => [
            \App\Listeners\SendRequestStatusNotifications::class,
        ],
        \App\Events\RequestApproved::class => [
            \App\Listeners\SendRequestStatusNotifications::class,
        ],
    ];

    public function boot(): void
    {
        //
    }
}
