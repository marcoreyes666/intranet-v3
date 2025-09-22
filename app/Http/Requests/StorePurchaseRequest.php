<?php

// app/Http/Requests/StorePurchaseRequest.php
namespace App\Http\Requests;
use Illuminate\Foundation\Http\FormRequest;

class StorePurchaseRequest extends FormRequest {
    public function authorize(): bool { return $this->user()->can('create', \App\Models\RequestForm::class); }
    public function rules(): array {
        return [
            'justification'     => ['required','string'],
            'urls'              => ['nullable','array'],
            'urls.*'            => ['url'],
            'items'             => ['required','array','min:1'],
            'items.*.qty'       => ['required','numeric','min:0.01'],
            'items.*.unit'      => ['required','string','max:30'],
            'items.*.description'=>['required','string','max:255'],
        ];
    }
}
