<?php

namespace App\Services;

use App\Models\Unidad;
use App\Helpers\AdminHelper;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;

class PlantillaCarteraService
{
    /**
     * Crear el archivo Excel de plantilla para cartera con todas las unidades
     *
     * @param string $rutaCompleta Ruta completa donde se guardará el archivo
     * @return bool
     */
    public static function crearPlantilla($rutaCompleta)
    {
        try {
            $propiedad = AdminHelper::getPropiedadActiva();
            
            if (!$propiedad) {
                return false;
            }

            $spreadsheet = new Spreadsheet();
            $sheet = $spreadsheet->getActiveSheet();
            
            // Encabezados
            $headers = [
                'numero',
                'torre',
                'bloque',
                'saldo_corriente',
                'saldo_mora',
                'saldo_total'
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
            
            // Obtener todas las unidades de la propiedad
            $unidades = Unidad::where('propiedad_id', $propiedad->id)
                ->orderBy('torre')
                ->orderBy('bloque')
                ->orderBy('numero')
                ->get();
            
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
            
            // Estilo para columnas numéricas (saldos)
            $numberStyle = [
                'borders' => [
                    'allBorders' => [
                        'borderStyle' => Border::BORDER_THIN,
                    ],
                ],
                'alignment' => [
                    'horizontal' => Alignment::HORIZONTAL_RIGHT,
                    'vertical' => Alignment::VERTICAL_CENTER,
                ],
                'numberFormat' => [
                    'formatCode' => '#,##0.00',
                ],
            ];
            
            // Escribir datos de todas las unidades con saldos en cero
            $row = 2;
            foreach ($unidades as $unidad) {
                $sheet->setCellValue('A' . $row, $unidad->numero);
                $sheet->getStyle('A' . $row)->applyFromArray($dataStyle);
                
                $sheet->setCellValue('B' . $row, $unidad->torre ?? '');
                $sheet->getStyle('B' . $row)->applyFromArray($dataStyle);
                
                $sheet->setCellValue('C' . $row, $unidad->bloque ?? '');
                $sheet->getStyle('C' . $row)->applyFromArray($dataStyle);
                
                // Saldos en cero por defecto
                $sheet->setCellValue('D' . $row, 0);
                $sheet->getStyle('D' . $row)->applyFromArray($numberStyle);
                
                $sheet->setCellValue('E' . $row, 0);
                $sheet->getStyle('E' . $row)->applyFromArray($numberStyle);
                
                $sheet->setCellValue('F' . $row, 0);
                $sheet->getStyle('F' . $row)->applyFromArray($numberStyle);
                
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
            \Log::error('Error al crear plantilla de cartera: ' . $e->getMessage());
            return false;
        }
    }
}
