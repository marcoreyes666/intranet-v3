<?php

namespace App\Http\Requests\Ticket;

use Illuminate\Foundation\Http\FormRequest;

class UpdateTicketRequest extends FormRequest
{
    public function authorize(): bool { return true; }
    public function rules(): array {
        return [
            'titulo'          => 'required|string|max:255',
            'descripcion'     => 'nullable|string',
            'categoria'       => 'required|in:Sistemas,Mantenimiento,Redes,Impresoras,Software,Infraestructura',
            'prioridad'       => 'required|in:Baja,Media,Alta,Crítica',
            'estado'          => 'required|in:Abierto,En proceso,Resuelto,Cerrado',
            'asignado_id'     => 'nullable|exists:users,id',
            'departamento_id' => 'nullable|exists:departments,id',
        ];
    }
}
