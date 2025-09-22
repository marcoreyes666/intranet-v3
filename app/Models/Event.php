<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Event extends Model
{
    protected $fillable = [
        'title','description','start','end','all_day','location',
    ];

    protected $casts = [
        'start'   => 'datetime',
        'end'     => 'datetime',
        'all_day' => 'boolean',
    ];
}
