<?php

namespace App\Providers;

use App\Models\Solicitud;
use App\Policies\RequestPolicy;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;

class AuthServiceProvider extends ServiceProvider
{
    protected $policies = [
        Solicitud::class => RequestPolicy::class,
    ];

    public function boot(): void {}
}
