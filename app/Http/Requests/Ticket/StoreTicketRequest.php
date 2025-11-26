<?php

namespace App\Http\Requests\Ticket;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreTicketRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('create', \App\Models\Ticket::class);
    }

    public function rules(): array
    {
        return [
            'titulo'          => ['required','string','max:150'],
            'descripcion'     => ['nullable','string','max:5000'],

            // AHORA OPCIONALES: se pueden definir después en Gestión
            'categoria'       => ['nullable', Rule::in(['Sistemas','Mantenimiento','Redes','Impresoras','Software','Infraestructura'])],
            'prioridad'       => ['nullable', Rule::in(['Baja','Media','Alta','Crítica'])],

            // El formulario sí pide departamento, así que aquí podemos exigirlo
            'departamento_id' => ['required','exists:departments,id'],

            'adjuntos.*'      => ['file','mimes:pdf,jpg,jpeg,png,webp,doc,docx,xls,xlsx','max:4096'],
        ];
    }
}
