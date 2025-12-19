<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class RequestApproval extends Model
{
    protected $table = 'request_approvals';

    protected $fillable = [
        'request_form_id',
        'level',
        'role',
        'state',
        'decided_by',
        'decided_at',
        'comment',
    ];

    protected $casts = [
        'decided_at' => 'datetime',
    ];

    public function requestForm(): BelongsTo
    {
        return $this->belongsTo(RequestForm::class);
    }

    public function decider(): BelongsTo
    {
        return $this->belongsTo(User::class, 'decided_by');
    }
}
