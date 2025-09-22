<?php

namespace App\Notifications;

use App\Models\RequestForm;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Notification;

class NewRequestNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(public RequestForm $requestForm) {}

    public function via($notifiable): array { return ['database']; }

    public function toArray($notifiable): array
    {
        return [
            'title' => 'Nueva solicitud recibida',
            'body'  => "Tipo: {$this->requestForm->tipo} | Folio: {$this->requestForm->folio}",
            'icon'  => 'file-plus',
            'url'   => route('requests.show', $this->requestForm->id),
            'meta'  => [
                'request_id' => $this->requestForm->id,
                'tipo'       => $this->requestForm->tipo,
            ],
        ];
    }
}
