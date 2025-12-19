<?php

namespace App\Http\Controllers\Requests;

use App\Http\Controllers\Controller;
use App\Models\RequestForm;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

// Eventos
use App\Events\RequestAdvanced as EvRequestAdvanced;
use App\Events\RequestRejected as EvRequestRejected;
use App\Events\RequestApproved as EvRequestApproved;

class RequestApprovalController extends Controller
{
    public function approve(Request $req, RequestForm $requestForm)
    {
        $this->authorize('approve', $requestForm);

        return $this->decide($req, $requestForm, 'aprobado', $req->input('comment'));
    }

    public function reject(Request $req, RequestForm $requestForm)
    {
        $this->authorize('approve', $requestForm);

        return $this->decide($req, $requestForm, 'rechazado', $req->input('comment'));
    }

    private function decide(Request $req, RequestForm $rf, string $decision, ?string $comment)
    {
        return DB::transaction(function () use ($req, $rf, $decision, $comment) {

            // 1) Validar que el usuario tenga un nivel pendiente
            $role = $this->roleForUser($req->user(), $rf);

            $approval = $rf->approvals()
                ->where('state', 'pendiente')
                ->where('role', $role)
                ->orderBy('level')
                ->first();

            abort_if(!$approval, 403, 'No tienes nivel pendiente en esta solicitud');

            // 2) Actualizar nivel actual
            $approval->update([
                'state'      => $decision,
                'decided_by' => $req->user()->id,
                'decided_at' => Carbon::now(),
                'comment'    => $comment,
            ]);

            // 3) Si se rechazó, cerramos todo
            if ($decision === 'rechazado') {
                $rf->update(['status' => 'rechazada']);

                event(new EvRequestRejected($rf));

                return back()->with('ok', 'Solicitud rechazada');
            }

            // 4) Ver si queda otro nivel pendiente
            $nextPending = $rf->approvals()
                ->where('state', 'pendiente')
                ->orderBy('level')
                ->first();

            if ($nextPending) {
                $rf->update([
                    'current_level' => $nextPending->level,
                    'status'        => 'en_revision',
                ]);

                event(new EvRequestAdvanced($rf));
            } else {
                // Sin niveles pendientes → aprobada
                $rf->update(['status' => 'aprobada']);

                event(new EvRequestApproved($rf));
            }

            return back()->with('ok', 'Decisión registrada');
        });
    }

    private function roleForUser($user, RequestForm $rf): string
    {
        if ($user->hasRole('Rector')) {
            return 'Rector';
        }

        if ($user->hasRole('Compras') && $rf->type === 'compra') {
            return 'Compras';
        }

        if ($user->hasRole('Contabilidad') && $rf->type === 'cheque') {
            return 'Contabilidad';
        }

        if (
            $user->hasRole('Encargado de departamento') &&
            $rf->type === 'permiso' &&
            $user->department_id === $rf->department_id
        ) {
            return 'Encargado';
        }

        abort(403, 'Rol no autorizado para esta decisión.');
    }
}
