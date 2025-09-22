<?php

// app/Http/Controllers/Requests/PurchaseCompletionController.php
namespace App\Http\Controllers\Requests;

use App\Http\Controllers\Controller;
use App\Models\RequestForm;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class PurchaseCompletionController extends Controller
{
    public function complete(Request $req, RequestForm $requestForm)
    {
        $this->authorize('complete', $requestForm);
        abort_unless($requestForm->type === 'compra', 422);

        return DB::transaction(function () use ($req, $requestForm) {
            $detail = $requestForm->compra()->firstOrFail();
            $detail->update([
                'delivered_at' => Carbon::now(),
                'completed_by' => $req->user()->id,
            ]);
            $requestForm->update(['status' => 'completada']);
            return back()->with('ok','Compra marcada como completada');
        });
    }
}
