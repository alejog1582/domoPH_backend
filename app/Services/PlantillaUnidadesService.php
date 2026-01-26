<?php

namespace App\Services;

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;

class PlantillaUnidadesService
{
    /**
     * Crear el archivo Excel de plantilla para unidades
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
                'numero',
                'torre',
                'bloque',
                'tipo',
                'area_m2',
                'coeficiente',
                'habitaciones',
                'banos',
                'estado',
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
                ['101', 'Torre A', 'Bloque 1', 'apartamento', '85.50', '100', '2', '2', 'ocupada', 'Unidad en buen estado'],
                ['102', 'Torre A', 'Bloque 1', 'apartamento', '90.00', '105', '3', '2', 'desocupada', ''],
                ['201', 'Torre B', 'Bloque 2', 'apartamento', '75.30', '95', '2', '1', 'ocupada', 'Requiere mantenimiento'],
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
            \Log::error('Error al crear plantilla de unidades: ' . $e->getMessage());
            return false;
        }
    }
}
