<?php

// app/Models/RequestForm.php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RequestForm extends Model
{
    protected $table = 'request_forms';
    protected $guarded = [];

    protected $casts = [
        'created_at'   => 'datetime',
        'updated_at'   => 'datetime',
        'submitted_at' => 'datetime',
    ];

    const TYPE_PERMISO = 'permiso';
    const TYPE_CHEQUE  = 'cheque';
    const TYPE_COMPRA  = 'compra';

    public function user()
    {
        return $this->belongsTo(User::class);
    }
    public function department()
    {
        return $this->belongsTo(Department::class);
    }

    public function approvals()
    {
        return $this->hasMany(RequestApproval::class);
    }

    public function permiso()
    {
        return $this->hasOne(PermissionDetail::class);
    }
    public function cheque()
    {
        return $this->hasOne(ChequeDetail::class);
    }
    public function compra()
    {
        return $this->hasOne(PurchaseDetail::class);
    }

    // helpers de estado
    public function scopeMine($q, $userId)
    {
        return $q->where('user_id', $userId);
    }

    // app/Models/RequestForm.php
    public function scopePendingForApprover($q, \App\Models\User $user)
    {
        if ($user->hasRole('Encargado de departamento') && $user->department_id) {
            return $q->where('department_id', $user->department_id)
                ->whereHas(
                    'approvals',
                    fn($a) =>
                    $a->where('state', 'pendiente')->where('role', 'Encargado')
                );
        }
        if ($user->hasRole('Compras')) {
            return $q->where('type', 'compra')
                ->whereHas(
                    'approvals',
                    fn($a) =>
                    $a->where('state', 'pendiente')->where('role', 'Compras')
                );
        }
        if ($user->hasRole('Contabilidad')) {
            return $q->where('type', 'cheque')
                ->whereHas(
                    'approvals',
                    fn($a) =>
                    $a->where('state', 'pendiente')->where('role', 'Contabilidad')
                );
        }
        if ($user->hasRole('Rector')) {
            return $q->whereHas(
                'approvals',
                fn($a) =>
                $a->where('state', 'pendiente')->where('role', 'Rector')
            );
        }
        return $q->whereRaw('0=1');
    }
}
