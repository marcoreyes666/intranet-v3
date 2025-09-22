<?php

// app/Models/RequestApproval.php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RequestApproval extends Model
{
    protected $guarded = [];

    protected $casts = [
        'created_at'  => 'datetime',
        'updated_at'  => 'datetime',
        'decided_at'  => 'datetime',
    ];

    public function requestForm() { return $this->belongsTo(RequestForm::class); }
    public function decider()     { return $this->belongsTo(User::class, 'decided_by'); }
}
