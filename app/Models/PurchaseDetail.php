<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class PurchaseDetail extends Model
{
    protected $table = 'purchase_details';

    protected $fillable = [
        'request_form_id',
        'justification',
        'urls',
        'delivered_at',
        'completed_by',
    ];

    protected $casts = [
        'urls'        => 'array',
        'delivered_at'=> 'datetime',
    ];

    public function requestForm(): BelongsTo
    {
        return $this->belongsTo(RequestForm::class);
    }

    public function items(): HasMany
    {
        return $this->hasMany(PurchaseItem::class);
    }

    public function completedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'completed_by');
    }
}
