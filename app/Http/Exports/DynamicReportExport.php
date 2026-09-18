<?php

namespace App\Http\Exports;

use App\Invoice;
use Carbon\Carbon;
use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\FromView;
use App\Http\Controllers\Reportes\ReporteDynamicReportController;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use Maatwebsite\Excel\Concerns\WithBackgroundColor;
use Maatwebsite\Excel\Concerns\WithStyles;

class DynamicReportExport implements
    FromView,
    ShouldAutoSize,
    WithBackgroundColor,
    WithStyles
{
    protected $fecha_inicial;
    protected $fecha_final;
    protected $cod_granja;
    protected $cod_location;
    protected $cod_categories;
    protected $empleados;
    protected $columns;
    protected $group_by;

    public function __construct(
        $fecha_inicial,
        $fecha_final,
        $cod_granja,
        $cod_location,
        $cod_categories,
        $empleados,
        $columns,
        $group_by
    ){
        $this->fecha_inicial = $fecha_inicial;
        $this->fecha_final = $fecha_final;
        $this->cod_granja = $cod_granja;
        $this->cod_location = $cod_location;
        $this->cod_categories = $cod_categories;
        $this->empleados = $empleados;
        $this->columns = $columns;
        $this->group_by = $group_by;
    }

    public function styles(Worksheet $worksheet){
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
            // Estilo para A3:C3: color de texto y borde grueso en la parte inferior
            // 'A3:C3' => [
            //     'font' => [
            //         'color' => ['argb' => '13274F'], // Color de texto #13274F
            //     ],
            //     'borders' => [
            //         'bottom' => [
            //             'borderStyle' => Border::BORDER_THICK,
            //             'color' => ['argb' => '000000'], // Color negro en formato ARGB
            //         ],
            //     ],
            // ],
            // 'C3' => [
            //     'alignment' => [
            //         'horizontal' => Alignment::HORIZONTAL_RIGHT, // Alineación a la derecha
            //     ],
            // ],
        ];
    }

    public function backgroundColor(){
        return 'ffffff';
    }

    public function view(): View
    {
        $controller = new ReporteDynamicReportController();
        return view('admin.reportes.exports.dynamic_report_export', [
            'data' => $controller->generarDynamicReportData(
                $this->fecha_inicial,
                $this->fecha_final,
                $this->cod_granja,
                $this->cod_location,
                $this->cod_categories,
                $controller->pluckIdEmpleados($this->empleados),
                $this->columns,
                $this->group_by
            ),
            'initial_date' => Carbon::createFromFormat('m-d-Y', $this->fecha_inicial)->format('l, F j, Y'),
            'final_date' => Carbon::createFromFormat('m-d-Y', $this->fecha_final)->format('l, F j, Y'),
            'today' => Carbon::now()->format('l, F j, Y')
        ]);
    }
}
