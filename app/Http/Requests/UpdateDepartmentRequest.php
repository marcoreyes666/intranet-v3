<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateDepartmentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->hasRole('Administrador') ?? false;
    }

    public function rules(): array
    {
        $id = $this->route('department'); // resource param
        return [
            'name' => ['required','string','max:150', Rule::unique('departments','name')->ignore($id)],
            'slug' => ['nullable','string','max:180', Rule::unique('departments','slug')->ignore($id)],
            'code' => 'nullable|string|max:20',
            'description' => 'nullable|string|max:1000',
            'is_active' => 'sometimes|boolean',
        ];
    }
}

