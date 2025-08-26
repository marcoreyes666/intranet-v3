<?php

namespace App\Http\Controllers;

use App\Models\Request as Rq;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class DocumentController extends Controller
{
    public function download(Rq $rq)
    {
        // Control de visibilidad mínimo (igual que en show)
        $user = Auth::user();
        if (! $this->puedeVer($user, $rq)) {
            abort(403);
        }

        $doc = $rq->documents()->latest()->firstOrFail();
        return Storage::download($doc->path);
    }

    private function puedeVer($user, Rq $rq): bool
    {
        if ($rq->user_id === $user->id) return true;

        if ($rq->type === 'permiso' && $user->hasRole(['Encargado de departamento','Rector'])) return true;
        if ($rq->type === 'cheque'  && $user->hasRole(['Contabilidad','Rector'])) return true;
        if ($rq->type === 'compra'  && $user->hasRole(['Compras','Rector'])) return true;

        return $rq->approvals()
            ->where('approver_id',$user->id)
            ->exists();
    }
}
