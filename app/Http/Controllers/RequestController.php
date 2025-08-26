<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;                  // HTTP Request
use App\Models\Request as Rq;                 // Modelo Request (alias)
use App\Models\Approval;
use Illuminate\Support\Facades\Auth;

class RequestController extends Controller
{
    public function index(Request $http)
    {
        $user = Auth::user();

        // Mis solicitudes (siempre)
        $mine = Rq::with('approvals','documents')
            ->where('user_id', $user->id)
            ->latest()
            ->paginate(10, ['*'], 'mine_page');

        // Pendientes por aprobar para el usuario (si tiene rol aprobador)
        $pending = null;
        if ($user->hasRole(['Encargado de departamento','Contabilidad','Compras','Rector'])) {
            $pending = Rq::with(['approvals' => function($q) use ($user) {
                    $q->where('approver_id', $user->id)->where('decision','pendiente');
                }])
                ->whereHas('approvals', function($q) use ($user) {
                    $q->where('approver_id', $user->id)->where('decision','pendiente');
                })
                ->latest()
                ->paginate(10, ['*'], 'pending_page');
        }

        // Si ?filter=pendientes, puedes en la vista mostrar el tab correspondiente
        $filter = $http->query('filter');

        return view('requests.index', compact('mine','pending','filter'));
    }

    public function create(string $type)
    {
        abort_unless(in_array($type, ['permiso','cheque','compra']), 404);
        return view("requests.forms.$type", ['type' => $type]);
    }

    public function store(Request $http)
    {
        $type = $http->input('type');
        abort_unless(in_array($type, ['permiso','cheque','compra']), 422, 'Tipo inválido');

        // Valida lo básico (ajusta reglas por tipo después)
        $rules = [
            'type' => 'required|in:permiso,cheque,compra',
        ];
        $http->validate($rules);

        $payload = collect($http->except(['_token']))->toArray();
        $user = Auth::user();

        $rq = Rq::create([
            'type'          => $type,
            'user_id'       => $user->id,
            'department_id' => $user->department_id, // puede ser null
            'payload'       => $payload,
            'status'        => match($type){
                'permiso' => 'pendiente_encargado',
                'cheque'  => 'pendiente_contabilidad',
                'compra'  => 'pendiente_compras',
            },
        ]);

        // Crear primer paso pendiente
        $firstStep = match($type){
            'permiso' => 'encargado',
            'cheque'  => 'contabilidad',
            'compra'  => 'compras',
        };

        Approval::create([
            'request_id' => $rq->id,
            'step'       => $firstStep,
            'approver_id'=> $this->resolverAprobador($firstStep, $rq),
        ]);

        return redirect()->route('requests.index')->with('ok', 'Solicitud enviada correctamente.');
    }

    public function show(Rq $requestModel)
    {
        // Control mínimo de visibilidad sin Policy (puedes reemplazar por authorize('view', $requestModel))
        $user = Auth::user();
        abort_unless($this->puedeVer($user, $requestModel), 403);

        $rq = $requestModel->load('approvals.approver','documents','user');
        return view('requests.show', compact('rq'));
    }

    private function puedeVer($user, Rq $rq): bool
    {
        if ($rq->user_id === $user->id) return true;

        // Aprobadores según tipo
        if ($rq->type === 'permiso' && $user->hasRole(['Encargado de departamento','Rector'])) return true;
        if ($rq->type === 'cheque'  && $user->hasRole(['Contabilidad','Rector'])) return true;
        if ($rq->type === 'compra'  && $user->hasRole(['Compras','Rector'])) return true;

        // Además, si es el aprobador asignado pendiente
        $isApprover = $rq->approvals()
            ->where('approver_id',$user->id)
            ->exists();

        return $isApprover;
    }

    private function resolverAprobador(string $step, Rq $rq): int
    {
        // Encargado del departamento del solicitante
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

        if ($step === 'rectoria') {
            $user = \App\Models\User::role('Rector')->first();
            if ($user) return $user->id;
        }

        abort(422, "No se encontró aprobador para el paso {$step}.");
    }
}
