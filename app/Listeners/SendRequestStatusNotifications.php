<?php

namespace App\Listeners;

use App\Events\RequestAdvanced;
use App\Events\RequestRejected;
use App\Events\RequestApproved;
use App\Services\NotifyRecipients;

class SendRequestStatusNotifications
{
    public function __construct(protected NotifyRecipients $notify) {}

    /**
     * Puede recibir cualquiera de los tres eventos: Advanced / Rejected / Approved
     */
    public function handle($event): void
    {
        $this->notify->onRequestStatusChanged($event->requestForm);
    }
}
