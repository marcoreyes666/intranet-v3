<?php

namespace App\Providers;

use App\Models\Solicitud;
use App\Policies\RequestPolicy;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;
use App\Models\PermissionRequest;
use App\Policies\PermissionRequestPolicy;

class AuthServiceProvider extends ServiceProvider
{
    protected $policies = [
        \App\Models\RequestForm::class => \App\Policies\RequestFormPolicy::class,
        \App\Models\Announcement::class => \App\Policies\AnnouncementPolicy::class,
        \App\Models\Event::class => \App\Policies\EventPolicy::class,
    ];

    public function boot(): void {}
}
