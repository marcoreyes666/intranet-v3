<?php

// app/Services/Documents/PermisoPdfBuilder.php
namespace App\Services\Documents;

use App\Models\Request as Rq;
use setasign\Fpdi\Fpdi;
use Illuminate\Support\Facades\Storage;
use App\Models\RequestDocument;

class PermisoPdfBuilder {
  public function build(Rq $rq): string {
    // Ruta a tu plantilla PDF subida (almácenala en storage/app/templates/)
    $templatePath = storage_path('app/templates/solicitud_permisos.pdf');
    // Sugerido: guarda la que subiste como "solicitud_permisos.pdf"

    $pdf = new Fpdi();
    $pdf->AddPage();
    $pdf->setSourceFile($templatePath);
    $tpl = $pdf->importPage(1);
    $pdf->useTemplate($tpl, 0, 0, 210);

    $pdf->SetFont('Helvetica','',11);

    // Mapear campos -> coordenadas (ejemplo)
    // Ajusta X/Y según tu PDF
    $data = $rq->payload; // nombre, depto, folio, tipo, fecha, hora, motivo, etc.
    $map = [
      ['key'=>'nombre',   'x'=>30,  'y'=>40],
      ['key'=>'departamento','x'=>30,'y'=>48],
      ['key'=>'folio',    'x'=>170, 'y'=>20],
      ['key'=>'tipo',     'x'=>30,  'y'=>56],
      ['key'=>'fecha',    'x'=>30,  'y'=>64],
      ['key'=>'hora',     'x'=>80,  'y'=>64],
      ['key'=>'motivo',   'x'=>30,  'y'=>85],
    ];

    foreach ($map as $m) {
      $pdf->SetXY($m['x'], $m['y']);
      $pdf->Write(5, (string)($data[$m['key']] ?? ''));
    }

    $outDir = "requests/{$rq->id}";
    $outPath = "{$outDir}/permiso_{$rq->id}.pdf";
    Storage::makeDirectory($outDir);
    $fileAbs = storage_path("app/{$outPath}");
    $pdf->Output($fileAbs, 'F');

    RequestDocument::create([
      'request_id'=>$rq->id,
      'doc_type'=>'pdf',
      'template'=>'solicitud_permisos.pdf',
      'path'=>$outPath,
    ]);

    return $outPath;
  }
}
