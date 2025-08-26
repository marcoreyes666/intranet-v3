<?php 

// app/Services/Documents/ExcelBuilder.php
namespace App\Services\Documents;

use App\Models\Request as Rq;
use PhpOffice\PhpSpreadsheet\IOFactory;
use Illuminate\Support\Facades\Storage;
use App\Models\RequestDocument;

class ExcelBuilder {
  public function build(Rq $rq): string {
    // Elige plantilla según tipo
    $template = $rq->type === 'cheque'
      ? storage_path('app/templates/solicitud_cheque.xlsx')
      : storage_path('app/templates/solicitud_compra.xlsx');

    $spreadsheet = IOFactory::load($template);
    $sheet = $spreadsheet->getActiveSheet();

    $data = $rq->payload;

    // Mapea tus celdas reales: (ejemplos)
    if ($rq->type === 'cheque') {
      $sheet->setCellValue('B3', $data['nombre'] ?? '');
      $sheet->setCellValue('E3', $data['folio'] ?? '');
      $sheet->setCellValue('B5', $data['concepto'] ?? '');
      $sheet->setCellValue('E5', $data['monto'] ?? '');
      $sheet->setCellValue('B7', $data['departamento'] ?? '');
      $sheet->setCellValue('E7', $data['fecha'] ?? '');
    } else { // compra
      $sheet->setCellValue('B3', $data['solicitante'] ?? '');
      $sheet->setCellValue('E3', $data['folio'] ?? '');
      $sheet->setCellValue('B5', $data['proveedor'] ?? '');
      $sheet->setCellValue('E5', $data['total'] ?? '');
      $sheet->setCellValue('B7', $data['justificacion'] ?? '');
      $sheet->setCellValue('E7', $data['fecha'] ?? '');
      // Si hay partidas, escribe filas a partir de la 10, etc.
    }

    $outDir = "requests/{$rq->id}";
    Storage::makeDirectory($outDir);
    $outPath = "{$outDir}/{$rq->type}_{$rq->id}.xlsx";
    $writer = IOFactory::createWriter($spreadsheet, 'Xlsx');
    $writer->save(storage_path("app/{$outPath}"));

    RequestDocument::create([
      'request_id'=>$rq->id,
      'doc_type'=>'xlsx',
      'template'=> $rq->type === 'cheque' ? 'solicitud_cheque.xlsx' : 'solicitud_compra.xlsx',
      'path'=>$outPath,
    ]);

    return $outPath;
  }
}
