<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Correspondencia;
use App\Helpers\AdminHelper;
use Illuminate\Http\Request;
use Carbon\Carbon;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

class CorrespondenciaController extends Controller
{
    /**
     * Mostrar la lista de correspondencias
     */
    public function index(Request $request)
    {
        $propiedad = AdminHelper::getPropiedadActiva();
        
        if (!$propiedad) {
            return redirect()->route('admin.dashboard')
                ->with('error', 'No hay propiedad asignada.');
        }

        $mesActual = Carbon::now()->format('Y-m');
        
        // Query base: correspondencias con sus relaciones
        $query = Correspondencia::with(['unidad', 'residente'])
            ->where('copropiedad_id', $propiedad->id)
            ->where('activo', true);

        // Filtro por fecha (por defecto: mes actual)
        if ($request->filled('fecha_desde')) {
            $query->whereDate('fecha_recepcion', '>=', $request->fecha_desde);
        } else {
            // Por defecto: mes actual
            $query->whereYear('fecha_recepcion', Carbon::now()->year)
                  ->whereMonth('fecha_recepcion', Carbon::now()->month);
        }

        if ($request->filled('fecha_hasta')) {
            $query->whereDate('fecha_recepcion', '<=', $request->fecha_hasta);
        }

        // Filtro por estado
        if ($request->filled('estado')) {
            $query->where('estado', $request->estado);
        }

        // Filtro por tipo
        if ($request->filled('tipo')) {
            $query->where('tipo', $request->tipo);
        }

        // Filtro por unidad
        if ($request->filled('unidad_id')) {
            $query->where('unidad_id', $request->unidad_id);
        }

        // Filtro por búsqueda de unidad
        if ($request->filled('buscar_unidad')) {
            $buscar = $request->buscar_unidad;
            $query->whereHas('unidad', function($q) use ($buscar) {
                $q->where('numero', 'like', "%{$buscar}%")
                  ->orWhere('torre', 'like', "%{$buscar}%")
                  ->orWhere('bloque', 'like', "%{$buscar}%");
            });
        }

        // Ordenar por fecha de recepción descendente
        $correspondencias = $query->orderBy('fecha_recepcion', 'desc')
            ->orderBy('id', 'desc')
            ->paginate(15)
            ->appends($request->query());

        // Obtener unidades para el filtro
        $unidades = \App\Models\Unidad::where('propiedad_id', $propiedad->id)
            ->orderBy('numero')
            ->get(['id', 'numero', 'torre', 'bloque']);

        return view('admin.correspondencias.index', compact(
            'correspondencias', 
            'propiedad', 
            'unidades',
            'mesActual'
        ));
    }

    /**
     * Mostrar la vista de carga de correspondencias con instrucciones
     */
    public function showCargarCorrespondencias()
    {
        $propiedad = AdminHelper::getPropiedadActiva();
        
        if (!$propiedad) {
            return redirect()->route('admin.dashboard')
                ->with('error', 'No hay propiedad asignada.');
        }

        return view('admin.correspondencias.cargar', compact('propiedad'));
    }

    /**
     * Descargar plantilla para carga de correspondencias
     */
    public function downloadTemplate()
    {
        $propiedad = AdminHelper::getPropiedadActiva();
        
        if (!$propiedad) {
            return redirect()->route('admin.dashboard')
                ->with('error', 'No hay propiedad asignada.');
        }

        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();

        // Encabezados
        $headers = [
            'numero_unidad',
            'torre_unidad',
            'bloque_unidad',
            'tipo',
            'descripcion',
            'remitente',
            'numero_guia',
            'fecha_recepcion',
            'estado'
        ];

        $sheet->fromArray($headers, null, 'A1');

        // Estilo para encabezados
        $sheet->getStyle('A1:I1')->getFont()->setBold(true);
        $sheet->getStyle('A1:I1')->getFill()
            ->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)
            ->getStartColor()->setARGB('FF4472C4');
        $sheet->getStyle('A1:I1')->getFont()->getColor()->setARGB('FFFFFFFF');

        // Ajustar ancho de columnas
        foreach (range('A', 'I') as $col) {
            $sheet->getColumnDimension($col)->setAutoSize(true);
        }

        // Ejemplo de datos
        $sheet->setCellValue('A2', '101');
        $sheet->setCellValue('B2', '1');
        $sheet->setCellValue('C2', 'A');
        $sheet->setCellValue('D2', 'paquete');
        $sheet->setCellValue('E2', 'Paquete de envío');
        $sheet->setCellValue('F2', 'Empresa XYZ');
        $sheet->setCellValue('G2', 'GUIA-123456');
        $sheet->setCellValue('H2', Carbon::now()->format('Y-m-d H:i:s'));
        $sheet->setCellValue('I2', 'recibido');

        $sheet->getStyle('A2:I2')->getFont()->getColor()->setARGB('FF808080');

        $filename = 'plantilla_correspondencias_' . date('Y-m-d') . '.xlsx';
        
        $writer = new Xlsx($spreadsheet);
        $response = response()->streamDownload(function() use ($writer) {
            $writer->save('php://output');
        }, $filename);

        $response->headers->set('Content-Type', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        
        return $response;
    }
}
