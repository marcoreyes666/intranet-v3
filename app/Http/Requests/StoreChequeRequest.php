<?php

// app/Http/Requests/StoreChequeRequest.php
namespace App\Http\Requests;
use Illuminate\Foundation\Http\FormRequest;

class StoreChequeRequest extends FormRequest {
    public function authorize(): bool { return $this->user()->can('create', \App\Models\RequestForm::class); }
    public function rules(): array {
        return [
            'pay_to'   => ['required','string','max:150'],
            'concept'  => ['required','string'],
            'currency' => ['required','in:MXN,USD'],
            'amount'   => ['required','numeric','min:0.01'],
        ];
    }
}
