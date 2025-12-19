<?php

namespace App\Events;

use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;

class UserNotificationPushed implements ShouldBroadcastNow
{
    public function __construct(
        public int $userId,
        public int $unreadCount,
        public array $latest // lista ya lista para pintar
    ) {}

    public function broadcastOn(): array
    {
        return [new PrivateChannel("users.{$this->userId}")];
    }

    public function broadcastAs(): string
    {
        return 'notification.pushed';
    }

    public function broadcastWith(): array
    {
        return [
            'unreadCount' => $this->unreadCount,
            'latest'      => $this->latest,
        ];
    }
}
