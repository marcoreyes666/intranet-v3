<?php

namespace App\Http\Requests\Ticket;

use Illuminate\Foundation\Http\FormRequest;

class StoreTicketRequest extends FormRequest {
    public function authorize(): bool {
        return $this->user()->can('create', \App\Models\Ticket::class);
    }
    public function rules(): array {
        return [
            'titulo'          => ['required','string','max:150'],
            'descripcion'     => ['nullable','string','max:5000'],
            'categoria'       => ['required', Rule::in(['Sistemas','Mantenimiento','Redes','Impresoras','Software','Infraestructura'])],
            'prioridad'       => ['required', Rule::in(['Baja','Media','Alta','Crítica'])],
            'departamento_id' => ['nullable','exists:departments,id'],
            'adjuntos.*'      => ['file','mimes:pdf,jpg,jpeg,png,webp,doc,docx,xls,xlsx','max:4096'],
        ];
    }
}
