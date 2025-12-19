<?php

namespace App\Services;

use App\Models\User;
use App\Models\Ticket;
use App\Models\RequestForm;
use App\Models\Announcement;
use Illuminate\Support\Facades\Notification;
use App\Notifications\TicketAssignedNotification;
use App\Notifications\TicketUpdatedNotification;
use App\Notifications\NewTicketForDepartmentNotification;
use App\Notifications\NewAnnouncementNotification;
use App\Notifications\NewRequestNotification;
use App\Notifications\RequestStatusNotification;

class NotifyRecipients
{
    /** Aviso institucional creado (respeta audience/audience_values) */
    public function onAnnouncementCreated(Announcement $a): void
    {
        // Si no está publicado, no se notifica (doble seguridad)
        if (($a->status ?? null) !== 'published') {
            return;
        }

        $audience = $a->audience ?? 'all';
        $values   = is_array($a->audience_values) ? $a->audience_values : [];

        $q = User::query();

        if ($audience === 'all') {
            // Sin filtros
        } elseif ($audience === 'department') {
            // values = [1,2,3]
            $deptIds = array_values(array_filter(array_map('intval', $values)));
            if (empty($deptIds)) return;

            $q->whereIn('department_id', $deptIds);
        } elseif ($audience === 'role') {
            // values = ["Administrador","Rector",...]
            $roles = array_values(array_filter(array_map('strval', $values)));
            if (empty($roles)) return;

            $q->whereHas('roles', fn ($r) => $r->whereIn('name', $roles));
        } else {
            // audience inválido → no notificar
            return;
        }

        $users = $q->get();

        if ($users->isEmpty()) {
            return;
        }

        Notification::send($users, new NewAnnouncementNotification($a));
    }

    /** Ticket creado y asignado a un departamento (avisar al departamento) */
    public function onTicketCreatedForDepartment(Ticket $t): void
    {
        if (! $t->departamento_id) {
            return;
        }

        $users = User::query()
            ->where('department_id', $t->departamento_id)
            ->get();

        if ($users->isEmpty()) {
            return;
        }

        Notification::send($users, new NewTicketForDepartmentNotification($t));
    }

    /** Ticket asignado a un técnico/usuario */
    public function onTicketAssigned(Ticket $t): void
    {
        if (! $t->asignado_id) {
            return;
        }

        $assignee = User::find($t->asignado_id);
        if ($assignee) {
            $assignee->notify(new TicketAssignedNotification($t));
        }
    }

    /** Comentario / actualización en un ticket */
    public function onTicketUpdated(Ticket $t, ?User $actor = null): void
    {
        $recipients = collect([
            User::find($t->usuario_id),
            $t->asignado_id ? User::find($t->asignado_id) : null,
        ])->filter()->unique('id');

        if ($actor) {
            $recipients = $recipients->reject(fn ($u) => $u->id === $actor->id);
        }

        if ($recipients->isEmpty()) {
            return;
        }

        Notification::send($recipients, new TicketUpdatedNotification($t));
    }

    /** Nueva solicitud (compras/cheque/permiso) */
    public function onRequestCreated(RequestForm $r): void
    {
        // Compras / Contabilidad reciben sus tipos (según tu lógica actual)
        $targets = User::role(['Compras', 'Contabilidad'])->get();
        if ($targets->isNotEmpty()) {
            Notification::send($targets, new NewRequestNotification($r));
        }

        // Encargados reciben nuevas solicitudes de permiso
        if ($r->type === RequestForm::TYPE_PERMISO) {
            $encargados = User::role(['Encargado de departamento'])->get();
            if ($encargados->isNotEmpty()) {
                Notification::send($encargados, new NewRequestNotification($r));
            }
        }
    }

    /** Cambio de estatus en cualquier solicitud */
    public function onRequestStatusChanged(RequestForm $r): void
    {
        $owner = User::find($r->user_id);
        if ($owner) {
            $owner->notify(new RequestStatusNotification($r));
        }
    }
}
