<?php

namespace App\Providers;

use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;

// Estos ya los tenías
use App\Models\Solicitud;
use App\Policies\RequestPolicy;
use App\Models\PermissionRequest;
use App\Policies\PermissionRequestPolicy;

// 👇 Nuevos use para Tickets
use App\Models\Ticket;
use App\Policies\TicketPolicy;

class AuthServiceProvider extends ServiceProvider
{
    protected $policies = [
        \App\Models\RequestForm::class   => \App\Policies\RequestFormPolicy::class,
        \App\Models\Announcement::class  => \App\Policies\AnnouncementPolicy::class,
        \App\Models\Event::class         => \App\Policies\EventPolicy::class,

        // 👇 Registro de la policy de Ticket
        Ticket::class                    => TicketPolicy::class,
    ];

    public function boot(): void {}
}
