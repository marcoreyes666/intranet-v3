<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use App\Models\TicketAttachment;
use App\Observers\TicketAttachmentObserver;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        TicketAttachment::observe(TicketAttachmentObserver::class);
    }
}
