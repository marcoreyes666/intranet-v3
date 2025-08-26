<?php

namespace App\Http\Controllers;

use App\Models\Solicitud;
use Illuminate\Support\Facades\Storage;

class FileDownloadController extends Controller
{
    public function __construct() { $this->middleware(['auth']); }

    public function download(Solicitud $solicitud)
    {
        $this->authorize('download', $solicitud);
        $path = $solicitud->final_file_path;
        abort_unless($path && Storage::exists($path), 404);
        return response()->download(storage_path("app/{$path}"));
    }
}
