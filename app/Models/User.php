<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Spatie\Permission\Traits\HasRoles;

class User extends Authenticatable
{
    use HasFactory, Notifiable, HasRoles;

    // Si usas Spatie con el guard web:
    protected $guard_name = 'web';

    protected $fillable = [
        'name',
        'email',
        'password',
        'department_id',
        'birth_date',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $casts = [
        'email_verified_at' => 'datetime',
        'birth_date'        => 'date',
    ];

    public function department()
    {
        return $this->belongsTo(\App\Models\Department::class);
    }
}
