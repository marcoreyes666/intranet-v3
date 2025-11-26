<?php

namespace App\Http\Controllers\Requests;

use App\Http\Controllers\Controller;
use App\Models\RequestForm;
use App\Models\RequestApproval;
use App\Models\PermissionDetail;
use App\Models\ChequeDetail;
use App\Models\PurchaseDetail;
use App\Models\PurchaseItem;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

// Requests form validations
use App\Http\Requests\StorePermissionRequest;
use App\Http\Requests\StoreChequeRequest;
use App\Http\Requests\StorePurchaseRequest;

// Evento (nuevo)
use App\Events\RequestCreated as EvRequestCreated;

class RequestFormController extends Controller
{
    public function index(Request $req)
    {
        $user = $req->user();
        $this->authorize('viewAny', RequestForm::class);

        // Bandeja base por rol
        if ($user->hasAnyRole(['Administrador', 'Rector'])) {
            $q = RequestForm::query()->latest();
        } elseif ($user->hasRole('Compras')) {
            $q = RequestForm::query()->where('type', 'compra');
        } elseif ($user->hasRole('Contabilidad')) {
            $q = RequestForm::query()->where('type', 'cheque');
        } elseif ($user->hasRole('Encargado de departamento')) {
            $q = RequestForm::query()->where('department_id', $user->department_id);
        } else {
            $q = RequestForm::query()->where('user_id', $user->id);
        }

        // Pendientes por aprobar (scope en el modelo)
        if ($req->get('filter') === 'pendientes' && $this->isApprover($user)) {
            $q = RequestForm::query()->pendingForApprover($user);
        }

        if ($status = $req->get('status')) {
            $q->where('status', $status);
        }

        if ($type = $req->get('type')) {
            $q->where('type', $type);
        }

        $requests = $q->with(['user', 'department'])->latest()->paginate(20);
        return view('requests.index', compact('requests'));
    }

    private function isApprover($user): bool
    {
        return $user->hasAnyRole(['Rector', 'Compras', 'Contabilidad', 'Encargado de departamento']);
    }

    public function create(string $type)
    {
        abort_unless(in_array($type, ['permiso', 'cheque', 'compra']), 404);
        $this->authorize('create', RequestForm::class);
        return view("requests.create-$type");
    }

    public function store(Request $req)
    {
        $this->authorize('create', RequestForm::class);
        $type = $req->input('type');
        abort_unless(in_array($type, ['permiso', 'cheque', 'compra']), 422);

        return DB::transaction(function () use ($req, $type) {
            $user = $req->user();

            $rf = RequestForm::create([
                'type'          => $type,
                'user_id'       => $user->id,
                'department_id' => $user->department_id,
                'status'        => 'en_revision',
                'current_level' => 1,
                'submitted_at'  => Carbon::now(),
            ]);

            // Detalles + siembra de niveles
            if ($type === 'permiso') {
                $val = app(StorePermissionRequest::class)->validated();
                PermissionDetail::create(['request_form_id' => $rf->id] + $val);
                $this->seedLevels($rf, [
                    ['level' => 1, 'role' => 'Encargado'],
                    ['level' => 2, 'role' => 'Rector'],
                ]);
            } elseif ($type === 'cheque') {
                $val = app(StoreChequeRequest::class)->validated();
                ChequeDetail::create(['request_form_id' => $rf->id] + $val);
                $this->seedLevels($rf, [
                    ['level' => 1, 'role' => 'Contabilidad'],
                    ['level' => 2, 'role' => 'Rector'],
                ]);
            } else { // compra
                $val = app(StorePurchaseRequest::class)->validated();
                $detail = PurchaseDetail::create([
                    'request_form_id' => $rf->id,
                    'justification'   => $val['justification'],
                    'urls'            => $val['urls'] ?? [],
                ]);
                foreach ($val['items'] as $it) {
                    PurchaseItem::create(['purchase_detail_id' => $detail->id] + $it);
                }
                $this->seedLevels($rf, [
                    ['level' => 1, 'role' => 'Compras'],
                    ['level' => 2, 'role' => 'Rector'],
                ]);
            }

            // 🚀 Notificación vía evento (listeners decidirán destinatarios)
            event(new EvRequestCreated($rf));

            return redirect()->route('requests.show', $rf)->with('ok', 'Solicitud creada');
        });
    }

    public function show(RequestForm $requestForm)
    {
        $this->authorize('view', $requestForm);
        $requestForm->load(['approvals', 'permiso', 'cheque', 'compra.items', 'user', 'department']);
        return view('requests.show', compact('requestForm'));
    }

    private function seedLevels(RequestForm $rf, array $levels): void
    {
        foreach ($levels as $l) {
            RequestApproval::create([
                'request_form_id' => $rf->id,
                'level'           => $l['level'],
                'role'            => $l['role'],
                'state'           => 'pendiente',
            ]);
        }
    }
}
