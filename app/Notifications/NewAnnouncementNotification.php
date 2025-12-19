<?php

namespace App\Notifications;

use App\Models\Announcement;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Notification;

class NewAnnouncementNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(public Announcement $announcement) {}

    public function via($notifiable): array
    {
        return ['database'];
    }

    public function toArray($notifiable): array
    {
        return [
            'title' => 'Nuevo aviso institucional',
            'body'  => $this->announcement->title, // ✅ correcto
            'icon'  => 'megaphone',
            'url'   => route('announcements.feed'),
            'meta'  => [
                'announcement_id' => $this->announcement->id,
            ],
        ];
    }
}
