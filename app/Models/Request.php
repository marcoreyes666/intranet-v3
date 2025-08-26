<?php

// app/Models/Request.php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Casts\AsArrayObject;

class Request extends Model {
  protected $fillable = ['type','user_id','department_id','payload','status'];
  protected $casts = ['payload' => AsArrayObject::class];

  public function user(){ return $this->belongsTo(User::class); }
  public function approvals(){ return $this->hasMany(Approval::class); }
  public function documents(){ return $this->hasMany(RequestDocument::class); }

  public function isFinalApproved(): bool { return $this->status === 'aprobado'; }
}
