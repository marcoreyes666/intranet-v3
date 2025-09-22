<?php

namespace App\Observers;

use App\Models\TicketAttachment;
use Illuminate\Support\Facades\Storage;

class TicketAttachmentObserver
{
    public function deleted(TicketAttachment $a): void
    {
        Storage::disk('local')->delete($a->path);
    }
}
