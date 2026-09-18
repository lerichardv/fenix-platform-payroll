<?php

namespace App\Http\Exports;

use App\Invoice;
use Carbon\Carbon;
use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\FromView;
use App\Http\Controllers\Reportes\ReporteEstimatedPayrollController;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use Maatwebsite\Excel\Concerns\WithBackgroundColor;
use Maatwebsite\Excel\Concerns\WithStyles;

class EstimatedPayrollExport implements
    FromView,
    ShouldAutoSize,
    WithBackgroundColor,
    WithStyles
{
    protected $initial_date;
    protected $final_date;
    protected $cod_farm;
    protected $cod_location;
    protected $employees;

    public function __construct(
        $initial_date,
        $final_date,
        $cod_farm,
        $cod_location,
        $employees
    ){
        $this->initial_date = $initial_date;
        $this->final_date = $final_date;
        $this->cod_farm = $cod_farm;
        $this->cod_location = $cod_location;
        $this->employees = $employees;
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
            'A3:F3' => [
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
            'C3' => [
                'alignment' => [
                    'horizontal' => Alignment::HORIZONTAL_RIGHT, // Alineación a la derecha
                ],
            ],
        ];
    }

    public function backgroundColor(){
        return 'ffffff';
    }

    public function view(): View
    {
        $controller = new ReporteEstimatedPayrollController();
        return view('admin.reportes.exports.estimated_payroll_export', [
            'usuarios' => $controller->generarReporte(
                $this->initial_date,
                $this->final_date,
                $this->cod_farm,
                $this->cod_location,
                $this->employees
            ),
            'initial_date' => Carbon::createFromFormat('m-d-Y', $this->initial_date)->format('l, F j, Y'),
            'final_date' => Carbon::createFromFormat('m-d-Y', $this->final_date)->format('l, F j, Y'),
            'today' => Carbon::now()->format('l, F j, Y')
        ]);
    }
}
