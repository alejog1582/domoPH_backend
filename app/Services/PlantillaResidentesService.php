<?php

namespace App\Services;

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;

class PlantillaResidentesService
{
    /**
     * Crear el archivo Excel de plantilla para residentes
     *
     * @param string $rutaCompleta Ruta completa donde se guardará el archivo
     * @return bool
     */
    public static function crearPlantilla($rutaCompleta)
    {
        try {
            $spreadsheet = new Spreadsheet();
            $sheet = $spreadsheet->getActiveSheet();
            
            // Encabezados
            $headers = [
                'numero_unidad',
                'torre_unidad',
                'bloque_unidad',
                'nombre',
                'email',
                'documento',
                'telefono',
                'tipo_relacion',
                'fecha_inicio',
                'fecha_fin',
                'es_principal',
                'recibe_notificaciones',
                'observaciones'
            ];
            
            // Estilo para encabezados
            $headerStyle = [
                'font' => [
                    'bold' => true,
                    'color' => ['rgb' => 'FFFFFF'],
                ],
                'fill' => [
                    'fillType' => Fill::FILL_SOLID,
                    'startColor' => ['rgb' => '4472C4'],
                ],
                'alignment' => [
                    'horizontal' => Alignment::HORIZONTAL_CENTER,
                    'vertical' => Alignment::VERTICAL_CENTER,
                ],
                'borders' => [
                    'allBorders' => [
                        'borderStyle' => Border::BORDER_THIN,
                    ],
                ],
            ];
            
            // Escribir encabezados
            $col = 'A';
            foreach ($headers as $header) {
                $sheet->setCellValue($col . '1', $header);
                $sheet->getStyle($col . '1')->applyFromArray($headerStyle);
                $sheet->getColumnDimension($col)->setAutoSize(true);
                $col++;
            }
            
            // Datos de ejemplo
            $ejemplos = [
                ['101', 'Torre A', 'Bloque 1', 'Juan Pérez', 'juan.perez@email.com', '1234567890', '3001234567', 'propietario', '2024-01-01', '', '1', '1', 'Residente principal'],
                ['101', 'Torre A', 'Bloque 1', 'María Pérez', 'maria.perez@email.com', '0987654321', '3007654321', 'propietario', '2024-01-01', '', '0', '1', 'Cónyuge'],
                ['102', 'Torre A', 'Bloque 1', 'Carlos García', 'carlos.garcia@email.com', '1122334455', '3001122334', 'arrendatario', '2024-03-01', '2025-02-28', '1', '1', ''],
            ];
            
            // Estilo para datos
            $dataStyle = [
                'borders' => [
                    'allBorders' => [
                        'borderStyle' => Border::BORDER_THIN,
                    ],
                ],
                'alignment' => [
                    'vertical' => Alignment::VERTICAL_CENTER,
                ],
            ];
            
            // Escribir datos de ejemplo
            $row = 2;
            foreach ($ejemplos as $ejemplo) {
                $col = 'A';
                foreach ($ejemplo as $valor) {
                    $sheet->setCellValue($col . $row, $valor);
                    $sheet->getStyle($col . $row)->applyFromArray($dataStyle);
                    $col++;
                }
                $row++;
            }
            
            // Ajustar altura de filas
            $sheet->getRowDimension(1)->setRowHeight(25);
            
            // Crear el directorio si no existe
            $directorio = dirname($rutaCompleta);
            if (!is_dir($directorio)) {
                mkdir($directorio, 0755, true);
            }
            
            // Guardar el archivo
            $writer = new Xlsx($spreadsheet);
            $writer->save($rutaCompleta);
            
            return true;
        } catch (\Exception $e) {
            \Log::error('Error al crear plantilla de residentes: ' . $e->getMessage());
            return false;
        }
    }
}
