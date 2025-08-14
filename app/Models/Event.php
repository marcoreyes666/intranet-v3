<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Event extends Model
{
    use HasFactory;

    protected $fillable = ['title','description','start','end','color','created_by','location','all_day'];

    protected $casts = [
        'start'   => 'datetime',
        'end'     => 'datetime',
        'all_day' => 'boolean',
    ];

    public function author()
    {
        return $this->belongsTo(\App\Models\User::class, 'created_by');
    }
}
