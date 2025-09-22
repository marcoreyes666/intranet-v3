<?php

namespace App\Http\Requests\Ticket;

use Illuminate\Foundation\Http\FormRequest;

class UpdateTicketRequest extends FormRequest {
    public function authorize(): bool {
        $t = $this->route('ticket');
        return $this->user()->can('update', $t);
    }
    public function rules(): array {
        return [
            'titulo'      => ['sometimes','required','string','max:150'],
            'descripcion' => ['nullable','string','max:5000'],
            'prioridad'   => ['sometimes','required', Rule::in(['Baja','Media','Alta','Crítica'])],
            'asignado_id' => ['nullable','exists:users,id'],
            'adjuntos.*'  => ['file','mimes:pdf,jpg,jpeg,png,webp,doc,docx,xls,xlsx','max:4096'],
        ];
    }
}
