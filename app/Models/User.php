<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;   // 👈 IMPORTANTE
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Spatie\Permission\Traits\HasRoles;

class User extends Authenticatable
{
    use HasFactory, Notifiable, HasRoles;

    // (Si usas Spatie con el guard web)
    // protected $guard_name = 'web';

    protected $fillable = [
        'name',
        'email',
        'password',
        'department_id',  // 👈 necesario para asignación masiva
    ];

    protected $hidden = ['password','remember_token'];

    public function department() // 👈 relación debe existir con este nombre
    {
        return $this->belongsTo(\App\Models\Department::class);
    }
}
