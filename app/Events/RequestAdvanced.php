<?php // app/Events/RequestAdvanced.php  (úsalo como "updated/avanzó de etapa")
namespace App\Events;

use App\Models\RequestForm;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class RequestAdvanced
{
    use Dispatchable, SerializesModels;
    public function __construct(public RequestForm $requestForm) {}
    public function context(): array {
        return [
            'type' => 'request.updated',
            'request_id' => $this->requestForm->id,
            'departamento_id' => $this->requestForm->department_id,
            'usuario_id' => $this->requestForm->user_id,
            'titulo' => $this->requestForm->title ?? null,
        ];
    }
}
