<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PermissionDetail extends Model
{
    protected $table = 'permission_details';

    protected $fillable = [
        'request_form_id',
        'date',
        'start_time',
        'end_time',
        'reason',
    ];

    protected $casts = [
        'date'       => 'date',
        'start_time' => 'datetime:H:i',
        'end_time'   => 'datetime:H:i',
    ];

    public function requestForm(): BelongsTo
    {
        return $this->belongsTo(RequestForm::class);
    }
}
