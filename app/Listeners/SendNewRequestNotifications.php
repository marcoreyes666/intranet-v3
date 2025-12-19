<?php

namespace App\Listeners;

use App\Events\RequestCreated;
use App\Services\NotifyRecipients;

class SendNewRequestNotifications
{
    public function __construct(protected NotifyRecipients $notify) {}

    public function handle(RequestCreated $event): void
    {
        $this->notify->onRequestCreated($event->requestForm);
    }
}
