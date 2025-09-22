<?php

// app/Models/PurchaseDetail.php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PurchaseDetail extends Model
{
    protected $guarded = [];
    protected $casts = [
        'urls'        => 'array',
        'delivered_at'=> 'datetime',
        'created_at'  => 'datetime',
        'updated_at'  => 'datetime',
    ];

    public function requestForm() { return $this->belongsTo(RequestForm::class); }
    public function items()       { return $this->hasMany(PurchaseItem::class); }
    public function completedBy() { return $this->belongsTo(User::class, 'completed_by'); }
}
