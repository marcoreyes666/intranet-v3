<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Event extends Model
{
    use HasFactory;

    protected $fillable = [
        'title',
        'start',
        'end',
        'all_day',
        'location',
        'description',
        'notes',
        'created_by',
        'department_id',
        'is_sound_only',
    ];

    protected $casts = [
        'start'         => 'datetime',
        'end'           => 'datetime',
        'all_day'       => 'boolean',
        'is_sound_only' => 'boolean',
    ];

    // Quién creó el evento
    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    // Departamento asociado (si aplica)
    public function department(): BelongsTo
    {
        return $this->belongsTo(Department::class);
    }

    // Solicitud de sonido asociada (si existe)
    public function soundRequest(): HasOne
    {
        return $this->hasOne(SoundRequest::class);
    }
}
