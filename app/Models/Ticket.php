<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Ticket extends Model
{
    protected $fillable = [
        'titulo',
        'descripcion',
        'categoria',
        'prioridad',
        'estado',
        'usuario_id',
        'asignado_id',
        'departamento_id',
        'resuelto_en',
    ];

    protected $casts = [
        'resuelto_en' => 'datetime',
    ];

    /**
     * Alcance: tickets visibles para un usuario dado.
     */
    public function scopeVisibleTo($query, User $u)
    {
        if ($u->hasAnyRole(['Administrador', 'Rector', 'Sistemas'])) {
            return $query;
        }

        if ($u->hasRole('Encargado de departamento')) {
            return $query->where('departamento_id', $u->department_id);
        }

        return $query->where(function ($w) use ($u) {
            $w->where('usuario_id', $u->id)
              ->orWhere('asignado_id', $u->id);
        });
    }

    // Reportante
    public function usuario(): BelongsTo
    {
        return $this->belongsTo(User::class, 'usuario_id');
    }

    // Técnico asignado
    public function asignado(): BelongsTo
    {
        return $this->belongsTo(User::class, 'asignado_id');
    }

    public function departamento(): BelongsTo
    {
        return $this->belongsTo(Department::class);
    }

    public function comments(): HasMany
    {
        return $this->hasMany(TicketComment::class);
    }

    public function attachments(): HasMany
    {
        return $this->hasMany(TicketAttachment::class);
    }
}
