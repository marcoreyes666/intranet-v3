<?php

// app/Http/Requests/StorePermissionRequest.php
namespace App\Http\Requests;
use Illuminate\Foundation\Http\FormRequest;

class StorePermissionRequest extends FormRequest {
    public function authorize(): bool { return $this->user()->can('create', \App\Models\RequestForm::class); }
    public function rules(): array {
        return [
            'date'       => ['required','date'],
            'start_time' => ['nullable','date_format:H:i'],
            'end_time'   => ['nullable','date_format:H:i'],
            'reason'     => ['nullable','string','max:200'],
        ];
    }
}
