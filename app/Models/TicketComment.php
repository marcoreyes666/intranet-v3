<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TicketComment extends Model
{
    protected $fillable = ['ticket_id','user_id','comentario','visibility'];
    protected $casts = ['created_at'=>'datetime','updated_at'=>'datetime'];

    public function ticket(): BelongsTo {
        return $this->belongsTo(Ticket::class);
    }

    public function user(): BelongsTo {
        return $this->belongsTo(User::class);
    }
}
