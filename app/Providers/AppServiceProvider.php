<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\View;
use App\Models\Announcement;
use App\Models\User;
use App\Observers\UserObserver;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        // Contadores para el sidebar
        View::composer('partials.sidebar', function ($view) {

            if (!auth()->check()) {
                $view->with([
                    'notifCount' => 0,
                    'unreadAnnouncementsCount' => 0,
                ]);
                return;
            }

            $user = auth()->user();

            // Notificaciones (Laravel notifications)
            $notifCount = $user->unreadNotifications()->count();

            // Avisos no leídos: visibles para el usuario, publicados y vigentes,
            // y que NO tengan un registro read_at para ese usuario.
            $unreadAnnouncementsCount = Announcement::query()
                ->visibleTo($user)
                ->whereDoesntHave('reads', function ($q) use ($user) {
                    $q->where('user_id', $user->id)
                      ->whereNotNull('read_at');
                })
                ->count();

            $view->with(compact('notifCount', 'unreadAnnouncementsCount'));
        });
        User::observe(UserObserver::class);
    }
}
