<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ChequeDetail extends Model
{
    protected $table = 'cheque_details';

    protected $fillable = [
        'request_form_id',
        'pay_to',
        'concept',
        'currency',
        'amount',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
    ];

    public function requestForm(): BelongsTo
    {
        return $this->belongsTo(RequestForm::class);
    }
}
