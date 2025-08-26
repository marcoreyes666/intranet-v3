<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TicketAttachment extends Model
{
    // Opción A (recomendada): especificar fillable exactos
    protected $fillable = [
        'ticket_id',
        'user_id',
        'path',
        'nombre_original',   // ← DEBE estar
        'mime_type',         // ← DEBE estar
        'size',
        'visibility',
    ];

    // Opción B (si quieres ir a lo seguro durante pruebas)
    // protected $guarded = []; // <- permite todo (quita si usas $fillable)

    public function ticket(): BelongsTo { return $this->belongsTo(Ticket::class); }
    public function user(): BelongsTo   { return $this->belongsTo(User::class); }
}
