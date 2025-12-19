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

    public function via($notifiable): array
    {
        return ['database'];
    }

    protected function statusLabel(): string
    {
        return match ($this->requestForm->status) {
            'borrador'     => 'en borrador',
            'en_revision'  => 'en revisión',
            'aprobada'     => 'aprobada',
            'rechazada'    => 'rechazada',
            'completada'   => 'completada',
            default        => $this->requestForm->status,
        };
    }

    protected function icon(): string
    {
        return match ($this->requestForm->status) {
            'aprobada', 'completada' => 'check-circle',
            'rechazada'              => 'x-circle',
            default                  => 'file-badge',
        };
    }

    public function toArray($notifiable): array
    {
        $label = $this->statusLabel();

        return [
            'title' => 'Estatus de tu solicitud',
            'body'  => "Tu solicitud #{$this->requestForm->id} ahora está {$label}.",
            'icon'  => $this->icon(),
            'url'   => route('requests.show', $this->requestForm->id),
            'meta'  => [
                'request_id' => $this->requestForm->id,
                'status'     => $this->requestForm->status,
                'type'       => $this->requestForm->type,
            ],
        ];
    }
}
