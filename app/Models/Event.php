<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Event extends Model
{
    protected $fillable = [
        'title',
        'description',
        'start',
        'end',
        'all_day',
        'location',
        'notes',    // 👈 ahora sí se guarda
        'user_id',  // 👈 quién creó el evento
        'color',    // ya existe la columna, no estorba
    ];

    protected $casts = [
        'start'   => 'datetime',
        'end'     => 'datetime',
        'all_day' => 'boolean',
    ];
}
