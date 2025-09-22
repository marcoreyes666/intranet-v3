<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class EventRequest extends FormRequest
{
    public function authorize(): bool { return true; }

    public function rules(): array
    {
        return [
            'title'     => 'required|string|max:255',
            'start'     => 'required|date',
            'end'       => 'nullable|date|after_or_equal:start',
            'all_day'   => 'sometimes|boolean',
            'location'  => 'nullable|string|max:255',
            'notes'     => 'nullable|string',
        ];
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'all_day' => filter_var($this->input('all_day', false), FILTER_VALIDATE_BOOLEAN),
        ]);
    }
}
