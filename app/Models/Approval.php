<?php

// app/Models/Approval.php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;

class Approval extends Model {
  protected $fillable = ['request_id','step','approver_id','decision','comments','decided_at'];
  public function request(){ return $this->belongsTo(Request::class); }
  public function approver(){ return $this->belongsTo(User::class,'approver_id'); }
}
