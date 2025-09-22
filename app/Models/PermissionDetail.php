<?php

// app/Models/PermissionDetail.php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PermissionDetail extends Model
{
    protected $guarded = [];
    protected $casts = [
        'date' => 'date',
        'start_time' => 'datetime:H:i',
        'end_time'   => 'datetime:H:i',
    ];

    public function requestForm() { return $this->belongsTo(RequestForm::class); }
}
