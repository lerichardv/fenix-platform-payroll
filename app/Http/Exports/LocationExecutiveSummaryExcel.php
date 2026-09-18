<?php

namespace App\Http\Exports;

use App\Models\User;
use Maatwebsite\Excel\Concerns\FromCollection;
use Carbon\Carbon;
use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\FromView;
use App\Http\Controllers\Reportes\ReporteLocationExecutiveSummaryController;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use Maatwebsite\Excel\Concerns\WithBackgroundColor;
use Maatwebsite\Excel\Concerns\WithStyles;

class LocationExecutiveSummaryExcel implements
    FromView,
    WithBackgroundColor,
    WithStyles,
    ShouldAutoSize
{
    public $week;
    public $farms;
    public $locations;

    public function __construct(
        $week,
        $farms,
        $locations
    ){
        $this->week = $week;
        $this->farms = $farms;
        $this->locations = $locations;
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
            'A3:I3' => [
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
        $controller = new ReporteLocationExecutiveSummaryController();
        $days = $controller->getDaysArray($this->week);
        $initial_date = Carbon::createFromFormat('Y-m-d', $days[0])->startOfDay()->format('Y-m-d H:i:s');
        $final_date = Carbon::createFromFormat('Y-m-d', end($days))->endOfDay()->format('Y-m-d H:i:s');
        return view('admin.reportes.exports.location_executive_summary_export', [
            'data' => $controller->generarLocationExecutiveReporteData(
                $days,
                $this->farms,
                $this->locations
            ),
            'initial_date' => Carbon::createFromFormat('Y-m-d H:i:s', $initial_date)->format('l, F j, Y'),
            'final_date' => Carbon::createFromFormat('Y-m-d H:i:s', $final_date)->format('l, F j, Y'),
            'today' => Carbon::now()->format('l, F j, Y')
        ]);
    }
}
