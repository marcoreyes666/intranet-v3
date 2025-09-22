<?php // app/Events/AvisoPublicado.php
namespace App\Events;

use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class AvisoPublicado
{
    use Dispatchable, SerializesModels;

    public function __construct(
        public int $aviso_id,
        public ?int $department_id = null, // null = a todos
        public ?string $titulo = null
    ) {}

    public function context(): array {
        return [
            'type' => 'aviso.published',
            'aviso_id' => $this->aviso_id,
            'departamento_id' => $this->department_id,
            'titulo' => $this->titulo,
        ];
    }
}
