<?php // app/Events/TicketCommented.php
namespace App\Events;

use App\Models\Ticket;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class TicketCommented
{
    use Dispatchable, SerializesModels;
    public function __construct(public Ticket $ticket, public int $comment_user_id) {}
    public function context(): array {
        return [
            'type' => 'ticket.commented',
            'ticket_id' => $this->ticket->id,
            'departamento_id' => $this->ticket->departamento_id,
            'asignado_id' => $this->ticket->asignado_id,
            'usuario_id' => $this->ticket->usuario_id,
            'comment_user_id' => $this->comment_user_id,
            'titulo' => $this->ticket->titulo,
        ];
    }
}
