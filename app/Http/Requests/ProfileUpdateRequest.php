<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ProfileUpdateRequest extends FormRequest
{
    public function authorize(): bool
    {
        // El usuario autenticado puede editar su propio perfil
        return true;
    }

    public function rules(): array
    {
        $userId = $this->user()->id;

        return [
            'name'       => ['required','string','max:255'],
            'email'      => [
                'required','string','email','max:255',
                Rule::unique('users','email')->ignore($userId),
            ],
            'birth_date' => ['nullable','date','before:today'],
            // agrega aquí otros campos de perfil si los usas (phone, position, etc.)
        ];
    }
}
