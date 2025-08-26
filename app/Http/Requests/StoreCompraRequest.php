<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;

class StoreCompraRequest extends FormRequest
{
    public function authorize(): bool { return Auth::check(); }

    public function rules(): array {
        return [
            'solicitante'    => 'required|string|max:255',
            'departamento'   => 'required|string|max:255',
            'fecha'          => 'required|date',
            'descripcion'    => 'required|string|max:1000',
            'total_estimado' => 'required|numeric|min:0',
        ];
    }
}
