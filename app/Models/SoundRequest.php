<?php

namespace App\Models;

use App\Enums\SoundRequestStatus;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

class SoundRequest extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'event_id',
        'event_title',
        'event_date',
        'start_time',
        'end_time',
        'requirements',
        'status',
        'is_late',
        'review_comment',
    ];

    protected $casts = [
        'event_date' => 'date',                      // solo fecha
        'is_late'    => 'boolean',
        'status'     => SoundRequestStatus::class,
        // start_time y end_time se manejan como string (HH:ii)
    ];

    /*
    |--------------------------------------------------------------------------
    | Relaciones
    |--------------------------------------------------------------------------
    */

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function event(): BelongsTo
    {
        return $this->belongsTo(Event::class);
    }

    /*
    |--------------------------------------------------------------------------
    | Helpers de lógica de negocio
    |--------------------------------------------------------------------------
    */

    public function isLateByRule(): bool
    {
        if (! $this->event_date instanceof Carbon) {
            return false;
        }

        $limit = now()->addDays(3)->startOfDay();

        return $this->event_date->lt($limit);
    }

    public function isPendingForSystems(): bool
    {
        return in_array($this->status, [
            SoundRequestStatus::Submitted,
            SoundRequestStatus::UnderReview,
        ], true);
    }

    /*
    |--------------------------------------------------------------------------
    | Scopes útiles
    |--------------------------------------------------------------------------
    */

    public function scopeForReviewer($query)
    {
        return $query->whereIn('status', [
            SoundRequestStatus::Submitted->value,
            SoundRequestStatus::UnderReview->value,
            SoundRequestStatus::Returned->value,
        ]);
    }

    public function scopeUpcoming($query)
    {
        return $query
            ->whereDate('event_date', '>=', today())
            ->orderBy('event_date')
            ->orderBy('start_time');
    }
}
