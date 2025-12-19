<?php

namespace App\Events;

use App\Models\Ticket;
use Illuminate\Broadcasting\Channel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Queue\SerializesModels;

class TicketUpdated implements ShouldBroadcastNow
{
    use SerializesModels;

    public function __construct(public Ticket $ticket, public string $action) {}

    public function broadcastOn(): array
    {
        return [new Channel('tickets')];
    }

    public function broadcastAs(): string
    {
        return 'ticket.updated';
    }

    public function broadcastWith(): array
    {
        return [
            'id'            => $this->ticket->id,
            'action'        => $this->action, // created|updated|assigned|status_changed|commented|attachment_changed|deleted
            'estado'        => $this->ticket->estado,
            'prioridad'     => $this->ticket->prioridad,
            'departamento_id'=> $this->ticket->departamento_id,
            'asignado_id'   => $this->ticket->asignado_id,
            'updated_at'    => optional($this->ticket->updated_at)->toISOString(),
        ];
    }
}
