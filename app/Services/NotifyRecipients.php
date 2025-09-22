<?php

namespace App\Services;

use App\Models\User;
use App\Models\Ticket;
use App\Models\RequestForm; // ajusta al nombre real de tu modelo de solicitudes
use App\Models\Announcement; // ajusta si tu modelo se llama distinto
use Illuminate\Support\Facades\Notification;
use App\Notifications\TicketAssignedNotification;
use App\Notifications\TicketUpdatedNotification;
use App\Notifications\NewTicketForDepartmentNotification;
use App\Notifications\NewAnnouncementNotification;
use App\Notifications\NewRequestNotification;
use App\Notifications\RequestStatusNotification;

class NotifyRecipients
{
    /** Aviso institucional creado */
    public function onAnnouncementCreated(Announcement $a): void
    {
        // Todos los usuarios (si usas audiencias, filtra aquí)
        $users = User::query()->get();
        Notification::send($users, new NewAnnouncementNotification($a));
    }

    /** Ticket creado y asignado a un departamento (avisar al departamento) */
    public function onTicketCreatedForDepartment(Ticket $t): void
    {
        if (!$t->departamento_id) return;

        $users = User::query()->where('department_id', $t->departamento_id)->get();
        Notification::send($users, new NewTicketForDepartmentNotification($t));
    }

    /** Ticket asignado a un técnico/usuario */
    public function onTicketAssigned(Ticket $t): void
    {
        if ($t->asignado_id) {
            $assignee = User::find($t->asignado_id);
            if ($assignee) $assignee->notify(new TicketAssignedNotification($t));
        }
    }

    /** Comentario / actualización en un ticket */
    public function onTicketUpdated(Ticket $t, ?User $actor = null): void
    {
        // Notificar a quien abrió y a quien esté asignado, excluyendo al actor si aplica
        $recipients = collect([
            User::find($t->usuario_id),
            $t->asignado_id ? User::find($t->asignado_id) : null,
        ])->filter()->unique('id');

        if ($actor) $recipients = $recipients->reject(fn($u) => $u->id === $actor->id);

        Notification::send($recipients, new TicketUpdatedNotification($t));
    }

    /** Nueva solicitud (compras/cheque/permiso) */
    public function onRequestCreated(RequestForm $r): void
    {
        // Compras y Contabilidad reciben todo lo suyo
        $targets = User::role(['Compras','Contabilidad'])->get(); // requiere spatie/permission
        Notification::send($targets, new NewRequestNotification($r));

        // Encargados reciben notificación por nuevas solicitudes de permiso
        if ($r->tipo === 'permiso') {
            $encargados = User::role(['Encargado de departamento'])->get();
            Notification::send($encargados, new NewRequestNotification($r));
        }
    }

    /** Cambio de estatus en cualquier solicitud */
    public function onRequestStatusChanged(RequestForm $r): void
    {
        $owner = User::find($r->usuario_id);
        if ($owner) $owner->notify(new RequestStatusNotification($r));
    }
}
