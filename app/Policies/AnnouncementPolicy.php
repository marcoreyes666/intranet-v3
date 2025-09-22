<?php

namespace App\Policies;

use App\Models\Announcement;
use App\Models\User;

class AnnouncementPolicy
{
    // Quienes administran todo (pueden borrar/publicar)
    protected array $admins   = ['Administrador','Rector'];
    // Quienes pueden crear (y editar lo suyo)
    protected array $creators = ['Administrador','Rector','Encargado de departamento'];

    public function viewAny(User $user): bool { return true; }
    public function view(User $user, Announcement $a): bool { return true; }

    public function create(User $user): bool {
        return $user->hasAnyRole($this->creators);
    }

    public function update(User $user, Announcement $a): bool {
        // Admins o el autor
        return $user->hasAnyRole($this->admins) || $user->id === $a->author_id;
    }

    public function delete(User $user, Announcement $a): bool {
        return $user->hasAnyRole($this->admins);
    }

    public function publish(User $user, Announcement $a): bool {
        return $user->hasAnyRole($this->admins);
    }
}
