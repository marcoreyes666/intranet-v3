<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PurchaseItem extends Model
{
    protected $table = 'purchase_items';

    protected $fillable = [
        'purchase_detail_id',
        'qty',
        'unit',
        'description',
    ];

    protected $casts = [
        'qty' => 'decimal:2',
    ];

    public function detail(): BelongsTo
    {
        return $this->belongsTo(PurchaseDetail::class, 'purchase_detail_id');
    }
}
