<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;

class StoreChequeRequest extends FormRequest
{
    public function authorize(): bool { return Auth::check(); }

    public function rules(): array {
        return [
            'solicitante'  => 'required|string|max:255',
            'departamento' => 'required|string|max:255',
            'fecha'        => 'required|date',
            'concepto'     => 'required|string|max:500',
            'monto'        => 'required|numeric|min:0',
        ];
    }
}
