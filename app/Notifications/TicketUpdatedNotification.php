<?php

namespace App\Notifications;

use App\Models\Ticket;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Notification;

class TicketUpdatedNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(public Ticket $ticket) {}

    public function via($notifiable): array
    {
        return ['database'];
    }

    public function toArray($notifiable): array
    {
        return [
            'title' => 'Actualización en tu ticket',
            'body'  => "Hubo cambios/comentarios en: {$this->ticket->titulo}",
            'icon'  => 'message-square',
            'url'   => route('tickets.show', $this->ticket->id),
            'meta'  => ['ticket_id' => $this->ticket->id],
        ];
    }
}
