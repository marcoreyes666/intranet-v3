<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request as HttpRequest;
use App\Models\Request as Rq;
use App\Models\Approval;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Carbon;

class ApprovalController extends Controller
{
    public function approve(HttpRequest $http, Rq $rq)
    {
        $step = $this->miStep($rq);
        abort_unless($step, 403, 'No tienes un paso pendiente en esta solicitud.');

        $step->update([
            'decision'   => 'aprobado',
            'comments'   => $http->input('comments'),
            'decided_at' => Carbon::now(),
        ]);

        // Avanzar flujo
        if ($rq->type === 'permiso' && $step->step === 'encargado') {
            // Crear paso rectoría
            Approval::create([
                'request_id' => $rq->id,
                'step'       => 'rectoria',
                'approver_id'=> $this->resolverAprobador('rectoria', $rq),
            ]);
            $rq->update(['status' => 'pendiente_rectoria']);

        } elseif (in_array($rq->type, ['cheque','compra']) && in_array($step->step, ['contabilidad','compras'])) {
            // Crear paso rectoría
            Approval::create([
                'request_id' => $rq->id,
                'step'       => 'rectoria',
                'approver_id'=> $this->resolverAprobador('rectoria', $rq),
            ]);
            $rq->update(['status' => 'pendiente_rectoria']);

        } else {
            // Aprobación final (rectoría)
            $rq->update(['status' => 'aprobado']);

            // Generar documento final si existen los servicios
            if ($rq->type === 'permiso' && class_exists(\App\Services\Documents\PermisoPdfBuilder::class)) {
                app(\App\Services\Documents\PermisoPdfBuilder::class)->build($rq);
            } elseif (in_array($rq->type, ['cheque','compra']) && class_exists(\App\Services\Documents\ExcelBuilder::class)) {
                app(\App\Services\Documents\ExcelBuilder::class)->build($rq);
            }
        }

        return back()->with('ok', 'Aprobado.');
    }

    public function reject(HttpRequest $http, Rq $rq)
    {
        $step = $this->miStep($rq);
        abort_unless($step, 403, 'No tienes un paso pendiente en esta solicitud.');

        $step->update([
            'decision'   => 'rechazado',
            'comments'   => $http->input('comments'),
            'decided_at' => Carbon::now(),
        ]);

        $rq->update([
            'status' => match($step->step) {
                'encargado'    => 'rechazado_encargado',
                'contabilidad' => 'rechazado_contabilidad',
                'compras'      => 'rechazado_compras',
                'rectoria'     => 'rechazado_rectoria',
                default        => $rq->status,
            }
        ]);

        return back()->with('ok', 'Rechazado.');
    }

    private function miStep(Rq $rq): ?Approval
    {
        return $rq->approvals()
            ->where('approver_id', Auth::id())
            ->where('decision', 'pendiente')
            ->latest()
            ->first();
    }

    private function resolverAprobador(string $step, Rq $rq): int
    {
        // (Mismo criterio que en RequestController)
        if ($step === 'rectoria') {
            $user = \App\Models\User::role('Rector')->first();
            if ($user) return $user->id;
        }
        if ($step === 'encargado' && $rq->department_id) {
            $user = \App\Models\User::role('Encargado de departamento')
                ->where('department_id', $rq->department_id)
                ->first();
            if ($user) return $user->id;
        }
        if ($step === 'contabilidad') {
            $user = \App\Models\User::role('Contabilidad')->first();
            if ($user) return $user->id;
        }
        if ($step === 'compras') {
            $user = \App\Models\User::role('Compras')->first();
            if ($user) return $user->id;
        }

        abort(422, "No se encontró aprobador para el paso {$step}.");
    }
}
