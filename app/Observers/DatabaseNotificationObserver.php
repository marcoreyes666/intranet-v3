<?php

namespace App\Observers;

use Illuminate\Notifications\DatabaseNotification;

class DatabaseNotificationObserver
{
    public function created(DatabaseNotification $notification): void
    {
        $user = $notification->notifiable;

        if (! $user || ! method_exists($user, 'unreadNotifications')) {
            return;
        }

        $unreadCount = $user->unreadNotifications()->count();

        $latest = $user->unreadNotifications()
            ->latest()
            ->limit(5)
            ->get()
            ->map(function ($n) {
                return [
                    'id'    => $n->id,
                    'title' => data_get($n->data, 'title', 'Notificación'),
                    'body'  => data_get($n->data, 'body', ''),
                    'url'   => route('notifications.go', $n->id),
                ];
            })
            ->values()
            ->all();

        event(new \App\Events\UserNotificationPushed(
            $user->id,
            $unreadCount,
            $latest
        ));
    }
}
