<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreDepartmentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->hasRole('Administrador') ?? false;
    }

    public function rules(): array
    {
        return [
            'name' => 'required|string|max:150|unique:departments,name',
            'slug' => 'nullable|string|max:180|unique:departments,slug',
            'code' => 'nullable|string|max:20',
            'description' => 'nullable|string|max:1000',
            'is_active' => 'sometimes|boolean',
        ];
    }
}

