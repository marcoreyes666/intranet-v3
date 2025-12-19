<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class RequestForm extends Model
{
    public const TYPE_PERMISO = 'permiso';
    public const TYPE_CHEQUE  = 'cheque';
    public const TYPE_COMPRA  = 'compra';

    protected $table = 'request_forms';

    protected $fillable = [
        'type',
        'status',
        'user_id',
        'department_id',
        'current_level',
        'submitted_at',
    ];

    protected $casts = [
        'submitted_at' => 'datetime',
    ];

    // Relación con usuario que solicita
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    // Departamento del solicitante
    public function department(): BelongsTo
    {
        return $this->belongsTo(Department::class);
    }

    // Niveles de aprobación
    public function approvals(): HasMany
    {
        return $this->hasMany(RequestApproval::class);
    }

    // Detalles por tipo
    public function permiso(): HasOne
    {
        return $this->hasOne(PermissionDetail::class);
    }

    public function cheque(): HasOne
    {
        return $this->hasOne(ChequeDetail::class);
    }

    public function compra(): HasOne
    {
        return $this->hasOne(PurchaseDetail::class);
    }

    /**
     * Scope: solicitudes pendientes para un aprobador concreto.
     * Usado en RequestFormController@index cuando filter = 'pendientes'.
     */
    public function scopePendingForApprover($query, User $user)
    {
        $query->where('status', 'en_revision')
            ->whereHas('approvals', function ($q) use ($user) {
                $q->where('state', 'pendiente')
                  ->where(function ($q2) use ($user) {
                      if ($user->hasRole('Rector')) {
                          $q2->orWhere('role', 'Rector');
                      }
                      if ($user->hasRole('Compras')) {
                          $q2->orWhere('role', 'Compras');
                      }
                      if ($user->hasRole('Contabilidad')) {
                          $q2->orWhere('role', 'Contabilidad');
                      }
                      if ($user->hasRole('Encargado de departamento')) {
                          $q2->orWhere('role', 'Encargado');
                      }
                  });
            });

        // Encargado sólo ve las de su departamento
        if ($user->hasRole('Encargado de departamento')) {
            $query->where('department_id', $user->department_id);
        }

        return $query;
    }
}
