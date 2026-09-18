<?php

namespace App\Http\Exports;

use App\Invoice;
use Carbon\Carbon;
use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\FromView;
use App\Http\Controllers\Reportes\ReporteMiscPieceRateReportController;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use Maatwebsite\Excel\Concerns\WithBackgroundColor;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Events\AfterSheet;

class MiscPieceRateReportExcel implements
    FromView,
    WithBackgroundColor,
    WithStyles,
    ShouldAutoSize
{
    protected $employees;
    protected $initial_date;
    protected $final_date;
    protected $farms;
    // protected $crops;

    public function __construct(
        $employees,
        $initial_date,
        $final_date,
        $farms
        // $crops
    ){
        $this->employees = $employees;
        $this->initial_date = $initial_date;
        $this->final_date = $final_date;
        $this->farms = $farms;
        // $this->crops = $crops;
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
            // Estilo para A3:T3: color de texto y borde grueso en la parte inferior
            'A3:T3' => [
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
        $controller = new ReporteMiscPieceRateReportController();
        return view('admin.reportes.exports.misc_piece_rate_report_excel', [
            'data' => $controller->generarMiscPieceRateReportData(
                $ids = collect($this->employees)->pluck('id')->toArray(),
                $this->initial_date,
                $this->final_date,
                $this->farms
                // $this->crops
            ),
            'initial_date' => Carbon::createFromFormat('m-d-Y', $this->initial_date)->format('l, F j, Y'),
            'final_date' => Carbon::createFromFormat('m-d-Y', $this->final_date)->format('l, F j, Y'),
            'today' => Carbon::now()->format('l, F j, Y')
        ]);
    }
}
