<?php

namespace App\Services;

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;

class PlantillaMascotasService
{
    /**
     * Crear el archivo Excel de plantilla para mascotas
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
                'identificacion_residente',
                'nombre',
                'tipo',
                'raza',
                'color',
                'sexo',
                'fecha_nacimiento',
                'edad_aproximada',
                'peso_kg',
                'tamanio',
                'numero_chip',
                'vacunado',
                'esterilizado',
                'estado_salud',
                'fecha_vigencia_vacunas',
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
                ['101', 'Torre A', 'Bloque 1', '1234567890', 'Max', 'perro', 'Labrador', 'Dorado', 'macho', '2020-03-15', '', '28.50', 'grande', '1234567890', '1', '1', 'saludable', '2025-12-31', 'Mascota muy amigable'],
                ['101', 'Torre A', 'Bloque 1', '1234567890', 'Luna', 'gato', 'Persa', 'Blanco', 'hembra', '', '36', '4.20', 'pequeño', '0987654321', '1', '1', 'saludable', '2025-06-15', 'Gata tranquila'],
                ['102', 'Torre A', 'Bloque 1', '', 'Rocky', 'perro', 'Bulldog Francés', 'Atigrado', 'macho', '2022-08-20', '', '12.30', 'mediano', '', '1', '0', 'saludable', '2025-03-20', ''],
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
            \Log::error('Error al crear plantilla de mascotas: ' . $e->getMessage());
            return false;
        }
    }
}
