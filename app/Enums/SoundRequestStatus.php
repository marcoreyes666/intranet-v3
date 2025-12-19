<?php

namespace App\Enums;

enum SoundRequestStatus: string
{
    case Draft       = 'draft';
    case Submitted   = 'submitted';
    case UnderReview = 'under_review';
    case Returned    = 'returned';
    case Accepted    = 'accepted';
    case Rejected    = 'rejected';
    case Cancelled   = 'cancelled';   // ← NUEVO

    public function label(): string
    {
        return match ($this) {
            self::Draft       => 'Borrador',
            self::Submitted   => 'Enviada',
            self::UnderReview => 'En revisión',
            self::Returned    => 'Devuelta para corrección',
            self::Accepted    => 'Aceptada',
            self::Rejected    => 'Rechazada',
            self::Cancelled   => 'Cancelada',
        };
    }
}
