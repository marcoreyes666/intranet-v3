<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class SoundRequestStoreRequest extends FormRequest
{
    public function authorize(): bool
    {
        // Los permisos finos los maneja la policy / roles
        return true;
    }

    public function rules(): array
    {
        return [
            'event_title'  => 'required|string|max:255',
            'event_date'   => 'required|date',
            'start_time'   => 'required|date_format:H:i',
            'end_time'     => 'required|date_format:H:i|after:start_time',
            'requirements' => 'required|string',
        ];
    }

    public function messages(): array
    {
        return [
            'event_title.required' => 'El título del evento es obligatorio.',
            'event_date.required'  => 'La fecha del evento es obligatoria.',
            'start_time.required'  => 'La hora de inicio es obligatoria.',
            'end_time.after'       => 'La hora de fin debe ser posterior a la hora de inicio.',
            'requirements.required'=> 'Describe qué equipo de sonido necesitas.',
        ];
    }
}
