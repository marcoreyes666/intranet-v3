<?php

namespace App\Notifications;

use App\Models\RequestForm;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Notification;

class RequestStatusNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(public RequestForm $requestForm) {}

    public function via($notifiable): array { return ['database']; }

    public function toArray($notifiable): array
    {
        return [
            'title' => 'Estatus de tu solicitud',
            'body'  => "Solicitud {$this->requestForm->folio} fue {$this->requestForm->estatus}",
            'icon'  => $this->requestForm->estatus === 'Aprobada' ? 'check-circle' : 'x-circle',
            'url'   => route('requests.show', $this->requestForm->id),
            'meta'  => [
                'request_id' => $this->requestForm->id,
                'estatus'    => $this->requestForm->estatus,
            ],
        ];
    }
}
