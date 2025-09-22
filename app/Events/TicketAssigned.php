<?php // app/Events/TicketAssigned.php
namespace App\Events;

use App\Models\Ticket;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class TicketAssigned
{
    use Dispatchable, SerializesModels;
    public function __construct(public Ticket $ticket) {}
    public function context(): array {
        return [
            'type' => 'ticket.assigned',
            'ticket_id' => $this->ticket->id,
            'departamento_id' => $this->ticket->departamento_id,
            'asignado_id' => $this->ticket->asignado_id,
            'usuario_id' => $this->ticket->usuario_id,
            'titulo' => $this->ticket->titulo,
        ];
    }
}
