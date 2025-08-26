<?php

namespace App\Http\Requests\Ticket;

use Illuminate\Foundation\Http\FormRequest;

class StoreTicketRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }
    public function rules(): array
    {
        return [
            'titulo'          => 'required|string|max:255',
            'descripcion'     => 'nullable|string',
            'departamento_id' => 'required|exists:departments,id',
        ];
    }
}
