<?php

namespace App\Notifications;

use App\Models\Ticket;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Notification;

class TicketAssignedNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(public Ticket $ticket) {}

    public function via($notifiable): array
    {
        return ['database']; // agrega 'mail','broadcast' si lo quieres
    }

    public function toArray($notifiable): array
    {
        return [
            'title' => 'Nuevo ticket asignado',
            'body'  => "Se te asignó el ticket: {$this->ticket->titulo}",
            'icon'  => 'ticket', // para tu UI
            'url'   => route('tickets.show', $this->ticket->id),
            'meta'  => [
                'ticket_id' => $this->ticket->id,
                'prioridad' => $this->ticket->prioridad,
            ],
        ];
    }
}
