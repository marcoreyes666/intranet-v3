<?php

use Illuminate\Support\Facades\Broadcast;
use App\Models\Ticket;
use App\Models\Announcement;

Broadcast::channel('users.{userId}', function ($user, $userId) {
    return (int) $user->id === (int) $userId;
});

Broadcast::channel('tickets', function ($user) {
    return $user->can('viewAny', Ticket::class);
});

Broadcast::channel('announcements', function ($user) {
    return $user->can('viewAny', Announcement::class);
});

// Si luego agregas Requests con policy:
// Broadcast::channel('requests', fn($user) => $user->can('viewAny', \App\Models\Request::class));
