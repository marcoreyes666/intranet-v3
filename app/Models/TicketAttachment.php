<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TicketAttachment extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'ticket_id','user_id','path','nombre_original','mime_type','size','visibility',
    ];

    public function ticket(): BelongsTo {
        return $this->belongsTo(Ticket::class);
    }

    public function user(): BelongsTo {
        return $this->belongsTo(User::class);
    }
}
