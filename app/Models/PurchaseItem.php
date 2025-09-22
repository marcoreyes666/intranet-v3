<?php

// app/Models/PurchaseItem.php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PurchaseItem extends Model
{
    protected $guarded = [];

    public function purchaseDetail() { return $this->belongsTo(PurchaseDetail::class); }
}
