<?php

namespace App\Notifications;

use App\Models\Ticket;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Notification;

class NewTicketForDepartmentNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(public Ticket $ticket) {}

    public function via($notifiable): array { return ['database']; }

    public function toArray($notifiable): array
    {
        return [
            'title' => 'Nuevo ticket en tu departamento',
            'body'  => "Departamento recibió: {$this->ticket->titulo}",
            'icon'  => 'inbox',
            'url'   => route('tickets.show', $this->ticket->id),
            'meta'  => [
                'departamento_id' => $this->ticket->departamento_id,
                'ticket_id' => $this->ticket->id,
            ],
        ];
    }
}
