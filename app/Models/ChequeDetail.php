<?php

// app/Models/ChequeDetail.php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ChequeDetail extends Model
{
    protected $guarded = [];
    protected $casts = [
        'amount' => 'decimal:2',
    ];

    public function requestForm() { return $this->belongsTo(RequestForm::class); }
}
