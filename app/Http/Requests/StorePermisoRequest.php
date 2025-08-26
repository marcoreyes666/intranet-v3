<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;

class StorePermisoRequest extends FormRequest
{
    public function authorize(): bool { return Auth::check(); }

    public function rules(): array {
        return [
            'nombre'           => 'required|string|max:255',
            'departamento'     => 'required|string|max:255',
            'numero_empleado'  => 'required|string|max:50',
            'fecha'            => 'required|date',
            'hora'             => 'required',
            'motivo'           => 'required|string|max:255',
        ];
    }
}
