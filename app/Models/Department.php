<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class Department extends Model
{
    use HasFactory;

    protected $fillable = ['name','slug','code','description','is_active'];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    protected static function booted(): void
    {
        static::saving(function (Department $dept) {
            if (blank($dept->slug) && filled($dept->name)) {
                $dept->slug = Str::slug($dept->name);
            }
        });
    }

    public function users()
    {
        return $this->hasMany(\App\Models\User::class);
    }
}
