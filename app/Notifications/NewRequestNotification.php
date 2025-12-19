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

    public function via($notifiable): array
    {
        return ['database'];
    }

    protected function typeLabel(): string
    {
        return match ($this->requestForm->type) {
            RequestForm::TYPE_PERMISO => 'permiso',
            RequestForm::TYPE_CHEQUE  => 'cheque',
            RequestForm::TYPE_COMPRA  => 'compra',
            default                   => $this->requestForm->type,
        };
    }

    public function toArray($notifiable): array
    {
        $typeLabel = $this->typeLabel();

        return [
            'title' => 'Nueva solicitud recibida',
            'body'  => "Tipo: {$typeLabel} | Folio: #{$this->requestForm->id}",
            'icon'  => 'file-plus',
            'url'   => route('requests.show', $this->requestForm->id),
            'meta'  => [
                'request_id' => $this->requestForm->id,
                'type'       => $this->requestForm->type,
                'status'     => $this->requestForm->status,
            ],
        ];
    }
}
