<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Carbon;

class Announcement extends Model
{
    protected $fillable = [
        'title',
        'body',
        'author_id',
        'status',
        'is_pinned',
        'starts_at',
        'ends_at',
        'audience',
        'audience_values'
    ];

    protected $casts = [
        'is_pinned' => 'boolean',
        'starts_at' => 'datetime',
        'ends_at'   => 'datetime',
        'audience_values' => 'array',
    ];

    public function author()
    {
        return $this->belongsTo(User::class, 'author_id');
    }
    public function reads()
    {
        return $this->hasMany(AnnouncementRead::class);
    }

    // Visibilidad por usuario (roles Spatie + department_id)
    public function scopeVisibleTo(Builder $q, \App\Models\User $user): Builder
    {
        $now = now();

        $q->where('status', 'published')
            ->where(function ($w) use ($now) {
                $w->whereNull('starts_at')->orWhere('starts_at', '<=', $now);
            })
            ->where(function ($w) use ($now) {
                $w->whereNull('ends_at')->orWhere('ends_at', '>=', $now);
            })
            ->where(function ($w) use ($user) {
                $w->where('audience', 'all')
                    ->orWhere(function ($w2) use ($user) {
                        $w2->where('audience', 'role')
                            ->where(function ($x) use ($user) {
                                foreach ($user->getRoleNames() as $role) {
                                    $x->orWhereJsonContains('audience_values', $role);
                                }
                            });
                    })
                    ->orWhere(function ($w3) use ($user) {
                        $deptId = $user->department_id;
                        if ($deptId) {
                            $w3->where('audience', 'department')
                                ->whereJsonContains('audience_values', (int)$deptId);
                        }
                    });
            });

        return $q;
    }
}
