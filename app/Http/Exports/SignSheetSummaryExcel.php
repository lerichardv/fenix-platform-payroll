<?php

namespace App\Http\Exports;

use Carbon\Carbon;
use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\FromView;
use App\Http\Controllers\Reportes\ReporteSingSheetsController;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use Maatwebsite\Excel\Concerns\WithBackgroundColor;
use Maatwebsite\Excel\Concerns\WithStyles;

class SignSheetSummaryExcel implements
    FromView,
    WithBackgroundColor,
    WithStyles,
    ShouldAutoSize
{
    public $week;
    public $farms;
    public $locations;
    public $empleadosSeleccionados;
    public $categorias_empleados;

    public function __construct(
        $week,
        $farms,
        $locations,
        $empleadosSeleccionados,
        $categorias_empleados
    ) {
        $this->week = $week;
        $this->farms = $farms;
        $this->locations = $locations;
        $this->empleadosSeleccionados = $empleadosSeleccionados;
        $this->categorias_empleados = $categorias_empleados;
    }

    public function styles(Worksheet $worksheet)
    {
        return [
            // Estilo para A1: color y tamaño de fuente
            'A1' => [
                'font' => [
                    'color' => ['argb' => '13274F'], // Color #13274F
                    'size'  => 16, // Tamaño de fuente 16px
                ],
            ],
            // Estilo para C1: alineación a la derecha
            'C1' => [
                'alignment' => [
                    'horizontal' => Alignment::HORIZONTAL_RIGHT, // Alineación a la derecha
                ],
            ],
            'B3:I3' => [
                'alignment' => [
                    // 'horizontal' => Alignment::HORIZONTAL_CENTER, // Alineación horizontal centrada
                    'vertical' => Alignment::VERTICAL_CENTER, // Alineación vertical centrada
                ],
                'fill' => [
                    'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                    'color' => ['argb' => 'FDE9D9'], // Color de fondo amarillo
                ],
            ],
            // Estilo para A3:K3: color de texto y borde grueso en la parte inferior
            'A4:K4' => [
                'font' => [
                    'color' => ['argb' => '13274F'], // Color de texto #13274F
                ],
                'borders' => [
                    'bottom' => [
                        'borderStyle' => Border::BORDER_THICK,
                        'color' => ['argb' => '000000'], // Color negro en formato ARGB
                    ],
                ],
            ],
            'C4' => [
                'alignment' => [
                    'horizontal' => Alignment::HORIZONTAL_RIGHT, // Alineación a la derecha
                ],
            ],
        ];
    }

    public function backgroundColor()
    {
        return 'ffffff';
    }

    public function view(): View
    {
        $controller = new ReporteSingSheetsController();
        $days = $controller->getDaysArray($this->week);
        $initial_date = Carbon::createFromFormat('Y-m-d', $days[0])->startOfDay()->format('Y-m-d H:i:s');
        $final_date = Carbon::createFromFormat('Y-m-d', end($days))->endOfDay()->format('Y-m-d H:i:s');

        $dataReporte =  $controller->generarSignSheetReportData(
            $days,
            $this->farms,
            $this->locations,
            $this->empleadosSeleccionados,
            $this->categorias_empleados,
            true
        );

        $dataReporteArray = $dataReporte->toArray();
        usort($dataReporteArray, function ($a, $b) {
            return strcmp($a->full_name, $b->full_name);
        });
        $mergedData = [];

        // Recorre el arreglo y suma los valores de los empleados que se repiten y unificar los datos por nombre
        foreach ($dataReporteArray as $item) {



            if (isset($mergedData[$item->cod_usuario])) {

                if (!isset($mergedData[$item->cod_usuario]->granjas)) {
                    $mergedData[$item->cod_usuario]->granjas = [];
                }

                //Verifica si la granja ya fue agregada al arreglo de granjas para no repetir la suma de los valores
                if ($item->granja && !in_array($item->granja . $item->cod_usuario . $item->locacion, $mergedData[$item->cod_usuario]->granjas)) {
                    $mergedData[$item->cod_usuario]->granjas[] = $item->granja . $item->cod_usuario . $item->locacion;
                    $mergedData[$item->cod_usuario]->dia1 += $item->dia1;
                    $mergedData[$item->cod_usuario]->dia2 += $item->dia2;
                    $mergedData[$item->cod_usuario]->dia3 += $item->dia3;
                    $mergedData[$item->cod_usuario]->dia4 += $item->dia4;
                    $mergedData[$item->cod_usuario]->dia5 += $item->dia5;
                    $mergedData[$item->cod_usuario]->dia6 += $item->dia6;
                    $mergedData[$item->cod_usuario]->dia7 += $item->dia7;
                    $mergedData[$item->cod_usuario]->total += $item->total;
                }
            } else {
                $mergedData[$item->cod_usuario] = $item;
                $mergedData[$item->cod_usuario]->granjas = [];
                $mergedData[$item->cod_usuario]->granjas[] = $item->granja . $item->cod_usuario . $item->locacion;

            }
        }
        $dataReporteArray = array_values($mergedData);

        // foreach ($dataReporte as $key => $value) {
        //     // Aquí puedes realizar las operaciones necesarias con cada elemento del array
        // }

        return view('admin.reportes.exports.sign_sheets_summary_export', [
            // 'data' => $controller->generarSignSheetReportData(
            //     $days,
            //     $this->farms,
            //     $this->locations,
            //     $this->empleadosSeleccionados,
            //     $this->categorias_empleados,
            //     false
            'data' => $dataReporteArray,
            // 'data' => $dataReporte,
            'initial_date' => Carbon::createFromFormat('Y-m-d H:i:s', $initial_date)->format('l, F j, Y'),
            'final_date' => Carbon::createFromFormat('Y-m-d H:i:s', $final_date)->format('l, F j, Y'),
            'today' => Carbon::now()->format('l, F j, Y')
        ]);
    }
}
