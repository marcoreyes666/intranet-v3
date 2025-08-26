<?php 

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RequestDocument extends Model
{
    protected $fillable = ['request_id','doc_type','template','path'];

    public function request()
    {
        return $this->belongsTo(Request::class);
    }
}
