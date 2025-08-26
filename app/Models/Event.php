<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Event extends Model
{
    protected $fillable = [
        'title', 'description', 'location', 'start', 'end', 'all_day', 'color', 'user_id'
    ];

    protected $casts = [
        'start' => 'datetime',
        'end'   => 'datetime',
        'all_day' => 'boolean',
    ];

    public function user() {
        return $this->belongsTo(User::class);
    }
}
